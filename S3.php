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

    public function putObject($bucket, $uri, $file, $headers = null) {

    }
}

class S3Headers {

    private $headers;

    public function withContentLength($size) {
        $headers['Content-Length'] = (int)$size;
        return $this;
    }

    public function withHost($host) {
        $headers['Host'] = $host;
        return $this;
    }
}

class S3Request {

    const PUT = 'PUT';

    private $s3_action;
    private $endpoint;
    private $virtualHosting;
    private $bucket;
    private $uri;
    private $headers;
    private $curl_opts;

    public function __construct($s3_action, $endpoint, $virtualHosting) {
        $this->s3_action = $s3_action;
        $this->endpoint = $endpoint;
        $this->virtualHosting = $virtualHosting;
    }

    public static function put($endpoint, $virtualHosting) {
        return new S3Request(self::PUT, $endpoint, $virtualHosting);
    }

    public function inBucket($bucket) {
        $this->bucket = $bucket;
        return $this;
    }

    public function atURI($uri) {
        $this->uri = $uri;
        return $this;
    }

    public function withHeaders($headers) {
        $this->headers = $headers;
        return $this;
    }

    public function withCurlOpts($curl_opts) {
        $this->curl_opts = $curl_opts;
        return $this;
    }

    public function sign($accessKey, $secretKey) {
        $amz_headers = array_filter($this->headers, function($header) {
            return stripos($header, 'x-amz-') === 0;
        }, ARRAY_FILTER_USE_KEY);

        $amz_headers = array_change_key_case($amz_headers);
        ksort($amz_headers);

        $canonical_amz_headers = array();
        foreach ($amz_headers as $header => $value) {
            $canonical_amz_headers[] = "$header:$value";
        }

        $canonical_resource =
            $this->bucket ? ('/' . $this->bucket : '') .
            $this->uri;

        $string_to_sign = implode(array(
            $this->s3_action,
            $this->headers['Content-MD5'],
            $this->headers['Content-Type'],
            $this->headers['Date'],
            implode($canonical_amz_headers, "\n"),
            $canonical_resource
        ), "\n");

        $signature = base64_encode(
            hash_hmac('sha1', $string_to_sign, $secretKey, true));

        $this->headers['Authorization'] = "AWS $accessKey:$signature";
    }

    public function execute() {
        $ch = curl_init();
    }
}
