<?php

use S3\Client as S3;

class S3Test extends \PHPUnit_Framework_TestCase {

    public function test_simpletest() {
        $a = new S3('', '');
        $this->assertTrue(true);
    }

}
