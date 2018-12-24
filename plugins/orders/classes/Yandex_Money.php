<?php
namespace Cabinet\Plugin\Orders;


/**
 * Class Yandex_Money
 * @package Cabinet\Plugin\Orders
 */
class Yandex_Money {

    const ACTION_CHECK_ORDER   = 'checkOrder';
    const ACTION_PAYMENT_AVISO = 'paymentAviso';
    const ACTION_CANCEL_ORDER  = 'cancelOrder';

    private $settings;

    /**
     * Yandex_Money constructor.
     * @param array $settings
     */
    public function __construct($settings) {
        $this->settings = [
            'security_type' => ! empty($settings['security_type']) ? $settings['security_type'] : '',
            'shop_pwd'      => ! empty($settings['shop_pwd'])      ? $settings['shop_pwd'] : '',
            'shop_id'       => ! empty($settings['shop_id'])       ? $settings['shop_id'] : '',
            'scid'          => ! empty($settings['scid'])          ? $settings['scid'] : '',
        ];
    }


    /**
     * @param array $request
     * @return bool
     */
    public function checkSecurity($request) {

        switch ($this->settings['security_type']) {
            case 'MD5':   return $this->checkMD5($request); break;
            case 'PKCS7': return $this->verifySign();       break;
        }

        return true;
    }


    /**
     * Building XML response.
     * @param  string $function_name "checkOrder" or "paymentAviso" string
     * @param  string $invoice_id    transaction number
     * @param  string $result_code   result code
     * @param  string $message       error message. May be null.
     * @return string                prepared XML response
     */
    public function buildResponse($function_name, $invoice_id, $result_code, $message = null) {

        $date = new \DateTime();
        $performed_datetime = $date->format("Y-m-d") . "T" . $date->format("H:i:s") . ".000" . $date->format("P");

        $response = '<?xml version="1.0" encoding="UTF-8"?><' . $function_name . 'Response ' .
            'performedDatetime="' . $performed_datetime  . '" ' .
            'code="' . $result_code . '" ' .
            ($message != null ? 'message="' . $message . '" ' : "") .
            'invoiceId="' . $invoice_id . '" ' .
            'shopId="' . $this->settings['shop_id'] . '"/>';

        return $response;
    }


    /**
     *
     */
    public function sendHeaders() {

        header("HTTP/1.0 200");
        header("Content-Type: application/xml");
    }


    /**
     * Checking the MD5 sign.
     * @param  array $request payment parameters
     * @return bool true if MD5 hash is correct
     */
    private function checkMD5($request) {

        $str = $request['action'] . ";" .
            $request['orderSumAmount'] . ";" . $request['orderSumCurrencyPaycash'] . ";" .
            $request['orderSumBankPaycash'] . ";" . $request['shopId'] . ";" .
            $request['invoiceId'] . ";" . trim($request['customerNumber']) . ";" . $this->settings['shop_pwd'];


        return strtoupper(md5($str)) === strtoupper($request['md5']);
    }


    /**
     * Checking for sign when XML/PKCS#7 scheme is used.
     * @return bool if request is successful, returns key-value array of request params, null otherwise.
     */
    private function verifySign() {

        $descriptor_spec = [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
            2 => ["pipe", "w"]
        ];
        $certificate = __DIR__ . '/yamoney.pem';
        $process     = proc_open(
            'openssl smime -verify -inform PEM -nointern -certfile ' . $certificate . ' -CAfile ' . $certificate,
            $descriptor_spec,
            $pipes
        );

        if (is_resource($process)) {
            if (proc_close($process) != 0) {
                return false;

            } else {
                return true;
            }
        }

        return false;
    }
}