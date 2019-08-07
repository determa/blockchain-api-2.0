<?php  
	require_once 'connection.php';
	$json_input = file_get_contents('php://input'); //Получение json запроса 
	$obj = json_decode($json_input); 
	$user= $obj->user;
	$pass= $obj->pass;

	if($user!="" && $pass!=""){
		$connection = pg_connect ($connection_string) 
		or die(header("HTTP/1.1 500 Internal Server Error"));
		$query = 'select * from "ApiKey" where "user" = '."'".$user."'".' and "pass" = ' . "'".$pass."'";
	    $result = pg_query($connection, $query);
	    while($row=pg_fetch_assoc($result)){ //Сохраняем результат в переменные
			$username = $row['user'];
		    $password = $row['pass'];
		    $secret_key = $row['secret'];
		}
		pg_close($connection);
	}
	
	if($username!="" && $password!="" && $user==$username && $password == $pass) {
		$connection = pg_connect ($connection_string) 
		or die(header("HTTP/1.1 500 Internal Server Error"));
		$token = generate_token($username, $secret_key);
		$query = 'UPDATE "ApiKey" set "token"= '."'".$token."'".' where "user" = ' . "'".$user."'".' and "pass" = ' . "'".$pass."'";
		$result = pg_query($connection, $query);
		pg_close($connection);
		echo $token;
	} else {
		header("HTTP/1.1 401 Unauthorized");
		exit();
	}
	
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