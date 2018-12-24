<?php
namespace Cabinet\Theme\Vypusk;
use Cabinet\Theme;
use Cabinet\Mtpl;

require_once DOC_ROOT . '/cabinet/classes/Theme.php';
require_once DOC_ROOT . '/cabinet/classes/Mtpl.php';


/**
 * Class Controller
 * @package Cabinet\Theme\Vypusk
 */
class Controller extends Theme {

    /**
     * Меню
     * @param string $content
     * @param string $title
     * @return Mtpl
     * @throws \Exception
     */
    public function getMenu($content = '', $title = 'Личный кабинет') {


        $tpl = new Mtpl(__DIR__ . '/../' . $this->config->system->theme . '/html/menu.html');
        $tpl->assign('[SYSTEM_NAME]', $this->config->system->name);
        $tpl->assign('[TITLE]',       $title);

        if ($this->auth) {
            $name = $this->auth->lastname
                ? $this->auth->lastname  . ' ' . mb_substr($this->auth->firstname, 0, 1, 'utf8') . '.'
                : $this->auth->firstname;
            $tpl->is_auth->assign('[NAME]', $name);


            $plugins = $this->getPluginsList();
            if ( ! empty($plugins)) {
                foreach ($plugins as $plugin_name => $plugin) {
                    if ($plugin_name != 'profile') {
                        $tpl->plugin->assign('[NAME]', $plugin_name);
                        $tpl->plugin->assign('[TITLE]', $plugin['title']);
                        $tpl->plugin->reassign();
                    }
                }
            }

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
     * Страница подтверждения регистрации
     * @param bool $is_success
     * @return string
     * @throws \Exception
     */
    public function getRegistrationApprove($is_success) {

        $back_url = ! empty($_GET['back_url']) ? $_GET['back_url'] : '';
        $tpl      = new Mtpl(__DIR__ . '/html/registration_approve.html');

        $title   = 'Подтверждение регистрации';
        $message = 'Ваша регистрация успешно подтверждена.';

        $tpl->assign('[TITLE]', $title);

        if ($is_success) {
            if ($message) {
                $tpl->success->message->assign('[MESSAGE]', $message);
            }

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
}