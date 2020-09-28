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
