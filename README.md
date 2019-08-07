# Blockchain Wallet API

## Содержание

  * [Если сервер настроен](#Если-сервер-настроен)
  * [Использование](#Использование)
  * [Настройка сервера](#Настройка-сервера)


## Если сервер настроен

Для использования этого API вам нужно будет запустить локальную службу, которая будет отвечать за управление Blockchain кошельком. Приложение взаимодействует с этой службой локально через вызовы HTTP API.

Если вы не установили локальную службу blockchain, перейдите в [Установка Blockchain Wallet API](#Установка-Blockchain-Wallet-API)

Для запуска локальной службы пишем в консоль
1. `$ screen -S blockchain-wallet blockchain-wallet-service start --port 3000`

Копируем php файлы в папку сайта.

В файл `connection.php` вписываем данные от бд.

В БД, в таблице config вносите данные от blockchain.

Файл `Blockchain.php` содержит функции работы с blockchain.

Для получения токена используйте файл login.php, отправьте в него тело json объекта вида 
```json
{"user":"YOUR_USER_NAME","pass":"YOUR_PASSWORD"}
```

## Использование

### Вызов функций
Для получения результата неоходимо отправить запрос в файл `api.php` с параметрами в формате json
Также нужно отправить токен в заголовке bearer token, который вы должны получить в login.php

Для получения **общего баланса вместе с xpub кошельками**, если они есть используйте функцию `CheckBalance`. Отправте json запрос в `api.php`
```json
{"function":"CheckBalance"}
```
Данная функция вернет баланс всех ваших кошельков в сатоши.

Для **отправки** платежа используйте функцию `Payment`, где

1. `address` - адрес, на который нужно совершить платеж
2. `amount` - количество btc в сатоши, которые нужно перевести
3. `fee` - комиссия в сатоши
```json
{"function":"Payment","address":"YOUR_ADDRESS","amount":2000,"fee":600}
```
Данная функция отдаст результат с `txid` транзакции или вернет ошибку `Fail`

Для **генерации нового адреса** отправте json запрос с функцией `GetAddress`
```json
{"function":"GetAddress"}
```
Для получения **баланса одного адресса** отправте json запрос с параметром `address` и функцией
`CheckAddressBalance`
```json
{"function":"CheckAddressBalance","address":"YOUR_ADDRESS"}
```

### Получение результатов
Результат выполнения функции прийдет на указанный URL методом POST в формате json

Принимаемые переменные:
`function` - возвращает используемую функцию и в зависимости от используемой функции может прийти разный ответ:

1) **CheckBalance** возвращает `Balance` - баланс всех адресов вместе с вашим xpub кошельком

2) **Payment** возвращает  `txid` - идентификатор транзакции, также может вернуть `Fail`

3) **GetAddress** возвращает  `Address` - Сгенерированный адресс

4) **CheckAddressBalance** возвращает `AddressBalance` - баланс одного адреса(для проверки платежа)

## Настройка сервера
Здесь будет описано как полностью установить и настроить сервер.

  * [Установка apache](#Установка-apache)
  * [Установка Postgresql](#Установка-Postgresql)
  * [Установка PHP](#Установка-PHP)
  * [Установка nodejs и npm](#Установка-nodejs-и-npm)
  * [Установка Blockchain Wallet API](#Установка-Blockchain-Wallet-API)
  * [Создание таблиц Postgresql](#Создание-таблиц-Postgresql)

### Установка apache
Пишем в консоли. Я использовал ubuntu для работы сервера.
1. `$ sudo apt update`
2. `$ sudo apt upgrade`
3. `$ sudo apt install apache2`

Добавление сервера в автозагрузку
1. `$ sudo systemctl enable apache2`
2. `$ sudo systemctl restart apache2`
3. `$ sudo systemctl reload apache2`

### Установка Postgresql
Устанавливаем Postgresql.
1. `$ sudo apt update`
2. `$ sudo apt install postgresql postgresql-contrib`

Создаем нового пользователя

3. `$ sudo -u postgres createuser --interactive`
Указываем имя. Потом входим в postgresql

4. `$ sudo -u postgres psql`

Создаем бд blockchain
* `postgres=# CREATE DATABASE blockchain OWNER jeka;`

* `postgres=# GRANT all privileges ON DATABASE blockchain TO jeka;`

* `\q` - выход из psql

Переходим в /usr/share/postgresql/10/pg_hba.conf и добавляем в конце 

`host    all         all             194.125.224.0/22            md5`

затем в /usr/share/postgresql/10/postgresql.conf ищем `#listen_addresses = 'localhost'`, раскоментируем и вместо localhost пишем `listen_addresses = '*'`

4. `$ sudo apt-get install php-pgsql`
5. `$ sudo apt-get install php-curl`

Далее [создаем таблицы](#Создание-таблиц-Postgresql)

### Установка PHP
1. `$ sudo apt-get install php`
2. `$ sudo apt-get install libapache2-mod-php`
3. `$ sudo apt-get install php-fpm`

### Установка nodejs и npm
1. `$ sudo apt install nodejs`
2. `$ sudo apt install npm`

### Установка Blockchain Wallet API
1. `$ npm install -g blockchain-wallet-service`
2. `$ npm update -g blockchain-wallet-service`

### Создание таблиц Postgresql
Возможно таблицы будут меняться
```PostgreSQL
CREATE TABLE "public"."config" (
  "guid" varchar(50) NOT NULL,
  "password" varchar(100) NOT NULL,
  "api_key" varchar(50) NOT NULL
)
;
CREATE TABLE "public"."ApiKey" (
  "id" serial4,
  "user" varchar(50) NOT NULL,
  "pass" varchar(50) NOT NULL,
  "token" varchar(255) NOT NULL,
  "secret" varchar(255) NOT NULL,
  PRIMARY KEY ("id")
)
;
```
