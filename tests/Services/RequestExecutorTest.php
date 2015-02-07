<?php

namespace S3\Services;

use S3\Dependencies\CurlMulti;
use S3\Dependencies\Curl;
use S3\Request;

class RequestExecutorTest extends \PHPUnit_Framework_TestCase {

    public function setUp() {

        $this->curl_multi_mock =
            $this->getMockBuilder('\S3\Dependencies\CurlMulti')
                 ->getMock();

        $this->curl_mock = $this->getMockBuilder('\S3\Dependencies\Curl')
                                ->getMock();

        $this->curl_spy = new \S3\Tests\Spy\CurlSpy();

        $this->request_executor = new RequestExecutor(
            $this->curl_multi_mock, $this->curl_mock);

        $this->request_executor_spy = new RequestExecutor(
            $this->curl_multi_mock, $this->curl_spy);

        $this->default_request = new Request('GET', 's3.amazonaws.com',
            '/test.txt');
    }

    /**
     * @expectedException Exception
     */
    public function test_initThrowsExceptionOnCurlMultiInitFailure() {
        $this->curl_multi_mock->method('init')
                              ->willReturn(false);

        $this->request_executor->init();
    }

    /**
     * @expectedException Exception
     */
    public function test_executeThrowsExceptionOnCurlInitFailure() {
        $this->curl_mock->method('init')
                        ->willReturn(false);

        $this->request_executor->execute($this->default_request);
    }

}
