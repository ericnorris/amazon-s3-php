amazon-s3-php
=============

Inspired by [tpyo/amazon-s3-php-class](https://github.com/tpyo/amazon-s3-php-class), this is a simple and configurable S3 PHP library. Including the S3.php file is enough to be able to upload, delete, and retrieve objects from an Amazon S3 store.

## Usage
`$client = new S3(ACCESS_KEY, SECRET_KEY, [optional S3 endpoint]);`

## Configuration
### `useCurlOpts($curl_opts_array)`
Provide the S3 class with any curl options to use in making requests.

The following options are passed by default in order to prevent 'hung' requests:
```
curl_opts = array(
    CURLOPT_CONNECTTIMEOUT => 30,
    CURLOPT_LOW_SPEED_LIMIT => 1,
    CURLOPT_LOW_SPEED_TIME => 30
);
```
**Note:** *If you call this method, these defaults will not be used.*

### `$headers`
All of the available S3 operations take an optional `$headers` array that will be passed along to S3. These can include `x-amx-meta-`, `x-amz-server-side-encryption`, `Content-Type`, etc. Any Amazon headers specified will be properly included in the AWS signature as per http://docs.aws.amazon.com/AmazonS3/latest/dev/RESTAuthentication.html.

## Methods
### `putObject($bucket, $path, $file [, $headers = array()])`
Uploads a file to the specified path and bucket. `$file` can either be the raw representation of a file (e.g. the result of `file_get_contents()`) or a valid stream resource.
