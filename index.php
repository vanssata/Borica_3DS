<?php

/**
 * Class Boorica
 * Базова имлементация на БОРИКА 3DS
 * Това е тестван и работещ пример.
 * Ако след като въведете $terminal_id, $merchant_test_private_key,$merchant_test_passphrase,$borica_test_public_key,$test_action
 * или еквивалентите им за ранла среда изпълните скрипта и не сработо, значи имате със сигурност проблем в ключовете.
 *
 */
class Boorica
{

    const TEST_MODE = 'TEST';
    const PROD_MODE = 'REAL';
    const SUCCECSSFUL_CODE = "00";
    const ERR_GWP_CODES = [
        -1 => 'A mandatory request field is not filled in / В заявката не е попълнено задължително поле',
        -3 => "Acquirer host (NS) does not respond or wrong format of e-gateway response template file / Aвторизационният хост не отговаря или форматът на отговора е неправилен",
        -4 => 'No connection to the acquirer host (NS) / Няма връзка с авторизационния хост',
        -11 => 'Error in the "Currency" request field / Грешка в поле "Валута"  в заявката',
        -12 => 'Error in the "Merchant ID" request field / Грешка в поле "Merchant ID / Идентификатор на търговец"',
        -15 => 'Error in the "RRN" request field / Грешка в поле "RRN" в заявката',
        -17 => 'Грешка при проверка на P_SIGN',
        -19 => 'Error in the authentication information request or authentication failed / Грешка в искането за автентификация или неуспешна автентификация',
        -20 => 'A permitted time interval (1 hour by default) between the transaction Time Stam prequest field and the e-Gateway time is exceeded / Разрешената  разлика между времето на сървъра на търговеца и e-Gateway сървъра е надвишена',
        -21 => 'The transaction has already been executed / Транзакцията вече е била изпълнена',
        -25 => 'Transaction canceled (e.g. by user) / Транзакцията е отказана (напр. от картодържателя)',
        -27 => 'Invalid merchant name / Неправилно име на търговеца',
        -32 => 'Duplicate declined transaction / Дублирана отказана транзакция',
    ];

    const ERR_PAYPROCCESS_CODES = [
        null => "Timeout",
        01 => "Refer to card issuer",
        04 => "Pick Up",
        05 => "Do not Honour",
        13 => 'Invalid amount',
        30 =>  "Format error",
        65 =>  "Soft Decline",
        91 => 'Issuer or switch is inoperative',
        96 =>  "System Malfunction",
    ];
    private $mode = self::TEST_MODE;

    /** Терминал ИД койято банка са дали */
    private $terminal_id;

    /** Частния ключ с който е генериран ключа който се изпраща към Банката за тестовия терминал */
    private $merchant_test_private_key;
    /** Паролата за ключа  */
    private $merchant_test_passphrase=null;

    /** Частния ключ с който е генериран ключа който се изпраща към Банката за реалния терминал */
    private $merchant_real_private_key;
    /** Паролата за ключа  */
    private $merchant_real_passphrase = null;

    /** Публичния ключ който се връща от банката за тестовата среда */
    private $borica_test_public_key;
    /** Публичния ключ който се връща от банката за реална среда */
    private $borica_real_public_key;
    /** Линка на който се формата за тестова среда */
    private $test_action = 'https://3dsgate-dev.borica.bg/cgi-bin/cgi_link';
    /**
     * Линка на който се формата за реална среда
     * Тук очкавам поясение от борика дали не трябва да е https://3dsgate.borica.bg/cgi-bin/cgi_link
     */
    private $real_action = 'https://3dsgate.borica.bg';

    private $real_return_url = 'http://localhost';

    private $test_return_url = 'http://localhost';

    private $merch_url = 'http://localhost';


    public function getReturnUrl()
    {
        if ($this->mode === self::TEST_MODE) {
            return $this->test_return_url;
        }
        return $this->real_return_url;
    }

    /**
     * @return string
     */
    public function getMerchantPrivateKey()
    {
        if ($this->mode === self::TEST_MODE) {
            return $this->merchant_test_private_key;
        }
        return $this->merchant_real_private_key;

    }

    /**
     * @return string
     */
    public function getMerchantPrivateKeyPassphrase()
    {
        if ($this->mode === self::TEST_MODE) {
            return $this->merchant_test_passphrase;
        }
        return $this->merchant_real_passphrase;
    }

    public function getActionUrl()
    {
        if ($this->mode === self::TEST_MODE) {
            return $this->test_action;
        }
        return $this->real_action;
    }

    public function getBoricaPublicKey()
    {
        if ($this->mode === self::TEST_MODE) {
            return $this->borica_test_public_key;
        }
        return $this->borica_real_public_key;
    }

    /**
     * Съобщене за при подписа за TRYTIPE 1
     * За всеки TRYTIPE е различен подпипса може да се види в документацията
     * Пордеността е изкючително важна
     *
     * @param $treminal_id
     * @param $amount
     * @param $timestamp
     * @param string $currency
     * @return string
     */
    public function generate_signature_message_from_TRYTIPE_1($treminal_id, $amount, $timestamp, $currency = 'BGN')
    {

        $message = strlen($this->terminal_id) . "{$treminal_id}";
        $message .= "11";
        $message .= strlen($amount) . "{$amount}";
        $message .= strlen($currency) . "{$currency}";
        $message .= strlen($timestamp) . "{$timestamp}";
        return $message;
    }

    /**
     * Префоратиране на полето ORDER във формата
     * Много важно е да се зане че това всъщност не е order_id-то в платформата/магазина а номера на плащането
     * Трябва да е уникален за деня 6 циферен int примерно 000001, 000002, 001001 и т.н
     * @param $payment_id
     * @return string
     */
    protected function formatOrderID($payment_id)
    {

        return str_pad($payment_id, 6, "0", STR_PAD_LEFT);
    }

    /**
     * AD.CUST_BOR_ORDER_ID Идентификатор на поръчка ORDER + до 16 символа**
     * Това е реално може да се разгледа като кобинация от индентификатора за плащане (ORDER) в борика и индентификатора за поръчка в ситемата
     * @param $pyment_id int отговаря на полето ORDER във формата
     * @param $order_id mixed отговаря на уникалния индентификатор на поръчката при клиента
     * @param string $divider стринг който разделя двете части
     * @return string
     */
    protected function generate_CUST_BOR_ORDER_ID($pyment_id, $order_id, $divider = "@ORD/")
    {

        return "{$pyment_id}{$divider}{$order_id}";
    }


    function generateBoricaRequestFieldsForTRTYPE_1()
    {

        //Стойност на поръчата
        $amount = 22;
        //Коректен формат на стойноста на поръчлата примерно 22.00
        $amount = number_format($amount, 2, '.', '');
        //Дата в
        $timestamp = date('YmdHis');
        /**
         * Валутата в която е натроен терминалата
         * !!! ВАЖНО ТАЗИ ВАЛУТА Е САМО ТАЗИ КОЯТО Е НАСТОРЕНА В ТЕРМИНАЛА
         */
        $currency = 'BGN';

        $message = $this->generate_signature_message_from_TRYTIPE_1($this->terminal_id, $amount, $timestamp, $currency);


        $pkeyid = openssl_get_privatekey($this->getMerchantPrivateKey(), $this->getMerchantPrivateKeyPassphrase());
        openssl_sign($message, $signature, $pkeyid, OPENSSL_ALGO_SHA256);
        openssl_free_key($pkeyid);


        $form["AMOUNT"] = $amount;
        $form["CURRENCY"] = $currency;
        $form["DESC"] = "Some Order Description";
        $form["TERMINAL"] = $this->terminal_id;
        $form["MERCH_NAME"] = "Borica Example";
        $form["MERCH_URL"] = $this->merch_url;
        $form["MERCHANT"] = "Borica Example";
        $form["EMAIL"] = 'email_for_notification@test.localhost';
        $form["TRTYPE"] = 1;

        $random_payment_id = $this->formatOrderID(rand(99, 999));
        $random_order_id = rand(1000, 9999);

        $form["ORDER"] = $random_payment_id;
        $form["AD.CUST_BOR_ORDER_ID"] = $this->generate_CUST_BOR_ORDER_ID($random_payment_id, $random_order_id);
        $form["COUNTRY"] = 'BG';
        $form["TIMESTAMP"] = $timestamp;
        $form["MERCH_GMT"] = '+03';
        $form["NONCE"] = $this->getRandomHex();
        $form["BACKREF"] = $this->getReturnUrl();
        $form["ADDENDUM"] = 'AD,TD';
        $form["P_SIGN"] = strtoupper(bin2hex($signature));
        return $form;

    }

    function getRandomHex($num_bytes = 16)
    {
        return strtoupper(bin2hex(openssl_random_pseudo_bytes($num_bytes)));
    }

    /**
     * @param $response
     */
    public function generateMessage_TRYTIPE_1($response){

    }

    public function responseHandler()
    {
        if ($_POST) {
            if(array_key_exists($_POST['RC'], self::ERR_GWP_CODES)){
                throw new Exception('Грешка при обработката на данните при опрецията: <hr/><pre>'.self::ERR_GWP_CODES[$_POST['RC']].'</pre>');
            }


            $terminal = $_POST['TERMINAL'];
            $trtype = $_POST['TRTYPE'];
            $amount = $_POST['AMOUNT'];
            $timestamp = $_POST['TIMESTAMP'];
            $p_sign = hex2bin($_POST['P_SIGN']);
            $order_id = ltrim($_POST['ORDER'], '0');

            $data = strlen($terminal) . "{$terminal}";
            $data .= strlen($trtype) . "{$trtype}";
            $data .= strlen($amount) . "{$amount}";
            $data .= strlen($timestamp) . "{$timestamp}";

            if (strpos($this->getBoricaPublicKey(), 'CERTIFICATE') !== false) {
                $pkeyid = openssl_get_publickey($this->getBoricaPublicKey());
            } else {
                $pkeyid = $this->getBoricaPublicKey();
            }

            $result = openssl_verify($data, $p_sign, $pkeyid, OPENSSL_ALGO_SHA256);
            echo("<pre>".print_r($_POST,1)."</pre>").PHP_EOL;
            if($result == 1){
                echo("<pre>".print_r("Резултата е подписан",1)."</pre>").PHP_EOL;
                 if ($_POST['RC'] === self::SUCCECSSFUL_CODE) {
                     echo("<pre>".print_r("Плащането е успешно",1)."</pre>").PHP_EOL;
                 }elseif(array_key_exists($_POST['RC'], self::ERR_PAYPROCCESS_CODES)){
                     echo("<pre>".print_r(self::ERR_PAYPROCCESS_CODES[$_POST['RC']],1)."</pre>").PHP_EOL;
                 } else{
                     echo("<pre>".print_r("Не дефиниранo RC {$_POST['RC']} ",1)."</pre>").PHP_EOL;
                 }
            }else{
                echo("<pre>".print_r("Грешка пи продписване на сертификата",1)."</pre>").PHP_EOL;
            }

        }


    }
}

?>
<?php
$borica = new Boorica();
if(isset($_POST) AND !empty($_POST)){
    $borica->responseHandler();
}else{
?>

<form name="pay" action="<?php echo $borica->getActionUrl(); ?>" method="POST">
    <?php foreach ($borica->generateBoricaRequestFieldsForTRTYPE_1() as $field => $value): ?>
        <label for="<?php echo $field; ?>"><?php echo $field; ?> </label>
        <input type="text" name="<?php echo $field; ?>" value="<?php echo $value; ?>" readonly="readonly"><br>
    <?php endforeach; ?>
    <input type="submit" name="Submit" value="Approve"><br>
</form>
<?php } ?>
