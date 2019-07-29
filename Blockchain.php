<?php  

	require_once 'connection.php';

	$mysqli = new mysqli("$ip_bd", "$name", "$pass"); // Подключение к MySQL
	if (mysqli_connect_errno()) {
	    printf("error connect data base: %s\n", mysqli_connect_error());
	    exit();
	} 
	$selected = mysqli_select_db($mysqli, $db); //Подключение к БД
	if(!$selected) { 
		echo 'Подключение к БД: ERROR<br>';
	} 

	$sql= "select * from `config`"; // форрмируем запрос к таблице
	$res=mysqli_query($mysqli, $sql); //выполняем данный запрос
	while($row=mysqli_fetch_assoc($res)){ //Сохраняем результат в переменные
		$domen = $row['domen'];
		$password = $row['firstpassword'];
	    $xpub = $row['xpub'];
	    $api_key = $row['api_key'];
	    $guid = $row['guid']; 
		$port = $row['port'];
	}
	$root_url = 'http://'.$domen.':'.$port.'/merchant/'.$guid.'/';
//-----------------------------------------------------------------------------
	function payment($address,$amount,$fee) { //Функция отправки платежа, нужно указать адресс, сумму и комсу
		global $password, $root_url;
		$parameters = 'password='.$password.'&to='.$address.'&amount='.$amount.'&fee='.$fee;
		//отправка запроса на blockchain и получение разультата
		$response = file_get_contents($root_url . 'payment?' . $parameters); 
		$object = json_decode($response); //преобразование данных в json
		/* Если адресс пришел возвращаем txid */
		if($object->to) { 
			if($object->success == 1) {
				return $object->txid;
			}
		} else { //или ошибку
			return "Sending failed";
		}
	};
	function checkAllBalance() { //Общий баланс вместе с xpub кошельками, если они есть
		global $password, $root_url;
		$parameters = 'password=' . $password;
		$response = file_get_contents($root_url . 'balance?' . $parameters); 
		$object = json_decode($response); //преобразование в json
		$total_balance = $object->balance;
		return $total_balance;
	};
	function checkBalance() { //баланс только адрессов пользователей
		global $password, $root_url;
		$parameters = 'password=' . $password;
		//отправка запроса на blockchain и получение разультата
		$response = file_get_contents($root_url . 'list?' . $parameters); 
		$object = json_decode($response); //преобразование в json

		$countAddr = count($object->addresses); //количество адрессов
		$total_balance = 0;
		for ($i=0; $i < $countAddr; $i++) { //подсчет общего баланса
			$total_balance += $object->addresses[$i]->balance;
		}
		return $total_balance;
	};
	function generateAddress($id) { //пока только id, для безопасности лучше еще использовать secret
		global $password, $root_url, $mysqli, $sql;
		$parameters = 'password='.$password;
		$response = file_get_contents($root_url . 'new_address?' . $parameters); 
		$object = json_decode($response); //преобразование в json
		if($object->address) { 
			/* формируем запрос к таблице */
			$sql= "replace INTO address (`newAddress`, `id_user`) 
					VALUES ('$object->address','$id')";
			$result = mysqli_query($mysqli, $sql);
		}
		return $object->address;
	}
?>