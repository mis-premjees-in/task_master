<?php
include_once 'includes.php';

//get groups from database
$groups=sql("SELECT * FROM membership_groups",$eo);
$Data=[];
foreach ($groups as $key => $value) {
    # code...
    $Data[$key]['groupID']=$value['groupID'];
    $Data[$key]['name']=$value['name'];
    $Data[$key]['description']=$value['description'];
    $Data[$key]['allowSignup']=$value['allowSignup'];
}
echo json_response(200, array(
    'Error'=>'0',
    'Message' =>"Groups Fetched Successfully",
    'Groups'=>$Data
));