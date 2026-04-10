<?php
include_once 'includes.php';

//get POST request parameters
$response= json_decode(file_get_contents('php://input'), true);
//required: username
$username=makeSafe(strtolower(trim($response['username'])));
//check if user exists
$hash=sqlValue("SELECT passMD5 FROM membership_users WHERE lcase(memberID)='{$username}' AND isApproved=1 AND isBanned=0");
if(empty($hash)){
    echo json_response(200, array(
        'Error'=>'1',
        'Message'=>"User does not exist or not approved or banned."
    ));
    exit;
}
if (empty($username)) {
	echo json_response(200, array(
		'Error' =>'1',
		'Message'=>"Kindly provide a username"
	));exit;
}

$where = "LCASE(`memberID`)='{$username}'";
$eo = ['silentErrors' => true];
$res = sql("SELECT * FROM `membership_users` WHERE {$where} LIMIT 1", $eo);
$row = db_fetch_assoc($res);

		// avoid admin password change
if($row && $row['memberID'] != $adminConfig['adminUsername']) {
			// generate and store password reset key, if no valid key already exists
	$no_valid_key = ($row['pass_reset_key'] == '' || ($row['pass_reset_key'] != '' && $row['pass_reset_expiry'] < (time() - $reset_expiry)));
	$key = ($no_valid_key ? substr(md5(microtime() . rand(0, 100000)), -17) : $row['pass_reset_key']);
	$expiry = ($no_valid_key ? time() + $reset_expiry : $row['pass_reset_expiry']);
	@db_query("UPDATE `membership_users` SET `pass_reset_key`='{$key}', `pass_reset_expiry`='{$expiry}' WHERE `memberID`='" . makeSafe($row['memberID']) . "'");

			// determine password reset URL
	$ResetLink = application_url("membership_passwordReset.php?key={$key}");

			// send reset instructions
	sendEmail($row['email'],$Translation['password reset subject'],nl2br(str_replace('<ResetLink>', $ResetLink, $Translation['password reset message'])));
	echo json_response(200, array(
		'Error' =>'0',
		'Message'=>"Password Has Been reset. Kindly check your email for further instructions."
	));exit;
}