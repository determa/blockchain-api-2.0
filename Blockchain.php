<?php  
	//Подключение к БД произойдет только при вызове функций через api
	$connection = pg_connect ($connection_string) 
	or die(header("HTTP/1.1 401 Unauthorized"));
	$query = "select * from config";
    $result = pg_query($connection, $query);
    while($row=pg_fetch_assoc($result)){ //Сохраняем результат в переменные
		$password = $row['password'];
	    $api_key = $row['api_key'];
	    $guid = $row['guid']; 
	}
	pg_close($connection);
	$root_url = 'http://localhost:3000/merchant/'.$guid.'/';
	
	function curl($func, $param) {
		global $root_url;
		$ch = curl_init($root_url . $func . $param);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HEADER, false);
		$response = curl_exec($ch);
		curl_close($ch);
		return $response;
	}

	function payment($address,$amount,$fee) { //Функция отправки платежа, нужно указать адресс, сумму и комсу
		//для безопасности лучше еще использовать secret, который необходимо сравнить с бд
		global $password;
		$parameters = 'password='.$password.'&to='.$address.'&amount='.$amount.'&fee='.$fee;
		//отправка запроса на blockchain и получение разультата
		$response = curl('payment?',$parameters);
		$object = json_decode($response); //преобразование данных в json
		if($object->error) {
			return array("message" => $object->error);
		} else {
			if($object->to) { // Если адресс пришел возвращаем txid 
				if($object->success == 1) {
					return $object->txid;
				} else {
					return array("message" =>"success = false");
				}
			} else { //или ошибку
				return array("message" =>"no address");
			}
		}
	}
	function checkBalance() { //Общий баланс вместе с xpub кошельками, если они есть
		global $password;
		$parameters = 'password=' . $password;
		$response = curl('balance?',$parameters);
		$object = json_decode($response); //преобразование в json
		$total_balance = $object->balance;
		if($object->error) {
			return array("message" => $object->error);
		} else {
			if($total_balance ==null) $total_balance =0;
			return $total_balance;
		}
	}

	function checkAddressBalance($address) { //Баланс определенного адреса
		global $password;
		$parameters = 'password=' . $password . '&address=' . $address;
		$response = curl('address_balance?',$parameters);
		$object = json_decode($response); //преобразование в json
		if($object->error) {
			return array("message" => $object->error);
		} else {
			$total_balance = $object->balance;
			if($total_balance ==null) $total_balance =0;
			return $total_balance;
		}
	}
	function generateAddress() { 
		global $password;
		$parameters = 'password='.$password;
		$response = curl('new_address?',$parameters);
		$object = json_decode($response); //преобразование из json
		if($object->error) {
			return array("message" => $object->error);
		} else {
			return $object->address;
		}
	}
?>