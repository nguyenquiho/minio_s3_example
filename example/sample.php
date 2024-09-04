<?php

ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use S3\MinIO\S3Connect;
use S3\MinIO\S3TemporaryLink;

$bucket = 'sms';

$connect = new S3Connect();
$temporaryLink = new S3TemporaryLink($bucket);

echo '<pre>';

// Get bucket sms of S3
print_r($connect->getBucket($bucket));

// Get object in bucket (response body content), can put to resource
print_r($connect->getObject($bucket, 'image_2023-03-15_08-45-37.png'));

// put object to S3
print_r($connect->putObject($bucket, 'images_name_s3.jpeg', fopen(__DIR__ . '/assets/images.jpeg', 'r')));

// get temporary link expires in minutes
$url = $temporaryLink->render('images_name_s3.jpeg', 1);
print_r($url);
echo "\n";
print_r("<img src='$url' />");

// delete object
print_r($connect->deleteObject($bucket, 'images_name_s3.jpeg'));