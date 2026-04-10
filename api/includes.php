<?php
$appginilte_dir = dirname(__FILE__);
include("{$appginilte_dir}/../defaultLang.php");
include("{$appginilte_dir}/../language.php");
include("{$appginilte_dir}/../lib.php");

function json_response($code = 200, $response = "null")
{
  //USAGE
  /*echo json_response(200, array(
      'Receiver' =>$Receiver,
      'Message'=>$Message,
      'username'=>$username,
      'apiKey'=>$apiKey
  ));*/
  //echo json_response(200,'Data received successfully');
  //echo json_response(500,'Server Error! Please Try Again!');

  // clear the old headers
  header_remove();
  // set the actual code
  http_response_code($code);
  // set the header to make sure cache is forced
  header("Cache-Control: no-transform,public,max-age=300,s-maxage=900");
  // treat this as json
  header('Content-Type: application/json');
  // ok, validation error, or failure
  header('Status: ' . $code);
  // return the encoded json
  return json_encode(array(
    'status' => $code < 300, // success or not?
    'response' => $response
  ));
}

function sendRequest($url, $data)
{
  //Initiate cURL.
  $ch = curl_init($url);

  //Tell cURL that we want to send a POST request.
  curl_setopt($ch, CURLOPT_POST, 1);

  //Attach our encoded JSON string to the POST fields.
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

  //Set the content type to application/json.
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

  //Dont return result to screen,store in a variable.
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  //Execute the request.
  $result = curl_exec($ch);
  return $result;
}

function sendemail($recipient, $subject, $body, $attachment_path = '', $attachment_name = '')
{
  $recipients = explode(",", $recipient);
  $sent = 0;
  foreach ($recipients as $recipient) {
    $mail = [
      "to" => $recipient,
      "message" => $body,
      "subject" => $subject,
      "tag" => ["attachment_path" => $attachment_path, "attachment_name" => $attachment_name]
    ];
    $send = sendmail($mail);
    $sent = ($send == true) ? $sent + 1 : $sent;
  }
  if ($sent > 0) {
    return true;
  } else {
    return false;
  }
}

function strong_password($pass)
{
  // min length 8 chars
  if (strlen($pass) < 8) return false;

  $no_shows = 0;

  // count of upper case
  $upper_case = preg_match_all('/[A-Z]{1}/', $pass);
  if (!$upper_case) $no_shows++;

  // count of lower case
  $lower_case = preg_match_all('/[a-z]{1}/', $pass);
  if (!$lower_case) $no_shows++;

  // count of numbers
  $numbers = preg_match_all('/[0-9]{1}/', $pass);
  if (!$numbers) $no_shows++;

  // count of special characters
  $special = preg_match_all('/[^0-9a-zA-Z]{1}/', $pass);
  if (!$special) $no_shows++;

  // must have at least one character of at least 3 of the above 4 classes
  if ($no_shows > 1) return false;

  return true;
}

function encyptString($simple_string)
{

	// Store the cipher method
	$ciphering = "AES-128-CTR";

	// Use OpenSSl Encryption method
	$options = 0;

	// Non-NULL Initialization Vector for encryption
	$encryption_iv = '1234567891011121'; //314151617181920

	// Store the encryption key
	$encryption_key = "AppginiEncryptionKey";

	// Use openssl_encrypt() function to encrypt the data
	$encryption = openssl_encrypt(
		$simple_string,
		$ciphering,
		$encryption_key,
		$options,
		$encryption_iv
	);

	// Display the encrypted string
	return  $encryption;
}

function decryptString($encryption){
	// Store the cipher method
	$ciphering = "AES-128-CTR";

	// Use OpenSSl Encryption method
	$options = 0;
	// Non-NULL Initialization Vector for decryption
	$decryption_iv = '1234567891011121'; //314151617181920

	// Store the decryption key
	$decryption_key = "AppginiEncryptionKey";

	// Use openssl_decrypt() function to decrypt the data
	$decryption = openssl_decrypt(
		$encryption,
		$ciphering,
		$decryption_key,
		$options,
		$decryption_iv
	);

	// Display the decrypted string
	return $decryption;
}

function getAdmins()
{
  //get Admins groupID
  $groupID = sqlValue("SELECT groupID FROM membership_groups WHERE name='Admins'");
  //get Admins members
  $admins = sql("SELECT memberID FROM membership_users WHERE groupID='$groupID'",$eo);
  $admin_names=array();
  foreach ($admins as $admin) {
    $admin_names[] = $admin['memberID'];
  }
  return $admin_names;
}