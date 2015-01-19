<?php

namespace S3;

class Response {

    public $error;
    public $code;
    public $headers;
    public $body;

    public function __construct() {
        $this->error = null;
        $this->code = null;
        $this->headers = array();
        $this->body = null;
    }

    public function saveToResource($resource) {
        $this->body = $resource;
    }

    public function __curlWriteFunction($ch, $data) {
        if (is_resource($this->body)) {
            return fwrite($this->body, $data);
        } else {
            $this->body .= $data;
            return strlen($data);
        }
    }

    public function __curlHeaderFunction($ch, $data) {
        $header = explode(':', $data);

        if (count($header) == 2) {
            list($key, $value) = $header;
            $this->headers[$key] = trim($value);
        }

        return strlen($data);
    }

    public function finalize($ch) {
        if (is_resource($this->body)) {
            rewind($this->body);
        }

        if (curl_errno($ch) || curl_error($ch)) {
            $this->error = array(
                'code' => curl_errno($ch),
                'message' => curl_error($ch),
            );
        } else {
            $this->code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

            if ($this->code > 300 && $content_type == 'application/xml') {
                if (is_resource($this->body)) {
                    $response = simplexml_load_string(
                        stream_get_contents($this->body)
                    );

                    rewind($this->body);
                } else {
                    $response = simplexml_load_string($this->body);
                }

                if ($response) {
                    $error = array(
                        'code' => (string)$response->Code,
                        'message' => (string)$response->Message,
                    );

                    if (isset($response->Resource)) {
                        $error['resource'] = (string)$response->Resource;
                    }

                    $this->error = $error;
                }
            }
        }
    }
}
