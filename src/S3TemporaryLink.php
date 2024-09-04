<?php

namespace S3\MinIO;

class S3TemporaryLink {

    private $accessKey, $secretKey, $bucket, $host;

    public function __construct($bucket) {
        $this->host = S3_ENDPOINT;
        $this->accessKey = S3_ACCESS_KEY;
        $this->secretKey = S3_SECRET_KEY;
        $this->bucket = $bucket;
    }

    private function crypto_hmacSHA1($key, $data, $blocksize = 64) {
        if (strlen($key) > $blocksize) $key = pack('H*', sha1($key));
        $key = str_pad($key, $blocksize, chr(0x00));
        $ipad = str_repeat(chr(0x36), $blocksize);
        $opad = str_repeat(chr(0x5c), $blocksize);
        $hmac = pack( 'H*', sha1(($key ^ $opad) . pack( 'H*', sha1(($key ^ $ipad) . $data))));
        return base64_encode($hmac);
    }
    
    public function render($path, $expires = 5) {
        // Calculate expiry time
        $expires = time() + intval(floatval($expires) * 60);
        // $expires = 1680864736;
        // Fix the path; encode and sanitize
        $path = str_replace('%2F', '/', rawurlencode($path = ltrim($path, '/')));
        // Path for signature starts with the bucket
        $signpath = '/'. $this->bucket .'/'. $path;
        // S3 friendly string to sign
        $signsz = implode("\n", $pieces = array('GET', null, null, $expires, $signpath));
        // Calculate the hash
        $signature = $this->crypto_hmacSHA1($this->secretKey, $signsz);
        // Glue the URL ...
        $url = sprintf('http://' . $this->host . '/%s/%s', $this->bucket, $path);
        // ... to the query string ...
        $qs = http_build_query($pieces = array(
          'AWSAccessKeyId' => $this->accessKey,
          'Expires' => $expires,
          'Signature' => $signature
        ));
        // ... and return the URL!
        return $url.'?'.$qs;
    }
    
}
