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
class FRMIMPORT_CLASS_GmailGetContacts
{
    /**
     * Get a request token.
     * @param string $consumer_key obtained when you registered your app
     * @param string $consumer_secret obtained when you registered your app
     * @param string $callback callback url can be the string 'oob'
     * @param bool $usePost use HTTP POST instead of GET
     * @param bool $useHmacSha1Sig use HMAC-SHA1 signature
     * @param bool $passOAuthInHeader pass OAuth credentials in HTTP header
     * @return array of response parameters or empty array on error
     */
    /**
     * @param FRMIMPORT_CLASS_GmailOath $oauth
     * @param $useHmacSha1Sig
     * @param $returnResponse
     * @return array|string
     */
    function get_request_token(FRMIMPORT_CLASS_GmailOath $oauth, $useHmacSha1Sig, $returnResponse)
    {

        $retarr = array();  // return value
        $response = array();

        $url = 'https://accounts.google.com/o/oauth2/auth';
        $params['oauth_version'] = '1.0';
        $params['oauth_nonce'] = mt_rand();
        $params['oauth_timestamp'] = time();
        $params['response_type'] = 'token';
        $params['client_id'] = $oauth->oauth_cunsumer_key;
        $params['redirect_uri'] = $oauth->callback;
        $params['scope'] = 'https://www.google.com/m8/feeds';//$url = "<a href="https://www.google.com/m8/feeds/contacts/default/full&quot" rel="nofollow">https://www.google.com/m8/feeds/contacts/default/full&quot</a>;;


        // compute signature and add it to the params list
        if ($useHmacSha1Sig) {

            $params['oauth_signature_method'] = 'HMAC-SHA1';
            $params['oauth_signature'] =
                $oauth->oauth_compute_hmac_sig('GET', $url, $params, $oauth->oauth_cunsumer_secret, null);
        } else {
            echo "signature mathod not support";
        }

        $query_parameter_string = $oauth->oauth_http_build_query($params);

        $request_url = $url . ($query_parameter_string ? ('?' . $query_parameter_string) : '');

        if(!$returnResponse) {
            return $request_url;
        }else {
            $response = $oauth->do_get($request_url, 443);

            // extract successful response
            if (!empty($response)) {
                list($info, $header, $body) = $response;
                $body_parsed = $oauth->oauth_parse_str($body);
                $retarr = $response;
                $retarr[] = $body_parsed;
                return $body_parsed;
            }
        }
    }

    /**
     * Call the Yahoo Contact API
     * @param string $consumer_key obtained when you registered your app
     * @param string $consumer_secret obtained when you registered your app
     * @param string $guid obtained from getacctok
     * @param string $access_token obtained from getacctok
     * @param string $access_token_secret obtained from getacctok
     * @param bool $usePost use HTTP POST instead of GET
     * @param bool $passOAuthInHeader pass the OAuth credentials in HTTP header
     * @return response string with token or empty array on error
     */
    /**
     * @param FRMIMPORT_CLASS_GmailOath $oauth
     * @param $access_token
     * @param $access_token_secret
     * @return array
     */
    function callcontact(FRMIMPORT_CLASS_GmailOath $oauth, $access_token, $access_token_secret)
    {
        $retarr = array();  // return value
        $response = array();
        $url = "https://www.google.com/m8/feeds/contacts/default/full";
        $params['alt'] = 'json';
        $params['max-results'] = '100';
        $params['oauth_version'] = '1.0';
        $params['oauth_nonce'] = mt_rand();
        $params['oauth_timestamp'] = time();
        $params['oauth_consumer_key'] = $oauth->oauth_cunsumer_key;
        $params['oauth_token'] = $access_token;

        $params['oauth_signature_method'] = 'HMAC-SHA1';
        $params['oauth_signature'] =
            $oauth->oauth_compute_hmac_sig('GET', $url, $params,
                $oauth->oauth_cunsumer_secret, $access_token_secret);

        $query_parameter_string = $oauth->oauth_http_build_query($params);

        $request_url = $url . ($query_parameter_string ?('?' . $query_parameter_string) : '');
        $response = FRMIMPORT_BOL_Service::getInstance()->do_get($request_url, 443);


        if (!empty($response)) {
            list($info, $header, $body) = $response;
            if ($body) {

                $contact = json_decode($oauth->json_pretty_print($body), true);

                return $contact['feed']['entry'];

            }
            $retarr = $response;
        }

        return $retarr;
    }
}