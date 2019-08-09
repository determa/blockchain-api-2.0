<?php  
	require_once 'config.php';
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
	$config = new ConfigClass();
	$connection = pg_connect ($config->connection_string) or die('Error connecting to DataBase');
	$query = 'select * from "ApiKey" where "user" = '."'".$user."'".' and "pass" = ' . "'".$pass."' LIMIT 1";
    $result = pg_query($connection, $query);
    if(pg_num_rows($result) ==0){
    	die("Unauthorized");
    }
	pg_close($connection);
	
	$token = $config->generate_token($user);
	echo $token;

	
?>