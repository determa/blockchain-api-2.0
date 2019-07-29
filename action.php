<?php  

	$balance = $_GET['balance']; 
	$txid = $_GET['payment']; 
	$all_balance = $_GET['all_balance'];
	$address = $_GET['addr'];

	if($balance){
		echo $balance . "<br>";
	}
	if ($txid) {
		echo $txid . "<br>";
	}
	if($all_balance) {
		echo $all_balance . "<br>";
	}
	if($address) {
		echo $address . "<br>";
	}

	//echo "ok<br>";


	

?>