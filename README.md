# Blockchain Wallet API

## Содержание

  * [Если сервер настроен](#Если-сервер-настроен)
  * [Настройка сервера](#Настройка-сервера)
  * [Установка](#Установка)
  * [RPC API](#rpc)
  * [Installation](#installation)
  * [Troubleshooting](#troubleshooting)
  * [Usage](#usage)
  * [Development](#development)
  * [Deployment](#deployment)


## Если сервер настроен
Для использования этого API вам нужно будет запустить локальную службу, которая будет отвечать за управление Blockchain кошельком. Приложение взаимодействует с этой службой локально через вызовы HTTP API.

Для этого пишем в консоль
  1. screen -r blockchain-wallet
  2. blockchain-wallet-service start --port 3000
копируем php файлы в папку сайта и пользуемся


## Настройка сервера
Здесь будет описано как полностью установить и настроить сервер.

## Установка apache
Пишем в консоли. Я использовал ubuntu для работы сервера.
1. sudo apt update
2. sudo apt upgrade
3. sudo apt install apache2

Добавление сервера в автозагрузку 
1. sudo systemctl enable apache2
2. sudo systemctl restart apache2
3. sudo systemctl reload apache2

## Установка MySQL
Устанавливаем MySQL.
1. sudo apt install mysql-server
2. sudo mysql_secure_installation
3. mysql -u root -p

создаем бд `blockchain`
* CREATE DATABASE `blockchain` CHARACTER SET 'utf8' COLLATE 'utf8_general_ci';

* use blockchain;
Создаем нового пользователя
* GRANT ALL ON *.* to YourName@'%' IDENTIFIED BY 'password';
* FLUSH PRIVILEGES;
* exit;
4. sudo apt install mysql-client
Перезагружаем сервер
5. reboot

//-----------------------php------------------------

sudo apt-get install php
sudo apt-get install libapache2-mod-php
sudo apt-get install php-fpm

sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf

//В нем найдите параметр bind-address
bind-address            = 0.0.0.0

//сохраняем
sudo apt-get install php-mysql

sudo apt-get install phpmyadmin
sudo ln -s /etc/phpmyadmin/apache.conf /etc/apache2/conf-available/phpmyadmin.conf
sudo a2enconf phpmyadmin
sudo /etc/init.d/apache2 reload
//------------------node.js and npm------------------

sudo apt install nodejs
sudo apt install npm

//---------------Blockchain Wallet API---------------

npm install -g blockchain-wallet-service
npm update -g blockchain-wallet-service

//------------------------MySQL----------------------

//создание таблиц
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

//--------------для работы через ссылки--------------

//-----------настройка apache2 и .htaccess-----------

/* настройка apache2 и .htaccess для сокращения ссылок */
/* (вместо domen/address.php domen/address) */
/* подключаем модуль mod_rewrite */

sudo a2enmod rewrite
sudo service apache2 restart

//включаем поддержку файлов .htaccess

sudo nano /etc/apache2/sites-enabled/000-default.conf

/* Ниже блока <VirtualHost *:80> */
/* вставляем следующий код: */

<Directory /var/www/html>
Options Indexes FollowSymLinks MultiViews
AllowOverride All
Order allow,deny
allow from all
</Directory>

/* Где /var/www/html путь к сайту */
/* Сохраняем файл и перезагружаем apache */

sudo service apache2 restart

/* Создание файла .htaccess */

sudo nano /var/www/html/.htaccess

/* вписываем туда код: */

RewriteEngine On
RewriteBase /
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^.*$ index.php [NC,L]

/*
^ начало строки
$ конец строки
. любой одиночный символ
* ноль или N предшествующих символов (N > 0)
‘nocase|NC’ не учитывать регистр
‘last|L’ последнее правило
*/

//Перекидываем php файлы в /var/www/html
//----------------------------------------------
