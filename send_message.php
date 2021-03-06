<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Lukas
 * Date: 11.06.13
 * Time: 21:21
 */

include "messenger.php";

$contact_id = $_POST['contact_id'];
$body = $_POST['body'];
$timestamp = $_POST['timestamp'];

if (!isset($timestamp)){
    $currentTime = mktime(date("H")+2,date("i"),date("s"),date("m"),date("d"),date("Y"));
    $timestamp = date('Y-m-d H:i:s', $currentTime);
}

if (!isset($contact_id) || !isset($body) || !isset($timestamp)) {
    echo "Not all required POST parameters are set";
}

$messageData = array($contact_id, $body, $timestamp);

if (sendMessage($messageData)){
    echo "OK";
} else {
    echo "Server Error";
}