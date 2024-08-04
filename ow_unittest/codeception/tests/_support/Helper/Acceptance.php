<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I
include_once 'BasicHelper.php';
use Codeception\TestInterface;
class Acceptance extends \Codeception\Module
{
    public function _before(TestInterface $test)
    {
        parent::_before($test);
        if(get_class($test)!="A_InstallTest")
        {
            include_once OW_DIR_ROOT . 'ow_includes' . DS . 'config.php';
        }
        $this->setCaptcha(true);
    }

    public function _after(TestInterface $test)
    {
        parent::_after($test);
        $this->setCaptcha(false);
    }

    public function setCaptcha($value)
    {
        if($value)
        {
            file_put_contents('../captchaTest','true');
        }else{
            @unlink('../captchaTest');
        }
    }

    /**
     * @param $key
     * @return string
     */
    public function getSiteInfo($key)
    {
        return BasicHelper::getSiteInfo($key);
    }

    /**
     * @return bool
     */
    public function isSiteInstalled()
    {
        return defined('OW_URL_HOME');
    }

}
