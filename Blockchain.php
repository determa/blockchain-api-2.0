<?php  
	//require_once 'config.php'; //вызывается в api.php
	class BlockChain
	{
		private function connection() {
			$config = new ConfigClass();
			$connection = pg_connect($config->connection_string) or die(header("HTTP/1.1 401 Unauthorized"));
			$query = 'select * from "config" limit 1';
			$result = pg_query($connection, $query);
			while ($row = pg_fetch_assoc($result)) {
				$password = $row['password'];
				$guid = $row['guid'];
			}
			pg_close($connection);
			$root_url = 'http://localhost:3000/merchant/'.$guid.'/';
			return array($root_url,$password);
		}
		private function curl($func, $param) { //отправка функции и параметров в блокчейн и получение результата
			$ch = curl_init($this->connection()[0] . $func . $param);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_HEADER, false);
			$response = curl_exec($ch);
			curl_close($ch);
			return $response;
		}
		public function payment($address,$amount,$fee) { //Функция отправки платежа, нужно указать адресс, сумму и комсу
			//для безопасности лучше еще использовать secret, который необходимо сравнить с бд
			$parameters = 'password='.$this->connection()[1].'&to='.$address.'&amount='.$amount.'&fee='.$fee;
			//отправка запроса на blockchain и получение разультата
			$response = $this->curl('payment?',$parameters);
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
		public function checkBalance() { //Общий баланс вместе с xpub кошельками, если они есть
			$parameters = 'password=' . $this->connection()[1];
			$response = $this->curl('balance?',$parameters);
			$object = json_decode($response); //преобразование в json
			$total_balance = $object->balance;
			if($object->error) {
				return array("message" => $object->error);
			} else {
				if($total_balance ==null) $total_balance =0;
				return $total_balance;
			}
		}
		public function checkAddressBalance($address) { //Баланс определенного адреса
			$parameters = 'password=' . $this->connection()[1] . '&address=' . $address;
			$response = $this->curl('address_balance?',$parameters);
			$object = json_decode($response); //преобразование в json
			if($object->error) {
				return array("message" => $object->error);
			} else {
				$total_balance = $object->balance;
				if($total_balance ==null) $total_balance =0;
				return $total_balance;
			}
		}
		public function generateAddress() { //Генерация адреса
			$parameters = 'password='.$this->connection()[1];
			$response = $this->curl('new_address?',$parameters);
			$object = json_decode($response); //преобразование из json
			if($object->error) {
				return array("message" => $object->error);
			} else {
				return $object->address;
			}
		}
	}	
?>