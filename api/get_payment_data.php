<?php

session_start();

include_once 'includes.php';



//get POST request parameters

$response = json_decode(file_get_contents('php://input'), true);



//required: username, table_name, record_id

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

if($decoded_token!==$hash){

    echo json_response(200, array(

        'Error'=>'1',

        'Message'=>"Invalid access token."

    ));

    exit;

}

else {

    $table_name = makeSafe(strtolower($response['table_name']));

    $select_fields = $response['select_fields'];

    $prepare_select=(empty($select_fields))?"*":implode(",",$select_fields);

    $custom_where = $response['custom_where'];

    $showand = $custom_where ? "AND $custom_where" : '';

    $_SESSION['memberID'] = $username;

    $_SESSION['groupID'] = sqlValue("SELECT groupID FROM membership_users WHERE lcase(memberID)='{$username}'");

    $_SESSION['memberGroupID'] = sqlValue("SELECT groupID FROM membership_users WHERE lcase(memberID)='{$username}'");

    $admins=getAdmins();

    //check if user is admin

    if(in_array($username,$admins)){

        $skip_permissions=TRUE;

    }

    else{

        $skip_permissions=FALSE;

    }

    $from_statement = get_sql_from($table_name,$skip_permissions,FALSE,FALSE);

    $page = (empty($response['page_number'])) ? 1 : $response['page_number'];

    $results_per_page = (empty($response['results_per_page'])) ? 5 : $response['results_per_page'];

    //Do calculations and determination of limit range to suply to the query

    $page_first_result = ($page > 1) ? ($results_per_page * ($page - 1)) : 0;

    $thelimit = $page_first_result . ',' . $results_per_page;

    //select all records from table

    $o = array('silentErrors' => true);

    //file_put_contents("get_data.txt","SELECT {$prepare_select} FROM {$from_statement} {$showand} LIMIT ".$thelimit);

    $records = sql("SELECT {$prepare_select} FROM {$from_statement} {$showand} LIMIT ".$thelimit, $o);

    

    $num_rows=sqlValue("SELECT COUNT(*) FROM {$from_statement} {$showand} ", $o);

    $total_pages=ceil($num_rows/$results_per_page);

    if (!$records) {

        echo json_response(200, array(

            'Error' => '1',

            'Message' => 'Error fetching records. No records found.'

        ));

        exit;

    }

    $Data = [];

    foreach ($records as $key => $value) {

        # code...

        $Data[$key] = $value;

    }

    if (empty($Data)) {

        echo json_response(200, array(

            'Error' => '1',

            'Message' => "No records found"

        ));

        exit;

    } else {

        echo json_response(200, array(

            'Error' => '0',

            'Message' => "Records found",

            'Records' => $Data,

            'Total_Records' => $num_rows,

            'Total_Pages' => $total_pages,

            'Current_Page' => $page,

            'Results_Per_Page' => $results_per_page

        ));

        exit;

    }

}

