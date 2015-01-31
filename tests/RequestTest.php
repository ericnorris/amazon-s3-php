<?php

namespace S3;

class RequestTest extends \PHPUnit_Framework_TestCase {

    const TEST_METHOD   = 'GET';
    const TEST_URI      = '/test.txt';
    const TEST_ENDPOINT = 's3.amazonaws.com';

    public function setUp() {
        $this->request = new Request(
            self::TEST_METHOD,
            self::TEST_URI,
            self::TEST_ENDPOINT
        );
    }

    public function test_dateHeaderSet() {
        $headers = $this->request->getHeaders();

        $this->assertArrayHasKey('Date', $headers);
        $this->assertTrue($headers['Date']);
    }

    public function test_getMethod() {
        $this->assertSame(self::TEST_METHOD, $this->request->getMethod());
    }

    public function test_getUri() {
        $this->assertSame(self::TEST_URI, $this->request->getUri());
    }

    public function test_getEndpoint() {
        $this->assertSame(self::TEST_ENDPOINT, $this->request->getEndpoint());
    }

    public function test_includeHeaders() {
        $this->request->includeHeaders(array('Content-Type' => 'text/plain'));

        $headers = $this->request->getHeaders();

        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('text/plain', $headers['Content-Type']);
    }

    public function test_includeFile() {
        $file = 'hello world!';

        $this->request->includeFile($file);

        $this->assertSame($file, $this->request->getFile());
    }

}

// === Stubbing global functions ===
function gmdate($format) {
    return $format === Request::AWS_DATE_FORMAT;
}
