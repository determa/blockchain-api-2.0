<?php 
    /* Вызов функций для проверки работы сделан через ссылки */
    
    require_once 'connection.php';
    $url = explode('/',strtolower(substr($_SERVER['REQUEST_URI'], 1)));

    switch($url[0]) {
        case '': { //Если пусто в адресе, то ничего не делаем
            break;
        }    
        case 'balance':{
            require_once 'urle.php';
            $balance = checkBalance();
            echo $balance/100000000 . " btc<br>";  
            echo $balance . " in satoshi<br>"; //вывод общего баланса в сатошах
            break;
        }   
        case 'all':{
            require_once 'urle.php';
            $all_balance = checkAllBalance();
            echo $all_balance/100000000 . " btc<br>";  
            echo $all_balance . " in satoshi<br>"; //вывод общего баланса в сатошах
            break;
        }   
    } 

    /* Проверка на наличие send, адреса, суммы и комиссии */
    $str = $_SERVER['REQUEST_URI'];
    if (preg_match("/\bsend\b/i", $str)) {  //проверка на send в ссылке
        if (preg_match("/\baddress\b/i", $str)) {
            if(preg_match("/\bamount\b/i", $str)) {
                $result = explode("&", parse_url($str, PHP_URL_QUERY)); 
                $address= substr(strstr( $result[0], '='), 1, strlen($str)); //парсинг адресса и
                $amount= substr(strstr( $result[1], '='), 1, strlen($str));  // суммы платежа
                $fee = 600; //пока что фиксированная комиссия
                if($address !="" && $amount !="") {
                    require_once 'urle.php';
                    $balance = checkbalance();
                    if($amount <= $balance+$fee && $amount >=1000){
                        $txid = payment($address,$amount,$fee);
                        echo "txid = " . $txid;
                    } else {
                        echo "wrong amount of satoshi";
                    }
                }
            }
        }
    }
    if (preg_match("/\baddr\b/i", $str)) {
        if (preg_match("/\binvoice_id\b/i", $str)) {
            $invoice_id = substr(strstr( $str, '='), 1, strlen($str));
            if($invoice_id !="") {
                require_once 'urle.php';
                $address = generateAddress($invoice_id);
                echo $address;
            }
        }
    }
?>

