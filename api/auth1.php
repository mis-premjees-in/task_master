<?php
include_once 'includes.php';

//get POST request parameters
$response = json_decode(file_get_contents('php://input'), true);
include 'default_api.creds.php';
//required: username, password
$username = makeSafe(strtolower($response['username'] ?? $default_user));
$password = $response['password'] ?? $default_pass;
$mobile = $response['mobile'] ?? "";
$hash = sqlValue("select passMD5 from membership_users where lcase(memberID)='{$username}' and isApproved=1 and isBanned=0");

if (password_match($password, $hash)) {
    # code...
    //generate access token
    $access_token = encyptString($hash);
    $o = array('silentErrors' => true);
    $user_data = sql("SELECT * FROM customer WHERE customer_contact = '{$mobile}' AND lcase(customer_status)='active'", $o);
    $_SESSION['otp'] = rand(1001, 9999);
    file_put_contents("log.txt",json_encode($user_data)."\r\n", FILE_APPEND);
    file_put_contents("log.txt","otp ".($response['otp']).": "."SELECT * FROM customer WHERE lcase(customer_contact) = '{$mobile}' AND lcase(customer_status)='active'\r\n", FILE_APPEND);
    file_put_contents("log.txt",count((array)$user_data)."\r\n",FILE_APPEND);
    $Data = [];
    // file_put_contents("log.txt",count((array)$user_data));  
    if (!$user_data || !count((array)$user_data)) {
        
        //file_put_contents("log.txt",'http://sms.srvc.in/app/smsapi/index.php?key=4590B5F439F01C&entity=1501634090000012410&tempid=1507161717957825188&campaign=0&routeid=7&type=text&contacts='.$response['mobile'].'&senderid=SRVCIN&msg=The+one+time+verification+code+on+SIOM+portal+is+:+'.$_SESSION['otp'].'+SRVC+Service');
        if (isset($response['mobile']) && $response['otp'] === "true"){
            file_get_contents('http://sms.srvc.in/app/smsapi/index.php?key=4590B5F439F01C&entity=1501634090000012410&tempid=1507161717957825188&campaign=0&routeid=7&type=text&contacts=' . $response['mobile'] . '&senderid=SRVCIN&msg=The+one+time+verification+code+on+SIOM+portal+is+:+' . $_SESSION['otp'] . '+SRVC+Service');
        }

        $Data['memberID'] = $username;
        $Data['customer_logged'] = false;
        $Data['customer_status'] = 0;
        $Data['customer_otp'] = $_SESSION['otp'];

        echo json_response(200, array(
            'Error' => '1',
            'Message' => "User Not Exists",
            'User_Data' => $Data,
            'Access_Token' => $access_token
        ));
    } else {
        
        foreach ($user_data as $key => $value) {
            # code...
            $Data['memberID'] = $username;
            $Data['customer_id'] = $value['customer_id'];
            $Data['customer_name'] = $value['customer_name'];
            $Data['customer_contact'] = $value['customer_contact'];
            $Data['customer_dob'] = $value['customer_dob'];
            $Data['customer_timestamp'] = $value['customer_timestamp'];

            $Data['customer_logged'] = true;
            $Data['customer_status'] = 1;
        }
        echo json_response(200, array(
            'Error' => '0',
            'Message' => "User Authenticated Successfully",
            'User_Data' => $Data,
            'Access_Token' => $access_token
        ));
    }
} else {
    echo json_response(200, array(
        'Error' => '1',
        'Message' => "Login attempt failed, try again."
    ));
}
