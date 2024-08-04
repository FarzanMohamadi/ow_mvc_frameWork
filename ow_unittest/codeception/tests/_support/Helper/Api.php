<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I
include_once 'BasicHelper.php';
use Codeception\TestInterface;
use PHPUnit\Framework\Assert;

class Api extends \Codeception\Module
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

    /****************************
     * assertions
     * **************************
     * 
     * @param $condition
     * @param string $message
     */
    public function assertTrue($condition, $message = '')
    {
        parent::assertTrue($condition, $message);
    }

    public function assertFalse($condition, $message = '')
    {
        parent::assertFalse($condition, $message);
    }

    public function assertNotFalse($condition, $message = '')
    {
        parent::assertNotFalse($condition, $message);
    }

}
