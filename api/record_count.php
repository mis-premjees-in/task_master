<?php
session_start();
include_once 'includes.php';

//get POST request parameters
$response = json_decode(file_get_contents('php://input'), true);
//required:username
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
//store session variables
$_SESSION['memberID'] = $username;
$_SESSION['groupID'] = sqlValue("SELECT groupID FROM membership_users WHERE lcase(memberID)='{$username}'");
$_SESSION['memberGroupID'] = sqlValue("SELECT groupID FROM membership_users WHERE lcase(memberID)='{$username}'");
//api request parameters: required table_name
$table = $response['table_name'];
//Do get table records count
$sql_from = get_sql_from($table, false, true);
$count_records = ($sql_from ? sqlValue("SELECT COUNT(1) FROM " . $sql_from) : 0);
//return response
echo json_response(200, array(
    'Error' => '0',
    'Message' => "Success",
    'Table' => $table,
    'RecordCount' => $count_records
));