<?php
// Часова зона на сървъра
date_default_timezone_set('UTC');

// Основни задължителни данни за извършване на електронното плащане 
$ORDER = str_pad($_REQUEST["ORDER"], 6, "0", STR_PAD_LEFT); // 6 знака с водещи нули 
$AMOUNT = number_format($_REQUEST["AMOUNT"], 2, '.', ''); // Формат: xx.xx,  Пример: 12.34
$DESC   = $_REQUEST["DESC"]; // Пример: "Пробна поръчка"
$TIMESTAMP = date("YmdHis"); // Формат: YYYYMMDDHHMMSS

// Формиране на сигнатура за подписване
$P_SIGN = strlen($TERMINAL).$TERMINAL.strlen($TRTYPE).$TRTYPE.strlen($AMOUNT).$AMOUNT.strlen($CURRENCY).$CURRENCY.strlen($TIMESTAMP).$TIMESTAMP;

// Информация за файла с цифровия сертификат
$private_key_file = "1234.key";
$private_key_pass = '1234';

// Отваряне на файла с цифровия сертификат
$fp = fopen($private_key_file, "r"); 
$private_key_file = fread($fp, filesize($private_key_file)); 
fclose($fp); 

// Подписване на съобщението с цифров сертификат
$private_key_id = openssl_get_privatekey($private_key_file, $private_key_pass); 
openssl_sign($P_SIGN, $signature, $private_key_id, OPENSSL_ALGO_SHA256);   
openssl_free_key($private_key_result);   

// Формиране окончателна подписана сигнатура
$P_SIGN = strtoupper(bin2hex($signature)); 

/*
// Допълнително задължителни данни за извършване на електронното плащане
$TERMINAL = "V1234567"; // Идентификатор на търговеца получен от БОРИКА
$MERCH_URL = "http://localhost"; // Линк към електронният магазина на търговеца
$BACKREF = "http://localhost"; // Линк към връщане към електронният магазин след плащане в БОРИКА 
$AD_CUST_BOR_ORDER_ID = $ORDER."@ORD";
*/

// Отпечатване на формуляр за извършване на плащане
echo<<<EOT
<!-- Формуляр за извършване на плащане -->
<form action="https://3dsgate-dev.borica.bg/cgi-bin/cgi_link" method="post">
<!-- Фиксирани -->
<input type="hidden" name="TRTYPE" value="1" />
<input type="hidden" name="COUNTRY" value="BG" />
<input type="hidden" name="CURRENCY" value="BGN" />
<input type="hidden" name="ADDENDUM" value="AD,TD" />
<input type="hidden" name="MERCH_GMT" value="+03" />
<!-- Основни -->
<input type="hidden" name="ORDER"  value="$ORDER" />
<input type="hidden" name="AMOUNT" value="$AMOUNT" />
<input type="hidden" name="DESC"  value="$DESC" />
<input type="hidden" name="TIMESTAMP" value="$TIMESTAMP" />
<!-- Допълнителни -->
<input type="hidden" name="TERMINAL" value="$TERMINAL" />
<input type="hidden" name="MERCH_URL" value="$MERCH_URL" />
<input type="hidden" name="BACKREF" value="$BACKREF" />
<input type="hidden" name="AD.CUST_BOR_ORDER_ID" value="$AD_CUST_BOR_ORDER_ID" />
<!-- Сигнатури -->
<input type="hidden" name="NONCE" value="$NONCE" />
<input type="hidden" name="P_SIGN" value="$P_SIGN" />
<!-- Буттон -->
<input type="submit" value="Продължи" /> 
</form>
<!-- /Формуляр за извършване на плащане -->
EOT;
