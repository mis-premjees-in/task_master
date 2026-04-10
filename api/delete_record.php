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
//api request parameters: required table_name,data
$table = $response['table_name'];
$selected_id = $response['selected_id'];
//get table primary key field
$pk = getPKFieldName($table);
// mm: can member delete record?
if (!check_record_permission($table, $selected_id, 'delete')) {
    echo json_response(200, array(
        'Error' => '1',
        'Message' => "You are not authorized to delete this record."
    ));
    exit;
}
//check if record exists
$record_exists = sqlValue("SELECT COUNT(1) FROM `{$table}` WHERE `{$pk}`='{$selected_id}'");
if (!$record_exists) {
    echo json_response(200, array(
        'Error' => '1',
        'Message' => "Record does not exist."
    ));
    exit;
}
##: table before delete hook
include("{$appginilte_dir}/../../hooks/{$table}.php");
$bdf="{$table}_before_delete";
if(function_exists($bdf)) {
    $args = [];
    $skipChecks = false;
    if(!$bdf($selected_id,$skipChecks, getMemberInfo(), $args))
    {
       echo json_response(200, array(
            'Error' => '1',
            'Message' => "Record could not be deleted."
        ));
        exit;
    }
}
//delete the data from the table
$delete = sql("DELETE FROM {$table} WHERE {$pk}='{$selected_id}'", $eo);
if($delete){
##: table after delete hook
$adf = "{$table}_after_delete";
if(function_exists($adf)) {
    $args = [];
    $adf($selected_id, getMemberInfo(), $args);
}
// mm: delete ownership data
sql("DELETE FROM `membership_userrecords` WHERE `tableName`='$table' AND `pkValue`='{$selected_id}'", $eo);
//return response
echo json_response(200, array(
    'Error' => '0',
    'Message' => "Record deleted successfully."
));
exit;
}else{
    echo json_response(200, array(
        'Error' => '1',
        'Message' => "Record could not be deleted."
    ));
    exit;
}
