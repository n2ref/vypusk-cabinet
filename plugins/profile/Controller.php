<?php
namespace Cabinet\Plugin\Profile;
use Cabinet\Common;
use Cabinet\Curl;
use Cabinet\Tabs;
use Cabinet\Tools;

require_once DOC_ROOT . '/cabinet/classes/Common.php';
require_once DOC_ROOT . '/cabinet/classes/Curl.php';
require_once DOC_ROOT . '/cabinet/classes/Tabs.php';



/**
 * Class Controller
 * @package Cabinet\Plugin\Profile
 */
class Controller extends Common {

    /**
     * @return string
     * @throws \Exception
     */
    public function pageIndex() {

        $url = '/#plugin=profile';

        $tabs = new Tabs();
        $tabs->addTab('Профиль',       'profile',  $url);
        $tabs->addTab('Смена пароля',  'password', $url);
        $tabs->addTab('Техподдержка ', 'support',  $url);

        ob_start();

        switch ($tabs->getActiveTab()) {
            case 'profile':
                $profile = $this->getProfile();

                if (empty($_GET['edit'])) {
                    $tpl = file_get_contents(__DIR__ . '/html/profile.html');
                } else {
                    echo Tools::getCss('/plugins/profile/html/css/style.css');
                    echo Tools::getJs('/plugins/profile/html/js/jquery.maskedinput.min.js');
                    echo Tools::getJs('/plugins/profile/html/js/profile.js');

                    $tpl = file_get_contents(__DIR__ . '/html/profile_edit.html');
                    $tpl = str_replace('[CHECKED]',        $profile['is_subscribe_sw'] == 'Y' ? 'checked="checked"' : '', $tpl);
                    $tpl = str_replace('[CHECKED_UPDATE]', $profile['is_subscribe_update_sw'] == 'Y' ? 'checked="checked"' : '', $tpl);
                }


                $tpl = str_replace('[FIRSTNAME]', $profile['firstname'], $tpl);
                $tpl = str_replace('[LASTNAME]',  $profile['lastname'], $tpl);
                $tpl = str_replace('[EMAIL]',     $profile['email'], $tpl);
                $tpl = str_replace('[PHONE]',     $profile['phone'], $tpl);

                echo $tpl;
                break;

            case 'password':
                echo Tools::getJs('/plugins/profile/html/js/md5.js');
                echo Tools::getJs('/plugins/profile/html/js/profile.js');

                $tpl = file_get_contents(__DIR__ . '/html/profile_password.html');
                echo $tpl;
                break;

            case 'support':
                echo Tools::getJs('/plugins/profile/html/js/profile.js');

                $tpl = file_get_contents(__DIR__ . '/html/profile_support.html');
                echo $tpl;
                break;
        }

        $tabs->setContent(ob_get_clean());
        return $tabs->render();
    }


    /**
     * Отписка
     * @return string
     */
    public function pageMailerUnsubscribe() {

        if (empty($_GET['public_id']) || empty($_GET['type'])) {
            return 'Ошибка адреса';
        }

        $this->unsubscribeProfile($_GET['public_id'], $_GET['type']);

        $tpl = file_get_contents(__DIR__ . '/html/profile_unsubscribe.html');
        $tpl = str_replace('[PUBLIC_ID]', $_GET['public_id'], $tpl);
        $tpl = str_replace('[TYPE]',      $_GET['type'],      $tpl);
        return $tpl;
    }


    /**
     * Переподписка
     * @return string
     */
    public function pageMailerResubscribe() {

        if (empty($_GET['public_id']) || empty($_GET['type'])) {
            return 'Ошибка адреса';
        }

        $this->resubscribeProfile($_GET['public_id'], $_GET['type']);

        $tpl = file_get_contents(__DIR__ . '/html/profile_resubscribe.html');
        return $tpl;
    }


    /**
     * Сохранение профиля
     * @return string
     */
    public function pageEmailChange() {

        $status = 'success';

        if ( ! empty($_GET['email_token'])) {
            $result  = $this->approveEmail($_GET['email_token']);

            if (empty($result['status']) || $result['status'] != 'success') {
                if ( ! empty($result['error_code'])) {
                    switch ($result['error_code']) {
                        case '144': $message = 'Открытый адрес неверен. Проверьте его и повторите попытку.'; break;
                        case '145': $message = 'Ссылка для смены email устарела.'; break;
                        default: $message    = 'Ошибка. Попробуйте повторить попытку позже.';
                    }

                } else {
                    $message = 'Ошибка. Попробуйте повторить попытку позже.';
                }

                $status = 'error';

            } else {
                $message = 'Email успешно изменен.';
            }

        } else {
            $status  = 'error';
            $message = 'Ошибка. Проверьте адрес.';
        }

        $style = Tools::getCss('/plugins/profile/html/css/style.css');

        $tpl = file_get_contents(__DIR__ . '/html/profile_email_approve.html');

        $tpl = str_replace('[MESSAGE]', $message, $tpl);
        $tpl = str_replace('[STATUS]',  $status, $tpl);

        return $style . $tpl;
    }


    /**
     * @return array
     */
    private function getProfile() {

        $profile = [];
        $result  = Curl::get($this->config->system->apiserver . "/api/clients/profile", [], [
            'Core2-apikey: ' . $this->config->system->apikey,
            'Core2-auth-token: ' . $this->auth->token,
        ]);


        if ($result->getHttpCode() == '403') {
            header("HTTP/1.1 403 Forbidden");
            $this->logout();

        } else {
            $result_decode = $result->toArray();
            $profile = ! empty($result_decode['data']) ? $result_decode['data'] : [];
        }


        return $profile;
    }


    /**
     * @param string $public_id
     * @param string $type
     * @return bool
     */
    private function unsubscribeProfile($public_id, $type) {

        $is_unsubscribe = false;
        $result = Curl::post($this->config->system->apiserver . "/api/mailer/unsubscribe", [
            'public_id' => $public_id,
            'type'      => $type,
        ], [
            'Core2-apikey: ' . $this->config->system->apikey
        ]);


        if ($result->getHttpCode() == '403') {
            header("HTTP/1.1 403 Forbidden");
            $this->logout();

        } else {
            $result_decode = $result->toArray();
            $data = ! empty($result_decode['data']) ? $result_decode['data'] : [];
            $is_unsubscribe = ! empty($data['success']) && $data['success'];
        }

        return $is_unsubscribe;
    }


    /**
     * @param string $public_id
     * @param string $type
     * @return bool
     */
    private function resubscribeProfile($public_id, $type) {

        $is_resubscribe = false;
        $result = Curl::post($this->config->system->apiserver . "/api/mailer/resubscribe", [
            'public_id' => $public_id,
            'type'      => $type,
        ], [
            'Core2-apikey: ' . $this->config->system->apikey
        ]);


        if ($result->getHttpCode() == '403') {
            header("HTTP/1.1 403 Forbidden");
            $this->logout();

        } else {
            $result_decode = $result->toArray();
            $data = ! empty($result_decode['data']) ? $result_decode['data'] : [];
            $is_resubscribe = ! empty($data['success']) && $data['success'];
        }

        return $is_resubscribe;
    }


    /**
     * @param string $email_token
     * @return array
     */
    private function approveEmail($email_token) {

        $data   = [];
        $result = Curl::post($this->config->system->apiserver . "/api/clients/profile/email/approve", [
            'email_token' => $email_token
        ], [
            'Core2-apikey: ' . $this->config->system->apikey
        ]);


        $result_decode = $result->toArray();
        if ($result_decode !== null) {
            $data = ! empty($result_decode['data']) ? $result_decode['data'] : $result_decode;

            if ( ! empty($data['status']) && $data['status'] == 'success' && ! empty($data['email'])) {
                $this->auth->email = $data['email'];

                $_SESSION['auth']->email = $data['email'];
            }
        }

        return $data;
    }


    /**
     * @param string $email_token
     * @return string
     */
    private function approveEmailPage($email_token) {


    }
}