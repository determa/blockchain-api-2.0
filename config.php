<?php  

	class ConfigClass
	{
		private const secretWord = "bJLX86zq3mZvVseZ";
		private const host = "localhost";
		private const user = "jeka";
		private const pass = "PvL2O8WRD";
		private const db = "blockchain";
		private const port = "5432";
		public $connection_string = "host=".self::host." port=".self::port." dbname=".self::db." user=".self::user." password=".self::pass;
		
		public function generate_token($username) { //генерация токена
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

			$signature = base64_encode(hash_hmac('sha256', $header_and_payload_combined, self::secretWord, true));
			$jwt_token = $header_and_payload_combined . '.' . $signature;
			return $jwt_token;
		}
		public function authorization($jwt_token) { //проверка токена на подлинность
			$jwt_values = explode('.', $jwt_token); //отделение header payload и signature
		
			$recieved_signature = $jwt_values[2];
			$recieved_header_and_payload = $jwt_values[0] . '.' . $jwt_values[1];
			 
			$what_signature_should_be = base64_encode(hash_hmac('sha256', $recieved_header_and_payload, self::secretWord, true));
			if($what_signature_should_be == $recieved_signature) { 
			//сравнение header+payload с signature, если правильные, возвращаем ок
			    return "ok";
			}
		}
	}
	
?>