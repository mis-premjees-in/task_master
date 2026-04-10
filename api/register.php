<?php
include_once 'includes.php';

//get POST request parameters
$response = json_decode(file_get_contents('php://input'), true);
//required: username, password,email,groupID
$memberID = is_allowed_username(makeSafe($response['username']));
$password = $response['password'];
$email = isEmail(makeSafe($response['email']));
$custom1 = makeSafe($response['custom1']);
$custom2 = makeSafe($response['custom2']);
$custom3 = makeSafe($response['custom3']);
$custom4 = makeSafe($response['custom4']);
$groupID = makeSafe($response['groupID']);

//check is sign up is allowed
if (!$cg = sqlValue("SELECT COUNT(1) FROM membership_groups WHERE allowSignup=1")) {
    echo json_response(200, array(
        'Error' => "1",
        'Message' => "Sign up is currently disabled"
    ));
    exit;
}
//validate request parameters
if (!$memberID) {
    echo json_response(200, array(
        'Error' => "1",
        'Message' => "Invalid username."
    ));
    exit;
}
if(!strong_password($password)) {
    echo json_response(200, array(
        'Error'=>'1',
        'Message'=>"Password is too short or not strong.Ensure it is at least 8 characters long and contains at least one uppercase letter, one lowercase letter, one digit and one special character."
    ));
    exit;
}
if (!$email) {
    echo json_response(200, array(
        'Error' => "1",
        'Message' => "Invalid email."
    ));
    exit;
}
if (!sqlValue("SELECT COUNT(1) FROM membership_groups WHERE groupID='$groupID' AND allowSignup=1")) {
    echo json_response(200, array(
        'Error' => "1",
        'Message' => "Invalid group."
    ));
    exit;
}

// save member data
$needsApproval = sqlValue("SELECT needsApproval FROM membership_groups WHERE groupID='$groupID'");
sql("INSERT INTO `membership_users` SET memberID='{$memberID}', passMD5='" . password_hash($password, PASSWORD_DEFAULT) . "', email='{$email}', signupDate='" . @date('Y-m-d') . "', groupID='{$groupID}', isBanned='0', isApproved='" . ($needsApproval == 1 ? '0' : '1') . "', custom1='{$custom1}', custom2='{$custom2}', custom3='{$custom3}', custom4='{$custom4}', comments='member signed up through API'", $eo);

//You can write your own custom logic here eg. send email to admin, etc.

//send response
echo json_response(200, array(
    'Error' => "0",
    'Message' => "User created successfully."
));
