<?php  
	require_once 'connection.php';
	$json_input = file_get_contents('php://input'); //Получение json запроса 
	$obj = json_decode($json_input); 
	$user= $obj->user;
	$pass= $obj->pass;
	$match='/^[a-zA-Z0-9_-]{4,10}$/';
	
	if($user=="" || $pass==""){
		header("HTTP/1.1 401 Unauthorized");
		exit();
	}
	
	$username_valid = preg_match($match, $user) ? "true":"false";
	$password_valid = preg_match($match, $pass) ? "true":"false";

	if($username_valid !="true" || $password_valid !="true") {
		die("Bad request");
	}
	
	$connection = pg_connect ($connection_string) or die('Error connecting to DataBase');
	$query = 'select * from "ApiKey" where "user" = '."'".$user."'".' and "pass" = ' . "'".$pass."' LIMIT 1";
    $result = pg_query($connection, $query);
    if(pg_num_rows($result) ==0){
    	die("Unauthorized");
    }
	pg_close($connection);

	require_once 'config.php';
	$token = generate_token($username, secretWord);
	echo $token;

	function generate_token($username, $secret) {
		$header = array(
			"alg" => "HS256", 
			"typ" => "JWT"
		);
		$Payload = array( 
			"user" => $username
		);
		
		$encoded_header = base64_encode(json_encode($header));
		$encoded_payload = base64_encode(json_encode($Payload));

		$header_and_payload_combined = $encoded_header . '.' . $encoded_payload;

		$signature = base64_encode(hash_hmac('sha256', $header_and_payload_combined, $secret, true));
		$jwt_token = $header_and_payload_combined . '.' . $signature;
		return $jwt_token;
	}
?>