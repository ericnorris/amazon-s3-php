<?php

namespace S3\Dependency;

class Container {

    private $curl_multi;

    public function __construct() {
        $this->curl_multi = new CurlMulti();
    }

    public function getCurlMulti() {
        return $this->curl_multi;
    }

    public function createCurl() {
        return new Curl();
    }

}
