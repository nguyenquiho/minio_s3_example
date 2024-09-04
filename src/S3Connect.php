<?php

namespace S3\MinIO;

class S3Connect {

    private $access_key = S3_ACCESS_KEY;
    private $secret_key = S3_SECRET_KEY;

    private $endpoint;

    private $multi_curl;

    private $curl_opts;

    public function __construct($endpoint = null) {
        $this->endpoint = $endpoint ?: S3_ENDPOINT;

        $this->multi_curl = curl_multi_init();

        $this->curl_opts = array(
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_LOW_SPEED_LIMIT => 1,
            CURLOPT_LOW_SPEED_TIME => 30
        );
    }

    public function __destruct() {
        curl_multi_close($this->multi_curl);
    }

    public function useCurlOpts($curl_opts) {
        $this->curl_opts = $curl_opts;
        return $this;
    }

    public function putObject($bucket, $path, $file, $headers = array()) {
        $uri = "$bucket/$path";

        $request = (new S3Request('PUT', $this->endpoint, $uri))
            ->setFileContents($file)
            ->setHeaders($headers)
            ->useMultiCurl($this->multi_curl)
            ->useCurlOpts($this->curl_opts)
            ->sign($this->access_key, $this->secret_key);

        return $request->getResponse();
    }

    public function getObjectInfo($bucket, $path, $headers = array()) {
        $uri = "$bucket/$path";

        $request = (new S3Request('HEAD', $this->endpoint, $uri))
            ->setHeaders($headers)
            ->useMultiCurl($this->multi_curl)
            ->useCurlOpts($this->curl_opts)
            ->sign($this->access_key, $this->secret_key);

        return $request->getResponse();
    }

    public function getObject(
        $bucket,
        $path,
        $resource = null,
        $headers = array()
    ) {
        $uri = "$bucket/$path";

        $request = (new S3Request('GET', $this->endpoint, $uri))
            ->setHeaders($headers)
            ->useMultiCurl($this->multi_curl)
            ->useCurlOpts($this->curl_opts)
            ->sign($this->access_key, $this->secret_key);

        if (is_resource($resource)) {
            $request->saveToResource($resource);
        }

        return $request->getResponse();
    }

    public function deleteObject($bucket, $path, $headers = array()) {
        $uri = "$bucket/$path";

        $request = (new S3Request('DELETE', $this->endpoint, $uri))
            ->setHeaders($headers)
            ->useMultiCurl($this->multi_curl)
            ->useCurlOpts($this->curl_opts)
            ->sign($this->access_key, $this->secret_key);

        return $request->getResponse();
    }

    public function getBucket($bucket, $headers = array()) {
        $request = (new S3Request('GET', $this->endpoint, $bucket))
            ->setHeaders($headers)
            ->useMultiCurl($this->multi_curl)
            ->useCurlOpts($this->curl_opts)
            ->sign($this->access_key, $this->secret_key);

        $response = $request->getResponse();

        if (!isset($response->error) && isset($response->body)) {
            $body = simplexml_load_string($response->body);

            if ($body) {
                $response->body = $body;
            }
        }

        return $response;
    }
}