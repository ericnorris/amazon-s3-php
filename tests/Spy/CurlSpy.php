<?php

namespace S3\Tests\Spy;

class CurlSpy extends \S3\Dependencies\Curl {

    private $options;

    public function __construct() {
        parent::__construct();

        $this->options = array();
    }

    public function setopt($option, $value) {
        $this->options[$option] = $value;
    }

    public function setoptArray(array $options) {
        $this->options = array_merge($this->options, $headers);
    }

    public function getopt($option) {
        return isset($this->options[$option]) ? $this->options[$option] : null;
    }

    public function getoptArray() {
        return $this->options;
    }

    public function init() {
        return true;
    }

}