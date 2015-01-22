<?php

namespace S3\Dependency;

class Curl {

    private $curl_handle;

    public function __construct() {
        $this->curl_handle;
    }

    public function init() {
        return ($this->curl_handle = curl_init());
    }

    public function close() {
        curl_close($this->curl_handle);
    }

    public function getHandle() {
        return $this->curl_handle;
    }

    public function setopt($option, $value) {
        return curl_setopt($this->curl_handle, $option, $value);
    }

    public function setoptArray($options) {
        return curl_setopt_array($this->curl_handle, $options);
    }

    public function errno() {
        return curl_errno($this->curl_handle);
    }

    public function error() {
        return curl_error($this->curl_handle);
    }

    public function getinfo($opt = null) {
        return curl_getinfo($this->curl_handle, $opt);
    }

}
