<?php

namespace S3;

class Response {

    public $error;
    public $code;
    public $headers;
    public $body;

    public function __construct() {
        $this->error = null;
        $this->code = null;
        $this->headers = array();
        $this->body = null;
    }

}
