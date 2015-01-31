<?php

namespace S3;

class ResponseTest extends \PHPUnit_Framework_TestCase {

    public function test_defaultValues() {
        $response = new Response();

        $this->assertNull($response->error);
        $this->assertNull($response->code);
        $this->assertEmpty($response->headers);
        $this->assertNull($response->body);
    }

}
