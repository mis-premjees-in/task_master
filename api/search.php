<?php
session_start();
include_once 'includes.php';

//get POST request parameters
$response = json_decode(file_get_contents('php://input'), true);

//required: username, table_name, record_id
$username = makeSafe(strtolower($response['username']));
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
} else {
    $tn = makeSafe(strtolower($response['table_name']));
    $search = makeSafe($response['search_term']);
    $page = (empty($response['page_number'])) ? 1 : $response['page_number'];
    $results_per_page = (empty($response['results_per_page'])) ? 5 : $response['results_per_page'];
    //Do calculations and determination of limit range to suply to the query
    $page_first_result = ($page > 1) ? ($results_per_page * ($page - 1)) : 0;
    $thelimit = $page_first_result . ',' . $results_per_page;
    //session variables
    $_SESSION['memberID'] = $username;
    $_SESSION['groupID'] = sqlValue("SELECT groupID FROM membership_users WHERE lcase(memberID)='{$username}'");
    $_SESSION['memberGroupID'] = sqlValue("SELECT groupID FROM membership_users WHERE lcase(memberID)='{$username}'");
    //Perform validation
    if (!$search) {
        echo json_response(200, array(
            'Error' => '1',
            'Message' => "Please provide a search term."
        ));
        exit;
    }
    $fields = list_of_fields($tn);
    if (!$fields) {
        echo json_response(200, array(
            'Error' => '1',
            'Message' => "No fields found for the given table."
        ));
        exit;
    }
    //create search query
    $safe_search = makeSafe($search);
    $where = " AND CONCAT_WS('||', " . implode(', ', $fields) . ") LIKE '%{$safe_search}%'";
    $pk = "`{$tn}`.`" . getPKFieldName($tn) . "` as 'PRIMARY_KEY_VALUE'";
    $query = "SELECT {$pk}, " . get_sql_fields($tn) . " FROM " . get_sql_from($tn) . $where . " LIMIT {$thelimit}";
    //count all records without limit
    $count_query = "SELECT COUNT(*) FROM " . get_sql_from($tn) . $where;
    $count = sqlValue($count_query);
    $total_pages = ceil($count / $results_per_page);
    /* perform search */
    $results = array();
    $eo = array('silentErrors' => true);
    $res = sql($query, $eo);
    while ($row = db_fetch_assoc($res)) {
        $results[] = array(
            'id' => $row['PRIMARY_KEY_VALUE'],
            'record' => array_slice($row, 1, NULL, true)
        );
    }
    //if no result found show eror response
    if (empty($results)) {
        echo json_response(200, array(
            'Error' => '1',
            'Message' => "No results found."
        ));
        exit;
    }
    //return the results found from table
    echo json_response(200, array(
        'Error' => '0',
        'Message' => "Search results found.",
        'Results' => $results,
        'Total_Records' => $count,
        'Total_Pages' => $total_pages,
        'Current_Page' => $page,
        'Results_Per_Page' => $results_per_page
    ));
}

/* function to get a list of query fields of a given table */
function list_of_fields($tn)
{
    $fields = preg_split('/ as \'.*?\',? ?/', get_sql_fields($tn));
    if (!count($fields) || $fields === false) return false;

    array_pop($fields); // remove last element as it's an empty string
    return $fields;
}
