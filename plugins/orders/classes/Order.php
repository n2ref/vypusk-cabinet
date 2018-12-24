<?php
namespace Cabinet\Plugin\Orders;
use Cabinet\Common;
use Cabinet\Tools;
use Cabinet\Mtpl;
use function PHPSTORM_META\map;

require_once DOC_ROOT . '/cabinet/classes/Mtpl.php';
require_once DOC_ROOT . '/cabinet/classes/Tools.php';


/**
 * Class Order
 * @package Cabinet\Plugin\Orders
 */
class Order extends Common {

    private $order = [];


    /**
     * Order constructor.
     * @param array $order_data
     */
    public function __construct($order_data) {
        $this->order = $order_data;
        parent::__construct();
    }


    /**
     * @return string
     * @throws \Exception
     */
    public function getOrder() {

        $tpl = new Mtpl(__DIR__ . '/../html/orders.order.html');
        $tpl->assign('[DATE_CREATED]',           date('d.m.Y', strtotime($this->order['date_created'])));
        $tpl->assign('[SCHOOL_TITLE]',           $this->order['school_title']);
        $tpl->assign('[GROUP_NAME]',             implode(', ', explode(',', $this->order['group_name'])));
        $tpl->assign('[PUPILS_COUNT]',           $this->order['pupils_count']);
        $tpl->assign('[ALBUMS_COUNT_PUPILS]',    $this->order['album_count_pupil']);
        $tpl->assign('[ALBUMS_COUNT_TEACHERS]',  $this->order['album_count_teacher']);
        $tpl->assign('[ALBUMS_COUNT_FREE]',      $this->order['album_count_free']);
        $tpl->assign('[ALBUMS_COUNT_TOTAL]',     $this->order['album_count_pupil'] + $this->order['album_count_teacher'] + $this->order['album_count_free']);
        $tpl->assign('[ALBUM_PRICE]',            Tools::commafy($this->order['album_price']));
        $tpl->assign('[ADDITIONAL_PRICE]',       Tools::commafy($this->order['additional_price']));
        $tpl->assign('[ALBUM_PRICE_ADDITIONAL]', Tools::commafy($this->order['album_price'] + $this->order['additional_price']));

        if ( ! empty($this->order['additional'])) {
            foreach ($this->order['additional'] as $additional) {
                $tpl->additional->assign('[COUNT]', $additional['count']);
                $tpl->additional->assign('[TITLE]', $additional['title']);
                $tpl->additional->reassign();
            }
        }

        $tpl->assign('[SERVICES_PRICE]', Tools::commafy($this->order['services_price']));


        if ( ! empty($this->order['shots'])) {
            foreach ($this->order['shots'] as $date_shot) {
                $tpl->shot->assign('[DATE_SHOT]', $date_shot);
                $tpl->shot->reassign();
            }

        } else {
            $tpl->shot->assign('[DATE_SHOT]', 'не назначена');
        }

        if ( ! empty($this->order['services'])) {
            foreach ($this->order['services'] as $services) {
                $tpl->services->assign('[COUNT]', $services['count']);
                $tpl->services->assign('[TITLE]', $services['title']);
                $tpl->services->reassign();
            }
        }

        $tpl->assign('[ALBUM_PAGES]', $this->order['count_pages']);

        if ( ! empty($this->order['discount']) && $this->order['discount'] > 0) {
            $discount_price = round(($this->order['total_price_no_discount'] / 100) * $this->order['discount']);

            $tpl->discount->assign('[TOTAL_PRICE_NO_DISCOUNT]', Tools::commafy($this->order['total_price_no_discount']));
            $tpl->discount->assign('[DISCOUNT]',                $this->order['discount']);
            $tpl->discount->assign('[DISCOUNT_PRICE]',          Tools::commafy($discount_price));
        }


        $tpl->assign('[TOTAL_PRICE]', Tools::commafy($this->order['total_price']));

        return $tpl->render();
    }


    /**
     * @return string
     * @throws \Exception
     */
    public function getMaterials() {

        $order_api = new Order_Api();
        $materials = $order_api->getMaterials($this->order['id']);

        if ( ! empty($materials)) {
            $tpl = new Mtpl(__DIR__ . '/../html/orders.materials.html');
            $tpl->assign('[DESIGN]',      $materials['design']);
            $tpl->assign('[COLLECTION]',  $materials['collection']);
            $tpl->assign('[PAPER]',       $materials['paper']);
            $tpl->assign('[FORMAT]',      $materials['format']);
            $tpl->assign('[PAGES_COUNT]', $materials['pages_count']);

            if ($materials['cover_ready'] !== 'N') {
                $tpl->cover_ready->assign('[COVER_READY]', $materials['cover_ready'] == 'Y' ? 'Да' : 'Нет');
            }

            if ($materials['cover_cardboard'] !== 'Нет') {
                $tpl->cover_cardboard->assign('[COVER_CARDBOARD]', $materials['cover_cardboard']);
            }

            if ($materials['cover_lamination'] !== 'Нет') {
                $tpl->cover_lamination->assign('[COVER_LAMINATION]', $materials['cover_lamination']);
            }

            if ($materials['binding_materials'] !== 'Нет') {
                $tpl->binding_materials->assign('[BINDING_MATERIALS]', $materials['binding_materials']);
            }

            if ($materials['cliche'] !== 'Нет') {
                $tpl->cliche->assign('[CLICHE]', $materials['cliche']);
            }

            if ($materials['cliche_extra'] !== 'Нет') {
                $tpl->cliche_extra->assign('[CLICHE_EXTRA]', $materials['cliche_extra']);
            }

            if ($materials['foil'] !== 'Нет') {
                $tpl->foil->assign('[FOIL]', $materials['foil']);
            }

            if ($materials['endpapers'] !== 'Нет') {
                $tpl->endpapers->assign('[ENDPAPERS]', $materials['endpapers']);
            }

            if ($materials['lamination_block'] !== 'Нет') {
                $tpl->lamination_block->assign('[LAMINATION_BLOCK]', $materials['lamination_block']);
            }
        } else {
            $tpl = new Mtpl(__DIR__ . '/../html/orders.materials_empty.html');
        }



        return $tpl->render();
    }


    /**
     * @return false|string
     * @throws \Exception
     */
    public function getClientImages() {

        $order_api = new Order_Api();
        $data      = $order_api->getSections($this->order['id']);


        $tpl = new Mtpl(__DIR__ . '/../html/orders.client_images.html');
        $tpl->assign('[ORDER_ID]', $this->order['id']);

        ob_start();
        if ( ! empty($data['sections'])) {
            foreach ($data['sections'] as $section_name => $section) {

                $tpl->sections->section->assign('[SECTION_TITLE]', $section['title']);

                if ($section_name == 'personal') {
                    $tpl->sections->section->assign('[COUNT_PAGES_TEXT]', 'Количество учеников: ' . count($data['pupils']));

                    foreach ($data['pupils'] as $pupil) {
                        $page_id = 0;
                        $text    = '';
                        $comment = '';
                        $images  = [];
                        $files   = [];

                        if ( ! empty($section['pages'])) {
                            foreach ($section['pages'] as $page) {
                                if ($page['pupil_id'] == $pupil['id']) {

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

                                    continue;
                                }
                            }
                        }

                        $tpl->sections->section->page->assign('[SECTION_NAME]', $section_name);
                        $tpl->sections->section->page->assign('[PAGE]',         $pupil['name']);
                        $tpl->sections->section->page->assign('[ID]',           $pupil['id']);
                        $tpl->sections->section->page->assign('[TEXT]',         $text ? htmlspecialchars($text) : '--');
                        $tpl->sections->section->page->assign('[COMMENT]',      $comment ? htmlspecialchars($comment) : '--');

                        if ( ! empty($images)) {
                            foreach ($images as $image) {
                                $tpl->sections->section->page->image->assign('[SRC]',  $image['previewUrl']);
                                $tpl->sections->section->page->image->assign('[NAME]', $image['name']);
                                $tpl->sections->section->page->image->reassign();
                            }
                        }

                        $tpl->sections->section->page->reassign();
                    }


                } else {
                    $tpl->sections->section->assign('[COUNT_PAGES_TEXT]', 'Количество страниц: ' . $section['count_pages']);

                    for ($i = 1; $i <= $section['count_pages']; $i++) {
                        $page_id = 0;
                        $text    = '';
                        $comment = '';
                        $images  = [];
                        $files   = [];

                        if ( ! empty($section['pages'])) {
                            foreach ($section['pages'] as $page) {
                                if ($page['page_num'] == $i) {

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

                                    continue;
                                }
                            }
                        }

                        $tpl->sections->section->page->assign('[SECTION_NAME]', $section_name);
                        $tpl->sections->section->page->assign('[PAGE]',        'Страница ' . $i);
                        $tpl->sections->section->page->assign('[ID]',          $i);
                        $tpl->sections->section->page->assign('[TEXT]',        $text ? htmlspecialchars($text) : '--');
                        $tpl->sections->section->page->assign('[COMMENT]',     $comment ? htmlspecialchars($comment) : '--');

                        if ( ! empty($images)) {
                            foreach ($images as $image) {
                                $tpl->sections->section->page->image->assign('[SRC]',  $image['previewUrl']);
                                $tpl->sections->section->page->image->assign('[NAME]', $image['name']);
                                $tpl->sections->section->page->image->reassign();
                            }
                        }

                        $tpl->sections->section->page->reassign();
                    }
                }

                $tpl->sections->section->reassign();
            }

        } else {
            $tpl->touchBlock('no_sections');
        }

        echo Tools::getCss('/plugins/orders/html/css/fileup.min.css');
        echo Tools::getCss('/plugins/orders/html/css/fileup.theme2.min.css');
        echo Tools::getCss('/plugins/orders/html/css/orders.client_images.css');
        echo Tools::getJs('/plugins/orders/html/js/fileup.min.js');
        echo Tools::getJs('/plugins/orders/html/js/orders.client_images.js');

        echo $tpl->render();

        return ob_get_clean();
    }


    /**
     * @param $makets
     * @return Mtpl
     * @throws \Exception
     */
    public function getMaketsList($makets) {

        $tpl = new Mtpl(__DIR__ . '/../html/orders.makets_2_list.html');

        foreach ($makets as $k => $maket) {
            $tpl->maket->assign('[#]',            $k + 1);
            $tpl->maket->assign('[ORDER_ID]',     $this->order['id']);
            $tpl->maket->assign('[MAKET_ID]',     $maket['id']);
            $tpl->maket->assign('[VERSION]',      $maket['version']);
            $tpl->maket->assign('[DESCRIPTION]',  $maket['description']);
            $tpl->maket->assign('[DATE_CREATED]', date('d.m.Y', strtotime($maket['date_created'])));
            $tpl->maket->reassign();
        }
        return $tpl;
    }


    /**
     * @param $maket
     * @return Mtpl
     * @throws \Exception
     */
    public function getMaketsSectionsList($maket) {

        $tpl = new Mtpl(__DIR__ . '/../html/orders.makets_2_sections_list.html');
        $tpl->assign('[ORDER_ID]', $this->order['id']);
        $tpl->assign('[MAKET_ID]', $maket['id']);
        $tpl->assign('[VERSION]',  $maket['version']);

        if ( ! empty($maket['sections'])) {
            foreach ($maket['sections'] as $k => $section) {
                $tpl->section->assign('[#]',          $k + 1);
                $tpl->section->assign('[SECTION_ID]', $section['id']);
                $tpl->section->assign('[TITLE]',      $section['title']);
                $tpl->section->assign('[PAGE_COUNT]', $section['count_pages']);

                if ($section['status'] == 'pending') {
                    $tpl->section->touchBlock('status_pending');

                } elseif ($section['status'] == 'accepted') {
                    $tpl->section->touchBlock('status_accepted');

                } else {
                    $tpl->section->touchBlock('status_rework');
                }

                $tpl->section->reassign();
            }
        }
        return $tpl;
    }


    /**
     * @param $section
     * @param $page_edit_id
     * @return Mtpl
     * @throws \Exception
     */
    public function getMaketsSection($section, $page_edit_id = 0) {

        $tpl = new Mtpl(__DIR__ . '/../html/orders.makets_2_edit.html');
        $tpl->assign('[ORDER_ID]',      $this->order['id']);
        $tpl->assign('[MAKET_ID]',      $section['maket_id']);
        $tpl->assign('[VERSION]',       $section['maket_version']);
        $tpl->assign('[SECTION_ID]',    $section['id']);
        $tpl->assign('[SECTION_TITLE]', $section['title']);

        if ( ! empty($section['pages'])) {
            foreach ($section['pages'] as $k => $page) {
                $tpl->page->assign('[#]',        $k + 1);
                $tpl->page->assign('[ID]',       $page['id']);
                $tpl->page->assign('[IMAGE_ID]', $page['image_id']);


                if ($page_edit_id == $page['id']) {
                    $images = [];
                    $files  = [];

                    if ( ! empty($page['images'])) {
                        foreach ($page['images'] as $image) {
                            $images[] = [
                                'id'         => $image['id'],
                                'name'       => $image['filename'],
                                'size'       => $image['filesize'],
                                'previewUrl' => '/raw/orders/page/image?image_id=' . $image['id'],
                            ];
                            $files[] = [
                                'id' => $image['id']
                            ];
                        }
                    }


                    $tpl->page->connect_edit->assign('[COMMENT]', $page['comment']);
                    $tpl->page->connect_edit->assign('[IMAGES]',  json_encode($images));
                    $tpl->page->connect_edit->assign('[FILES]',   json_encode($files));

                } else {
                    $tpl->page->connect_read->assign('[COMMENT]', $page['comment']);

                    if ( ! empty($page['images'])) {
                        foreach ($page['images'] as $image) {
                            $tpl->page->connect_read->image->assign('[IMAGE_ID]', $image['id']);
                            $tpl->page->connect_read->image->reassign();
                        }
                    }
                }

                $tpl->page->reassign();
            }
        }

        ob_start();
        echo Tools::getCss('/plugins/orders/html/css/fileup.min.css');
        echo Tools::getCss('/plugins/orders/html/css/fileup.theme2.min.css');
        echo Tools::getCss('/plugins/orders/html/css/lightbox.min.css');
        echo Tools::getCss('/plugins/orders/html/css/orders.makets.css');
        echo Tools::getJs('/plugins/orders/html/js/fileup.min.js');
        echo Tools::getJs('/plugins/orders/html/js/lightbox.min.js');
        echo Tools::getJs('/plugins/orders/html/js/orders.makets.js');

        echo $tpl->render();
        return ob_get_clean();
    }


    /**
     * @param $childrens
     * @return string
     * @throws \Exception
     */
    public function getChildrensList($childrens) {

        $tpl = new Mtpl(__DIR__ . '/../html/orders.childrens_list.html');
        $tpl->assign('[ORDER_ID]', $this->order['id']);

        if ( ! empty($childrens)) {
            foreach ($childrens as $key => $children) {
                $tpl->children->assign('[#]',            $key + 1);
                $tpl->children->assign('[CHILD_ID]',     $children['id']);
                $tpl->children->assign('[GROUP_NAME]',   $children['group_name']);
                $tpl->children->assign('[NAME]',         $children['lastname'] .' '. $children['firstname']);
                $tpl->children->assign('[ALBUMS_COUNT]', $children['albums_count']);
                $tpl->children->assign('[ADDITIONAL]',   $children['additional']);
                $tpl->children->assign('[COMMENT]',      $children['comment']);
                $tpl->children->reassign();
            }
        }

        $js = Tools::getJs('plugins/orders/html/js/orders.childrens.js');
        return $js . $tpl->render();
    }


    /**
     * @param $child_id
     * @return string
     * @throws \Exception
     */
    public function getChildrensEdit($child_id) {

        $order_api = new Order_Api();
        $child = $order_api->getChildren($this->order['id'], $child_id);

        $groups = array_map('trim', explode(',', $this->order['group_name']));
        $groups = array_combine(array_values($groups), array_values($groups));

        $tpl = new Mtpl(__DIR__ . '/../html/orders.childrens_edit.html');
        $tpl->fillDropDown('group', $groups);
        $tpl->assign('[ORDER_ID]',     $this->order['id']);
        $tpl->assign('[CHILD_ID]',     $child_id);
        $tpl->assign('[LAST_NAME]',    $child['lastname']);
        $tpl->assign('[FIRST_NAME]',   $child['firstname']);
        $tpl->assign('[ALBUMS_COUNT]', $child['albums_count']);
        $tpl->assign('[ADDITIONAL]',   $child['additional']);
        $tpl->assign('[COMMENT]',      $child['comment']);

        $js = Tools::getJs('plugins/orders/html/js/orders.childrens.js');
        return $js . $tpl->render();
    }


    /**
     * @return string
     * @throws \Exception
     */
    public function getChildrensAdd() {

        $groups = array_map('trim', explode(',', $this->order['group_name']));
        $groups = array_combine(array_values($groups), array_values($groups));

        $tpl = new Mtpl(__DIR__ . '/../html/orders.childrens_add.html');
        $tpl->fillDropDown('group', $groups);
        $tpl->assign('[ORDER_ID]', $this->order['id']);

        $js = Tools::getJs('plugins/orders/html/js/orders.childrens.js');
        return $js . $tpl->render();
    }


    /**
     * @param $teachers
     * @return string
     * @throws \Exception
     */
    public function getTeachersList($teachers) {

        $tpl = new Mtpl(__DIR__ . '/../html/orders.teachers_list.html');
        $tpl->assign('[ORDER_ID]', $this->order['id']);

        if ( ! empty($teachers)) {
            foreach ($teachers as $key => $teacher) {
                $tpl->teacher->assign('[#]',            $key + 1);
                $tpl->teacher->assign('[TEACHER_ID]',   $teacher['id']);
                $tpl->teacher->assign('[NAME]',         $teacher['name']);
                $tpl->teacher->assign('[POSITION]',     $teacher['position']);
                $tpl->teacher->assign('[ALBUMS_COUNT]', $teacher['albums_count']);
                $tpl->teacher->reassign();
            }
        }

        $js = Tools::getJs('plugins/orders/html/js/orders.teachers.js');
        return $js . $tpl->render();
    }


    /**
     * @param $teacher_id
     * @return string
     * @throws \Exception
     */
    public function getTeachersEdit($teacher_id) {

        $order_api = new Order_Api();
        $teacher = $order_api->getTeacher($this->order['id'], $teacher_id);

        $groups = array_map('trim', explode(',', $this->order['group_name']));
        $groups = array_combine(array_values($groups), array_values($groups));

        $tpl = new Mtpl(__DIR__ . '/../html/orders.teachers_edit.html');
        $tpl->fillDropDown('group',    $groups);
        $tpl->assign('[ORDER_ID]',     $this->order['id']);
        $tpl->assign('[TEACHER_ID]',   $teacher_id);
        $tpl->assign('[NAME]',         $teacher['name']);
        $tpl->assign('[POSITION]',     $teacher['position']);
        $tpl->assign('[ALBUMS_COUNT]', $teacher['albums_count']);

        $js = Tools::getJs('plugins/orders/html/js/orders.teachers.js');
        return $js . $tpl->render();
    }


    /**
     * @return string
     * @throws \Exception
     */
    public function getTeachersAdd() {

        $groups = array_map('trim', explode(',', $this->order['group_name']));
        $groups = array_combine(array_values($groups), array_values($groups));

        $tpl = new Mtpl(__DIR__ . '/../html/orders.teachers_add.html');
        $tpl->fillDropDown('group', $groups);
        $tpl->assign('[ORDER_ID]', $this->order['id']);

        $js = Tools::getJs('plugins/orders/html/js/orders.teachers.js');
        return $js . $tpl->render();
    }


    /**
     * @param $documents
     * @return string
     * @throws \Exception
     */
    public function getDocumentsList($documents) {

        $tpl = new Mtpl(__DIR__ . '/../html/orders.documents_list.html');
        $tpl->assign('[ORDER_ID]', $this->order['id']);

        if ( ! empty($documents)) {
            foreach ($documents as $key => $document) {
                $tpl->document->assign('[#]',             $key + 1);
                $tpl->document->assign('[DOCUMENT_ID]',   $document['id']);
                $tpl->document->assign('[TITLE]',         $document['title']  . ($document['num'] ? '№' . $document['num'] : ''));
                $tpl->document->assign('[DATE_DOCUMENT]', date('d.m.Y', strtotime($document['date_document'])));

                if ($document['is_accepted_sw'] == 'Y') {
                    $tpl->document->touchBlock('status_accepted');
                } elseif ($document['is_accepted_sw'] == 'N') {
                    $tpl->document->touchBlock('status_rejected');
                } else {
                    $tpl->document->touchBlock('status_null');
                }

                $tpl->document->reassign();
            }
        }

        $js = Tools::getJs('plugins/orders/html/js/orders.documents.js');
        return $js . $tpl->render();
    }


    /**
     * @return string
     * @throws \Exception
     */
    public function getDocumentsEmpty() {

        $tpl = new Mtpl(__DIR__ . '/../html/orders.documents_empty.html');
        return $tpl->render();
    }


    /**
     * @param $payments
     * @return string
     * @throws \Exception
     */
    public function getPaymentsList($payments) {

        $tpl = new Mtpl(__DIR__ . '/../html/orders.payments_list.html');
        $tpl->assign('[ORDER_ID]', $this->order['id']);

        if ( ! empty($payments)) {
            foreach ($payments as $key => $payment) {
                $tpl->payment->assign('[#]',            $key + 1);
                $tpl->payment->assign('[PAYMENT_ID]',   $payment['id']);
                $tpl->payment->assign('[NUM]',          $payment['num']);
                $tpl->payment->assign('[NOTE]',         $payment['note']);
                $tpl->payment->assign('[PRICE_FORMAT]', Tools::commafy($payment['price']));
                $tpl->payment->assign('[CURRENCY]',     $payment['currency']);

                if ($payment['status_transaction'] == 'pending') {
                    $protocol = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ||
                                (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') ? 'https' : 'http';

                    $tpl->payment->status_pending->assign('[PRICE]',        $payment['price']);
                    $tpl->payment->status_pending->assign('[PAYMENT_URL]',  $this->config->yandex->money->payment_url);
                    $tpl->payment->status_pending->assign('[SHOP_ID]',      $this->config->yandex->money->shop_id);
                    $tpl->payment->status_pending->assign('[SCID]',         $this->config->yandex->money->scid);

                    $tpl->payment->status_pending->assign('[ORDER_AGREEMENT]', $payment['order_agreement']);
                    $tpl->payment->status_pending->assign('[PAYMENT_NOTE]',    addslashes(htmlspecialchars($payment['note'])));
                    $tpl->payment->status_pending->assign('[USER_ID]',         $this->auth->id);
                    $tpl->payment->status_pending->assign('[USER_NAME]',       addslashes(htmlspecialchars($this->auth->lastname . ' ' . $this->auth->firstname)));
                    $tpl->payment->status_pending->assign('[USER_EMAIL]',      $this->auth->email);
                    $tpl->payment->status_pending->assign('[USER_PHONE]',      $this->auth->phone ? '+' . preg_replace('~[^\d]~', '', $this->auth->phone) : '');

                    $tpl->payment->status_pending->assign('[SUCCESS_URL]',  $protocol . '://' . $_SERVER['SERVER_NAME'] . '/orders/pay/success');
                    $tpl->payment->status_pending->assign('[FAIL_URL]',     $protocol . '://' . $_SERVER['SERVER_NAME'] . '/orders/pay/fail');


                } elseif ($payment['status_transaction'] == 'completed') {
                    $tpl->payment->touchBlock('status_completed');
                }

                $tpl->payment->reassign();
            }
        }

        $js = Tools::getJs('plugins/orders/html/js/orders.payments.js');
        return $js . $tpl->render();
    }


    /**
     * @return string
     * @throws \Exception
     */
    public function getPaymentsEmpty() {

        $tpl = new Mtpl(__DIR__ . '/../html/orders.payments_empty.html');
        return $tpl->render();
    }


    /**
     * @return string
     * @throws \Exception
     */
    public function getSupportPage() {
//
//        $tpl = new Mtpl(__DIR__ . '/../html/orders.payments_empty.html');
//        return $tpl->render();


        echo Tools::getCss('/plugins/orders/html/css/support.support.css');
        //echo Tools::getJs('/plugins/orders/html/js/support.chat.js');
        return file_get_contents(__DIR__ . '/../html/orders.support.html');
    }
}