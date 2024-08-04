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
class FRMIMPORT_BOL_Service
{
    private static $classInstance;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private $usersDao;
    private $usersTryDao;

    private function __construct()
    {
        $this->usersDao = FRMIMPORT_BOL_UsersDao::getInstance();
        $this->usersTryDao = FRMIMPORT_BOL_UsersTryDao::getInstance();
    }

    /***
     * @param $userId
     * @param $type
     * @return array
     */
    public function getEmailsByUserId($userId, $type)
    {
        return $this->usersDao->getEmailsByUserId($userId, $type);
    }

    /***
     * @param $userId
     * @param $email
     * @param $type
     * @return mixed
     */
    public function getUser($userId, $email, $type)
    {
        return $this->usersDao->getUser($userId, $email, $type);
    }

    public function checkUserAuth()
    {
        if (!OW::getUser()->isAuthenticated()) {
            $this->redirect(OW_URL_HOME);
        }
    }

    public function adminAccessToType($type)
    {
        return OW::getConfig()->getValue('frmimport','use_import_'.$type);
    }

    /***
     * @param $userId
     * @param $type
     * @return FRMIMPORT_BOL_UsersTry
     */
    public function getUserTry($userId, $type)
    {
        return $this->usersTryDao->getUserTry($userId, $type);
    }

    /***
     * @param $userId
     * @param $type
     * @return array|FRMIMPORT_BOL_UsersTry
     */
    public function addOrUpdateUserTry($userId, $type)
    {
        return $this->usersTryDao->addOrUpdateUserTry($userId, $type);
    }

    /***
     * @param $userId
     * @param $type
     * @return bool
     */
    public function accessToAccount($userId, $type){
        $userTry = $this->getUserTry($userId, $type);
        if($userTry==null){
            return true;
        }

        if($userTry->time + 2*60 < time()){
            return true;
        }

        return false;
    }

    /***
     * @param $userId
     * @param $email
     * @param $type
     * @return FRMIMPORT_BOL_Users
     */
    public function addUser($userId, $email, $type)
    {
        return $this->usersDao->addUser($userId, $email, $type);
    }

    /***
     * @param $emailInviter
     * @param $emailInvited
     */
    public function sendEmailToInviter($emailInviter, $emailInvited)
    {
        $mails = array();
        $mail = OW::getMailer()->createMail();
        $mail->addRecipientEmail($emailInviter);
        $mail->setSubject(OW::getLanguage()->text('frmimport', 'invitation_email_to_inviter_subject', array('site_name' => OW::getConfig()->getValue('base', 'site_name'))));
        $mail->setHtmlContent($this->getInvitationRegisteredEmailContent($emailInvited));
        $mail->setTextContent($this->getInvitationRegisteredEmailContent($emailInvited));
        $mails[] = $mail;
        OW::getMailer()->addListToQueue($mails);
    }

    /***
     * @param $email
     * @param $username
     */
    public function sendEmailForInvitation($email, $username)
    {
        $mails = array();
        $mail = OW::getMailer()->createMail();
        $mail->addRecipientEmail($email);
        $mail->setSubject(OW::getLanguage()->text('frmimport', 'invitation_email_subject', array('site_name' => OW::getConfig()->getValue('base', 'site_name'))));
        $mail->setHtmlContent($this->getInvitationEmailContent($username));
        $mail->setTextContent($this->getInvitationEmailContent($username));
        $mails[] = $mail;
        OW::getMailer()->addListToQueue($mails);
    }

    /***
     * @param $username
     * @return string
     */
    public function getInvitationEmailContent($username){
        return OW::getLanguage()->text('frmimport', 'invitation_email_content', array('username' => $username, 'join_url' => OW::getRouter()->urlForRoute('base_join'), 'site_name' => OW::getConfig()->getValue('base', 'site_name')));
    }

    /***
     * @param $email
     * @return array
     */
    public function getUsersByEmail($email)
    {
        return $this->usersDao->getUsersByEmail($email);
    }

    /***
     * @param $emailInvited
     * @return string
     */
    public function getInvitationRegisteredEmailContent($emailInvited){
        return OW::getLanguage()->text('frmimport', 'invitation_email_to_inviter_content', array('email' => $emailInvited,'site_name' => OW::getConfig()->getValue('base', 'site_name')));
    }

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

    /***
     * @param $consumer_secret
     * @param $token_secret
     * @return string
     */
    function oauth_compute_plaintext_sig($consumer_secret, $token_secret)
    {
        return ($consumer_secret . '&' . $token_secret);
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
        $signature_key = $this->rfc3986_encode($consumer_secret) . '&' . $this->rfc3986_encode($token_secret);
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
        $query_str = parse_url($url, PHP_URL_QUERY);
        if ($query_str) {
            $parsed_query = $this->oauth_parse_str($query_str);
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
        $service = FRMIMPORT_BOL_Service::getInstance();
        if (is_array($raw_input)) {
            return array_map(array($service, 'rfc3986_encode'), $raw_input);
        } else if (is_scalar($raw_input)) {
            return str_replace('%7E', '~', rawurlencode($raw_input));
        } else {
            return '';
        }
    }

    function rfc3986_decode($raw_input)
    {
        return rawurldecode($raw_input);
    }

    /**
     * Do an HTTP GET
     * @param string $url
     * @param int $port (optional)
     * @param array $headers an array of HTTP headers (optional)
     * @return array ($info, $header, $response) on success or empty array on error.
     */
    function do_get($url, $port = 80, $headers = NULL)
    {
        $retarr = array();  // Return value

        $curl_opts = array(CURLOPT_URL => $url,
            CURLOPT_PORT => $port,
            CURLOPT_POST => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true);

        if ($headers) {
            $curl_opts[CURLOPT_HTTPHEADER] = $headers;
        }

        $response = $this->do_curl($curl_opts);

        if (!empty($response)) {
            $retarr = $response;
        }

        return $retarr;
    }

    /**
     * Make a curl call with given options.
     * @param array $curl_opts an array of options to curl
     * @return array ($info, $header, $response) on success or empty array on error.
     */
    function do_curl($curl_opts)
    {
        $retarr = array();  // Return value

        if (!$curl_opts) {
            return $retarr;
        }


        // Open curl session
        $ch = curl_init();

        if (!$ch) {
            return $retarr;
        }

        // Set curl options that were passed in
        curl_setopt_array($ch, $curl_opts);

        // Ensure that we receive full header
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt( $ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );

        $response = curl_exec($ch);
        $curl_spew = ob_get_contents();
        // Check for errors
        if (curl_errno($ch)) {
            $errno = curl_errno($ch);
            $errmsg = curl_error($ch);
            curl_close($ch);
            unset($ch);
            return $retarr;
        }

        // Get information about the transfer
        $info = curl_getinfo($ch);

        // Parse out header and body
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);

        // Close curl session
        curl_close($ch);
        unset($ch);

        // Set return value
        array_push($retarr, $info, $header, $body);

        return $retarr;
    }

    /***
     * @param $consumer_key
     * @param $consumer_secret
     * @param $guid
     * @param $access_token
     * @param $access_token_secret
     * @return array|string
     */
    function callcontact_yahoo($consumer_key, $consumer_secret, $guid, $access_token, $access_token_secret)
    {
        $response = array();

        $url = 'https://social.yahooapis.com/v1/user/' . $guid . '/contacts;count=1000';
        $params['format'] = 'json';
        $params['view'] = 'compact';
        $params['oauth_version'] = '1.0';
        $params['oauth_nonce'] = mt_rand();
        $params['oauth_timestamp'] = time();
        $params['oauth_consumer_key'] = $consumer_key;
        $params['oauth_token'] = $access_token;
        $params['oauth_signature_method'] = 'HMAC-SHA1';
        $params['oauth_signature'] = $this->oauth_compute_hmac_sig('GET', $url, $params, $consumer_secret, $access_token_secret);

        $query_parameter_string = $this->oauth_http_build_query($params);

        $request_url = $url . ($query_parameter_string ? ('?' . $query_parameter_string) : '');
        $response = $this->do_get($request_url, 443);
        $newList = array();

        if (!empty($response)) {
            list($info, $header, $body) = $response;
            if ($body) {
                $yahoo_array = json_decode($body);
                if (sizeof($yahoo_array->error) == 0) {
                    foreach ($yahoo_array as $key => $values) {
                        foreach ($values->contact as $keys => $values_sub) {
                            $fields = $values_sub->fields;
                            foreach ($fields as $field) {
                                if ($field != null && isset($field->type) && ($field->type == 'email' || $field->type == 'yahooid')) {
                                    $email = $field->value;
                                    if (trim($email) != "") {
                                        if ($field->type == 'yahooid') {
                                            $email = $email . '@yahoo.com';
                                        }
                                        $newList[] = $email;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }


        return $newList;
    }

    /***
     * @param $consumer_key
     * @param $consumer_secret
     * @param $request_token
     * @param $request_token_secret
     * @param $oauth_verifier
     * @return array
     */
    function get_access_token_yahoo($consumer_key, $consumer_secret, $request_token, $request_token_secret, $oauth_verifier)
    {
        $retarr = array();  // return value
        $response = array();
        $url = 'https://api.login.yahoo.com/oauth/v2/get_token';
        $params['oauth_version'] = '1.0';
        $params['oauth_nonce'] = mt_rand();
        $params['oauth_timestamp'] = time();
        $params['oauth_consumer_key'] = $consumer_key;
        $params['oauth_token'] = $request_token;
        $params['oauth_verifier'] = $oauth_verifier;
        $params['oauth_signature_method'] = 'PLAINTEXT';
        $params['oauth_signature'] = $this->oauth_compute_plaintext_sig($consumer_secret, $request_token_secret);

        $query_parameter_string = $this->oauth_http_build_query($params);
        $request_url = $url . ($query_parameter_string ? ('?' . $query_parameter_string) : '');
        $response = $this->do_get($request_url, 443);

        if (!empty($response)) {
            list($info, $header, $body) = $response;
            $body_parsed = $this->oauth_parse_str($body);
            $retarr = $response;
            $retarr[] = $body_parsed;
        }

        return $retarr;
    }

    /***
     * @param $consumer_key
     * @param $consumer_secret
     * @param $callback
     * @return array
     */
    function get_request_token($consumer_key, $consumer_secret, $callback)
    {
        $retarr = array();
        $response = array();

        $url = 'https://api.login.yahoo.com/oauth/v2/get_request_token';
        $params['oauth_version'] = '1.0';
        $params['oauth_nonce'] = mt_rand();
        $params['oauth_timestamp'] = time();
        $params['oauth_consumer_key'] = $consumer_key;
        $params['oauth_callback'] = $callback;
        $params['oauth_signature_method'] = 'PLAINTEXT';
        $params['oauth_signature'] = $this->oauth_compute_plaintext_sig($consumer_secret, null);

        $query_parameter_string = $this->oauth_http_build_query($params);
        $request_url = $url . ($query_parameter_string ? ('?' . $query_parameter_string) : '');
        $response = $this->do_get($request_url, 443);

        if (!empty($response)) {
            list($info, $header, $body) = $response;
            $body_parsed = $this->oauth_parse_str($body);
            $retarr = $response;
            $retarr[] = $body_parsed;
        }

        return $retarr;
    }

    public function getRegisteredExceptFriendEmails($emails, $userId)
    {
        $emailsInformation = array();
        $number = 1;
        $friendList = FRIENDS_BOL_Service::getInstance()->findFriendIdList($userId, 0, 10000, 'friends');
        foreach ($emails as $emailInfo) {
            $email = $emailInfo->email;
            $user = BOL_UserService::getInstance()->findByEmail($email);
            if ($user != null && !in_array($user->getId(), $friendList)) {
                $avatar = BOL_AvatarService::getInstance()->getAvatarUrl($user->getId());
                $emailsInformation[] = array('email' => $email, 'avatar' => $avatar, 'number' => $number);
                $number++;
            }
        }
        return $emailsInformation;
    }

    public function getNotSubscribedUserEmails($emails)
    {
        $emailsInformation = array();
        $number = 1;
        foreach ($emails as $emailInfo) {
            $email = $emailInfo->email;
            $user = BOL_UserService::getInstance()->findByEmail($email);
            if ($user == null) {
                $emailsInformation[] = array('email' => $email, 'number' => $number);
                $number++;
            }
        }
        return $emailsInformation;
    }
}
