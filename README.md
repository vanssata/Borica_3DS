# Borica 
Към момента това е просто тестов пример. 

За да го тествате първо трябва да въведете стойности на, или техните еквиваленти за реална среда: 
```php
$terminal_id, $merchant_test_private_key,$merchant_test_passphrase,$borica_test_public_key,$test_return_url
```
Ако нямате сървър може да използвате: 
```bash docker-compose up -d```

Отваряте в браузера : localhost ще видите елементарна форма с предварително попълнени данни.

### Ако полето "P_SIGN" и или "NONCE" са празни, значи имате проблеми с ключовете. 

## Този скрипт е тестван и работи на 2 терминала в тестов модел. 
#TODO 
1. Добавяне на коректна документация.
2. Добавяне на различните видове заявки към APGW

#Скрипта работи на PHP >=5.6
## Работеща генерация на ключ на сървъра: 
```bash
# {terminal} - terminal id 
# {date} - current date format is YYYYMMDD
# {sate} - P for production or D for development
openssl genrsa -out {teminal_id}_{date}_{state}.key -aes256 2048 #generate private key
openssl req -new -key {teminal_id}_{date}_{state}.key -out {teminal_id}_{date}_{state}.csr
```
Благодарение на [dimitarminchev](https://gist.github.com/dimitarminchev)


## Тестови карти
| CART Number      | Response code       |
|------------------|---------------------|
|5100770000000022  | 00                  |
|5100770000000022  | 00                  |
|4341792000000044  | 00 3DS pass: 111111 |
|5555000000070019  | 04                  |
|5555000000070027  | 13                  |
|5555000000070035  | 91                  |
|4010119999999897  | amount dependant    |
|5100789999999895  | amount dependant 3DS pass: 111111 |

----------------------------------
|AMOUNT| 	RC| 	RC DESCRIPTION|
|------|------|-------------------|
|1.00 - 1.99| 	01| 	Refer to card issuer|
|2.00 - 2.99| 	04| 	Pick Up|
|3.00 - 3.99| 	05| 	Do not Honour|
|4.00 - 4.99| 	13| 	Invalid amount|
|5.00 - 5.99| 	30| 	Format error|
|6.00 - 6.99| 	91| 	Issuer or switch is inoperative|
|7.00 - 7.99| 	96| 	System Malfunction|
|8.00 - 8.99| 	  |     Timeout|
|30.00 - 40.00| 	01| 	Refer to card issuer|
|50.00 - 70.00| 	04| 	Pick Up|
|80.00 - 90.00| 	05| 	Do not Honour|
|100.00 - 110.00| 	13| 	Invalid amount|
|120.00 - 130.00| 	30| 	Format error|
|140.00 - 150.00| 	91| 	Issuer or switch is inoperative|
|160.00 - 170.00| 	96| 	System Malfunction|
|180.00 - 190.00| 	  |Timeout|

## Simple exmaple
