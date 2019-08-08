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
		}//проверка токена
	}

	$bearer_token = apache_request_headers()['Authorization']; //берем данные из header authorization
	if($bearer_token) {
		$parse_jwt_token = explode(' ', $bearer_token)[1]; //отделяем токен от слова bearer
	} else {
		header("HTTP/1.1 401 Unauthorized");
		die("Bad request");
	}
	
	require_once 'config.php';
	if(authorization($parse_jwt_token,secretWord) =="ok") { //проверяем, успешна ли авторизация
	    require_once 'Blockchain.php';
	    if ($obj->function) {
			$function=$obj->function; //получаем функцию из тела запроса
	    } else {
	    	header("HTTP/1.1 401 Unauthorized");
			die("Bad request");
	    }

		$json = array('error' => $function);

	    switch ($function) {
	    	default: 
	    		$json['error'] = "unknown function";
	    		header("HTTP/1.1 401 Unauthorized");
	    		break;
	    	case 'Payment': 
		    	$address = $obj->address;
				$amount = $obj->amount;
				$fee = $obj->fee;
				if($address && $amount && $fee){
					$txid = payment($address,$amount,$fee); //выполняем функцию и получаем ответ
					if($txid) {
						if(is_array($txid)) {
							$json = array_merge(array('error' => $function),$txid);
						} else {
							$json = array( 
								'function' => $function,
								'txid' => $txid
							);
						}
					} else {
						$json['message'] = "function ".$function." did not work";
					}
				} else {
					header("HTTP/1.1 401 Unauthorized");
					die("Bad request");
				}
	    		break;
	    	case 'CheckBalance':
				$balance = checkBalance();
				if($balance) {
					if(is_array($balance)) {
							$json = array_merge(array('error' => $function),$balance);
					} else {
						$json = array( 
							'function' => $function,
							'balance' => $balance
						);
					}
				} else {
					$json['message'] = "function ".$function." did not work";
				}
				break;
			case 'CheckAddressBalance':
				$address = $obj->address;
				if($address) {
					$addr_balance = checkAddressBalance($address);
					if(is_array($addr_balance)) {
						$json = array_merge(array('error' => $function),$addr_balance);
					} else {
						$json = array( 
							'function' => $function,
							'addressBalance' => $addr_balance
						);
					}
				} else {
					$json['message'] = "function ".$function." did not work";
				}
				break;
			case 'GetAddress':
				$address = generateAddress();
				if($address) { 
					if(is_array($address)) {
						$json = array_merge(array('error' => $function),$address);
					} else {
						$json = array(
							'function' => $function,
							'address' => $address
						);
					}
				} else {
					$json['message'] = "function ".$function." did not work";
				}
				break;
	    }
	    if($json) { //вывод json
			$json_string= json_encode($json);
			echo $json_string;
		}
	}
	else {
		header("HTTP/1.1 401 Unauthorized");
		die("Bad request");
	}
?>