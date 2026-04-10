<?php
include_once 'includes.php';

//get POST request parameters
$response= json_decode(file_get_contents('php://input'), true);
//required: username
$username=makeSafe(strtolower(trim($response['username'])));
$acess_token=$response['access_token'];
$decoded_token=decryptString($acess_token);
//check if user exists
$hash=sqlValue("SELECT passMD5 FROM membership_users WHERE lcase(memberID)='{$username}' AND isApproved=1 AND isBanned=0");
if(empty($hash)){
    echo json_response(200, array(
        'Error'=>'1',
        'Message'=>"User does not exist or not approved or banned."
    ));
    exit;
}
if($decoded_token!==$hash){
    echo json_response(200, array(
        'Error'=>'1',
        'Message'=>"Invalid access token."
    ));
    exit;
}
$email=isEmail(makeSafe($response['email']));
$custom1=makeSafe($response['custom1']);
$custom2=makeSafe($response['custom2']);
$custom3=makeSafe($response['custom3']);
$custom4=makeSafe($response['custom4']);
if(!$email){
    echo json_response(200, array(
        'Error'=>'1',
        'Message'=>"Invalid email address."
    ));
    exit;
}
/* update profile */
$updateDT = date($adminConfig['PHPDateTimeFormat']);
sql("UPDATE `membership_users` set email='$email', custom1='$custom1', custom2='$custom2', custom3='$custom3', custom4='$custom4', comments=CONCAT_WS('\\n', comments, 'member updated his profile on $updateDT from API') WHERE memberID='{$username}'", $eo);
echo json_response(200, array(
    'Error'=>'0',
    'Message'=>"Profile updated successfully."
));