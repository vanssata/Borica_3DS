# Borica 
Към момнета това е просто тестов пример. 

За да го тествате първо трябва да въведете стойности на, или техните еквиваленти за реялна среда: 
```php
$terminal_id, $merchant_test_private_key,$merchant_test_passphrase,$borica_test_public_key,$test_return_url
```
Ако нямате сървър може да използвате: 
```bash docker-compose up -d```

Отваряте в браузреа : localhost ще видите елементарна форма с предварително попълнени данни.

### Ако в полето "P_SIGN" и или "NONCE" са празни, значи имате проблеми с ключовете. 

## Този скрипт е тестван и работи на 2 терминала в тестов модел. 
#TODO 
1. Добавяне на коректна докоментация.
2. Добавяне на разаличните видове заявки към APGW

#Скрипта работи на PHP >=5.6
