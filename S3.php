<?php

class S3 {

    const DEFAULT_ENDPOINT = 's3.amazonaws.com';

    private $access_key;
    private $secret_key;

    private $endpoint;

    public function __construct($access_key, $secret_key, $endpoint = self::DEFAULT_ENDPOINT) {
        $this->access_key = $access_key;
        $this->secret_key = $secret_key;
        $this->endpoint = $endpoint;
    }

    public function putObject($bucket, $uri, $file, S3Headers $headers = null) {
        if ($headers == null) {
            $headers = new S3Headers();
        }

        $headers->withContentLength(strlen($file))
                ->withContentMD5(base64_encode(md5($file, true)))
                ->withHost($this->endpoint)
                ->withDate(gmdate('D, d M Y H:i:s T'));

        $uri = "/$bucket/$uri";
        $response = S3Request::put($this->endpoint, $uri, $file)
            ->withS3Headers($headers)
            ->sign($this->access_key, $this->secret_key)
            ->execute();

        return $response;
    }
}

class S3Headers {

    public $headers;

    public function __construct() {
        $this->headers = array();
    }

    public function withContentType($type) {
        $this->headers['Content-Type'] = $type;
        return $this;
    }

    public function withContentLength($size) {
        $this->headers['Content-Length'] = (int)$size;
        return $this;
    }

    public function withContentMD5($md5) {
        $this->headers['Content-MD5'] = $md5;
        return $this;
    }

    public function withHost($host) {
        $this->headers['Host'] = $host;
        return $this;
    }

    public function withDate($date) {
        $this->headers['Date'] = $date;
        return $this;
    }

    public function setAuthorization($authorization) {
        $this->headers['Authorization'] = $authorization;
        return $this;
    }

    /* Accessors */
    public function getCurlHeaders() {
        $curl_headers = array();

        foreach ($this->headers as $key => $value) {
            $curl_headers[] = "$key: $value";
        }

        return $curl_headers;
    }

    public function getAmzHeaders() {
        $amz_headers = array();

        foreach ($this->headers as $key => $value) {
            if (stripos($key, 'x-amz-') === 0) {
                $amz_headers[$key] = $value;
            }
        }

        return $amz_headers;
    }
}

class S3Request {

    const GET = 'GET';
    const PUT = 'PUT';
    const HEAD = 'HEAD';
    const DELETE = 'DELETE';

    private $s3_action;
    private $endpoint;
    private $uri;
    private $s3_headers;
    private $curl_opts;

    private $s3_response;

    // optional
    private $data;

    public function __construct($s3_action, $endpoint, $uri) {
        $this->s3_action = $s3_action;
        $this->endpoint = $endpoint;
        $this->uri = $uri;

        $this->s3_response = new S3Response($uri);
    }

    public static function put($endpoint, $uri, $data) {
        return (new S3Request(self::PUT, $endpoint, $uri))
            ->withData($data);
    }

    public function withS3Headers(S3Headers $s3_headers) {
        $this->s3_headers = $s3_headers;
        return $this;
    }

    public function withCurlOpts($curl_opts) {
        $this->curl_opts = $curl_opts;
        return $this;
    }

    public function withData($data) {
        $this->data = $data;
        return $this;
    }

    public function sign($access_key, $secret_key) {
        $amz_headers = $this->s3_headers->getAmzHeaders();
        $amz_headers = array_change_key_case($amz_headers, CASE_LOWER);
        ksort($amz_headers);

        $canonical_amz_headers = array();
        foreach ($amz_headers as $header => $value) {
            $canonical_amz_headers[] = "$header:$value";
        }

        $string_to_sign = implode(array(
            $this->s3_action,
            $this->s3_headers->headers['Content-MD5'],
            $this->s3_headers->headers['Content-Type'],
            $this->s3_headers->headers['Date']
        ), "\n");

        if (!empty($canonical_amz_headers)) {
            $string_to_sign .= "\n" . implode($canonical_amz_headers, "\n");
        }

        $string_to_sign .= "\n{$this->uri}";

        $signature = base64_encode(
            hash_hmac('sha1', $string_to_sign, $secret_key, true));

        $this->s3_headers->setAuthorization("AWS $access_key:$signature");

        return $this;
    }

    public function execute() {
        $ch = curl_init();
        $curl_headers = $this->s3_headers->getCurlHeaders();

        curl_setopt($ch, CURLOPT_USERAGENT, 'ericnorris/amazon-s3-php');
        curl_setopt($ch, CURLOPT_URL, "https://{$this->endpoint}{$this->uri}");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $curl_headers);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, array($this->s3_response, '__curlWriteFunction'));
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this->s3_response, '__curlHeaderFunction'));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        switch ($this->s3_action) {
            case self::PUT:
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, self::PUT);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->data);
                break;
        }

        $success = curl_exec($ch);

        if ($success) {
            $this->s3_response->finalize($ch);
        } else {
            $this->s3_response->formatCurlError($ch);
        }

        curl_close($ch);

        return $this->s3_response;
    }
}

class S3Response {

    public $uri;

    public $error;
    public $headers;
    public $body;

    public function __construct($uri) {
        $this->uri = $uri;

        $this->error = null;
        $this->headers = array();
        $this->body = null;
    }

    public function formatCurlError($ch) {
        $this->error = array(
            'code' => curl_errno($ch),
            'message' => curl_error($ch),
            'resource' => $this->uri
        );
    }

    public function finalize($ch) {
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        if ($code > 300 && $content_type == 'application/xml') {
            $response = simplexml_load_string($this->body);

            if ($response) {
                $this->error = array(
                    'code' => (string)$response->Code,
                    'message' => (string)$response->Message,
                );

                if (isset($response->Resource)) {
                    $this->error['resource'] = $response->Resource;
                }
            }
        }
    }

    public function __curlWriteFunction($ch, $data) {
        $this->body .= $data;
        return strlen($data);
    }

    public function __curlHeaderFunction($ch, $data) {
        $header = explode(':', $data);

        if (count($header) == 2) {
            list($key, $value) = $header;
            $this->headers[$key] = trim($value);
        }

        return strlen($data);
    }

}
