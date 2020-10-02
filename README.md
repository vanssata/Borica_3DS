# Borica 1.0
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

# Скрипта работи на PHP >=5.6
## Работеща генерация на ключ на сървъра: 
```bash
# {terminal} - terminal id 
# {date} - current date format is YYYYMMDD
# {sate} - P for production or D for development
openssl genrsa -out {teminal_id}_{date}_{state}.key -aes256 2048 #generate private key
openssl req -new -key {teminal_id}_{date}_{state}.key -out {teminal_id}_{date}_{state}.csr
```
## Response codes 

|Response Code (RC)|RC DESCRIPTION |    
|------------------|---------------|   
|00                | Sucessfull    |
|                  | => Timeout |
|"01"              | Refer to card issuer |
|"04"              | Pick Up |
|"05"              | Do not Honour |
|"13"              | Invalid amount |
|"30"              | Format error |
|"65"              | Soft Decline |
|"91"              | Issuer or switch is inoperative |
|"96"              | System Malfunction |    
                 
| ERR_GWP_CODES    |   DESCRIPTION |
|------------------|---------------|
| -1 | A mandatory request field is not filled in / В заявката не е попълнено задължително поле|
| -3 | Acquirer host (NS) does not respond or wrong format of e-gateway response template file / Aвторизационният хост не отговаря или форматът на отговора е неправилен|
| -4 | No connection to the acquirer host (NS) / Няма връзка с авторизационния хост|
| -11 | Error in the "Currency" request field / Грешка в поле "Валута"  в заявката|
| -12 | Error in the "Merchant ID" request field / Грешка в поле "Merchant ID / Идентификатор на търговец|
| -15 | Error in the "RRN" request field / Грешка в поле "RRN" в заявката|
| -17 | Грешка при проверка на P_SIGN|
| -19 | Error in the authentication information request or authentication failed / Грешка в искането за автентификация или неуспешна автентификация|
| -20 | A permitted time interval (1 hour by default) between the transaction Time Stam prequest field and the e-Gateway time is exceeded / Разрешената  разлика между времето на сървъра на търговеца и e-Gateway сървъра е надвишена|
| -21 | The transaction has already been executed / Транзакцията вече е била изпълнена|
| -25 | Transaction canceled (e.g. by user) / Транзакцията е отказана (напр. от картодържателя|
| -27 | Invalid merchant name / Неправилно име на търговеца|
| -32 | Duplicate declined transaction / Дублирана отказана транзакция|



# Примери
Примери благодарение на [dimitarminchev](https://www.github.com/dimitarminchev/).

## Borica Request 1.0
Изпращане на заявка: [borica_request.php](https://gist.github.com/dimitarminchev/08d8ab833fa481a8cc5c1e365a16c05e)

### Request Test Credit Cards
| CART Number | Response code |
|-|-|
| 5100770000000022  | 00 |
| 4341792000000044  | 00 3DS pass: 111111 |
| 5555000000070019  | 04 |
| 5555000000070027  | 13 |
| 5555000000070035  | 91 |
| 4010119999999897  | amount dependant |
| 5100789999999895  | amount dependant 3DS pass: 111111 |

### Request Amount Dependant Credit Cards
| AMOUNT | RC | RC DESCRIPTION |
|-|-|-|
| 1.00 - 1.99 | 01 | Refer to card issuer |
| 2.00 - 2.99 | 04 | Pick Up |
| 3.00 - 3.99 | 05 | Do not Honour |
| 4.00 - 4.99 | 13 | Invalid amount |
| 5.00 - 5.99 | 30 | Format error |
| 6.00 - 6.99 | 91 | Issuer or switch is inoperative |
| 7.00 - 7.99 | 96 | System Malfunction |
| 8.00 - 8.99 | | Timeout |
| 30.00 - 40.00 | 01 | Refer to card issuer |
| 50.00 - 70.00 | 04 | Pick Up |
| 80.00 - 90.00 | 05 | Do not Honour |
| 100.00 - 110.00 | 13 | Invalid amount |
| 120.00 - 130.00 | 30 | Format error |
| 140.00 - 150.00 | 91 | Issuer or switch is inoperative |
| 160.00 - 170.00 | 96 | System Malfunction |
| 180.00 - 190.00 | | Timeout |
| 10000.65 - 10000.65 | 65/1A | Soft Decline |

## Borica Response 1.0
Получаване на отговор: [borica_response.php](https://gist.github.com/dimitarminchev/34265c2c780ccff86a545eb4a1ffd88c)

### Responce Codes [ISO-8583](https://en.wikipedia.org/wiki/ISO_8583)

| Code | Meaning |
|-|-|
| 0 | Successful approval/completion or that   VIP PIN verification is valid |
| 1 | Refer to card issuer |
| 2 | Refer to card issuer, special condition |
| 3 | Invalid merchant or service provider |
| 4 | Pickup |
| 5 | Do not honor |
| 6 | General error |
| 7 | Pickup card, special condition (other   than lost/stolen card) |
| 8 | Honor with identification |
| 9 | Request in progress |
| 10 | Partial approval |
| 11 | VIP approval |
| 12 | Invalid transaction |
| 13 | Invalid amount (currency conversion   field overflow) or amount exceeds maximum for card program |
| 14 | Invalid account number (no such number) |
| 15 | No such issuer |
| 16 | Insufficient funds |
| 17 | Customer cancellation |
| 18 | Customer dispute |
| 19 | Re-enter transaction |
| 20 | Invalid response |
| 21 | No action taken (unable to back out   prior transaction) |
| 22 | Suspected Malfunction |
| 23 | unacceptable transaction fee |
| 24 | File update not supported by receiver |
| 25 | Unable to locate record in file, or   account number is missing from the inquiry |
| 26 | Duplicate file update record, old   record replaced |
| 27 | File update field edit error |
| 28 | File is temporarily unavailable |
| 29 | File update not successful, contact   acquirer |
| 30 | Format error |
| 31 | Bank not supported |
| 32 | Completed partially |
| 33 | Expired card |
| 34 | Suspected fraud |
| 35 | Card acceptor contact acquirer |
| 36 | Restricted card |
| 37 | Card acceptor call acquirer security |
| 38 | Allowed PIN tries exceeded |
| 39 | No credit account |
| 40 | Requested function not supported |
| 41 | Merchant should retain card (card   reported lost) |
| 42 | No universal account |
| 43 | Merchant should retain card (card   reported stolen) |
| 51 | Insufficient funds |
| 52 | No checking account |
| 53 | No savings account |
| 54 | Expired card |
| 55 | Incorrect PIN |
| 56 | No Card Record |
| 57 | Transaction not permitted to cardholder |
| 58 | Transaction not allowed at terminal |
| 59 | Suspected fraud |
| 61 | Activity amount limit exceeded |
| 62 | Restricted card (for example, in   country exclusion table) |
| 63 | Security violation |
| 65 | Activity count limit exceeded |
| 68 | Response received too late |
| 75 | Allowable number of PIN-entry tries   exceeded |
| 76 | Unable to locate previous message (no   match on retrieval reference number) |
| 77 | Previous message located for a repeat   or reversal, but repeat or reversal data are inconsistent with original   message |
| 78 | ’Blocked, first used’—The transaction   is from a new cardholder, and the card has not been properly unblocked. |
| 80 | Visa transactions: credit issuer   unavailable. Private label and check acceptance: Invalid date |
| 81 | PIN cryptographic error found (error   found by VIC security module during PIN decryption) |
| 82 | Negative CAM, dCVV, iCVV, or CVV   results |
| 83 | Unable to verify PIN |
| 85 | No reason to decline a request for   account number verification, address verification, CVV2 verification; or a   credit voucher or merchandise return |
| 91 | Issuer unavailable or switch   inoperative (STIP not applicable or available for this transaction) |
| 92 | Destination cannot be found for routing |
| 93 | Transaction cannot be completed,   violation of law |
| 94 | Duplicate transmission |
| 95 | Reconcile error |
| 96 | System malfunction, System malfunction   or certain field error conditions |
| B1 | Surcharge amount not permitted on Visa   cards (U.S. acquirers only) |
| N0 | Force STIP |
| N3 | Cash service not available |
| N4 | Cashback request exceeds issuer limit |
| N7 | Decline for CVV2 failure |
| P2 | Invalid biller information |
| P5 | PIN change/unblock request declined |
| P6 | Unsafe PIN |
| Q1 | Card authentication failed |
| R0 | Stop payment order |
| R1 | Revocation of authorization order |
| R3 | Revocation of all authorizations order |
| XA | Forward to issuer |
| XD | Forward to issuer |
| Z3 | Unable to go online |
| -1 | A mandatory request field is not filled in |
| -2 | Bad CGI request |
| -3 | Acquirer host (NS) does not respond or wrong format of   e-gateway response template file  |
| -4 | No   connection to the acquirer host (NS)  |
| -9 | Error in the "Card expiration date" request field |
| -11 | Error   in the "Currency" request field  |
| -12 | Error in the "Merchant ID" request field  |
| -15  | Error   in the "RRN" request field |
| -17 | The   terminal is denied access to the e-Gateway |
| -19 | Error   in the authentication information request or authentication failed  |
| -20 | A permitted time interval (1 hour by default) between the   transaction Time Stam prequest field and the e-Gateway time is exceeded  |
| -21 | The   transaction has already been executed |
| -24 | Transaction   context mismatch |
| -25 | Transaction canceled (e.g. by user) |
| -27 | Invalid   merchant name  |
| -32 | Duplicate   declined transaction  |
