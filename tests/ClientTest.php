<?php

class ClientTest extends \PHPUnit_Framework_TestCase {

    public function test_simpletest() {
        new S3\Client('', '', '');
        $this->assertTrue(true);
    }

}
