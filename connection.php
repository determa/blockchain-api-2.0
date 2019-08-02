<?php  
	$host = "localhost";
	$user = "jeka";
	$pass = "PvL2O8WRD";
	$db = "blockchain";
	$port = "5432";

	//Подключение к БД
	$connection = pg_connect ('host='.$host.' port='.$port.' dbname='.$db.' user='.$user.' password='.$pass) 
	or die("Could not open connection to database server");
?>