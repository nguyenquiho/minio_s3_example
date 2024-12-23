<?php

ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/MyMongoDriver.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/includes/MongoPBXConnection.php';

use S3\MinIO\S3Connect;
use S3\MinIO\S3TemporaryLink;

$bucket = 'pvcb-voice-recording';

$connect = new S3Connect();
$temporaryLink = new S3TemporaryLink($bucket);

$dateFolder = date('Ymd');
$dirPathSIP = '/var/spool/asterisk/monitor/'.$dateFolder.'/SIP';
$dirPathLocal = '/var/spool/asterisk/monitor/'.$dateFolder.'/Local';
$dirPath = ['SIP' => $dirPathLocal, 'Local' => $dirPathSIP];
foreach($dirPath as $key => $path){
    $files = scandir($path);  
    foreach ($files as $file) {
        $filePath = $path . '/' . $file;
        $file_parts = pathinfo($filePath)['extension'];
        if($file_parts == 'mp3'){
            $s3_path = $dateFolder.'/'.$key;
            $bucket = 'pvcb-voice-recording/'.$s3_path;
            $res = $connect->putObject($bucket, $file, fopen($filePath, 'r'));
            if($res->code != 200){
                $res->filename = $file;
                $res->time = date("d-m-Y H:i:s");
                print_r($res);
                MongoPBXConnection::getInstance()->insert("log_s3_" . $dateFolder, (array)$res);
            }
        }
    }
}