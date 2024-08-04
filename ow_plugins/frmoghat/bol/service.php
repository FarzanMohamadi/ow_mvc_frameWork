<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmoghat.bol
 * @since 1.0
 */
class FRMOGHAT_BOL_Service
{
    CONST CATCH_REQUESTS_KEY = 'frmoghat.catch';

    private static $classInstance;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private $frmOghatDao;

    private function __construct()
    {
        $this->frmOghatDao = FRMOGHAT_BOL_CityDao::getInstance();
    }

    public function importingDefaultItems()
    {
        if (OW::getConfig()->configExists('frmoghat', 'importDefaultItem') && !OW::getConfig()->getValue('frmoghat', 'importDefaultItem')) {
            OW::getConfig()->saveConfig('frmoghat', 'importDefaultItem', true);
            $xml = simplexml_load_file(OW::getPluginManager()->getPlugin('frmoghat')->getStaticDir() . 'xml'.DIRECTORY_SEPARATOR.'defaultItems.xml');
            $cities = $xml->xpath("/cities");
            $cities = $cities[0]->xpath('child::city');
            foreach ($cities as $city) {
                $information = explode(',',(string)$city->name);
                $name = $information[2];
                $latitude = $information[0];
                $longitude = $information[1];
                $default = $information[3];
                if(!$this->existCity($name, $longitude, $latitude)) {
                    $this->addCity($name, $longitude, $latitude, $default);
                }
            }
        }
    }

    /***
     * @return array
     */
    public function getAllCity()
    {
        return $this->frmOghatDao->getAllCity();
    }

    /***
     * @param $name
     * @param $logitude
     * @param $latitude
     * @param int $default
     * @return FRMOGHAT_BOL_City|void
     */
    public function addCity($name, $logitude, $latitude, $default = 0){
        return $this->frmOghatDao->addCity($name, $logitude, $latitude, $default);
    }


    /***
     * @param $name
     * @param $logitude
     * @param $latitude
     * @return bool
     */
    public function existCity($name, $logitude, $latitude){
        return $this->frmOghatDao->existCity($name, $logitude, $latitude);
    }

    /***
     * @param $name
     */
    public function deleteCity($name){
        $this->frmOghatDao->deleteCity($name);
    }
}
