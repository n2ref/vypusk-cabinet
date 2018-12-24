<?php
namespace Cabinet\Plugin\Pages;
use Cabinet\Common;

require_once DOC_ROOT . '/cabinet/classes/Common.php';


/**
 * Class Controller
 * @package Cabinet\Plugin\Pages
 */
class Controller extends Common {

	
    /**
     * Страница пользовательского соглашения
     * @return string
     */
    public function pageAgreement() {

        return file_get_contents(__DIR__ . '/html/agreement.html');
	}
}