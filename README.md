# Blockchain Wallet API

## Содержание

  * [Если сервер настроен](#Если-сервер-настроен)
  * [Функции работы с blockchain](##Функции-работы-с-blockchain)
  * [Настройка сервера](#Настройка-сервера)
  * [Установка apache](##Установка-apache)
  * [Установка MySQL](##Установка-MySQL)
  * [Установка PHP](##Установка-PHP)
  * [Установка nodejs и npm](##Установка-nodejs-и-npm)
  * [Установка Blockchain Wallet API](##Установка-Blockchain-Wallet-API)
  * [Создание таблиц MySQL](##Создание-таблиц-MySQL)


## Если сервер настроен
Для использования этого API вам нужно будет запустить локальную службу, которая будет отвечать за управление Blockchain кошельком. Приложение взаимодействует с этой службой локально через вызовы HTTP API.

Если вы не установили локальную службу blockchain, перейдите в [Установка Blockchain Wallet API](##Установка-Blockchain-Wallet-API)

Для этого пишем в консоль
  1. `$ screen -r blockchain-wallet`
  2. `$ blockchain-wallet-service start --port 3000`

Копируем php файлы в папку сайта.

В файл `connection.php` вписываем данные от бд. 

В БД, в таблице config вносите данные от blockchain.

Файл `Blockchain.php` содержит несколько функций

### Функции работы с blockchain
#### Функция отправки платежа
Для отправки платежа используйте функцию `payment`, где

1. `$address` - адрес, на который нужно совершить платеж
2. `$amount` - количество btc в сатоши, которые нужно перевести
3. `$fee` - комиссия

Данная функция отдаст результат с `txid` транзакции или вернет ошибку `Sending failed`
```php
$result = payment($address,$amount,$fee)
```
#### Проверка общего баланса всех кошельков
Для получения общего баланса вместе с xpub кошельками, если они есть используйте функцию `checkAllBalance`

Данная функция вернет баланс всех ваших кошельков в сатоши.
```php
$balance = checkAllBalance();
```

#### Проверка общего баланса без вашего кошелька
Для получения баланса только адрессов пользователей(без вашего xpub кошелька) в сатошах используйте функцию `checkBalance`

```php
$balance = checkBalance();
```
#### Генерация нового адреса
Данная функция генерирует новый адрес для пользователя по id. Для лучшей безопасности лучше добавить также secret(сгенерированный идентификатор пользователя)

```php
$address = generateAddress($id);
```
Также адреса сохраняются в бд.

## Настройка сервера
Здесь будет описано как полностью установить и настроить сервер.

### Установка apache
Пишем в консоли. Я использовал ubuntu для работы сервера.
1. `$ sudo apt update`
2. `$ sudo apt upgrade`
3. `$ sudo apt install apache2`

Добавление сервера в автозагрузку
1. `$ sudo systemctl enable apache2`
2. `$ sudo systemctl restart apache2`
3. `$ sudo systemctl reload apache2`

### Установка MySQL
Устанавливаем MySQL.
1. `$ sudo apt install mysql-server`
2. `$ sudo mysql_secure_installation`
3. `$ mysql -u root -p`

Создаем бд blockchain
* `mysql> CREATE DATABASE blockchain CHARACTER SET 'utf8' COLLATE 'utf8_general_ci';`

* `mysql> use blockchain;`
Создаем нового пользователя
* Создаем нового пользователя `mysql> GRANT ALL ON *.* to YourName@'%' IDENTIFIED BY 'password';`
* Обновляем права пользователя `mysql> FLUSH PRIVILEGES;`
* `mysql> exit;`

4. `$ sudo apt install mysql-client`
5. Перезагружаем сервер `$ reboot`

### Установка PHP

1. `$ sudo apt-get install php`
2. `$ sudo apt-get install libapache2-mod-php`
3. `$ sudo apt-get install php-fpm`

4. Заходим в файл конфигурации `sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf`

В нем найдите параметр bind-address и смените его на 
`bind-address            = 0.0.0.0`
Cохраняем.

Если не установился mysql, делаем следующие действия:
1. `$ sudo apt-get install php-mysql`

1. `$ sudo apt-get install phpmyadmin`
2. `$ sudo ln -s /etc/phpmyadmin/apache.conf /etc/apache2/conf-available/phpmyadmin.conf`
3. `$ sudo a2enconf phpmyadmin`
4. `$ sudo /etc/init.d/apache2 reload`
Мне помогла только установка phpMyAdnin чтобы заработал mysql

### Установка nodejs и npm

1. `$ sudo apt install nodejs`
2. `$ sudo apt install npm`

### Установка Blockchain Wallet API

1. `$ npm install -g blockchain-wallet-service`
2. `$ npm update -g blockchain-wallet-service`

### Создание таблиц MySQL

Возможно таблицы будут меняться
```MySQL
CREATE TABLE `blockchain`.`address`  (
  `address_id` int(0) NOT NULL AUTO_INCREMENT,
  `newAddress` varchar(255) NULL,
  `id_user` int(0) NULL,
  `secret` varchar(255) NULL,
  PRIMARY KEY (`address_id`)
);
CREATE TABLE `blockchain`.`config`  (
  `id` int(0) NOT NULL AUTO_INCREMENT,
  `domen` varchar(255) NULL,
  `port` int(5) NULL,
  `guid` varchar(255) NULL,
  `firstpassword` varchar(255) NULL,
  `xpub` varchar(255) NULL,
  `api_key` varchar(255) NULL,
  PRIMARY KEY (`id`)
);
CREATE TABLE `blockchain`.`callback`  (
  `callback_id` int(0) NOT NULL AUTO_INCREMENT,
  `transaction_hash` varchar(255) NULL,
  `address` varchar(255) NULL,
  `confirmations` varchar(255) NULL,
	`value` varchar(255) NULL,
  `invoice_id` varchar(255) NULL,
  `id_user` varchar(255) NULL,
  PRIMARY KEY (`callback_id`)
);
```
