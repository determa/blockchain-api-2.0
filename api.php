<?php 
	require_once 'Blockchain.php';
	$function=$_GET['function'];
	
	//Выполняет функцию и возвращает ответ на сервер
	if($function == 'balance') {
		$balance = checkBalance();
		if($balance) { //GET запрос
			$query = $function .'='. $balance;
		}
	}
	if($function == 'payment') {
		$address = $_GET['address'];
		$amount = $_GET['amount'];
		$fee = $_GET['fee'];
		if($address && $amount && $fee){ 
			$txid = payment($address,$amount,$fee);
			if($txid) { //GET запрос
				$query = $function .'='. $txid;
			}
		}
	}
	if($function == 'all_balance') {
		$all_balance = checkAllBalance();
		if($all_balance) { //GET запрос
			$query = $function .'='. $all_balance;
		}
	}
	if($function == 'addr') {
		$id = $_GET['id'];
		$address = generateAddress($id);
		if($address) { //GET запрос
			$query = $function .'='. $address;
		}
	}
	if($query) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, 'https://testpay.tk/action.php?'.$query); //отправляет ссылкой на сервер
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$out = curl_exec($curl);
		curl_close($curl);
		echo $out;  //вывод для проверки
	}
?>