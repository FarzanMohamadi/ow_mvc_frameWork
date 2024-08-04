<?php

namespace Helper;

define('DS', DIRECTORY_SEPARATOR);
define('OW_DIR_ROOT', dirname(dirname(__FILE__)) . DS . '..' . DS . '..' . DS . '..' . DS . '..' . DS);

if(!file_exists('./Settings.php')){
    file_put_contents('Settings.php', "<?php \ndefine('site_admin_password', '12345');");
}
include_once './Settings.php';

class BasicHelper
{
    /**
     * Data Base Info
     * change these values based on your data base name and user
     */
    const DB_HOST_VALUE='localhost';
    const DB_USER_VALUE='oxwall-test';
    const DB_NAME_VALUE='oxwall-test';
    const DB_PASSWORD_VALUE='oxTest123';
    const DB_PREFIX_VALUE='shub_';

    /**
     * Site Info
     * change these values based on your site info
     */
    const SITE_TITLE_VALUE='shub tests server title';
    const SITE_ADMIN_EMAIL_VALUE='admin@test.com';
    const SITE_ADMIN_USERNAME_VALUE='admin';
    const SITE_ADMIN_PASSWORD_VALUE='asdf@1111';


    /**
     * @param $key
     * @return string
     */
    public static function getSiteInfo($key)
    {
        $siteInfo = array(
            'site_title'=>self::SITE_TITLE_VALUE,
            'site_admin_email'=>self::SITE_ADMIN_EMAIL_VALUE,
            'site_admin_username'=>self::SITE_ADMIN_USERNAME_VALUE,
            'site_admin_password'=>self::SITE_ADMIN_PASSWORD_VALUE,
            'db_host'=>self::DB_HOST_VALUE,
            'db_user'=>self::DB_USER_VALUE,
            'db_name'=>self::DB_NAME_VALUE,
            'db_password'=>self::DB_PASSWORD_VALUE,
            'db_prefix'=>self::DB_PREFIX_VALUE
        );

        foreach($siteInfo as $k=>$v){
            if(defined($k)){
                $siteInfo[$k] = constant($k);
            }
        }
        return $siteInfo[$key];
    }
}