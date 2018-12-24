<?php
namespace Cabinet\Plugin\Orders;
use Cabinet\Common;
use Cabinet\Curl;
use Cabinet\Plugin\Orders\Order_Api;
use Cabinet\Tools;
use Cabinet\WebServiceException;

require_once DOC_ROOT . '/cabinet/classes/Common.php';
require_once DOC_ROOT . '/cabinet/classes/WebServiceException.php';


require_once 'classes/Yandex_Money.php';
require_once 'classes/Order_Api.php';


/**
 * Class Rest_1_0
 * @package Cabinet\Plugin\Orders
 */
class Rest_1_0 extends Common {


    /**
     * Проверка корректности заказа
     * @return string
     * @throws WebServiceException
     */
    public function paymentCheck() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new WebServiceException('incorrect request method. Need POST', 100, 400);
        }

        if (empty($_POST['action']))                  throw new WebServiceException('Param action is empty', 101, 400);
        if (empty($_POST['md5']))                     throw new WebServiceException('Param md5 is empty', 102, 400);
        if (empty($_POST['shopId']))                  throw new WebServiceException('Param shopId is empty', 103, 400);
        if (empty($_POST['invoiceId']))               throw new WebServiceException('Param invoiceId is empty', 104, 400);
        if (empty($_POST['payment_id']))              throw new WebServiceException('Param payment_id is empty', 105, 400);
        if (empty($_POST['customerId']))              throw new WebServiceException('Param customerId is empty', 106, 400);
        if (empty($_POST['orderSumAmount']))          throw new WebServiceException('Param orderSumAmount is empty', 107, 400);
        if (empty($_POST['orderSumCurrencyPaycash'])) throw new WebServiceException('Param orderSumCurrencyPaycash is empty', 108, 400);
        if (empty($_POST['orderSumBankPaycash']))     throw new WebServiceException('Param orderSumBankPaycash is empty', 109, 400);

        if ( ! in_array($_POST['action'], ['checkOrder', 'cancelOrder'])) {
            throw new WebServiceException('Param action not equal "checkOrder"', 110, 400);
        }


        $action = $_POST['action'] == 'checkOrder' ? Yandex_Money::ACTION_CHECK_ORDER : Yandex_Money::ACTION_CANCEL_ORDER;

        $yandex_money = new Yandex_Money([
            'security_type' => $this->config->yandex->money->security_type,
            'shop_id'       => $this->config->yandex->money->shop_id,
            'shop_pwd'      => $this->config->yandex->money->shop_pwd,
            'scid'          => $this->config->yandex->money->scid
        ]);


        if ($yandex_money->checkSecurity($_POST)) {
            try {
                switch ($_POST['action']) {
                    case 'checkOrder':  $this->checkOrder($_POST['payment_id'], $_POST['orderSumAmount']); break;
                    case 'cancelOrder': /* $this->cancelOrder();*/ break;
                }

                $response = $yandex_money->buildResponse($action, $_POST['invoiceId'], 0);
                $this->log('info', $_POST['action'] . ' ' . $response, $_POST);

            } catch (\Exception $e) {
                $response = $yandex_money->buildResponse($action, $_POST['invoiceId'], 100, $e->getMessage());
                $this->log('error', $_POST['action'] . ' ' . $response, $_POST);
            }

        } else {
            $response = $yandex_money->buildResponse($action, $_POST['invoiceId'], 1);
            $this->log('error', $_POST['action'] . ' ' . $response, $_POST);
        }

        $yandex_money->sendHeaders();
        return $response;
    }


    /**
     * Подтверждение оплаты заказа
     * @return string
     * @throws WebServiceException
     */
    public function paymentSuccess() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new WebServiceException('incorrect request method. Need POST', 100, 400);
        }

        if (empty($_POST['action']))                  throw new WebServiceException('Param action is empty', 101, 400);
        if (empty($_POST['md5']))                     throw new WebServiceException('Param md5 is empty', 102, 400);
        if (empty($_POST['shopId']))                  throw new WebServiceException('Param shopId is empty', 103, 400);
        if (empty($_POST['invoiceId']))               throw new WebServiceException('Param invoiceId is empty', 104, 400);
        if (empty($_POST['payment_id']))              throw new WebServiceException('Param payment_id is empty', 105, 400);
        if (empty($_POST['customerId']))              throw new WebServiceException('Param customerId is empty', 106, 400);
        if (empty($_POST['orderSumAmount']))          throw new WebServiceException('Param orderSumAmount is empty', 107, 400);
        if (empty($_POST['orderSumCurrencyPaycash'])) throw new WebServiceException('Param orderSumCurrencyPaycash is empty', 108, 400);
        if (empty($_POST['orderSumBankPaycash']))     throw new WebServiceException('Param orderSumBankPaycash is empty', 109, 400);

        if ($_POST['action'] != 'paymentAviso') {
            throw new WebServiceException('Param action not equal "paymentAviso"', 110, 400);
        }

        $yandex_money = new Yandex_Money([
            'security_type' => $this->config->yandex->money->security_type,
            'shop_id'       => $this->config->yandex->money->shop_id,
            'shop_pwd'      => $this->config->yandex->money->shop_pwd,
            'scid'          => $this->config->yandex->money->scid
        ]);


        if ($yandex_money->checkSecurity($_POST)) {
            $order_api      = new Order_Api();
            $result_payment = $order_api->setPaymentSuccess($_POST['payment_id'], $_POST['customerId'], $_POST['invoiceId'], $_POST);

            try {
                if (empty($result_payment)) {
                    throw new \Exception("При смене статуса платежа на 'оплачен' вернулся некорректный ответ.");
                }

                if ( ! empty($result_payment['error_code'])) {
                    throw new \Exception("При смене статуса платежа на 'оплачен' произошла ошибка: " . ( ! empty($result_payment['message']) ? $result_payment['message'] : ''));
                }


                if (empty($result_payment['status'])) {
                    throw new \Exception("В ответе на запрос смены статуса платежа на 'оплачен' не удалось найти некоторые важные поля.");
                }

                if ($result_payment['status'] != 'success') {
                    throw new \Exception("Не удалось подтвердить успешную смену статуса платежа на 'оплачен'.");
                }

                $response = $yandex_money->buildResponse(Yandex_Money::ACTION_PAYMENT_AVISO, $_POST['invoiceId'], 0);
                $this->log('info', 'paymentAviso ' . $response, $_POST);

            } catch (\Exception $e) {
                $response = $yandex_money->buildResponse(Yandex_Money::ACTION_PAYMENT_AVISO, $_POST['invoiceId'], 100, $e->getMessage());
                $this->log('error', 'paymentAviso ' . $response, $_POST);
            }

        } else {
            $response = $yandex_money->buildResponse(Yandex_Money::ACTION_PAYMENT_AVISO, $_POST['invoiceId'], 1);
            $this->log('error', 'paymentAviso ' . $response, $_POST);
        }

        $yandex_money->sendHeaders();
        return $response;
    }


    /**
     * Отмена заказа
     * @throws \Exception
     */
    private function cancelOrder() {

//        $order_api = new Order_Api();
//        $payment = $order_api->getPayment($_POST['orderNumber']);

        $curl_response = Curl::post($this->config->system->apiserver . "/api/orders/update", [
            'orders' => [
                'id'     => $_POST['orderNumber'],
                'status' => 'canceled',
            ]
        ], [
            'Core2-Apikey: ' . $this->config->system->apikey,
            'Core2-Private-Key: ' . $this->config->system->private_key,
        ]);

        if ($curl_response->isError()) {
            throw new \Exception("Не удалось выполнить запрос для смены статуса заказа. Причина: " . $curl_response->getErrorMessage());
        }

        $data = $curl_response->toArray();

        if (empty($data)) {
            throw new \Exception("При смене статуса заказа на 'отменен' вернулся некорректный ответ.");
        }

        if ( ! empty($data['error_code'])) {
            throw new \Exception("При смене статуса заказа на 'отменен' произошла ошибка: " . ( ! empty($data['message']) ? $data['message'] : ''));
        }

        if (empty($data['orders']) || empty($data['success_count'])) {
            throw new \Exception("В ответе на запрос смены статуса заказа на 'отменен' не удалось найти некоторые важные поля.");
        }

        if ($data['success_count'] !== 1) {
            throw new \Exception("Не удалось подтвердить успешную смену статуса заказа на 'отменен'.");
        }
    }


    /**
     * @param $payment_id
     * @param $sum_amount
     * @throws \Exception
     */
    private function checkOrder($payment_id, $sum_amount) {

        $order_api = new Order_Api();
        $payment = $order_api->getPayment($payment_id);

        if (empty($payment)) {
            throw new \Exception("На запрос данных платежа вернулся некорректный ответ.");
        }

        if ( ! empty($data['error_code'])) {
            throw new \Exception("При запросе данных заказа произошла ошибка: " . ( ! empty($data['message']) ? $data['message'] : ''));
        }


        if (empty($payment['price']) || empty($payment['status_transaction'])) {
            throw new \Exception("При запросе данных платежа не удалось найти некоторые важные поля.");
        }

        if ($payment['price'] != $sum_amount) {
            throw new \Exception("Сумма платежа не сходится. Сумма платежа {$payment['price']}, а оплачивается {$sum_amount}.");
        }

        if ($payment['status_transaction'] != 'pending') {
            switch ($payment['status_transaction']) {
                case 'completed': throw new \Exception("Указанный платеж уже имеет статус 'оплачен'."); break;
                default:          throw new \Exception("Указанный платеж имеет неизвестный статус.");
            }
        }
    }


    /**
     * @param string $type
     * @param string $message
     * @param array  $context
     */
    protected function log($type, $message, $context = []) {

        $file = DOC_ROOT . '/../logs/yandex-money.log';

        $resource = fopen($file, 'a');
        fwrite($resource, date('[Y-m-d H:i:s] ') . strtoupper($type) . ' ' . $message . ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) . "\n");
        fclose($resource);
    }
}