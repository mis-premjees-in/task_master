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
$data = $response['data'];
// mm: can member insert record?
$arrPerm = getTablePermissions($table);
if (!$arrPerm['insert']) {
    echo json_response(200, array(
        'Error' => '1',
        'Message' => "You are not authorized to insert records."
    ));
    exit;
}
##: table before insert hook

include("{$appginilte_dir}/../hooks/{$table}.php");
//check if function $table_before_insert exists
$bif = "{$table}_before_insert";
if (function_exists($bif)) {
    $args = [];
    if (!$bif($data, getMemberInfo(), $args)) {
        if (isset($args['error_message'])) {
            echo json_response(200, array(
                'Error' => '1',
                'Message' => $args['error_message']
            ));
            exit;
        }
    }
}
$data[$table."_created"]=time();
$data[$table."_update"]=$data[$table."_created"];

//sanitize data
$data = array_map(function ($value) {
    return makeSafe($value);
}, $data);
//insert the data into the table
$o = array('silentErrors' => true);
file_put_contents("log.txt","INSERT INTO {$table} (" . implode(',', array_keys($data)) . ") VALUES ('" . implode("','", array_values($data)) . "')");
$insert = sql("INSERT INTO {$table} (" . implode(',', array_keys($data)) . ") VALUES ('" . implode("','", array_values($data)) . "')", $o);

if ($insert) {
    //save member ownsership
    $recID = db_insert_id(db_link());
    //update calculated fields
    update_calc_fields($table, $recID, calculated_fields()[$table]);
    ##: table after insert hook
    //check if function $table_after_insert exists
    $aif = "{$table}_after_insert";
    if (function_exists($aif)) {
        //get table primary key field
        $pk = getPKFieldName($table);
        $res = sql("SELECT * FROM `{$table}` WHERE `{$pk}`='" . makeSafe($recID, false) . "' LIMIT 1", $eo);
        if ($row = db_fetch_assoc($res)) {
            $data = array_map('makeSafe', $row);
        }
        $data['selectedID'] = makeSafe($recID, false);
        $args = [];
        if (!$aif($data, getMemberInfo(), $args)) {
            return $recID;
        }
    }
    // mm: save ownership data
    set_record_owner($table, $recID, getLoggedMemberID());
    //return response
    echo json_response(200, array(
        'Error' => '0',
        'recID'=>$recID,
        'Message' => "Record inserted successfully."
    ));
} else {
    echo json_response(200, array(
        'Error' => '1',
        'Message' => "Error inserting record."
    ));
}
