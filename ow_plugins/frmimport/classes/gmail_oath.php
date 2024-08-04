<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmimport.bol
 * @since 1.0
 */
class FRMIMPORT_CLASS_GmailOath
{

    public $oauth_cunsumer_key;
    public $oauth_cunsumer_secret;
    public $callback;

    function __construct($cuncumer_key, $cunsumer_secret, $callback)
    {
        $this->oauth_cunsumer_key = $cuncumer_key;
        $this->oauth_cunsumer_secret = $cunsumer_secret;
        $this->callback = $callback;
    }

    /***
     * @param $json
     * @param bool $html_output
     * @return string
     */
    function json_pretty_print($json, $html_output = false)
    {
        $spacer = '  ';
        $level = 1;
        $indent = 0; // current indentation level
        $pretty_json = '';
        $in_string = false;

        $len = strlen($json);

        for ($c = 0; $c < $len; $c++) {
            $char = $json[$c];
            switch ($char) {
                case '{':
                case '[':
                    if (!$in_string) {
                        $indent += $level;
                        $pretty_json .= $char . "\n" . str_repeat($spacer, $indent);
                    } else {
                        $pretty_json .= $char;
                    }
                    break;
                case '}':
                case ']':
                    if (!$in_string) {
                        $indent -= $level;
                        $pretty_json .= "\n" . str_repeat($spacer, $indent) . $char;
                    } else {
                        $pretty_json .= $char;
                    }
                    break;
                case ',':
                    if (!$in_string) {
                        $pretty_json .= ",\n" . str_repeat($spacer, $indent);
                    } else {
                        $pretty_json .= $char;
                    }
                    break;
                case ':':
                    if (!$in_string) {
                        $pretty_json .= ": ";
                    } else {
                        $pretty_json .= $char;
                    }
                    break;
                case '"':
                    if ($c > 0 && $json[$c - 1] != '\\') {
                        $in_string = !$in_string;
                    }
                default:
                    $pretty_json .= $char;
                    break;
            }
        }

        return ($html_output) ?
            '<pre>' . htmlentities($pretty_json) . '</pre>' :
            $pretty_json . "\n";
    }

    ///////////////////global.php close/////////////


    function oauth_http_build_query($params, $excludeOauthParams = false)
    {

        $query_string = '';
        if (!empty($params)) {

            // rfc3986 encode both keys and values
            $keys = $this->rfc3986_encode(array_keys($params));
            $values = $this->rfc3986_encode(array_values($params));
            $params = array_combine($keys, $values);

            // Parameters are sorted by name, using lexicographical byte value ordering.
            // http://oauth.net/core/1.0/#rfc.section.9.1.1
            uksort($params, 'strcmp');

            // Turn params array into an array of "key=value" strings
            $kvpairs = array();
            foreach ($params as $k => $v) {
                if ($excludeOauthParams && substr($k, 0, 5) == 'oauth') {
                    continue;
                }
                if (is_array($v)) {
                    // If two or more parameters share the same name,
                    // they are sorted by their value. OAuth Spec: 9.1.1 (1)
                    natsort($v);
                    foreach ($v as $value_for_same_key) {
                        array_push($kvpairs, ($k . '=' . $value_for_same_key));
                    }
                } else {
                    // For each parameter, the name is separated from the corresponding
                    // value by an '=' character (ASCII code 61). OAuth Spec: 9.1.1 (2)
                    array_push($kvpairs, ($k . '=' . $v));
                }
            }

            // Each name-value pair is separated by an '&' character, ASCII code 38.
            // OAuth Spec: 9.1.1 (2)
            $query_string = implode('&', $kvpairs);
        }
        return $query_string;
    }

    /**
     * Parse a query string into an array.
     * @param string $query_string an OAuth query parameter string
     * @return array an array of query parameters
     * @link http://oauth.net/core/1.0/#rfc.section.9.1.1
     */
    function oauth_parse_str($query_string)
    {
        $query_array = array();

        if (isset($query_string)) {

            // Separate single string into an array of "key=value" strings
            $kvpairs = explode('&', $query_string);

            // Separate each "key=value" string into an array[key] = value
            foreach ($kvpairs as $pair) {
                list($k, $v) = explode('=', $pair, 2);

                // Handle the case where multiple values map to the same key
                // by pulling those values into an array themselves
                if (isset($query_array[$k])) {
                    // If the existing value is a scalar, turn it into an array
                    if (is_scalar($query_array[$k])) {
                        $query_array[$k] = array($query_array[$k]);
                    }
                    array_push($query_array[$k], $v);
                } else {
                    $query_array[$k] = $v;
                }
            }
        }

        return $query_array;
    }

    /**
     * Compute an OAuth HMAC-SHA1 signature
     * @param string $http_method GET, POST, etc.
     * @param string $url
     * @param array $params an array of query parameters for the request
     * @param string $consumer_secret
     * @param string $token_secret
     * @return string a base64_encoded hmac-sha1 signature
     * @see http://oauth.net/core/1.0/#rfc.section.A.5.1
     */
    function oauth_compute_hmac_sig($http_method, $url, $params, $consumer_secret, $token_secret)
    {

        $base_string = $this->signature_base_string($http_method, $url, $params);
        if($token_secret==null){
            $signature_key = $this->rfc3986_encode($consumer_secret);
        }else{
            $signature_key = $this->rfc3986_encode($consumer_secret) . '&' . $this->rfc3986_encode($token_secret);
        }
        $sig = base64_encode(hash_hmac('sha1', $base_string, $signature_key, true));

        return $sig;
    }

    /**
     * Make the URL conform to the format scheme://host/path
     * @param string $url
     * @return string the url in the form of scheme://host/path
     */
    function normalize_url($url)
    {
        $parts = parse_url($url);

        $scheme = $parts['scheme'];
        $host = $parts['host'];
        $port = $parts['port'];
        $path = $parts['path'];

        if (!$port) {
            $port = ($scheme == 'https') ? '443' : '80';
        }
        if (($scheme == 'https' && $port != '443')
            || ($scheme == 'http' && $port != '80')
        ) {
            $host = "$host:$port";
        }

        return "$scheme://$host$path";
    }

    /***
     * @param $http_method
     * @param $url
     * @param $params
     * @return string
     */
    function signature_base_string($http_method, $url, $params)
    {
        // Decompose and pull query params out of the url
        $query_str = parse_url($url, PHP_URL_QUERY);
        if ($query_str) {
            $parsed_query = $this->oauth_parse_str($query_str);
            // merge params from the url with params array from caller
            $params = array_merge($params, $parsed_query);
        }

        // Remove oauth_signature from params array if present
        if (isset($params['oauth_signature'])) {
            unset($params['oauth_signature']);
        }

        // Create the signature base string. Yes, the $params are double encoded.

        $base_string = $this->rfc3986_encode(strtoupper($http_method)) . '&' .
            $this->rfc3986_encode($this->normalize_url($url)) . '&' .
            $this->rfc3986_encode($this->oauth_http_build_query($params));

        return $base_string;
    }

    /**
     * Encode input per RFC 3986
     * @param string|array $raw_input
     * @return string|array properly rfc3986 encoded raw_input
     * If an array is passed in, rfc3896 encode all elements of the array.
     * @link http://oauth.net/core/1.0/#encoding_parameters
     */
    function rfc3986_encode($raw_input)
    {

        if (is_array($raw_input)) {
            //return array_map($this->rfc3986_encode, $raw_input);
            return array_map(array($this, 'rfc3986_encode'), $raw_input);

            // return $this->rfc3986_encode($raw_input);
        } else if (is_scalar($raw_input)) {
            return str_replace('%7E', '~', rawurlencode($raw_input));
        } else {
            return '';
        }
    }
}