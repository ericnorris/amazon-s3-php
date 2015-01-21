<?php

namespace S3\Dependency;

class CurlMultiTest extends \PHPUnit_Framework_TestCase {

    const SENTINEL = 808;

    public function setUp() {
        $this->mh = new CurlMulti();
    }

    public function tearDown() {
        if (is_resource($this->mh->getHandle())) {
            $this->mh->close();
        }
    }


    public function test_init() {
        $this->mh->init();

        $this->assertTrue(is_resource($this->mh->getHandle()));
    }

    public function test_close() {
        $this->mh->init();
        $this->mh->close();

        $this->assertFalse(is_resource($this->mh->getHandle()));
    }

    public function test_addHandle() {
        $this->assertSame(
            self::SENTINEL,
            $this->mh->addHandle(self::SENTINEL)
        );
    }

    public function test_exec() {
        $value = null;
        $return = $this->mh->exec($value);

        $this->assertSame(
            self::SENTINEL,
            $value
        );

        $this->assertSame(
            self::SENTINEL,
            $return
        );
    }

    public function test_select() {
        $this->assertSame(
            self::SENTINEL,
            $this->mh->select()
        );
    }

    public function test_removeHandle() {
        $this->assertSame(
            self::SENTINEL,
            $this->mh->removeHandle(self::SENTINEL)
        );
    }

}

// === Stubbing global functions ===
function curl_multi_add_handle($mh, $handle) {
    return $handle;
}

function curl_multi_exec($mh, &$still_running) {
    $still_running = CurlMultiTest::SENTINEL;
    return CurlMultiTest::SENTINEL;
}

function curl_multi_select($mh) {
    return CurlMultiTest::SENTINEL;
}

function curl_multi_remove_handle($mh, $handle) {
    return $handle;
}
