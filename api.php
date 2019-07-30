<?php 
	require_once 'Blockchain.php';
	$json = file_get_contents('php://input'); //Получение POST запроса 
	$obj = json_decode($json);
	$function=$obj->function;

	//Выполняет функцию и возвращает ответ на сервер
	if($function == 'CheckBalance') {
		$balance = checkBalance();
		if($balance) { 
			$json = array(
				'function' => $function,
				'Balance' => $balance
			);
		}
	}
	if($function == 'Payment') {
		$address = $obj->address;
		$amount = $obj->amount;
		$fee = $obj->fee;
		if($address && $amount && $fee){ 
			$txid = payment($address,$amount,$fee);
			if($txid) {
				$json = array( //для POST запроса
					'function' => $function,
					'txid' => $txid
				);
			}
		}
	}
	if($function == 'CheckAllBalance') {
		$all_balance = checkAllBalance();
		if($all_balance) {
			$json = array( //для POST запроса
				'function' => $function,
				'AllBalance' => $all_balance
			);
		}
	}
	if($function == 'CheckAddressBalance') {
		$address = $obj->address;
		$addr_balance = checkAddressBalance($address);
		if($addr_balance) {
			$json = array( //для POST запроса
				'function' => $function,
				'AddressBalance' => $addr_balance
			);
		}
	}
	if($function == 'GetAddress') {
		$id = $_GET['id'];
		$address = generateAddress($id);
		if($address) { 
			$json = array(
				'function' => $function,
				'Address' => $address
			);
		}
	}
	if($json) { //POST запрос на указанный URL
		$json_string= json_encode($json);
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $callbackURL);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $json_string);
		$out = curl_exec($curl);
		curl_close($curl);
	}
?>