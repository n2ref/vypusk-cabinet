<?php
namespace Cabinet\Plugin\Orders;
use Cabinet\Common;
use Cabinet\Mtpl;
use Cabinet\Tools;
use Cabinet\Alert;

require_once DOC_ROOT . '/cabinet/classes/Common.php';
require_once DOC_ROOT . '/cabinet/classes/Tools.php';
require_once DOC_ROOT . '/cabinet/classes/Mtpl.php';
require_once DOC_ROOT . '/cabinet/classes/Alert.php';

require_once 'classes/Order.php';
require_once 'classes/Order_Api.php';


/**
 * Class Controller
 * @package Cabinet\Plugin\Orders
 */
class Controller extends Common {

    /**
     * @return string
     * @throws \Exception
     */
    public function pageIndex() {

        $url       = '/#plugin=orders';
        $order_api = new Order_Api();

        // Заказ
        if ( ! empty($_GET['order_id'])) {

            $order_data = $order_api->getOrder($_GET['order_id']);

            if ($order_data) {
                $tpl_container = new Mtpl(__DIR__ . '/html/orders.container.html');
                $tpl_container->assign('[NUM]',    $order_data['num']);
                $tpl_container->assign('[STATUS]', $order_data['status']);

                $url .= '&order_id=' . $_GET['order_id'];
                $tabs = [
                    'order'          => 'Заказ',
                    'materials'      => 'Параметры альбома',
                    'clients_images' => 'Мои материалы',
                    'makets'         => 'Готовые макеты',
                    'childrens'      => 'Список учеников',
                    'teachers'       => 'Список учителей',
                    'documents'      => 'Документы',
                    'payments'       => 'Оплаты',
                    'support'        => 'Чат с администратором',
                ];
                $action = ! empty($_GET['action']) ? $_GET['action'] : key($tabs);

                foreach ($tabs as $tab_name => $tab_title) {
                    $tpl_container->tab->assign('[IS_ACTIVE]', $tab_name == $action ? 'active' : '');
                    $tpl_container->tab->assign('[URL]' ,      $url . '&action=' . $tab_name);
                    $tpl_container->tab->assign('[TITLE]',     $tab_title);
                    $tpl_container->tab->reassign();
                }

                ob_start();

                $order = new Order($order_data);

                switch ($action) {
                    case 'order':          echo $order->getOrder();   break;
                    case 'materials':      echo $order->getMaterials();   break;
                    case 'clients_images':
                        echo $order->getClientImages();
                        break;

                    case 'makets':
                        if ( ! empty($_GET['section_id'])) {
                            $section = $order_api->getMaketsSection($_GET['section_id']);

                            if ( ! empty($section)) {
                                $page_edit_id = ! empty($_GET['page_edit_id']) ? $_GET['page_edit_id'] : 0;
                                echo $order->getMaketsSection($section, $page_edit_id);
                            } else {
                                return file_get_contents(__DIR__ . '/html/orders.makets_incorrect_link.html');
                            }


                        } elseif ( ! empty($_GET['maket_id'])) {
                            $makets = $order_api->getMakets($_GET['order_id']);

                            $select_maket = [];
                            if ( ! empty($makets)) {
                                foreach ($makets as $maket) {
                                    if ($maket['id'] == $_GET['maket_id']) {
                                        $select_maket = $maket;
                                    }
                                }
                            }

                            if ( ! empty($select_maket)) {
                                echo $order->getMaketsSectionsList($select_maket);
                            } else {
                                return file_get_contents(__DIR__ . '/html/orders.makets_incorrect_link.html');
                            }


                        } else {
                            $makets = $order_api->getMakets($_GET['order_id']);

                            if ( ! empty($makets)) {
                                if (count($makets) > 1) {
                                    echo $order->getMaketsList($makets);

                                } else {
                                    echo $order->getMaketsSectionsList(current($makets));
                                }

                            } else {
                                return file_get_contents(__DIR__ . '/html/orders.makets_empty.html');
                            }
                        }
                        break;

                    case 'childrens':
                        if ( ! empty($_GET['edit'])) {
                            echo $order->getChildrensEdit($_GET['edit']);

                        } else {
                            $childrens = $order_api->getChildrens($_GET['order_id']);

                            if ((isset($_GET['edit']) && $_GET['edit'] == 0) || empty($childrens)) {
                                echo $order->getChildrensAdd();

                            } else {
                                echo $order->getChildrensList($childrens);
                            }
                        }
                        break;

                    case 'teachers':
                        if ( ! empty($_GET['edit'])) {
                            echo $order->getTeachersEdit($_GET['edit']);

                        } else {
                            $teachers = $order_api->getTeachers($_GET['order_id']);

                            if ((isset($_GET['edit']) && $_GET['edit'] == 0) || empty($teachers)) {
                                echo $order->getTeachersAdd();

                            } else {
                                echo $order->getTeachersList($teachers);
                            }
                        }
                        break;

                    case 'documents':
                        $documents = $order_api->getDocuments($_GET['order_id']);

                        if ( ! empty($documents)) {
                            echo $order->getDocumentsList($documents);

                        } else {
                            echo $order->getDocumentsEmpty();
                        }
                        break;

                    case 'payments':
                        $payments = $order_api->getPayments($_GET['order_id']);

                        if ( ! empty($payments)) {
                            echo $order->getPaymentsList($payments);

                        } else {
                            echo $order->getPaymentsEmpty();
                        }
                        break;

                    case 'support':
                        echo $order->getSupportPage();
                        break;
                }



                echo Tools::getCss('/plugins/orders/html/css/orders.css');

                $tpl_container->assign('[CONTENT]', ob_get_clean());
                return $tpl_container->render();

            } else {
                echo Alert::danger('Вернитесь к списку заказов и попробуйте еще раз', 'Указанный заказ не найден');
            }


        // Список заказов
        } else {
            $orders = $order_api->getOrders();

            if ( ! empty($orders)) {
                $tpl = new Mtpl(__DIR__ . '/html/orders.list.html');

                foreach ($orders as $order) {
                    $tpl->orders->assign('[ID]',             $order['id']);
                    $tpl->orders->assign('[NUM]',            $order['num']);
                    $tpl->orders->assign('[GROUP_NAME]',     $order['group_name']);
                    $tpl->orders->assign('[STATUS]',         $order['status']);
                    $tpl->orders->assign('[TOTAL_PRICE]',    Tools::commafy($order['total_price']));
                    $tpl->orders->assign('[TOTAL_PAYMENTS]', Tools::commafy($order['total_payments']));
                    $tpl->orders->assign('[TOTAL_DIFF]',     Tools::commafy($order['total_price'] - $order['total_payments']));
                    $tpl->orders->assign('[DATE_CREATED]',   $order['date_created']);
                    $tpl->orders->reassign();
                }

            } else {
                $tpl = new Mtpl(__DIR__ . '/html/orders.list.empty.html');
            }

            ob_start();
            echo Tools::getCss('/plugins/orders/html/css/orders.css');
            return ob_get_clean() . $tpl->render();
        }
    }


    /**
     * Страница успешной оплатой
     * @return string
     * @throws \Exception
     */
    public function pagePaySuccess() {

        $payment_id = ! empty($_REQUEST['paymentId']) ? $_REQUEST['paymentId'] : '';

        $tpl = new Mtpl(__DIR__ . '/html/orders.pay_success.html');


        if ($payment_id) {
            $order_api = new Order_Api();
            $payment   = $order_api->getPayment($payment_id);

            if ( ! empty($payment) && ! empty($payment['num'])) {
                $tpl->success->assign('[PAYMENT_NUM]',  $payment['num']);
                $tpl->success->assign('[PAYMENT_NOTE]', $payment['note']);
            } else {
                $tpl->error->assign('[MESSAGE]', 'Не удалось убедиться в завершении оплаты');
            }

        } else {
            $tpl->error->assign('[MESSAGE]', 'Не удалось убедиться в завершении оплаты');
        }

        $css = Tools::getCss('/plugins/orders/html/css/orders.pay-status.css');
        return $css . $tpl->render();
    }


    /**
     * Страница с ошибочной оплатой
     * @return string
     * @throws \Exception
     */
    public function pagePayFail() {

        $payment_id = ! empty($_REQUEST['paymentId']) ? $_REQUEST['paymentId'] : '';

        $tpl = new Mtpl(__DIR__ . '/html/orders.pay_fail.html');

        if ($payment_id) {
            $tpl->success->assign('[PAYMENT_NUM]', 'V' . $payment_id);
        } else {
            $tpl->error->assign('[MESSAGE]', 'Проверьте правильность открытого адреса');
        }


        $css = Tools::getCss('/plugins/orders/html/css/orders.pay-status.css');
        return $css . $tpl->render();
    }


    /**
     * @return string
     * @throws \Exception
     */
    public function rawClientPageEdit() {

        if (empty($_GET['order_id'])) {
            throw new \Exception('Отсутствует идентификатор заказа');
        }

        if (empty($_GET['section'])) {
            throw new \Exception('Не указана раздел');
        }

        if (empty($_GET['edit_page_num'])) {
            throw new \Exception('Не указан номер страницы');
        }

        $order_api = new Order_Api();
        $data      = $order_api->getSections($_GET['order_id']);

        if (empty($data['sections'])) {
            throw new \Exception('Не указан номер страницы');
        }

        $tpl = new Mtpl(__DIR__ . '/html/orders.client_images_edit.html');
        $tpl->assign('[ORDER_ID]', $_GET['order_id']);

        $page_id = 0;
        $text    = '';
        $comment = '';
        $images  = [];
        $files   = [];


        foreach ($data['sections'] as $section_name => $section) {
            if ($section_name == $_GET['section']) {
                if ( ! empty($section['pages'])) {
                    foreach ($section['pages'] as $page) {
                        if ($page['page_num'] == $_GET['edit_page_num']) {

                            if ( ! empty($page['images'])) {
                                foreach ($page['images'] as $image) {
                                    $images[] = [
                                        'id'         => $image['id'],
                                        'name'       => $image['filename'],
                                        'size'       => $image['filesize'],
                                        'previewUrl' => '/data/orders/get/section/image?image_id=' . $image['id'],
                                    ];
                                    $files[] = [
                                        'id' => $image['id']
                                    ];
                                }
                            }

                            $page_id = $page['id'];
                            $text    = $page['text'];
                            $comment = $page['comment'];
                            break 2;
                        }
                    }
                }
            }
        }

        $tpl->assign('[ID]',      $page_id);
        $tpl->assign('[TEXT]',    htmlspecialchars($text));
        $tpl->assign('[COMMENT]', htmlspecialchars($comment));
        $tpl->assign('[FILES]',   json_encode($files));
        $tpl->assign('[IMAGES]',  json_encode($images));

        return $tpl->render();
    }


    /**
     * @throws \Exception
     */
    public function rawClientPupilEdit() {

        if (empty($_GET['order_id'])) {
            throw new \Exception('Отсутствует идентификатор заказа');
        }

        if (empty($_GET['section'])) {
            throw new \Exception('Не указана раздел');
        }

        if (empty($_GET['edit_page_num'])) {
            throw new \Exception('Не указан номер страницы');
        }

        $order_api = new Order_Api();
        $data      = $order_api->getSections($_GET['order_id']);

        if (empty($data['sections'])) {
            throw new \Exception('Не указан номер страницы');
        }

        $tpl = new Mtpl(__DIR__ . '/html/orders.client_images_edit.html');
        $tpl->assign('[ORDER_ID]', $_GET['order_id']);

        $page_id = 0;
        $text    = '';
        $comment = '';
        $images  = [];
        $files   = [];


        foreach ($data['sections'] as $section_name => $section) {
            if ($section_name == $_GET['section']) {
                if ( ! empty($section['pages'])) {
                    foreach ($section['pages'] as $page) {
                        if ($page['page_num'] == $_GET['edit_page_num']) {

                            if ( ! empty($page['images'])) {
                                foreach ($page['images'] as $image) {
                                    $images[] = [
                                        'id'         => $image['id'],
                                        'name'       => $image['filename'],
                                        'size'       => $image['filesize'],
                                        'previewUrl' => '/data/orders/get/section/image?image_id=' . $image['id'],
                                    ];
                                    $files[] = [
                                        'id' => $image['id']
                                    ];
                                }
                            }

                            $page_id = $page['id'];
                            $text    = $page['text'];
                            $comment = $page['comment'];
                            break 2;
                        }
                    }
                }
            }
        }

        $tpl->assign('[ID]',      $page_id);
        $tpl->assign('[TEXT]',    htmlspecialchars($text));
        $tpl->assign('[COMMENT]', htmlspecialchars($comment));
        $tpl->assign('[FILES]',   json_encode($files));
        $tpl->assign('[IMAGES]',  json_encode($images));

        return $tpl->render();
    }


    /**
     * @return array|null|string
     * @throws \Exception
     */
    public function rawDocumentPdf() {

        if (empty($_GET['order_id'])) {
            throw new \Exception('Ошибка адреса');
        }

        if (empty($_GET['document_id'])) {
            throw new \Exception('Ошибка адреса');
        }

        $order_api = new Order_Api();
        $response = $order_api->getDocumentsPdf($_GET['order_id'], $_GET['document_id']);


        if (is_string($response)) {
            header("Expires: Mon, 1 Apr 1974 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
            header("Cache-Control: no-cache, must-revalidate");
            header("Pragma: no-cache");
            header('Content-type: application/pdf');

            return $response;
        }

        throw new \Exception('Не удалось получить документ. Попробуйте, пожалуйста, позже');
    }


    /**
     * @return array|null|string
     * @throws \Exception
     */
    public function rawPagePdf() {

        if (empty($_GET['section_id'])) {
            throw new \Exception('Ошибка адреса');
        }

        $order_api   = new Order_Api();
        $section_pdf = $order_api->getMaketsPdf($_GET['section_id']);

        if (empty($section_pdf)) {
            throw new \Exception('Не удалось получить картинку. Попробуйте, пожалуйста, позже');
        }

        $section_pdf['content'] = base64_decode($section_pdf['content']);

        header("Expires: Mon, 1 Apr 1974 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        header('Content-type: application/pdf');

        return $section_pdf['content'];
    }


    /**
     * @return array|null|string
     * @throws \Exception
     */
    public function rawPageImage() {

        if (empty($_GET['image_id'])) {
            throw new \Exception('Ошибка адреса');
        }

        $order_api = new Order_Api();
        $image = $order_api->getMaketsImage($_GET['image_id']);

        if (empty($image)) {
            throw new \Exception('Не удалось получить картинку. Попробуйте, пожалуйста, позже');
        }

        $image['content'] = base64_decode($image['content']);

        header("Expires: Mon, 1 Apr 1974 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        header('Content-type: ' . $image['type']);

        return $image['content'];
    }


    /**
     * @return array|null|string
     * @throws \Exception
     */
    public function rawPageImageFull() {

        if (empty($_GET['image_id'])) {
            throw new \Exception('Ошибка адреса');
        }

        $order_api = new Order_Api();
        $image = $order_api->getMaketsImageFull($_GET['image_id']);

        if (empty($image)) {
            throw new \Exception('Не удалось получить картинку. Попробуйте, пожалуйста, позже');
        }

        $image['content'] = base64_decode($image['content']);

        header("Expires: Mon, 1 Apr 1974 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        header('Content-type: ' . $image['type']);

        return $image['content'];
    }
}