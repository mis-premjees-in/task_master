<?php
include_once 'includes.php';

//get POST request parameters
$response= json_decode(file_get_contents('php://input'), true);
//required: username
$username=makeSafe(strtolower(trim($response['username'])));
$acess_token=$response['access_token'];
$decoded_token=decryptString($acess_token);
$oldPassword=makeSafe($response['oldPassword']);
$newPassword=makeSafe($response['newPassword']);
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
/* validate password */
if(!password_match($oldPassword, $hash)) {
    echo json_response(200, array(
        'Error'=>'1',
        'Message'=>"Old password is incorrect."
    ));
    exit;
}
if(!strong_password($newPassword)) {
    echo json_response(200, array(
        'Error'=>'1',
        'Message'=>"New password is too short or not strong.Ensure it is at least 8 characters long and contains at least one uppercase letter, one lowercase letter, one digit and one special character."
    ));
    exit;
}
/* update password */
$updateDT = date($adminConfig['PHPDateTimeFormat']);
sql("UPDATE `membership_users` set `passMD5`='" . password_hash($newPassword, PASSWORD_DEFAULT) . "', `comments`=CONCAT_WS('\\n', comments, 'member changed his password on $updateDT from API') WHERE memberID='{$username}'", $eo);
//send response
echo json_response(200, array(
    'Error'=>'0',
    'Message'=>"Password has been changed successfully."
));exit;