<?php
namespace Cabinet;

require_once 'Curl_Response.php';


/**
 * Class Curl
 */
class Curl {


    /**
     * @param string $url
     * @param array  $params
     * @param array  $headers
     * @param array  $options
     * @return Curl_Response
     */
    public static function get($url, $params = array(), $headers = array(), $options = array()) {

        return self::request('get', $url, $params, $headers, $options);
    }


    /**
     * @param string $url
     * @param array  $params
     * @param array  $headers
     * @param array  $options
     * @return Curl_Response
     */
    public static function post($url, $params = array(), $headers = array(), $options = array()) {

        return self::request('post', $url, $params, $headers, $options);
    }


    /**
     * @param string $method
     * @param string $url
     * @param array  $params
     * @param array  $headers
     * @param array  $options
     * @return Curl_Response
     * @throws \Exception
     */
    private static function request($method, $url, $params = array(), $headers = array(), $options = array()) {

        $ch = curl_init();

        if ( ! empty($options)) {
            foreach ($options as $option => $option_value) {
                curl_setopt($ch, $option, $option_value);
            }
        }

        if ( ! empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if ($method == 'post') {
            curl_setopt($ch, CURLOPT_POST,       true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($params) ? http_build_query($params) : $params);
        } else {
            $url .= ! empty($params) ? '?' . http_build_query($params) : '';
        }

        curl_setopt($ch, CURLOPT_URL,            $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = new Curl_Response($ch);

        curl_close($ch);

        return $response;
    }
}