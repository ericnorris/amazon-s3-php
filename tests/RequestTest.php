<?php

namespace S3;

class RequestTest extends \PHPUnit_Framework_TestCase {

    const TEST_METHOD   = 'GET';
    const TEST_PATH     = '/test.txt';
    const TEST_ENDPOINT = 's3.amazonaws.com';

    public function setUp() {
        $this->request = new Request(
            self::TEST_METHOD,
            self::TEST_ENDPOINT,
            self::TEST_PATH
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

    public function test_getPath() {
        $this->assertSame(self::TEST_PATH, $this->request->getPath());
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

    public function test_getHeaderString() {
        $headers = array(
            'Content-MD5' => 'test-md5',
            'Content-Type' => 'test-type',
            'Date' => 'test-date',
            'Host' => 'test-endpoint'
        );

        $header_string = "Content-MD5: test-md5\n" .
                         "Content-Type: test-type\n" .
                         "Date: test-date\n" .
                         "Host: test-endpoint";

        $this->request->includeHeaders($headers);

        $this->assertSame($header_string, $this->request->getHeaderString());
    }

}

// === Stubbing global functions ===
function gmdate($format) {
    return $format === Request::AWS_DATE_FORMAT;
}
