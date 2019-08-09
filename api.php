<?php 
	require_once 'config.php';
	$json_input = file_get_contents('php://input'); //Получение POST запроса 
	$obj = json_decode($json_input);
	echo $_POST['function'];
	$bearer_token = apache_request_headers()['Authorization']; //берем данные из header authorization
	if($bearer_token) {
		$parse_jwt_token = explode(' ', $bearer_token)[1]; //отделяем токен от слова bearer
	} else {
		header("HTTP/1.1 400 Bad request");
		exit;
	}

	$config = new ConfigClass();
	if($config->authorization($parse_jwt_token) =="ok") { //проверяем, успешна ли авторизация
	    require_once 'Blockchain.php';
	    $BlockchainFunction = new Blockchain();
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
					$txid = $BlockchainFunction->payment($address,$amount,$fee); //выполняем функцию и получаем ответ
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
					header("HTTP/1.1 400 Bad request");
					die("Bad request");
				}
	    		break;
	    	case 'CheckBalance':
				$balance = $BlockchainFunction->checkBalance();
				if($balance >=0) {
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
					$addr_balance = $BlockchainFunction->checkAddressBalance($address);
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
				$address = $BlockchainFunction->generateAddress();
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
		exit;
	}
?>