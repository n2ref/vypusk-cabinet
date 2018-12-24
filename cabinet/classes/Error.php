<?php
namespace Cabinet;


/**
 * Class Error
 */
class Error {

    /**
     * @param \Exception $e
     */
    public static function catchException(\Exception $e) {

        $config   = self::getConfig();
        $is_debug = ! empty($config) && $config->system->debug->on;

        if (PHP_SAPI === 'cli') {
            if ($is_debug) {
                echo $e->getMessage() . PHP_EOL;
                echo $e->getFile() . ': ' . $e->getLine() . PHP_EOL . PHP_EOL;
                echo $e->getTraceAsString();
            } else {
                echo $e->getMessage() . PHP_EOL;
            }

        } else {
            if ($e instanceof WebServiceException) {
                switch ($e->getHttpCode()) {
                    case 200 : header("HTTP/1.1 200 OK"); break;
                    case 400 : header("HTTP/1.1 400 Bad Request"); break;
                    case 401 : header("HTTP/1.1 401 Unauthorized"); break;
                    case 402 : header("HTTP/1.1 402 Payment Required"); break;
                    case 403 : header("HTTP/1.1 403 Forbidden"); break;
                    case 404 : header("HTTP/1.1 404 Not Found"); break;
                    case 405 : header("HTTP/1.1 405 Method Not Allowed"); break;
                    case 406 : header("HTTP/1.1 406 Not Acceptable"); break;
                    case 407 : header("HTTP/1.1 407 Proxy Authentication Required"); break;
                    case 408 : header("HTTP/1.1 408 Request Timeout"); break;
                    case 409 : header("HTTP/1.1 409 Conflict"); break;
                    case 410 : header("HTTP/1.1 410 Gone"); break;
                    case 411 : header("HTTP/1.1 411 Length Required"); break;
                    case 412 : header("HTTP/1.1 412 Precondition Failed"); break;
                    case 413 : header("HTTP/1.1 413 Request Entity Too Large"); break;
                    case 414 : header("HTTP/1.1 414 Request-URI Too Long"); break;
                    case 415 : header("HTTP/1.1 415 Unsupported Media Type"); break;
                    case 416 : header("HTTP/1.1 416 Requested Range Not Satisfiable"); break;
                    case 417 : header("HTTP/1.1 417 Expectation Failed"); break;
                    case 502 : header("HTTP/1.1 502 Bad Gateway"); break;
                    case 503 : header("HTTP/1.1 503 Service Unavailable"); break;
                    case 504 : header("HTTP/1.1 504 Gateway Timeout"); break;
                    case 505 : header("HTTP/1.1 505 HTTP Version Not Supported"); break;
                    case 500 :
                    default  : header("HTTP/1.1 500 Internal Server Error"); break;
                }
            }


            $message = 'Ошибка системы';
            $type    = self::getBestMathType('text/html');
            switch ($type) {
                default:
                case 'text/html':
                    header('Content-Type: text/html; charset=utf-8');
                    if ($is_debug) {
                        $msg  = '<pre>';
                        $msg .= $e->getMessage() . "\n";
                        $msg .= '<b>' . $e->getFile() . ': ' . $e->getLine() . "</b>\n\n";
                        $msg .= $e->getTraceAsString();
                        $msg .= '</pre>';
                        echo $msg;
                    } else {
                        echo $message;
                    }
                    break;

                case 'text/plain':
                    header('Content-Type: text/plain; charset=utf-8');
                    if ($is_debug) {
                        $msg  = $e->getMessage() . "\n";
                        $msg .= $e->getFile() . ': ' . $e->getLine() . "\n\n";
                        $msg .= $e->getTraceAsString();
                        echo $msg;
                    } else {
                        echo $message;
                    }
                    break;

                case 'application/json':
                    header('Content-type: application/json; charset="utf-8"');
                    if ($is_debug) {
                        echo json_encode([
                            'error_code'    => $e->getCode(),
                            'error_message' => $e->getMessage(),
                            'error_file'    => $e->getFile(),
                            'error_line'    => $e->getLine(),
                            'error_trace'   => $e->getTrace(),
                        ]);
                    } else {
                        echo json_encode([
                            'error_code'    => $e->getCode(),
                            'error_message' => $message
                        ]);
                    }
                    break;
            }
        }
    }


    /**
     * Получение формата для ответа
     * @param string $default
     * @return string
     */
    private static function getBestMathType($default = 'text/html') {

        $types           = [];
        $available_types = [
            'text/html', 'text/plain', 'application/json'
        ];

        if (isset($_SERVER['HTTP_ACCEPT']) && ($accept = strtolower($_SERVER['HTTP_ACCEPT']))) {
            $explode_accept = explode(',', $accept);

            if ( ! empty($explode_accept)) {
                foreach ($explode_accept as $accept_type) {
                    $accept_type = trim($accept_type);

                    if (strpos($accept_type, ';') !== false) {
                        $explode_accept_type = explode(';', $accept_type);
                        $quality             = '';

                        if (preg_match('/q=([0-9.]+)/', $explode_accept_type[1], $quality)) {
                            $types[$explode_accept_type[0]] = $quality[1];
                        } else {
                            $types[$explode_accept_type[0]] = 0;
                        }

                    } else {
                        $types[$accept_type] = 1;
                    }
                }

                arsort($types, SORT_NUMERIC);
            }
        }


        if ( ! empty($types)) {
            foreach ($types as $type => $v) {
                if (array_search($type, $available_types) !== false) {
                    return $type;
                }
            }
        }

        return $default;
    }


    /**
     * Получаем экземпляр конфига
     * @return mixed
     */
    private static function getConfig() {

        $config = [];

        if (class_exists('\Cabinet\Registry') && Registry::isRegistered('config')) {
            $config = Registry::get('config');
        }

        return $config;
    }
}