<?php

class S3 {

    const DEFAULT_ENDPOINT = 's3.amazonaws.com'

    private $accessKey;
    private $secretKey;

    private $endpoint = self::DEFAULT_ENDPOINT;
    private $virtualHosting = true;

    public function __construct($accessKey, $secretKey) {
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
    }

    public function putObject($bucket, $uri, $file, S3Headers $headers) {
        // TODO
    }
}

class S3Headers {

    public $headers;
    public $amz_headers;

    public function __construct() {
        $this->headers = array();
        $this->amz_headers = array();
    }

    public function withContentLength($size) {
        $headers['Content-Length'] = (int)$size;
        return $this;
    }

    public function withHost($host) {
        $headers['Host'] = $host;
        return $this;
    }

    public function getHTTPHeaders() {
        // TODO
    }
}

class S3Request {

    const PUT = 'PUT';

    private $s3_action;
    private $endpoint;
    private $uri;
    private $s3_headers;
    private $curl_opts;

    public function __construct($s3_action, $endpoint, $uri) {
        $this->s3_action = $s3_action;
        $this->uri = $uri;
    }

    public static function put($endpoint, $uri) {
        return new S3Request(self::PUT, $endpoint, $uri);
    }

    public function withS3Headers(S3Headers $s3_headers) {
        $this->s3_headers = $s3_headers;
        return $this;
    }

    public function withCurlOpts($curl_opts) {
        $this->curl_opts = $curl_opts;
        return $this;
    }

    public function sign($accessKey, $secretKey) {
        $amz_headers = $this->s3_headers->amz_headers;
        $amz_headers = array_change_key_case($amz_headers, CASE_LOWER);
        ksort($amz_headers);

        $canonical_amz_headers = array();
        foreach ($amz_headers as $header => $value) {
            $canonical_amz_headers[] = "$header:$value";
        }

        $string_to_sign = implode(array(
            $this->s3_action,
            $this->headers['Content-MD5'],
            $this->headers['Content-Type'],
            $this->headers['Date'],
            implode($canonical_amz_headers, "\n"),
            $this->uri
        ), "\n");

        $signature = base64_encode(
            hash_hmac('sha1', $string_to_sign, $secretKey, true));

        $this->headers['Authorization'] = "AWS $accessKey:$signature";
    }

    public function execute() {
        $ch = curl_init();
        $curl_headers = $this->s3_headers->getHTTPHeaders();

        curl_setopt($ch, CURLOPT_USERAGENT, 'ericnorris/amazon-s3-php');
        curl_setopt($ch, CURLOPT_URL, "https://{$this->endpoint}/{$this->uri}");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $curl_headers);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, array($this, 'curlWriteFunction'));
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, 'curlHeaderFunction'));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        // TODO
    }
}
