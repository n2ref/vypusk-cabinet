<?php
namespace Cabinet;

require_once 'Common.php';
require_once 'Mtpl.php';


/**
 * Class Theme
 * @package Cabinet
 */
class Theme extends Common {

    /**
     * Меню
     * @param string $content
     * @param string $title
     * @return Mtpl
     * @throws \Exception
     */
    public function getMenu($content = '', $title = 'Личный кабинет') {


        $tpl = new Mtpl(__DIR__ . '/../themes/' . $this->config->system->theme . '/html/menu.html');
        $tpl->assign('[SYSTEM_NAME]', $this->config->system->name);
        $tpl->assign('[TITLE]',       $title);

        if ($this->auth) {
            $name = $this->auth->lastname
                ? $this->auth->lastname  . ' ' . mb_substr($this->auth->firstname, 0, 1, 'utf8') . '.'
                : $this->auth->firstname;
            $tpl->is_auth->assign('[NAME]', $name);

        } else {
            $tpl->touchBlock('is_not_auth');
        }

        if (DOC_PATH != '/') {
            $tpl->assign(' href="/', ' href="' . DOC_PATH);
            $tpl->assign(' src="/', ' src="'  . DOC_PATH);
        }

        $tpl->assign('[CONTENT]', $content);

        return $tpl;
    }


    /**
     * @param string $content
     * @param string $title
     * @return bool|mixed|string
     */
    public function getMenuLogin($content = '', $title = '') {

        $tpl_common = file_get_contents(__DIR__ . '/../themes/' . $this->config->system->theme . '/html/login_common.html');
        $tpl_common = str_replace('[CONTENT]',     $content, $tpl_common);
        $tpl_common = str_replace('[SYSTEM_NAME]', $this->config->system->name, $tpl_common);
        $tpl_common = str_replace('[TITLE]',       $title, $tpl_common);

        if (DOC_PATH != '/') {
            $tpl_common = str_replace(' href="/', ' href="' . DOC_PATH, $tpl_common);
            $tpl_common = str_replace(' src="/', ' src="'  . DOC_PATH, $tpl_common);
        }

        return $tpl_common;
    }


    /**
     * Плагины
     * @return Mtpl
     * @throws \Exception
     */
    public function getPlugins() {

        $plugins = $this->getPluginsList();
        $tpl     = new Mtpl(__DIR__ . '/../themes/' . $this->config->system->theme . '/html/plugins.html');

        if ( ! empty($plugins)) {
            foreach ($plugins as $plugin_name => $plugin) {
                $tpl->plugin->assign('[NAME]',  $plugin_name);
                $tpl->plugin->assign('[TITLE]', $plugin['title']);
                $tpl->plugin->reassign();
            }
        }

        return $tpl;
    }


    /**
     * Форма входа
     * @return string
     * @throws \Exception
     */
    public function getLogin() {

        $back_url = ! empty($_GET['back_url']) ? $_GET['back_url'] : '';
        $token    = crypt(uniqid(), microtime());
        $protocol = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ||
                    (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') ? 'https' : 'http';

        $tpl = new Mtpl(__DIR__ . '/../themes/' . $this->config->system->theme . '/html/login.html');
        $tpl->assign('[TOKEN]',            $token);
        $tpl->assign('[BACK_URL_PARAM]',   $back_url ? 'back_url=' . urlencode($back_url) : '');
        $tpl->assign('[BACK_URL_ENCODED]', base64_encode($back_url));

        if ( ! empty($this->config->system->social)) {
            $tpl->social->assign('[HOST]', urlencode($protocol . '://' . $_SERVER['SERVER_NAME']));
            $tpl->social->assign('[VK_APP_ID]', $this->config->system->social->vk->app_id);
            $tpl->social->assign('[FB_APP_ID]', $this->config->system->social->fb->app_id);
            $tpl->social->assign('[OK_APP_ID]', $this->config->system->social->ok->app_id);
            $tpl->social->assign('[GOOGLE_APP_ID]', $this->config->system->social->google->app_id);
        }
        if (DOC_PATH != '/') {
            $tpl->assign(' href="/', ' href="' . DOC_PATH);
            $tpl->assign(' src="/',  ' src="'  . DOC_PATH);
        }

        return $tpl->render();
    }


    /**
     * Страница 404
     * @return string
     */
    public function get404() {

        return file_get_contents(__DIR__ . '/../themes/' . $this->config->system->theme . '/html/404.html');
    }


    /**
     * Страница ошибки входа через соц сети
     * @return string
     * @throws \Exception
     */
    public function getOauthError() {

        $back_url = ! empty($_GET['back_url']) ? $_GET['back_url'] : '';
        $tpl      = new Mtpl(__DIR__ . '/../themes/' . $this->config->system->theme . '/html/oauth_error.html');

        $tpl->assign('[BACK_URL]', $back_url ? '/?back_url=' . urlencode($back_url) : '/');

        if (DOC_PATH != '/') {
            $tpl->assign(' href="/', ' href="' . DOC_PATH);
            $tpl->assign(' src="/',  ' src="'  . DOC_PATH);
        }

        return $tpl->render();
    }


    /**
     * Страница ошибки входа через соц сети
     * @return string
     * @throws \Exception
     */
    public function getOauthFacebook() {

        $back_url = ! empty($_GET['back_url']) ? $_GET['back_url'] : '';
        $tpl      = new Mtpl(__DIR__ . '/../themes/' . $this->config->system->theme . '/html/oauth_fb.html');

        $tpl->assign('[BACK_URL]', $back_url ? '/?back_url=' . urlencode($back_url) : '/');

        if (DOC_PATH != '/') {
            $tpl->assign(' href="/', ' href="' . DOC_PATH);
            $tpl->assign(' src="/',  ' src="'  . DOC_PATH);
        }

        return $tpl->render();
    }


    /**
     * Форма восстановления пароля
     * @return string
     * @throws \Exception
     */
    public function getResetpass() {

        $back_url = ! empty($_GET['back_url']) ? $_GET['back_url'] : '';
        $tpl      = new Mtpl(__DIR__ . '/../themes/' . $this->config->system->theme . '/html/forgot_pass.html');

        if ($this->config->system->recaptcha->on) {
            $tpl->recaptcha->assign('[PUBLIC_KEY]', $this->config->system->recaptcha->public_key);
        }

        $tpl->assign('[BACK_URL]', $back_url ? '/?back_url=' . urlencode($back_url) : '/');

        if (DOC_PATH != '/') {
            $tpl->assign(' href="/', ' href="' . DOC_PATH);
            $tpl->assign(' src="/',  ' src="'  . DOC_PATH);
        }

        return $tpl->render();
    }


    /**
     * Форма сброса пароля
     * @param string $token
     * @param bool   $is_allow
     * @return string
     * @throws \Exception
     */
    public function getResetpassApprove($token, $is_allow) {

        $back_url = ! empty($_GET['back_url']) ? $_GET['back_url'] : '';

        $tpl = new Mtpl(__DIR__ . '/../themes/' . $this->config->system->theme . '/html/reset_pass.html');
        $tpl->assign('[TOKEN]',    $token);
        $tpl->assign('[BACK_URL]', $back_url ? '/?back_url=' . urlencode($back_url) : '/');

        if ($is_allow) {
            $tpl->touchBlock('reset_allow');
        } else {
            $tpl->touchBlock('reset_deny');
        }

        if (DOC_PATH != '/') {
            $tpl->assign(' href="/', ' href="' . DOC_PATH);
            $tpl->assign(' src="/',  ' src="'  . DOC_PATH);
        }

        return $tpl->render();
    }


    /**
     * Страница регистрации
     * @return string
     * @throws \Exception
     */
    public function getRegistration() {

        // TODO временно
        exit;
        $back_url = ! empty($_GET['back_url']) ? $_GET['back_url'] : '';
        $tpl      = new Mtpl(__DIR__ . '/../themes/' . $this->config->system->theme . '/html/registration.html');
        $protocol = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ||
                    (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') ? 'https' : 'http';

        $tpl->assign('[LOGIN_URL]',        $back_url ? '/?back_url=' . urlencode($back_url) : '/');
        $tpl->assign('[BACK_URL_ENCODED]', base64_encode($back_url));

        if ( ! empty($this->config->system->social)) {
            $tpl->social->assign('[HOST]',          urlencode($protocol . '://' . $_SERVER['SERVER_NAME']));
            $tpl->social->assign('[VK_APP_ID]',     $this->config->system->social->vk->app_id);
            $tpl->social->assign('[FB_APP_ID]',     $this->config->system->social->fb->app_id);
            $tpl->social->assign('[OK_APP_ID]',     $this->config->system->social->ok->app_id);
            $tpl->social->assign('[GOOGLE_APP_ID]', $this->config->system->social->google->app_id);
        }

        if (DOC_PATH != '/') {
            $tpl->assign(' href="/', ' href="' . DOC_PATH);
            $tpl->assign(' src="/',  ' src="'  . DOC_PATH);
        }

        return $tpl->render();
    }


    /**
     * Страница подтверждения регистрации
     * @param bool $is_success
     * @return string
     * @throws \Exception
     */
    public function getRegistrationApprove($is_success) {

        $back_url = ! empty($_GET['back_url']) ? $_GET['back_url'] : '';
        $tpl      = new Mtpl(__DIR__ . '/../themes/' . $this->config->system->theme . '/html/registration_approve.html');

        if ($is_success) {
            $tpl->touchBlock('success');
        } else {
            $tpl->touchBlock('error');
        }

        $tpl->assign('[BACK_URL]', $back_url ? '/?back_url=' . urlencode($back_url) : '/');

        if (DOC_PATH != '/') {
            $tpl->assign(' href="/', ' href="' . DOC_PATH);
            $tpl->assign(' src="/',  ' src="'  . DOC_PATH);
        }

        return $tpl->render();
    }


    /**
     * Список доступных плагинов
     * @return array
     * @throws \Exception
     */
    protected function getPluginsList() {

        $plugins = [];

        if ( ! empty($this->config->system->plugins)) {
            foreach ($this->config->system->plugins as $plugin_name => $plugin) {

                $controller_path = DOC_ROOT . '/plugins/' . $plugin_name . '/Controller.php';

                if ( ! file_exists($controller_path)) {
                    continue;
                }
                require_once($controller_path);

                $class_name = __NAMESPACE__ . '\\Plugin\\' . ucfirst(strtolower($plugin_name)) .'\\Controller';
                if ( ! class_exists($class_name)) {
                    continue;
                }

                if ( ! method_exists($class_name, 'pageIndex')) {
                    continue;
                }

                $plugins[$plugin_name] = [
                    'title' => $plugin->title
                ];
            }
        }

        return $plugins;
    }
}