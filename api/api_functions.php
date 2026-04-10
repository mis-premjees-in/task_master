<?php
session_start();
include_once 'includes.php';
include_once 'custom_functions.php';

//get POST request parameters
$response = json_decode(file_get_contents('php://input'), true);

//required: username, acess_token,functio_name,parameters
$username = makeSafe(strtolower($response['username']));
$acess_token=$response['access_token'];
$decoded_token=decryptString($acess_token);
//check if user exists
$hash = sqlValue("SELECT passMD5 FROM membership_users WHERE lcase(memberID)='{$username}' AND isApproved=1 AND isBanned=0");
if (empty($hash)) {
    echo json_response(200, array(
        'Error' => '1',
        'Message' => "User does not exist or not approved or banned."
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
//get request parameters: function_name and parameters
$function_name = makeSafe(strtolower($response['function_name']));
$parameters = $response['parameters'];
//check if function exists
if (function_exists($function_name)) {
    //call function
    $result = call_user_func_array($function_name, $parameters);
    echo json_response(200, array(
        'Error' => '0',
        'Message' => 'Success',
        'Result' => $result
    ));
    exit;
} else {
    echo json_response(200, array(
        'Error' => '1',
        'Message' => 'Function does not exist.'
    ));
    exit;
}