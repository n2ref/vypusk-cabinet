<?php
namespace Cabinet;

require_once 'Registry.php';


/**
 * Class Common
 */
class Common {

    protected $config;
    protected $auth = [];


    /**
     * Common constructor.
     */
    public function __construct() {

        $this->config = Registry::get('config');
        $this->auth   = Registry::get('auth');
    }


    /**
     * Проверка аутентификации
     * @return bool
     */
    public function isAuth() {

        return ! empty(Registry::get('auth'));
    }


    /**
     * Выход
     * @return bool
     */
    protected function logout() {

        Registry::set('auth', null);
        $this->auth = null;
        return session_destroy();
    }


    /**
     * Получение контроллера темы
     * @throws \Exception
     * @return \Cabinet\Theme
     */
    protected function getThemeController() {

        $controller_path = __DIR__ . '/../themes/' . $this->config->system->theme . '/Controller.php';

        if ( ! file_exists($controller_path)) {
            throw new \Exception(sprintf("Файл %s не найден", $controller_path), 500);
        }
        require_once($controller_path);

        $class_name = __NAMESPACE__ . '\\Theme\\' . ucfirst(strtolower($this->config->system->theme)) .'\\Controller';
        if ( ! class_exists($class_name)) {
            throw new \Exception(sprintf("Класс %s не найден", $class_name), 500);
        }

        return new $class_name();
    }
}