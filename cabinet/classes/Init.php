<?php
namespace Cabinet;

require_once 'Common.php';
require_once 'Curl.php';
require_once 'Social.php';


/**
 * Class Init
 */
class Init extends Common {


    /**
     * Init constructor.
     */
    public function __construct() {
        // сохранение сессии в реестре
        Registry::set('auth', isset($_SESSION['auth']) ? $_SESSION['auth'] : null);

        parent::__construct();

        $tz = $this->config->system->timezone;
        if ( ! empty($tz)) {
            date_default_timezone_set($tz);
        }
    }


    /**
     * @return string
     * @throws \Exception
     */
    public function dispatch() {

        if (isset($_SERVER['REQUEST_URI'])) {
            $matches = array();

            if (PHP_SAPI === 'cli') {
                return $this->dispatchCli();

            // REST api плагина
            } elseif (preg_match('~api/([a-zA-Z0-9_]+)/v([0-9]+\.[0-9]+)/(?:/|)([^?]*?)(?:/|)(?:\?|$)~', $_SERVER['REQUEST_URI'], $matches)) {
                if ( ! empty($matches[1]) && ! empty($matches[2]) && ! empty($matches[3])) {
                    $method = '';
                    foreach (explode('/', $matches[3]) as $path_element) {
                        $method .= ucfirst($path_element);
                    }
                    $method = lcfirst($method);
                    return $this->dispatchRest($matches[1], $method, $matches[2]);

                } else {
                    header('HTTP/1.1 404 Not found');
                    throw new \Exception('Incorrect api method');
                }

            // Обработчик данных плагина
            } elseif (preg_match('~data/([a-zA-Z0-9_]+)/(?:/|)([^?]*?)(?:/|)(?:\?|$)~', $_SERVER['REQUEST_URI'], $matches)) {
                if ( ! $this->isAuth()) {
                    header('HTTP/1.1 403 Forbidden');
                    throw new \Exception('Need authentication');

                } elseif ( ! empty($matches[1]) && ! empty($matches[2])) {
                    $method = '';
                    foreach (explode('/', $matches[2]) as $path_element) {
                        $method .= ucfirst($path_element);
                    }
                    $method = lcfirst($method);
                    return $this->dispatchHandler($matches[1], $method);

                } else {
                    header('HTTP/1.1 404 Not found');
                    throw new \Exception('Incorrect handler method');
                }

            // Страница плагина со своими данными
            } elseif (preg_match('~raw/([a-zA-Z0-9_]+)/(?:/|)([^?]*?)(?:/|)(?:\?|$)~', $_SERVER['REQUEST_URI'], $matches)) {
                if ( ! $this->isAuth()) {
                    header('HTTP/1.1 403 Forbidden');
                    throw new \Exception('Need authentication');

                } elseif ( ! empty($matches[1]) && ! empty($matches[2])) {
                    $method  = 'raw';
                    foreach (explode('/', $matches[2]) as $path_element) {
                        $method .= ucfirst($path_element);
                    }

                    if ($this->issetPluginMethod($matches[1], $method)) {
                        ob_start();
                        $plugin_controller = $this->getPluginController($matches[1]);
                        $plugin_content    = $plugin_controller->{$method}();
                        return ob_get_clean() . $plugin_content;

                    } else {
                        header('HTTP/1.1 404 Not found');
                        throw new \Exception('Incorrect raw method');
                    }


                } else {
                    header('HTTP/1.1 404 Not found');
                    throw new \Exception('Incorrect raw method');
                }

            // Обработчик входа через соц сеть
            } elseif (preg_match('~oauth/([a-zA-Z0-9_]+)~', $_SERVER['REQUEST_URI'], $matches)) {
                if ( ! empty($matches[1])) {
                    return $this->dispatchOauth($matches[1]);

                } else {
                    header('HTTP/1.1 404 Not found');
                    throw new \Exception('Incorrect oauth method');
                }

            // Отдельная страница плагина
            } elseif (preg_match('~([a-zA-Z0-9_]+)/(?:/|)([^?]*?)(?:/|)(?:\?|$)~', $_SERVER['REQUEST_URI'], $matches)) {
                header('Content-Type: text/html; charset=utf-8');
                $theme_controller = $this->getThemeController();

                if ( ! empty($matches[1]) && ! empty($matches[2]) && trim($matches[2], '/') != 'index') {
                    $method  = 'page';
                    foreach (explode('/', $matches[2]) as $path_element) {
                        $method .= ucfirst($path_element);
                    }

                    if ($this->issetPluginMethod($matches[1], $method)) {
                        ob_start();
                        $plugin_controller = $this->getPluginController($matches[1]);
                        $plugin_content    = $plugin_controller->{$method}();

                        return $theme_controller->getMenu(
                            ob_get_clean() . $plugin_content,
                            $this->config->system->plugins->{$matches[1]}->title
                        );

                    } else {
                        header('HTTP/1.1 404 Not found');
                        $content = $theme_controller->get404();
                        return $theme_controller->getMenu($content, '404');
                    }
                } else {
                    header('HTTP/1.1 404 Not found');
                    $content = $theme_controller->get404();
                    return $theme_controller->getMenu($content, '404');
                }
            }
        }


        if ($this->isAuth()) {
            // Выход
            if (isset($_GET['logout'])) {
                $this->logout();
                header("Location: " . DOC_PATH);
                return '';
            }

        } else {
            $page = isset($_GET['page']) ? $_GET['page'] : '';

            // Авторизация
            if ($page == 'login' && isset($_POST['email']) && isset($_POST['password'])) {
                header('Content-Type: application/json;');
                $email    = is_string($_POST['email'])    ? $_POST['email']    : '';
                $password = is_string($_POST['password']) ? $_POST['password'] : '';
                $result = $this->authLogin($email, $password);
                return json_encode($result);

            // Сброс пароля, шаг 1
            } elseif ($page == 'forgot') {
                if ( ! empty($_POST['email'])) {
                    header('Content-Type: application/json;');
                    $result = $this->resetpass($_POST);
                    return json_encode($result);

                // Страница сброса пароля
                } else {
                    header('Content-Type: text/html; charset=utf-8');
                    $theme_controller = $this->getThemeController();
                    $resetpass_content = $theme_controller->getResetpass();
                    return $theme_controller->getMenuLogin($resetpass_content, 'Восстановление пароля');
                }

            // Сброс пароля, шаг 2
            } elseif ($page == 'reset' && ! empty($_GET['token'])) {
                if ( ! empty($_POST['password'])) {
                    header('Content-Type: application/json;');
                    $result = $this->resetpassApprove($_GET['token'], $_POST['password']);
                    return json_encode($result);

                // Страница сброса пароля
                } else {
                    header('Content-Type: text/html; charset=utf-8');
                    $is_allow         = $this->resetpassCheck($_GET['token']);
                    $theme_controller = $this->getThemeController();
                    $resetpass_content = $theme_controller->getResetpassApprove($_GET['token'], $is_allow);
                    return $theme_controller->getMenuLogin($resetpass_content, 'Сброс пароля');
                }

            // Регистрация
            } elseif ($page == 'registration') {
                if ( ! empty($_POST)) {
                    header('Content-Type: application/json;');
                    $result = $this->registration($_POST);
                    return json_encode($result);

                // Страница регистрации
                } else {
                    header('Content-Type: text/html; charset=utf-8');
                    $theme_controller = $this->getThemeController();
                    $registration_content =  $theme_controller->getRegistration();
                    return $theme_controller->getMenuLogin($registration_content, 'Регистрация');
                }

            // Подтверждение регистрации
            } elseif ($page == 'registration_approve' && ! empty($_GET['token'])) {
                header('Content-Type: text/html; charset=utf-8');
                $result = $this->registrationApprove($_GET['token']);

                $theme_controller = $this->getThemeController();
                $registration_content = $theme_controller->getRegistrationApprove($result['status'] === 'success');
                return $theme_controller->getMenuLogin($registration_content, 'Подтверждение регистрации');

            // Страница форма входа
            }  else {
                if ( ! empty($_GET['plugin'])) {
                    header('HTTP/1.1 403 Forbidden');
                }
                header('Content-Type: text/html; charset=utf-8');
                $theme_controller = $this->getThemeController();
                $login_content = $theme_controller->getLogin();
                return $theme_controller->getMenuLogin($login_content, 'Вход');
            }
        }

        $plugin = ! empty($_GET['plugin']) ? $_GET['plugin'] : '';


        // Меню
        if (empty($plugin)) {
            header('Content-Type: text/html; charset=utf-8');
            $theme_controller = $this->getThemeController();
            return $theme_controller->getMenu();

        // Плагин, главная страница
        } else {
            ob_start();
            $plugin_controller = $this->getPluginController($plugin);
            $plugin_content = $plugin_controller->pageIndex();
            return ob_get_clean() . $plugin_content;
        }
    }


    /**
     * Cli
     * @return string
     * @throws \Exception
     */
    private function dispatchCli() {

        $options = getopt('p:m:a:s:h', [
            'plugin:',
            'method:',
            'argument:',
            'section:',
            'help',
        ]);

        if (empty($options) || isset($options['h']) || isset($options['help'])) {
            return implode(PHP_EOL, [
                'Core user cabinet',
                'Usage: php index.php [OPTIONS]',
                'Optional arguments:',
                "   -p    --plugin    Module name",
                "   -m    --method    Cli method name",
                "   -a    --argument  Parameter in method",
                "   -s    --section   Section name in config file",
                "   -h    --help      Help info",
                "Examples of usage:",
                "php index.php --plugin orders --method run",
                "php index.php --plugin orders --method run --section site.com",
                "php index.php --plugin orders --method run --argument 123" . PHP_EOL,
            ]);
        }

        if ((isset($options['p']) || isset($options['plugin'])) &&
            (isset($options['m']) || isset($options['method']))
        ) {
            $plugin = isset($options['plugin']) ? $options['plugin'] : $options['p'];
            $method = isset($options['method']) ? $options['method'] : $options['m'];

            $arguments = isset($options['argument'])
                ? $options['argument']
                : (isset($options['a']) ? $options['a'] : false);
            $arguments = $arguments === false
                ? []
                : (is_array($arguments) ? $arguments : [$arguments]);


            try {
                $plugin   = strtolower($plugin);
                $cli_path = __DIR__ . '/../../plugins/' . $plugin . '/Cli.php';

                if (empty($this->config->system->plugins) || empty($this->config->system->plugins->{$plugin})) {
                    throw new \Exception(sprintf("Плагин %s не найден или не активен", $plugin));
                }

                if ( ! file_exists($cli_path)) {
                    throw new \Exception(sprintf("Файл %s не найден", $cli_path));
                }
                require_once($cli_path);

                $class_name = __NAMESPACE__ . '\\Plugin\\' . ucfirst(strtolower($plugin)) .'\\Cli';
                if ( ! class_exists($class_name)) {
                    throw new \Exception(sprintf("Класс %s не найден", $class_name));
                }

                $all_class_methods = get_class_methods($class_name);
                if ($parent_class = get_parent_class($class_name)) {
                    $parent_class_methods = get_class_methods($parent_class);
                    $self_methods         = array_diff($all_class_methods, $parent_class_methods);
                } else {
                    $self_methods = $all_class_methods;
                }

                if (array_search($method, $self_methods) === false) {
                    throw new \Exception(sprintf("В классе %s не найден метод %s", $class_name, $method));
                }

                $class_instance = new $class_name();
                $result       = call_user_func_array([$class_instance, $method], $arguments);

                if (is_scalar($result)) {
                    return (string)$result . PHP_EOL;
                }


            } catch (\Exception $e) {
                $message = $e->getMessage();
                return $message . PHP_EOL;
            }

        }

        return PHP_EOL;
    }


    /**
     * @param string $plugin
     * @param string $method
     * @param string $version
     * @return mixed
     * @throws \Exception
     */
    private function dispatchRest($plugin, $method, $version = '1.0') {

        $plugin    = strtolower($plugin);
        $version   = str_replace('.', '_', $version);
        $rest_path = __DIR__ . '/../../plugins/' . $plugin . '/Rest_' . $version . '.php';

        if (empty($this->config->system->plugins) || empty($this->config->system->plugins->{$plugin})) {
            header('HTTP/1.1 400 Bad Request');
            throw new \Exception(sprintf("Плагин %s не найден или не активен", $plugin), 400);
        }

        if ( ! file_exists($rest_path)) {
            header('HTTP/1.1 400 Bad Request');
            throw new \Exception(sprintf("Файл %s не найден", $rest_path), 400);
        }
        require_once($rest_path);

        $class_name = __NAMESPACE__ . '\\Plugin\\' . ucfirst(strtolower($plugin)) .'\\Rest_' . $version;
        if ( ! class_exists($class_name)) {
            header('HTTP/1.1 400 Bad Request');
            throw new \Exception(sprintf("Класс %s не найден", $class_name), 400);
        }

        if ( ! is_callable([$class_name, $method])) {
            header('HTTP/1.1 400 Bad Request');
            throw new \Exception(sprintf("В классе %s не найден метод %s", $class_name, $method), 400);
        }

        $class_instance = new $class_name();
        return $class_instance->{$method}();
    }


    /**
     * @param string $plugin
     * @param string $method
     * @return mixed
     * @throws \Exception
     */
    private function dispatchHandler($plugin, $method) {

        $plugin       = strtolower($plugin);
        $handler_path = __DIR__ . '/../../plugins/' . $plugin . '/Handler.php';

        if (empty($this->config->system->plugins) || empty($this->config->system->plugins->{$plugin})) {
            header('HTTP/1.1 400 Bad Request');
            throw new \Exception(sprintf("Плагин %s не найден или не активен", $plugin), 400);
        }

        if ( ! file_exists($handler_path)) {
            header('HTTP/1.1 400 Bad Request');
            throw new \Exception(sprintf("Файл %s не найден", $handler_path), 400);
        }
        require_once($handler_path);

        $class_name = __NAMESPACE__ . '\\Plugin\\' . ucfirst(strtolower($plugin)) .'\\Handler';
        if ( ! class_exists($class_name)) {
            header('HTTP/1.1 400 Bad Request');
            throw new \Exception(sprintf("Класс %s не найден", $class_name), 400);
        }

        if ( ! is_callable([$class_name, $method])) {
            header('HTTP/1.1 400 Bad Request');
            throw new \Exception(sprintf("В классе %s не найден метод %s", $class_name, $method), 400);
        }

        $class_instance = new $class_name();
        return $class_instance->{$method}();
    }


    /**
     * @param string $plugin
     * @param string $method
     * @return mixed
     * @throws \Exception
     */
    private function dispatchRaw($plugin, $method) {

        $plugin       = strtolower($plugin);
        $handler_path = __DIR__ . '/../../plugins/' . $plugin . '/Co.php';

        if (empty($this->config->system->plugins) || empty($this->config->system->plugins->{$plugin})) {
            header('HTTP/1.1 400 Bad Request');
            throw new \Exception(sprintf("Плагин %s не найден или не активен", $plugin), 400);
        }

        if ( ! file_exists($handler_path)) {
            header('HTTP/1.1 400 Bad Request');
            throw new \Exception(sprintf("Файл %s не найден", $handler_path), 400);
        }
        require_once($handler_path);

        $class_name = __NAMESPACE__ . '\\Plugin\\' . ucfirst(strtolower($plugin)) .'\\Handler';
        if ( ! class_exists($class_name)) {
            header('HTTP/1.1 400 Bad Request');
            throw new \Exception(sprintf("Класс %s не найден", $class_name), 400);
        }

        if ( ! is_callable([$class_name, $method])) {
            header('HTTP/1.1 400 Bad Request');
            throw new \Exception(sprintf("В классе %s не найден метод %s", $class_name, $method), 400);
        }

        $class_instance = new $class_name();
        return $class_instance->{$method}();
    }


    /**
     * @param string $social_name
     * @return mixed
     * @throws \Exception
     */
    private function dispatchOauth($social_name) {

        try {
            switch ($social_name) {
                case 'vk':
                    if ( ! empty($_GET['code'])) {
                        $social   = new Social();
                        $reg_data = $social->oauthVk($_GET['code']);

                        if ( ! empty($reg_data)) {
                            $this->authLogin($reg_data['email'], '', true);

                            if ( ! $this->isAuth()) {
                                $reg_data['social'] = $social_name;
                                $this->registration($reg_data, true);
                            }

                            header('Location: ' . (isset($_GET['state']) ? '/' . base64_decode($_GET['state']) : '/'));
                            return '';
                        }
                    }
                    break;

                case 'fb':
                    if ( ! empty($_POST['access_token'])) {
                        $social   = new Social();
                        $reg_data = $social->oauthFb($_POST['access_token']);

                        if ( ! empty($reg_data)) {
                            $this->authLogin($reg_data['email'], '', true);

                            if ( ! $this->isAuth()) {
                                $reg_data['social'] = $social_name;
                                $this->registration($reg_data, true);
                            }

                            return json_encode([
                                'status'   => $this->isAuth() ? 'success' : 'error',
                                'back_url' => isset($_POST['state']) ? base64_decode(urldecode($_POST['state'])) : ''
                            ]);

                        } else {
                            return json_encode([
                                'status' => 'error'
                            ]);
                        }

                    } else {
                        $theme_controller = $this->getThemeController();
                        $content = $theme_controller->getOauthFacebook();
                        return $theme_controller->getMenuLogin($content, 'Вход через facebook');
                    }

                    break;

                case 'ok':
                    if ( ! empty($_GET['code'])) {
                        $social   = new Social();
                        $reg_data = $social->oauthOk($_GET['code']);

                        if ( ! empty($reg_data)) {
                            $this->authLogin($reg_data['email'], '', true);

                            if ( ! $this->isAuth()) {
                                $reg_data['social'] = $social_name;
                                $this->registration($reg_data, true);
                            }

                            header('Location: ' . (isset($_GET['state']) ? '/' . base64_decode($_GET['state']) : '/'));
                            return '';
                        }
                    }
                    break;

                case 'google':
                    if ( ! empty($_GET['code'])) {
                        $social   = new Social();
                        $reg_data = $social->oauthGoogle($_GET['code']);

                        if ( ! empty($reg_data)) {
                            $this->authLogin($reg_data['email'], '', true);

                            if ( ! $this->isAuth()) {
                                $reg_data['social'] = $social_name;
                                $this->registration($reg_data, true);
                            }

                            header('Location: ' . (isset($_GET['state']) ? '/' . base64_decode($_GET['state']) : '/'));
                            return '';
                        }
                    }
                    break;

                default:
                    header('HTTP/1.1 404 Not found');
                    throw new \Exception('Incorrect oauth method');
            }
        } catch (\Exception $e) {
            // ignore
        }


        $theme_controller = $this->getThemeController();
        $content = $theme_controller->getOauthError();
        return $theme_controller->getMenuLogin($content, 'Ошибка входа');
    }


    /**
     * Получение содержимого плагина
     * @param $plugin
     * @return string
     * @throws \Exception
     */
    private function getPluginController($plugin) {

        $plugin          = strtolower($plugin);
        $controller_path = __DIR__ . '/../../plugins/' . $plugin . '/Controller.php';

        if ( ! file_exists($controller_path)) {
            throw new \Exception(sprintf("Файл %s не найден", $controller_path), 500);
        }
        require_once($controller_path);

        $class_name = __NAMESPACE__ . '\\Plugin\\' . ucfirst(strtolower($plugin)) .'\\Controller';
        if ( ! class_exists($class_name)) {
            throw new \Exception(sprintf("Класс %s не найден", $class_name), 500);
        }

        return new $class_name();
    }


    /**
     * @param string $plugin
     * @param string $method
     * @return string
     */
    private function issetPluginMethod($plugin, $method) {

        $plugin = strtolower($plugin);

        if (empty($this->config->system->plugins) || empty($this->config->system->plugins->{$plugin})) {
            return false;
        }

        $controller_path = __DIR__ . '/../../plugins/' . $plugin . '/Controller.php';

        if ( ! file_exists($controller_path)) {
            return false;
        }
        require_once($controller_path);

        $class_name = __NAMESPACE__ . '\\Plugin\\' . ucfirst(strtolower($plugin)) .'\\Controller';
        if ( ! class_exists($class_name)) {
            return false;
        }

        $class_instance = new $class_name();

        if ( ! method_exists($class_instance, $method)) {
            return false;
        }

        return true;
    }


    /**
     * Вход пользователя
     * @param string $email
     * @param string $password
     * @param bool   $is_force
     * @return array
     */
    private function authLogin($email, $password, $is_force = false) {

        $headers = ['Core2-apikey: ' . $this->config->system->apikey];

        if ($is_force) {
            $headers[] = 'Core2-Private-Key: ' . $this->config->system->private_key;
        }

        $errors = [];
        $result = Curl::post($this->config->system->apiserver . "/api/clients/auth", [
            "email"    => $email,
            "password" => $password,
        ], $headers);

        try {
            if (empty($result->getContent())) {
                throw new \Exception('Empty answer');

            } else {
                $data_decoded = $result->toArray();

                if ($data_decoded === null) {
                    throw new \Exception('Answer incorrect');

                } elseif ( ! empty($data_decoded['error_code'])) {
                    switch ($data_decoded['error_code']) {
                        case '101': $errors['general']  = 'Указанный email некорректен'; break;
                        case '103': $errors['general']  = 'Неверный логин или пароль'; break;
                        case '105': $errors['general']  = 'Пользователь не подтвердил регистрацию'; break;
                        case '106': $errors['general']  = 'Пользователь выключен'; break;
                        default: throw new \Exception($data_decoded['message']);
                    }

                } elseif ($result->getHttpCode() !== 200) {
                    throw new \Exception('HTTP code - ' . $result->getHttpCode());

                } elseif (empty($data_decoded['data'])) {
                    throw new \Exception('Empty response parameter "data"');

                } elseif (empty($data_decoded['data']['status'])) {
                    throw new \Exception('Empty response parameter "data.status"');

                } elseif (empty($data_decoded['data']['user'])) {
                    throw new \Exception('Empty response parameter "data.user"');

                } elseif ($data_decoded['data']['status'] !== 'success') {
                    throw new \Exception('Response parameter "data.success" not equal success');

                } elseif (empty($data_decoded['data']['user']['id'])) {
                    throw new \Exception('Empty response parameter "data.user.id"');

                } elseif (empty($data_decoded['data']['user']['firstname'])) {
                    throw new \Exception('Empty response parameter "data.user.firstname"');

                } elseif ( ! array_key_exists('lastname', $data_decoded['data']['user'])) {
                    throw new \Exception('Not isset response parameter "data.user.lastname"');

                } elseif ( ! array_key_exists('phone' , $data_decoded['data']['user'])) {
                    throw new \Exception('Not isset response parameter "data.user.phone"');

                } elseif (empty($data_decoded['data']['user']['email'])) {
                    throw new \Exception('Empty response parameter "data.user.email"');

                } elseif (empty($data_decoded['data']['user']['auth_token'])) {
                    throw new \Exception('Empty response parameter "data.user.auth_token"');
                }
            }
        } catch (\Exception $e) {
            $errors['general'] = 'Ошибка. Попробуйте повторить попытку позже.' . $e->getMessage();
        }


        if (empty($errors)) {
            $auth = new \stdClass();
            $auth->id          = $data_decoded['data']['user']['id'];
            $auth->firstname   = $data_decoded['data']['user']['firstname'];
            $auth->lastname    = $data_decoded['data']['user']['lastname'];
            $auth->email       = $data_decoded['data']['user']['email'];
            $auth->phone       = $data_decoded['data']['user']['phone'];
            $auth->token       = $data_decoded['data']['user']['auth_token'];
            $auth->first_login = isset($data_decoded['data']['user']['first_login']) ? $data_decoded['data']['user']['first_login'] : false;

            $_SESSION['auth'] = $auth;
            Registry::set('auth', $auth);

            return ['status' => 'success'];

        } else {
            return ['status' => 'error', 'error_messages' => $errors];
        }
    }


    /**
     * Восстановление пароля
     * @param array  $data
     * @return array
     */
    private function resetpass($data) {

        if ($this->config->system->recaptcha->on) {
            $verify_response = Curl::post('https://www.google.com/recaptcha/api/siteverify', [
                'secret'   => $this->config->system->recaptcha->private_key,
                'response' => ! empty($data['g-recaptcha-response']) ? $data['g-recaptcha-response'] : '',
                'remoteip' => $_SERVER['REMOTE_ADDR'],
            ]);

            $verify_decoded = $verify_response->toArray();
            if ($verify_decoded === null || ! isset($verify_decoded['success']) || $verify_decoded['success'] !== true) {
                return [
                    'status'         => 'error',
                    'error_messages' => ['general' => 'Не пройдена проверка о том, что вы человек']
                ];
            }
        }


        $errors = [];
        $result = Curl::post($this->config->system->apiserver . "/api/clients/resetpass", [
            'email'    => ! empty($data['email']) ? $data['email'] : '',
            'back_url' => ! empty($data['back_url']) ? $data['back_url'] : ''
        ], [
            'Core2-apikey: ' . $this->config->system->apikey
        ]);

        try {
            if (empty($result->getContent())) {
                throw new \Exception('Empty answer');

            } else {
                $data_decoded = $result->toArray();
                if ($data_decoded === null) {
                    throw new \Exception('Answer incorrect');

                } elseif ( ! empty($data_decoded['error_code'])) {
                    switch ($data_decoded['error_code']) {
                        case '116': $errors['email'] = 'Пользователь с таким email не найден'; break;
                        default: throw new \Exception($data_decoded['message']);
                    }

                } elseif ($result->getHttpCode() !== 200) {
                    throw new \Exception('HTTP code - ' . $result->getHttpCode());

                } elseif (empty($data_decoded['data'])) {
                    throw new \Exception('Empty response parameter "data"');

                } elseif (empty($data_decoded['data']['status'])) {
                    throw new \Exception('Empty response parameter "data.status"');
                }
            }
        } catch (\Exception $e) {
            $errors['general'] = 'Ошибка. Попробуйте повторить попытку позже.';
        }


        if (empty($errors)) {
            return ['status' => 'success'];

        } else {
            return ['status' => 'error', 'error_messages' => $errors];
        }
    }


    /**
     * Восстановление пароля (переход по ссылке из письма)
     *
     * @param string $reset_token
     * @param string $new_password
     * @return  array
     */
    private function resetpassApprove($reset_token, $new_password) {

        $errors = [];
        $result = Curl::post($this->config->system->apiserver . "/api/clients/resetpass/approve", [
            "reset_token" => $reset_token,
            "password"    => $new_password
        ], [
            'Core2-apikey: ' . $this->config->system->apikey
        ]);

        try {
            if (empty($result->getContent())) {
                throw new \Exception('Empty answer');

            } else {
                $data_decoded = $result->toArray();
                if ($data_decoded === null) {
                    throw new \Exception('Answer incorrect');

                } elseif ( ! empty($data_decoded['error_code'])) {
                    switch ($data_decoded['error_code']) {
                        case '121': $errors['general'] = 'Возможно, ссылка устарела. Повторите процесс восстановления пароля.'; break;
                        default: throw new \Exception($data_decoded['message']);
                    }

                } elseif ($result->getHttpCode() !== 200) {
                    throw new \Exception('HTTP code - ' . $result->getHttpCode());

                } elseif (empty($data_decoded['data'])) {
                    throw new \Exception('Empty response parameter "data"');

                } elseif (empty($data_decoded['data']['status'])) {
                    throw new \Exception('Empty response parameter "data.status"');
                }
            }
        } catch (\Exception $e) {
            $errors['general'] = 'Ошибка. Попробуйте повторить попытку позже.';
        }

        if (empty($errors)) {
            return ['status' => 'success'];

        } else {
            return ['status' => 'error', 'error_message' => $errors];
        }
    }


    /**
     * Проверка восстановления пароля
     * @param string $reset_token
     * @return bool
     */
    private function resetpassCheck($reset_token) {

        $result = Curl::post($this->config->system->apiserver . "/api/clients/resetpass/check", [
            "reset_token" => $reset_token
        ], [
            'Core2-apikey: ' . $this->config->system->apikey
        ]);


        try {
            if (empty($result->getContent())) {
                throw new \Exception('Empty answer');

            } else {
                $data_decoded = $result->toArray();
                if ($data_decoded === null) {
                    throw new \Exception('Answer incorrect');

                } elseif ( ! empty($data_decoded['error_code'])) {
                    switch ($data_decoded['error_code']) {
                        case '118': $error = 'Возможно, ссылка устарела. Повторите процесс восстановления пароля.'; break;
                        default: throw new \Exception($data_decoded['message']);
                    }

                } elseif ($result->getHttpCode() !== 200) {
                    throw new \Exception('HTTP code - ' . $result->getHttpCode());

                } elseif (empty($data_decoded['data'])) {
                    throw new \Exception('Empty response parameter "data"');

                } elseif (empty($data_decoded['data']['status'])) {
                    throw new \Exception('Empty response parameter "data.status"');
                }
            }
        } catch (\Exception $e) {
            $error = 'Ошибка. Попробуйте повторить попытку позже.';
        }


        return empty($error);
    }


    /**
     * Регистрация пользователя
     * @param array $data
     * @param bool  $is_force
     * @return array
     */
    private function registration($data, $is_force = false) {

        $headers = ['Core2-apikey: ' . $this->config->system->apikey];

        if ($is_force) {
            $headers[] = 'Core2-Private-Key: ' . $this->config->system->private_key;
        }

        $errors = [];
        $result = Curl::post($this->config->system->apiserver . "/api/clients/registration", $data, $headers);

        try {
            if (empty($result->getContent())) {
                throw new \Exception('Empty answer');

            } else {
                $data_decoded = $result->toArray();
                if ($data_decoded === null) {
                    throw new \Exception('Answer incorrect');

                } elseif ( ! empty($data_decoded['error_code'])) {
                    switch ($data_decoded['error_code']) {
                        case '111': $errors['email'] = 'Такой email уже существует'; break;
                        default: throw new \Exception($data_decoded['message']);
                    }

                } elseif ($result->getHttpCode() !== 200) {
                    throw new \Exception('HTTP code - ' . $result->getHttpCode());

                } elseif (empty($data_decoded['data'])) {
                    throw new \Exception('Empty response parameter "data"');

                } elseif (empty($data_decoded['data']['status'])) {
                    throw new \Exception('Empty response parameter "data.status"');
                }
            }
        } catch (\Exception $e) {
            $errors['general'] = 'Ошибка. Попробуйте повторить попытку позже.';
        }


        if (empty($errors)) {
            if ( ! $this->isAuth() &&
                ! empty($data_decoded['data']['user']) &&
                ! empty($data_decoded['data']['user']['id']) &&
                ! empty($data_decoded['data']['user']['firstname']) &&
                ! empty($data_decoded['data']['user']['email']) &&
                ! empty($data_decoded['data']['user']['auth_token'])
            ) {
                $auth = new \stdClass();
                $auth->id          = $data_decoded['data']['user']['id'];
                $auth->firstname   = $data_decoded['data']['user']['firstname'];
                $auth->lastname    = $data_decoded['data']['user']['lastname'];
                $auth->email       = $data_decoded['data']['user']['email'];
                $auth->phone       = $data_decoded['data']['user']['phone'];
                $auth->token       = $data_decoded['data']['user']['auth_token'];
                $auth->first_login = isset($data_decoded['data']['user']['first_login']) ? $data_decoded['data']['user']['first_login'] : false;

                $_SESSION['auth'] = $auth;
                Registry::set('auth', $auth);
            }

            return ['status' => 'success'];

        } else {
            return ['status' => 'error', 'error_messages' => $errors];
        }
    }


    /**
     * @param string $approve_token
     * @return array
     */
    private function registrationApprove($approve_token) {

        $result = Curl::post($this->config->system->apiserver . "/api/clients/registration/approve", [
            "approve_token" => $approve_token
        ], [
            'Core2-apikey: ' . $this->config->system->apikey
        ]);

        try {
            if (empty($result->getContent())) {
                throw new \Exception('Empty answer');

            } else {
                $data_decoded = $result->toArray();
                if ($data_decoded === null) {
                    throw new \Exception('Answer incorrect');

                } elseif ( ! empty($data_decoded['error_code'])) {
                    switch ($data_decoded['error_code']) {
                        case '113': $error = 'Мы не смогли подтвердить вашу регистрацию. Возможно, вы сделали это раньше.'; break;
                        default: throw new \Exception($data_decoded['message']);
                    }

                } elseif ($result->getHttpCode() !== 200) {
                    throw new \Exception('HTTP code - ' . $result->getHttpCode());

                } elseif (empty($data_decoded['data'])) {
                    throw new \Exception('Empty response parameter "data"');

                } elseif (empty($data_decoded['data']['status'])) {
                    throw new \Exception('Empty response parameter "data.status"');
                }
            }
        } catch (\Exception $e) {
            $error = 'Ошибка. Попробуйте повторить попытку позже.';
        }


        if (empty($error)) {
            if ( ! $this->isAuth() &&
                ! empty($data_decoded['data']['user']) &&
                ! empty($data_decoded['data']['user']['id']) &&
                ! empty($data_decoded['data']['user']['firstname']) &&
                ! empty($data_decoded['data']['user']['email']) &&
                ! empty($data_decoded['data']['user']['auth_token'])
            ) {
                $auth = new \stdClass();
                $auth->id          = $data_decoded['data']['user']['id'];
                $auth->firstname   = $data_decoded['data']['user']['firstname'];
                $auth->lastname    = ! empty($data_decoded['data']['user']['lastname']) ? $data_decoded['data']['user']['lastname'] : '';
                $auth->email       = $data_decoded['data']['user']['email'];
                $auth->phone       = ! empty($data_decoded['data']['user']['phone']) ? $data_decoded['data']['user']['phone'] : '';
                $auth->token       = $data_decoded['data']['user']['auth_token'];
                $auth->first_login = isset($data_decoded['data']['user']['first_login']) ? $data_decoded['data']['user']['first_login'] : false;

                $_SESSION['auth'] = $auth;
                Registry::set('auth', $auth);
            }

            return array('status' => 'success');

        } else {
            return array('status' => 'error', 'error_message' => $error);
        }
    }
}