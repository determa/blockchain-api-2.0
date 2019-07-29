<?php  
	require_once 'Blockchain.php';
	$balance = checkBalance();
	echo $balance/100000000 . "btc<br>";
	echo $balance . " in satoshi<br>";

?>
   
	
