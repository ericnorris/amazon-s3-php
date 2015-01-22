<?php

namespace S3\Dependency;

class CurlMulti {

    private $multi_handle;

    public function __construct() {
        $this->multi_handle = null;
    }

    public function init() {
        return ($this->multi_handle = curl_multi_init());
    }

    public function close() {
        curl_multi_close($this->multi_handle);
    }

    public function getHandle() {
        return $this->multi_handle;
    }

    public function addHandle($ch) {
        return curl_multi_add_handle($this->multi_handle, $ch);
    }

    public function exec(&$still_running) {
        return curl_multi_exec($this->multi_handle, $still_running);
    }

    public function select() {
        return curl_multi_select($this->multi_handle);
    }

    public function removeHandle($ch) {
        return curl_multi_remove_handle($this->multi_handle, $ch);
    }

}
