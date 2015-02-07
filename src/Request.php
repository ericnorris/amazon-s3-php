<?php

namespace S3;

class Request {

    const AWS_DATE_FORMAT = 'D, d M Y H:i:s T';

    private $method;
    private $path;
    private $endpoint;

    private $headers;
    private $file;

    public function __construct($method, $endpoint, $path) {
        $this->method   = $method;
        $this->endpoint = $endpoint;
        $this->path     = $path;

        $this->headers = array(
            'Content-MD5'  => '',
            'Content-Type' => '',
            'Date'         => gmdate(self::AWS_DATE_FORMAT),
            'Host'         => $endpoint
        );

        $this->file = null;
    }

    public function includeHeaders(array $headers) {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    public function includeFile($file) {
        $this->file = $file;
        return $this;
    }

    public function getMethod() {
        return $this->method;
    }

    public function getPath() {
        return $this->path;
    }

    public function getEndpoint() {
        return $this->endpoint;
    }

    public function getHeaders() {
        return $this->headers;
    }

    public function getFile() {
        return $this->file;
    }

    public function getHeaderString() {
        return implode("\n", array_map(
            function($header, $value) {
                return "$header: $value";
            },
            array_keys($this->headers),
            array_values($this->headers)
        ));
    }

}
