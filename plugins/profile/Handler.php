<?php
namespace Cabinet\Plugin\Profile;
use Cabinet\Common;
use Cabinet\Curl;

require_once DOC_ROOT . '/cabinet/classes/Common.php';
require_once DOC_ROOT . '/cabinet/classes/Curl.php';


/**
 * Class Handler
 * @package Cabinet\Plugin\Licenses
 */
class Handler extends Common {


    /**
     * @return string
     */
    public function saveProfile() {

        $errors = [];

        if (empty($_POST['firstname'])) {
            $errors['firstname'] = 'Обязательное поле';
        }

        if (empty($_POST['email'])) {
            $errors['email'] = 'Обязательное поле';

        } elseif ( ! filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Неверный email';
        }

        if ($_POST['phone'] && ! preg_match('~^\+7 [\d]{3} [\d]{3} [\d]{4}$~', $_POST['phone'])) {
            $errors['phone'] = 'Неверный телефон';
        }


        if (empty($errors)) {
            $phone                  = !empty($_POST['phone']) ? $_POST['phone'] : '';
            $lastname               = !empty($_POST['lastname']) ? $_POST['lastname'] : '';
            $is_subscribe_sw        = !empty($_POST['is_subscribe_sw']) && $_POST['is_subscribe_sw'] == 'Y' ? 'Y' : 'N';
            $is_subscribe_update_sw = !empty($_POST['is_subscribe_update_sw']) && $_POST['is_subscribe_update_sw'] == 'Y' ? 'Y' : 'N';
            $result                 = $this->editProfile($_POST['email'], $phone, $_POST['firstname'], $lastname, $is_subscribe_sw, $is_subscribe_update_sw);

            if (empty($result['status']) || $result['status'] != 'success') {
                if ( ! empty($result['error_code'])) {
                    switch ($result['error_code']) {
                        case '139': $errors['email']     = 'Обязательное поле'; break;
                        case '140': $errors['email']     = 'Неверный email'; break;
                        case '141': $errors['firstname'] = 'Обязательное поле'; break;
                        case '142': $errors['general']   = 'Время жизни вашей сессии истекло. Чтобы войти в систему заново, обновите страницу (F5)'; break;
                        case '143': $errors['email']     = 'Указанный email уже кем-то занят'; break;
                        default: $errors['general']      = 'Ошибка. Попробуйте повторить попытку позже.';
                    }

                } else {
                    $errors['general'] = 'Ошибка. Попробуйте повторить попытку позже.';
                }
            }
        }



        if (empty($errors)) {
            return json_encode([
                'status'    => 'success',
                'new_email' => isset($result['new_email']) ? $result['new_email'] : false,
            ]);

        } else {
            return json_encode([
                'status'         => 'error',
                'error_messages' => $errors,
            ]);
        }
    }


    /**
     * @return string
     */
    public function changePassword() {

        try {
            if (empty($_POST['new_password'])) {
                throw new \Exception('Не заполнено обязательное поле');
            }


            $result = Curl::post($this->config->system->apiserver . "/api/clients/change/password", [
                'new_password' => $_POST['new_password']
            ], [
                'Core2-apikey: ' . $this->config->system->apikey,
                'Core2-auth-token: ' . $this->auth->token,
            ]);


            if ($result->getHttpCode() == '403') {
                header("HTTP/1.1 403 Forbidden");
                $this->logout();

            } else {
                $result_decode = $result->toArray();

                if ($result_decode !== null) {
                    if (empty($result_decode['data']) || empty($result_decode['data']['status']) || $result_decode['data']['status'] != 'success') {
                        $message = ! empty($result_decode['data']['message'])
                            ? $result_decode['data']['message']
                            : 'Ошибка. Попробуйте повторить попытку позже.';

                        throw new \Exception($message);

                    }
                } else {
                    throw new \Exception('Ошибка. Попробуйте повторить попытку позже.');
                }
            }


            return json_encode([
                'status' => 'success'
            ]);

        } catch (\Exception $e) {
            return json_encode([
                'status'        => 'error',
                'error_message' => $e->getMessage()
            ]);
        }
    }


    /**
     * Отправка сообщения в техподдержку
     * @return string
     */
    public function sendSupport() {

        try {
            if (empty($_POST['title']) || empty($_POST['message'])) {
                throw new \Exception('Не заполнено обязательное поле');
            }


            $result = Curl::post($this->config->system->apiserver . "/api/clients/send/support", [
                'title'   => $_POST['title'],
                'message' => $_POST['message'],
            ], [
                'Core2-apikey: ' . $this->config->system->apikey,
                'Core2-auth-token: ' . $this->auth->token,
            ]);


            if ($result->getHttpCode() == '403') {
                header("HTTP/1.1 403 Forbidden");
                $this->logout();

            } else {
                $result_decode = $result->toArray();

                if ($result_decode !== null) {
                    if (empty($result_decode['data']) || empty($result_decode['data']['status']) || $result_decode['data']['status'] != 'success') {
                        $message = ! empty($result_decode['data']['message'])
                            ? $result_decode['data']['message']
                            : 'Ошибка. Попробуйте повторить попытку позже.';

                        throw new \Exception($message);

                    }
                } else {
                    throw new \Exception('Ошибка. Попробуйте повторить попытку позже.');
                }
            }


            return json_encode([
                'status' => 'success'
            ]);

        } catch (\Exception $e) {
            return json_encode([
                'status'        => 'error',
                'error_message' => $e->getMessage()
            ]);
        }
    }


    /**
     * @param string $email
     * @param string $phone
     * @param string $firstname
     * @param string $lastname
     * @param string $is_subscribe_sw
     * @param string $is_subscribe_update_sw
     * @return array
     */
    private function editProfile($email, $phone, $firstname, $lastname = '', $is_subscribe_sw = 'Y', $is_subscribe_update_sw = 'Y') {

        $data   = [];
        $result = Curl::post($this->config->system->apiserver . "/api/clients/profile", [
            'email'                  => $email,
            'phone'                  => $phone,
            'firstname'              => $firstname,
            'lastname'               => $lastname,
            'is_subscribe_sw'        => $is_subscribe_sw,
            'is_subscribe_update_sw' => $is_subscribe_update_sw,
        ], [
            'Core2-apikey: ' . $this->config->system->apikey,
            'Core2-auth-token: ' . $this->auth->token,
        ]);


        if ($result->getHttpCode() == '403') {
            header("HTTP/1.1 403 Forbidden");
            $this->logout();

        } else {
            $result_decode = $result->toArray();
            if ($result_decode !== null) {
                $data = ! empty($result_decode['data']) ? $result_decode['data'] : $result_decode;

                if ( ! empty($data['status']) && $data['status'] == 'success') {
                    $this->auth->phone     = $phone;
                    $this->auth->firstname = $firstname;
                    $this->auth->lastname  = $lastname;

                    $_SESSION['auth']->phone     = $phone;
                    $_SESSION['auth']->firstname = $firstname;
                    $_SESSION['auth']->lastname  = $lastname;
                }
            }
        }

        return $data;
    }
}