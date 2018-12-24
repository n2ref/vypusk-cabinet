<?php
namespace Cabinet;

error_reporting(E_ALL);
ini_set('display_errors', true);

define('DOC_ROOT', realpath(__DIR__ . '/..'));
define("DOC_PATH", (substr(DOC_ROOT, strlen($_SERVER['DOCUMENT_ROOT'])) ?: '/'));

require_once 'classes/Error.php';
require_once 'classes/Tools.php';
require_once 'classes/Registry.php';
require_once 'classes/Init.php';


$conf_file = DOC_ROOT . "/conf.ini";
if ( ! file_exists($conf_file)) {
    throw new \Exception("Missing configuration file '{$conf_file}'.");
}

$config_inline = array(
    'system' => [
        'name'      => '',
        'theme'     => 'material',
        'cache'     => 'cache',
        'host'      => $_SERVER['SERVER_NAME'],
        'timezone'  => '',
        'apiserver' => '',
        'apikey'    => '',
        'plugins'   => [],
        'debug'     => [
            'on' => false
        ],
        'recaptcha' => [
            'on'          => false,
            'public_key'  => '',
            'private_key' => '',
        ],
        'tmp' => sys_get_temp_dir() ?: "/tmp"
    ]
);


//определяем имя секции для cli режима
if (PHP_SAPI === 'cli') {
    $options = getopt('p:m:a:s:', [
        'plugin:',
        'method:',
        'argument:',
        'section:'
    ]);
    if (( ! empty($options['section']) && is_string($options['section'])) || ( ! empty($options['s']) && is_string($options['s']))) {
        $_SERVER['SERVER_NAME'] = ! empty($options['section']) ? $options['section'] : $options['s'];
    }
}

if ( ! empty($_SERVER['SERVER_NAME'])) {
    $config_ini = Tools::getConfig($conf_file);
    $config_ini = isset($config_ini[$_SERVER['SERVER_NAME']])
        ? $config_ini[$_SERVER['SERVER_NAME']]
        : $config_ini['production'];
} else {
    $config_ini = Tools::getConfig($conf_file, 'production');
}

$config = array_replace_recursive($config_inline, $config_ini);


// отладка приложения
if ($config['system']['debug']['on']) {
    ini_set('display_errors', true);
} else {
    ini_set('display_errors', false);
}

// устанавливаем шкурку
define('THEME', $config['system']['theme']);


// определяем путь к папке кеша
if (strpos($config['system']['cache'], '/') !== 0) {
    $config['system']['cache'] = DOC_ROOT . trim($config['system']['cache'], "/");
}

if ( ! empty($config['system']['apiserver']) && substr($config['system']['apiserver'], -1) === '/') {
    $config['system']['apiserver'] = substr($config['system']['apiserver'], 0, -1);
}


//сохраняем конфиг
Registry::set('config', json_decode(json_encode($config)));


if (empty($config['system']['apiserver'])) {
    throw new \Exception("Empty setting 'system.apiserver' in config file '{$conf_file}'.");
}

if (empty($config['system']['apikey'])) {
    throw new \Exception("Empty setting 'system.apikey' in config file '{$conf_file}'.");
}


session_start();