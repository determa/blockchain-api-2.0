<?php 
	require_once 'connection.php';
	$json_input = file_get_contents('php://input'); //Получение POST запроса 
	$obj = json_decode($json_input);

	function authorization($jwt_token,$secret) {
		$jwt_values = explode('.', $jwt_token); //отделение header payload и signature
	 
		$recieved_signature = $jwt_values[2];
		$recieved_header_and_payload = $jwt_values[0] . '.' . $jwt_values[1];
		 
		$what_signature_should_be = base64_encode(hash_hmac('sha256', $recieved_header_and_payload, $secret, true));
		if($what_signature_should_be == $recieved_signature) { 
		//сравнение header+payload с signature, если правильные, возвращаем ок
		    return "ok";
		}
	}

	$bearer_token = apache_request_headers()['Authorization']; //берем данные из header authorization
	$parse_token = explode(' ', $bearer_token)[1]; //отделяем токен от слова bearer
	$token_values = explode('.', $parse_token); //парсим payload для получения user`a
	$obj_user = json_decode(base64_decode($token_values[1])); //декодируем payload
	$user = $obj_user->user; //получаем user

	if($user!=""){ //получаем secret из БД по данным user и token
		$connection = pg_connect ($connection_string) 
		or die(header("HTTP/1.1 500 Internal Server Error"));
		$query = 'select * from "ApiKey" where "user" = '."'".$user."'".' and "token" = ' . "'".$parse_token."'";
	    $result = pg_query($connection, $query);
	    while($row=pg_fetch_assoc($result)){ //Сохраняем результат в переменные
		    $secret_key = $row['secret'];
		}
		pg_close($connection);
	}

	if(authorization($parse_token,$secret_key) =="ok") { //проверяем, успешна ли авторизация
	    require_once 'Blockchain.php';
		$function=$obj->function; //получаем функцию из тела запроса
		//Выполняет функцию и возвращает ответ на сервер
		if($function == 'Payment') {
			$address = $obj->address;
			$amount = $obj->amount;
			$fee = $obj->fee;
			if($address && $amount && $fee){ 
				$txid = payment($address,$amount,$fee); //выполняем функцию и получаем ответ
				if($txid) {
					$json = array( 
						'function' => $function,
						'txid' => $txid
					);
				}
			}
		}
		if($function == 'CheckBalance') {
			$balance = checkBalance();
			if($balance) {
				$json = array( 
					'function' => $function,
					'Balance' => $balance
				);
			}
		}
		if($function == 'CheckAddressBalance') {
			$address = $obj->address;
			$addr_balance = checkAddressBalance($address);
			if($addr_balance) {
				$json = array( 
					'function' => $function,
					'AddressBalance' => $addr_balance
				);
			}
		}
		if($function == 'GetAddress') {
			$address = generateAddress();
			if($address) { 
				$json = array(
					'function' => $function,
					'Address' => $address
				);
			}
		}
		if($json) { //вывод json
			$json_string= json_encode($json);
			echo $json_string;
		}
	} else {
		header("HTTP/1.1 401 Unauthorized");
		exit();
	}
?>