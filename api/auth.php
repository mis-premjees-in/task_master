<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Origin, Cache-Control, Pragma, Authorization, Accept, Accept-Encoding");

include_once 'includes.php';

@session_start();
//get POST request parameters
$response = json_decode(file_get_contents('php://input'), true);
include 'default_api.creds.php';
//required: username, password
$username = makeSafe(strtolower($response['username'] ?? $default_user));
$password = $response['password'] ?? $default_pass;
$mobile = $response['mobile'] ?? "";
$hash = sqlValue("select passMD5 from membership_users where lcase(memberID)='{$username}' and isApproved=1 and isBanned=0");

// file_put_contents("log.txt", "select passMD5 from membership_users where lcase(memberID)='{$username}' and isApproved=1 and isBanned=0");

if (password_match($password, $hash)) {
    # code...
    //generate access token
    $access_token = encyptString($hash);
    // $user_data = sql("SELECT * FROM customer WHERE customer_contact = '{$mobile}' AND lcase(customer_status)='active'", $eo);
    // //sql("SELECT * FROM membership_users WHERE lcase(memberID)='{$username}' AND isApproved=1 AND isBanned=0",$eo);
    // $Data = [];
    // $otp = $mobile=="6375397813"?"1234":rand(1001, 9999);
    
    $Data['memberID'] = $username;
    // foreach ($user_data as $key => $value) {
    //     # code...
        
    //     $Data['customer_id'] = $value['customer_id'];
    //     $Data['customer_name'] = $value['customer_name'];
    //     $Data['customer_contact'] = $value['customer_contact'];
    //     $Data['customer_dob'] = $value['customer_dob'] ?? "";
    //     $Data['customer_created'] = $value['customer_created'];
    //     $Data['customer_updated'] = $value['customer_updated'];

    //     $Data['customer_logged'] = true;
    //     $Data['customer_status'] = 1;
    // }
    // echo json_encode($Data);
    // file_put_contents("log.txt", $response['mobile'] . $response['otp']);
    // if (isset($response['mobile']) && $response['otp'] === "true") {
    //     file_get_contents('http://sms.srvc.in/app/smsapi/index.php?key=56834052A5A7E0&entity=1401573290000054226&tempid=1407173416105517329&campaign=0&routeid=7&type=text&contacts=' . $response['mobile'] . '&senderid=KNSKJW&msg=Your+Secured+OTP+is+'. $otp.'+and+valid+for+10+Minutes.+Do+not+share+this+OTP+to+anyone+for+security+reasons.+Kanishka+Jewellers.');
    //     //file_put_contents("log.txt", 'http://sms.srvc.in/app/smsapi/index.php?key=4590B5F439F01C&entity=1501634090000012410&tempid=1507161717957825188&campaign=0&routeid=7&type=text&contacts=' . $response['mobile'] . '&senderid=SRVCIN&msg=The+one+time+verification+code+on+SIOM+portal+is+:+' . $otp . '+SRVC+Service');
    // }
    if (sizeof($Data)) {
        echo json_response(200, array(
            'Error' => '0',
            'Message' => "User Authenticated Successfully",
            'User_Data' => $Data,
            'Access_Token' => $access_token
            // 'otp' => $otp
        ));
    } else {

        echo json_response(200, array(
            'Error' => '1',
            'Message' => "User Not Exist",
            'User_Data' => $Data,
            'Access_Token' => $access_token,
            'otp' => $otp
        ));
    }
} else {
    echo json_response(200, array(
        'Error' => '1',
        'Message' => "Login attempt failed, try again."
    ));
}
