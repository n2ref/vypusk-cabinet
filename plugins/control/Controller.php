<?php
namespace Cabinet\Plugin\Control;
use Cabinet\Common;
use Cabinet\Curl;
use Cabinet\Mtpl;
use Cabinet\Tools;
use Cabinet\Plugin\Orders\Order_Api;

require_once DOC_ROOT . '/cabinet/classes/Common.php';
require_once DOC_ROOT . '/cabinet/classes/Registry.php';
require_once DOC_ROOT . '/cabinet/classes/Mtpl.php';
require_once DOC_ROOT . '/cabinet/classes/Tools.php';
require_once DOC_ROOT . '/cabinet/classes/Curl.php';

require_once DOC_ROOT . '/plugins/orders/classes/Order_Api.php';


/**
 * Class Controller
 * @package Cabinet\Plugin\Control
 */
class Controller extends Common {

    /**
     * @return string
     * @throws \Exception
     */
    public function pageIndex() {

        if ( ! $this->isAuth()) {
            return '';
        }

        //$news = $this->getNews();

        $order_api = new Order_Api();
        $orders = $order_api->getOrders();

        if ( ! empty($orders)) {
            $tpl_orders = new Mtpl(__DIR__ . '/html/orders.list.html');

            foreach ($orders as $order) {
                $tpl_orders->orders->assign('[ID]',         $order['id']);
                $tpl_orders->orders->assign('[NUM]',        $order['num']);
                $tpl_orders->orders->assign('[GROUP_NAME]', $order['group_name']);
                $tpl_orders->orders->assign('[STATUS]',     $order['status']);
                $tpl_orders->orders->assign('[TOTAL_DIFF]', Tools::commafy($order['total_price'] - $order['total_payments']));
                $tpl_orders->orders->reassign();
            }

        } else {
            $tpl_orders = new Mtpl(DOC_ROOT . '/plugins/orders/html/orders.list.empty.html');
        }



        $tpl = new Mtpl(__DIR__ . '/html/control.html');
        $tpl->assign('[FIRSTNAME]', $this->auth->firstname);
        $tpl->assign('[LASTNAME]',  $this->auth->lastname);
        $tpl->assign('[EMAIL]',     $this->auth->email);
        $tpl->assign('[PHONE]',     $this->auth->phone);


        $tpl->assign('[LIST_ORDERS]', $tpl_orders);


        //if ( ! empty($news)) {
        //    foreach ($news as $item) {
        //        $tpl->news_item->assign('[DATE]',  ! empty($item['date_created']) ? date('m.d.Y', strtotime($item['date_created'])) : '');
        //        $tpl->news_item->assign('[TITLE]', ! empty($item['title']) ? htmlspecialchars($item['title']) : '');
        //        $tpl->news_item->assign('[ID]',    ! empty($item['id']) ? htmlspecialchars($item['id']) : '');
        //        $tpl->news_item->reassign();
        //    }
        //}

        return $tpl->render();
    }


    /**
     * @return array
     */
    private function getLicenses() {

        $licenses = [];
        $result   = Curl::get($this->config->system->apiserver . "/api/licenses", [], [
            'Core2-apikey: ' . $this->config->system->apikey,
            'Core2-auth-token: ' . $this->auth->token,
        ]);

        if ($result->getHttpCode() == '403') {
            header("HTTP/1.1 403 Forbidden");
            $this->logout();

        } else {
            $result_decode = $result->toArray();
            if ($result_decode !== null) {
                $licenses = ! empty($result_decode['licenses'])
                    ? $result_decode['licenses']
                    : [];
            }
        }

        return $licenses;
    }


    /**
     * @return array
     */
    private function getNews() {

        $result = Curl::get($this->config->system->apiserver . "/api/news", [
            'limit' => 4
        ], [
            'Core2-apikey: ' . $this->config->system->apikey
        ]);

        $result_decode = $result->toArray();
        $data          = ! empty($result_decode['data']) && ! empty($result_decode['data']['news'])
            ? $result_decode['data']['news']
            : [];

        return $data;
    }
}