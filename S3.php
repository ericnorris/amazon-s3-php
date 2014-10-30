<?php

class S3 {

    const DEFAULT_ENDPOINT = 's3.amazonaws.com';

    private $access_key;
    private $secret_key;

    private $endpoint;

    public function __construct($access_key, $secret_key, $endpoint = null) {

        $this->access_key = $access_key;
        $this->secret_key = $secret_key;
        $this->endpoint = $endpoint ?: self::DEFAULT_ENDPOINT;
    }

    public function putObject($bucket, $path, $file, $headers) {
        $uri = "$bucket/$path";

        $request = (new S3Request('PUT', $this->endpoint, $uri))
            ->setFileContents($file)
            ->setHeaders($headers)
            ->sign($this->access_key, $this->secret_key);

        return $request->getResponse();
    }
}

class S3Request {

    private $action;
    private $endpoint;
    private $uri;
    private $curl;
    private $headers;

    public function __construct($action, $endpoint, $uri) {
        $this->action = $action;
        $this->endpoint = $endpoint;
        $this->uri = $uri;

        $this->headers = array(
            'Content-MD5' => '',
            'Content-Type' => '',
            'Date' => gmdate('D, d M Y H:i:s T'),
            'Host' => $this->endpoint
        );

        $this->curl = curl_init();
        $this->response = new S3Response();
    }

    public function setFileContents($file) {
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $file);

        $this->headers['Content-MD5'] = base64_encode(md5($file, true));

        return $this;
    }

    public function setHeaders($custom_headers) {
        $this->headers = array_merge($this->headers, $custom_headers);
        return $this;
    }

    public function sign($access_key, $secret_key) {
        $canonical_amz_headers = $this->getCanonicalAmzHeaders();

        $string_to_sign = '';
        $string_to_sign .= "{$this->action}\n";
        $string_to_sign .= "{$this->headers['Content-MD5']}\n";
        $string_to_sign .= "{$this->headers['Content-Type']}\n";
        $string_to_sign .= "{$this->headers['Date']}\n";

        if (!empty($canonical_amz_headers)) {
            $string_to_sign .= implode($canonical_amz_headers, "\n") . "\n";
        }

        $string_to_sign .= "/{$this->uri}";

        $signature = base64_encode(
            hash_hmac('sha1', $string_to_sign, $secret_key, true)
        );

        $this->headers['Authorization'] = "AWS $access_key:$signature";

        return $this;
    }

    public function getResponse() {
        $http_headers = array_map(
            function($header, $value) {
                return "$header: $value";
            },
            array_keys($this->headers),
            array_values($this->headers)
        );

        curl_setopt_array($this->curl, array(
            CURLOPT_USERAGENT => 'ericnorris/amazon-s3-php',
            CURLOPT_CUSTOMREQUEST => $this->action,
            CURLOPT_URL => "https://{$this->endpoint}/{$this->uri}",
            CURLOPT_HTTPHEADER => $http_headers,
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_WRITEFUNCTION => array(
                $this->response, '__curlWriteFunction'
            ),
            CURLOPT_HEADERFUNCTION => array(
                $this->response, '__curlHeaderFunction'
            )
        ));

        $success = curl_exec($this->curl);

        if (!$success) {
            $this->response->error = array(
                'code' => curl_errno($this->curl),
                'message' => curl_error($this->curl),
                'resource' => $this->uri
            );
        } else {
            $code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
            $content_type = curl_getinfo($this->curl, CURLINFO_CONTENT_TYPE);

            if ($code > 300 && $content_type == 'application/xml') {
                $response = simplexml_load_string($this->body);

                if ($response) {
                    $error = array(
                        'code' => (string)$response->Code,
                        'message' => (string)$response->Message,
                    );

                    if (isset($response->Resource)) {
                        $error['resource'] = $response->Resource;
                    }

                    $this->response->error = $error;
                }
            }
        }

        curl_close($this->curl);

        return $this->response;
    }

    private function getCanonicalAmzHeaders() {
        $canonical_amz_headers = array();

        foreach ($this->headers as $header => $value) {
            $header = trim(strtolower($header));
            $value = trim($value);

            if (strpos($header, 'x-amz-') === 0) {
                $canonical_amz_headers[$header] = "$header:$value";
            }
        }

        ksort($canonical_amz_headers);

        return $canonical_amz_headers;
    }

}

class S3Response {

    public $error;
    public $headers;
    public $body;

    public function __construct() {
        $this->error = null;
        $this->headers = array();
        $this->body = null;
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
