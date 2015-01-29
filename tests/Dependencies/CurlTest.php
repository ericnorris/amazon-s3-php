<?php

namespace S3\Dependencies;

class CurlTest extends \PHPUnit_Framework_TestCase {

    const SENTINEL = 64;

    public function setUp() {
        $this->ch = new Curl();
        $this->ch->init();
    }

    public function tearDown() {
        if (is_resource($this->ch->getHandle())) {
            $this->ch->close();
        }
    }

    public function test_init() {
       $this->assertTrue(is_resource($this->ch->getHandle()));
    }

    public function test_close() {
        $this->ch->close();

        $this->assertFalse(is_resource($this->ch->getHandle()));
    }

    public function test_setopt() {
        $this->assertSame(
            self::SENTINEL,
            $this->ch->setopt(self::SENTINEL, self::SENTINEL)
        );
    }

    public function test_setoptArray() {
        $this->assertContains(
            self::SENTINEL,
            $this->ch->setoptArray(array(self::SENTINEL))
        );
    }

    public function test_errno() {
        $this->assertSame(
            self::SENTINEL,
            $this->ch->errno()
        );
    }

    public function test_error() {
        $this->assertSame(
            "" . self::SENTINEL,
            $this->ch->error()
        );
    }

    public function test_getinfo() {
        $this->assertSame(
            self::SENTINEL,
            $this->ch->getinfo(self::SENTINEL)
        );
    }

}

// === Stubbing global functions ===
function curl_setopt($ch, $option, $value) {
    return is_resource($ch) ? ($option & $value) : 0;
}

function curl_setopt_array($ch, $options) {
    return is_resource($ch) ? $options : array();
}

function curl_errno($ch) {
    return is_resource($ch) ? CurlTest::SENTINEL : 0;
}

function curl_error($ch) {
    return is_resource($ch) ? "" . CurlTest::SENTINEL : 0;
}

function curl_getinfo($ch, $opt) {
    return is_resource($ch) ? $opt : 0;
}
