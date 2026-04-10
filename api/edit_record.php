<?php
session_start();
include_once 'includes.php';

//get POST request parameters
$response = json_decode(file_get_contents('php://input'), true); 
include 'default_api.creds.php';
//required:username
$username=makeSafe(strtolower($response['username']??$default_user));
$acess_token = $response['access_token'];
$decoded_token = decryptString($acess_token);
//check if user exists
$hash = sqlValue("SELECT passMD5 FROM membership_users WHERE lcase(memberID)='{$username}' AND isApproved=1 AND isBanned=0");
if (empty($hash)) {
    echo json_response(200, array(
        'Error' => '1',
        'Message' => "User does not exist or not approved or banned."
    ));
    exit;
}
if ($decoded_token !== $hash) {
    echo json_response(200, array(
        'Error' => '1',
        'Message' => "Invalid access token."
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
$data = $response['data'];
//get table primary key field
$pk = getPKFieldName($table);
// mm: can member edit record?
file_put_contents("edit_log.txt",$table);
file_put_contents("edit_log.txt",$selected_id,FILE_APPEND);
if (!check_record_permission($table, $selected_id, 'edit')) {
    echo json_response(200, array(
        'Error' => '1',
        'Message' => "You are not authorized to edit this record."
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
// get existing values
$old_data = getRecord($table, $selected_id);
if (is_array($old_data)) {
    $old_data = array_map('makeSafe', $old_data);
    $old_data['selectedID'] = makeSafe($selected_id);
}
##: table before update hook
include("{$appginilte_dir}/../../hooks/{$table}.php");
//check if function $table_before_update exists
$buf = "{$table}_before_update";
$data['selectedID'] = makeSafe($selected_id);
if (function_exists($buf)) {
    $args = ['old_data' => $old_data];
    if (!$buf($data, getMemberInfo(), $args)) {
        if (isset($args['error_message'])) {
            echo json_response(200, array(
                'Error' => '1',
                'Message' => $args['error_message']
            ));
            exit;
        }
    }
}
//remove selectedID from data
unset($data['selectedID']);
//sanitize data
// $data[$table."_created"]=date("Y-m-d H:i:s");
// $data[$table."_updated"]=$data[$table."_created"];

$data = array_map(function ($value) {
    return makeSafe($value);
}, $data);
//update the data into the table
$o = array('silentErrors' => true);
$update = sql("UPDATE {$table} SET " . implode(',', array_map(function ($key, $value) {
    return "{$key}='{$value}'";
}, array_keys($data), array_values($data))) . " WHERE {$pk}='{$selected_id}'", $o);
file_put_contents("edit_log.txt", "UPDATE {$table} SET " . implode(',', array_map(function ($key, $value) {
    return "{$key}='{$value}'";
}, array_keys($data), array_values($data))) . " WHERE {$pk}='{$selected_id}'", FILE_APPEND);
if ($update) {
    //update calculated fields
    update_calc_fields($table, $selected_id, calculated_fields()[$table]);
    // hook: $table_after_update
    $auf = "{$table}_after_update";
    if (function_exists($auf)) {
        $res = sql("SELECT * FROM `{$table}` WHERE `{$pk}`='{$selected_id}' LIMIT 1", $eo);
        if ($row = db_fetch_assoc($res)) $data = array_map('makeSafe', $row);

        $data['selectedID'] = $data[$pk];
        $args = ['old_data' => $old_data];
        if (!$auf($data, getMemberInfo(), $args)) {
            return false;
        }
    }
    // mm: update ownership data
    sql("UPDATE `membership_userrecords` SET `dateUpdated`='" . time() . "' WHERE `tableName`='$table' AND `pkValue`='" . makeSafe($selected_id) . "'", $eo);
    //return response
    echo json_response(200, array(
        'Error' => '0',
        'Message' => "Record updated successfully."
    ));
} else {
    echo json_response(200, array(
        'Error' => '1',
        'Message' => "Record update failed."
    ));
}
