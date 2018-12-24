<?php
namespace Cabinet;

require_once 'Common.php';
require_once 'Curl.php';


/**
 * Class Social
 * @package Cabinet
 */
class Social extends Common {


    /**
     * @param $code
     * @return array
     * @throws \Exception
     */
    public function oauthVk($code) {

        $protocol = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ||
                    (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') ? 'https' : 'http';

        $result = Curl::get("https://oauth.vk.com/access_token", [
            'client_id'     => $this->config->system->social->vk->app_id,
            'client_secret' => $this->config->system->social->vk->secret_key,
            'redirect_uri'  => $protocol . '://' . $_SERVER['SERVER_NAME'] . '/oauth/vk',
            'code'          => $code,
        ]);

        if (empty($result->getContent())) {
            throw new \Exception('Empty answer');

        } else {
            $access_data = $result->toArray();
            if ($access_data === null) {
                throw new \Exception('Answer incorrect');

            } elseif ( ! empty($access_data['error'])) {
                throw new \Exception($access_data['error']);

            } elseif (empty($access_data['access_token'])) {
                throw new \Exception('Empty response parameter "access_token"');

            } elseif (empty($access_data['user_id'])) {
                throw new \Exception('Empty response parameter "user_id"');

            } elseif (empty($access_data['email'])) {
                throw new \Exception('Empty response parameter "email"');

            } else {
                $result = Curl::get("https://api.vk.com/method/users.get", [
                    'user_ids'     => $access_data['user_id'],
                    'fields'       => 'contacts',
                    'access_token' => $access_data['access_token'],
                    'v'            => '5.68',
                ]);

                if (empty($result->getContent())) {
                    throw new \Exception('Empty answer');

                } else {
                    $personal_data = $result->toArray();
                    if ($personal_data === null) {
                        throw new \Exception('Answer incorrect');

                    } elseif ( ! empty($personal_data['error'])) {
                        throw new \Exception($personal_data['error']);

                    } elseif (empty($personal_data['response'])) {
                        throw new \Exception('Empty response parameter "response"');

                    } elseif (empty($personal_data['response'][0])) {
                        throw new \Exception('Empty response parameter "response.0"');

                    } elseif (empty($personal_data['response'][0]['first_name'])) {
                        throw new \Exception('Empty response parameter "response.0.first_name"');

                    } elseif (empty($personal_data['response'][0]['last_name'])) {
                        throw new \Exception('Empty response parameter "response.0.last_name"');

                    } else {
                        return [
                            'firstname' => $personal_data['response'][0]['first_name'],
                            'lastname'  => $personal_data['response'][0]['last_name'],
                            'phone'     => ! empty($personal_data['response'][0]['mobile_phone']) ? $personal_data['response'][0]['mobile_phone'] : '',
                            'email'     => $access_data['email'],
                        ];
                    }
                }
            }
        }
    }


    /**
     * @param $code
     * @return array
     * @throws \Exception
     */
    public function oauthOk($code) {

        $protocol = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ||
                    (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') ? 'https' : 'http';

        $result = Curl::post("https://api.ok.ru/oauth/token.do", [
            'client_id'     => $this->config->system->social->ok->app_id,
            'client_secret' => $this->config->system->social->ok->secret_key,
            'grant_type'    => 'authorization_code',
            'redirect_uri'  => $protocol . '://' . $_SERVER['SERVER_NAME'] . '/oauth/ok',
            'code'          => $code,
        ]);

        if (empty($result->getContent())) {
            throw new \Exception('Empty answer');

        } else {
            $access_data = $result->toArray();
            if ($access_data === null) {
                throw new \Exception('Answer incorrect');

            } elseif ( ! empty($access_data['error_description'])) {
                throw new \Exception($access_data['error_description']);

            } elseif (empty($access_data['access_token'])) {
                throw new \Exception('Empty response parameter "access_token"');

            } else {
                $public_key = $this->config->system->social->ok->public_key;
                $secret_key = $this->config->system->social->ok->secret_key;
                $sign       = md5("application_key={$public_key}format=jsonmethod=users.getCurrentUser" .
                                   md5("{$access_data['access_token']}{$secret_key}"));

                $result = Curl::get("https://api.odnoklassniki.ru/fb.do", [
                    'method'          => 'users.getCurrentUser',
                    'access_token'    => $access_data['access_token'],
                    'application_key' => $public_key,
                    'format'          => 'json',
                    'sig'             => $sign
                ]);

                if (empty($result->getContent())) {
                    throw new \Exception('Empty answer');

                } else {
                    $personal_data = $result->toArray();

                    if ($personal_data === null) {
                        throw new \Exception('Answer incorrect');

                    } elseif ( ! empty($personal_data['error'])) {
                        throw new \Exception($personal_data['error']);

                    } elseif (empty($personal_data['first_name'])) {
                        throw new \Exception('Empty response parameter "first_name"');

                    } elseif (empty($personal_data['last_name'])) {
                        throw new \Exception('Empty response parameter "last_name"');

                    } elseif (empty($personal_data['email'])) {
                        throw new \Exception('Empty response parameter "email"');

                    } else {
                        return [
                            'firstname' => $personal_data['first_name'],
                            'lastname'  => $personal_data['last_name'],
                            'phone'     => '',
                            'email'     => $personal_data['email'],
                        ];
                    }
                }
            }
        }
    }


    /**
     * @param $code
     * @return array
     * @throws \Exception
     */
    public function oauthGoogle($code) {

        $protocol = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ||
                    (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') ? 'https' : 'http';

        $result = Curl::post("https://accounts.google.com/o/oauth2/token", [
            'client_id'     => $this->config->system->social->google->app_id,
            'client_secret' => $this->config->system->social->google->secret_key,
            'grant_type'    => 'authorization_code',
            'redirect_uri'  => $protocol . '://' . $_SERVER['SERVER_NAME'] . '/oauth/google',
            'code'          => $code,
        ]);

        if (empty($result->getContent())) {
            throw new \Exception('Empty answer');

        } else {
            $access_data = $result->toArray();
            if ($access_data === null) {
                throw new \Exception('Answer incorrect');

            } elseif ( ! empty($access_data['error_msg'])) {
                throw new \Exception($access_data['error_msg']);

            } elseif (empty($access_data['access_token'])) {
                throw new \Exception('Empty response parameter "access_token"');

            } else {
                $result = Curl::get("https://www.googleapis.com/oauth2/v1/userinfo", [
                    'client_id'     => $this->config->system->social->google->app_id,
                    'client_secret' => $this->config->system->social->google->secret_key,
                    'grant_type'    => 'authorization_code',
                    'access_token'  => $access_data['access_token'],
                    'redirect_uri'  => $protocol . '://' . $_SERVER['SERVER_NAME'] . '/oauth/google',
                    'code'          => $code,
                ]);

                if (empty($result->getContent())) {
                    throw new \Exception('Empty answer');

                } else {
                    $personal_data = $result->toArray();

                    if ($personal_data === null) {
                        throw new \Exception('Answer incorrect');

                    } elseif ( ! empty($personal_data['error'])) {
                        throw new \Exception($personal_data['error']);

                    } elseif (empty($personal_data['given_name'])) {
                        throw new \Exception('Empty response parameter "given_name"');

                    } elseif (empty($personal_data['family_name'])) {
                        throw new \Exception('Empty response parameter "family_name"');

                    } elseif (empty($personal_data['email'])) {
                        throw new \Exception('Empty response parameter "email"');

                    } else {;
                        return [
                            'firstname' => $personal_data['given_name'],
                            'lastname'  => $personal_data['family_name'],
                            'phone'     => '',
                            'email'     => $personal_data['email'],
                        ];
                    }
                }
            }
        }
    }


    /**
     * @param $access_token
     * @return array
     * @throws \Exception
     */
    public function oauthFb($access_token) {

        $response =  Curl::get('https://graph.facebook.com/me', [
            'access_token' => $access_token,
            'fields'       => 'name,email'
        ]);

        if (empty($response->getContent())) {
            throw new \Exception('Empty answer');

        } else {
            $personal_data = $response->toArray();
            if ($personal_data === null) {
                throw new \Exception('Answer incorrect');

            } elseif ( ! empty($personal_data['error'])) {
                throw new \Exception($personal_data['error']);

            } elseif (empty($personal_data['name'])) {
                throw new \Exception('Empty response parameter "name"');

            } else {
                $explode_name = explode('  ', $personal_data['name'], 2);
                return [
                    'firstname' => current($explode_name),
                    'lastname'  => end($explode_name),
                    'phone'     => '',
                    'email'     => ! empty($personal_data['email']) ? $personal_data['email'] : '',
                ];
            }
        }
    }
}