<?php
namespace Cabinet\Plugin\Support;
use Cabinet\Common;
use Cabinet\Tools;

require_once DOC_ROOT . '/cabinet/classes/Common.php';


/**
 * Class Controller
 * @package Cabinet\Plugin\Pages
 */
class Controller extends Common {


    /**
     * @return bool|string
     */
    public function pageIndex() {

        echo Tools::getCss('/plugins/support/html/css/support.chat.css');
        echo Tools::getJs('/plugins/support/html/js/support.chat.js');
        return file_get_contents(__DIR__ . '/html/support.chat.html');
	}
}