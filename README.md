amazon-s3-php
=============

Inspired by [tpyo/amazon-s3-php-class](https://github.com/tpyo/amazon-s3-php-class), this is a simple and configurable S3 PHP library. It was written to be as lightweight as possible, while still enabling access to all of the features of AWS (e.g. server-side encryption).

Additionally, `curl_multi_exec` is used (rather than `curl_exec`) for better performance when doing bulk operations.

## Usage
`$client = new S3($access_key, $secret_key [, $endpoint = null]);`

## Configuration
### Specify Custom Curl Options
* `$client->useCurlOpts($curl_opts_array)`

Provides the S3 class with any curl options to use in making requests.

The following options are passed by default in order to prevent 'hung' requests:
```php
curl_opts = array(
    CURLOPT_CONNECTTIMEOUT => 30,
    CURLOPT_LOW_SPEED_LIMIT => 1,
    CURLOPT_LOW_SPEED_TIME => 30
);
```
**Note:** *If you call this method, these defaults will not be used.*

### Send Additional AWS Headers
All of the available S3 operations take an optional `$headers` array that will be passed along to S3. These can include `x-amz-meta-`, `x-amz-server-side-encryption`, `Content-Type`, etc. Any Amazon headers specified will be properly included in the AWS signature as per [AWS Signature v2](http://docs.aws.amazon.com/AmazonS3/latest/dev/RESTAuthentication.html).

Request headers that are common to all requests are located [here](http://docs.aws.amazon.com/AmazonS3/latest/API/RESTCommonRequestHeaders.html).

## S3Response Class
All methods in the S3 class will return an instance of the S3Response class.
```php
class S3Response {
    public $error;    // null if no error
    public $code;     // response code from AWS
    public $headers;  // response headers from AWS
    public $body;     // response body from AWS
}
```

If there is an error in curl or an error is returned from AWS, `$response->error` will be non-null and set to the following array:

```php
$error = array(
    'code' => xxx, // error code from either curl or AWS
    'message' => xxx, // error string from either curl or AWS
    'resource' => [optional] // the S3 resource from the request
)
```

## Methods
`putObject($bucket, $path, $file [, $headers = array()])`
* Uploads a file to the specified path and bucket. `$file` can either be the raw representation of a file (e.g. the result of `file_get_contents()`) or a valid stream resource.
* [AWS Documentation](http://docs.aws.amazon.com/AmazonS3/latest/API/RESTObjectPUT.html)

`getObjectInfo($bucket, $path, [, $headers = array()])`
* Retrieves metadata for the object.
* [AWS Documentation](http://docs.aws.amazon.com/AmazonS3/latest/API/RESTObjectHEAD.html)

`getObject($bucket, $path [, $resource = null  [, $headers = array()]])`
* Retrieves the contents of an object. If `$resource` is a valid stream resource, the contents will be written to the stream. Otherwise `$response->body` will contain the contents of the file.
* [AWS Documentation](http://docs.aws.amazon.com/AmazonS3/latest/API/RESTObjectGET.html)

`deleteObject($bucket, $path [, $headers = array()])`
* Deletes an object from S3.
* [AWS Documentation](http://docs.aws.amazon.com/AmazonS3/latest/API/RESTObjectDELETE.html)

`getBucket($bucket [, $headers = array()])`
* Returns a parsed response from S3 listing the contents of the specified bucket.
* [AWS Documentation](http://docs.aws.amazon.com/AmazonS3/latest/API/RESTBucketGET.html)

## Examples
Instantiating the S3 class:
```php
$client = new S3(ACCESS_KEY, SECRET_KEY);

// [OPTIONAL] Specify different curl options
$client->useCurlOpts(array(
    CURLOPT_MAX_RECV_SPEED_LARGE => 1048576,
    CURLOPT_CONNECTTIMEOUT => 10
));
```

### Upload an object
```php
$response = $client->putObject(
    'bucket',
    'hello_world.txt',
    'hello world!',
    array(
        'Content-Type' => 'text/plain'
    )
);

print_r($response);
```

Output:
```php
S3Response Object
(
    [error] => null
    [code] => 200
    [headers] => Array
        (
            [x-amz-id-2] => ...
            [x-amz-request-id] => ...
            [ETag] => "..."
            [Content-Length] => ...
            [Server] => ...
        )
    [body] => null
)
```
### Download an object
```php
$resource = tmpfile();
$client->getObject('bucket', 'hello_world.txt', $resource);

print_r($response);
echo stream_get_contents($resource) . "\n";
```
Output:
```php
S3Response Object
(
    [error] =>
    [code] => 200
    [headers] => Array
        (
            [x-amz-id-2] => ...
            [x-amz-request-id] => ...
            [ETag] => "..."
            [Accept-Ranges] => bytes
            [Content-Type] => text/plain
            [Content-Length] => 12
            [Server] => ...
        )

    [body] => Resource id #17
)

hello world!
```
