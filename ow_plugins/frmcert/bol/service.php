<?php
/**
 * FRM Cert
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcert
 * @since 1.0
 */

final class FRMCERT_BOL_Service
{
    private function __construct()
    {
    }

    /***
     * @var
     */
    private static $classInstance;

    /***
     * @return FRMCERT_BOL_Service
     */
    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function loadStaticFiles(){


        $cssFile = OW::getPluginManager()->getPlugin('frmcert')->getStaticCssUrl() . 'cert_mainpage.css';
        OW::getDocument()->addStyleSheet($cssFile);

        $path = $_SERVER['REQUEST_URI'];
        if(preg_match('#^(/(index(/){0,1}){0,1}){0,1}$#', $path, $matches))
        {
            $mainPageCssFile = OW::getPluginManager()->getPlugin('frmcert')->getStaticCssUrl() . 'cert_mainpage.css';
            OW::getDocument()->addStyleSheet($mainPageCssFile);
        }
    }

    public function getResults($name, $key = 'frmcert') {
        return OW::getConfig()->getValue($key, $name);
    }

    public function saveConfig($name, $value, $key = 'frmcert') {
        OW::getConfig()->saveConfig($key, $name, $value);
    }

    public function fetchStatistics() {
        $authData['username'] = 'apa97';
        $authData['password'] = 'apa13(&';

        $url = 'http://192.168.15.15';

        $fetchTokenUrl = '/api-token-auth/';
        $fetchStatisticsUrl = '/publicapi/statistics';

        $params = new UTIL_HttpClientParams();
        $params->setHeader('Content-Type' ,'application/x-www-form-urlencoded');
        try {

            // token
            $params->addParams($authData);
            $responseToken = UTIL_HttpClient::post($url . $fetchTokenUrl, $params);
            if ($responseToken != null) {
                $body = $responseToken->getBody();
                if (isset($body)) {
                    $data = json_decode($body);
                    if (isset($data->token)) {
                        $token = $data->token;

                        // statistics
                        $params->addParams(array('token' => $token));
                        $params->setBody('token=' . $token);
                        $responseStatistics = UTIL_HttpClient::post($url . $fetchStatisticsUrl, $params);
                        if ($responseStatistics != null) {
                            $body = $responseStatistics->getBody();
                            if (isset($body)) {
                                $data = json_decode($body);
                                $resultData = array();
                                if (isset($data->vuln) && isset($data->vuln[0])) {
                                    $resultData['vuln']['last_day'] = $data->vuln[0]->day;
                                    $resultData['vuln']['last_month'] = $data->vuln[0]->month;
                                    $resultData['vuln']['last_week'] = $data->vuln[0]->week;
                                }
                                if (isset($data->bot) && isset($data->bot[0])) {
                                    $resultData['bot']['last_day'] = $data->bot[0]->day;
                                    $resultData['bot']['last_month'] = $data->bot[0]->month;
                                    $resultData['bot']['last_week'] = $data->bot[0]->week;
                                }
                                $resultData['time'] = time();
                                $this->saveConfig('cert_report', json_encode($resultData));
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            OW::getLogger()->writeLog(OW_Log::INFO, 'fcm_post_to_mobile', [ 'result'=>'http_error', 'message' => $e->getMessage()]);
        }
    }
}