<?php
/**
 * FRM Cert
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmiosdetector
 * @since 1.0
 */

final class FRMIOSDETECTOR_BOL_Service
{
    private function __construct()
    {
    }

    /***
     * @var
     */
    private static $classInstance;

    /***
     * @return FRMIOSDETECTOR_BOL_Service
     */
    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function checkOS(OW_Event $event){
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmiosdetector')->getStaticJsUrl() . 'iosdetector.js');
        if($this->isiOS())
        {
            if (isset($_COOKIE['seen_ios_guide']) ) {
                return;
            }
            OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('frmiosdetector')->getStaticCssUrl() . 'frmiosdetector.css');
            $attachmentItemsNoPreview = new FRMIOSDETECTOR_CMP_Guide();
            $attachmentsNoPreviewHtml = $attachmentItemsNoPreview->render();
            OW::getDocument()->addScriptDeclaration('if (!(("standalone" in window.navigator) && window.navigator.standalone)) { $("body").css("overflow","hidden"); $("body").append(\'' . $this->stripString($attachmentsNoPreviewHtml) .'\'); } ');
        }
    }



    public function stripString($string){
        //remove multiple new lines
        $string = preg_replace("/[\r\n]+/", "\r\n", $string);
        $string = preg_replace("/[\n]+/", "\n", $string);
        $string = preg_replace("/[\r]+/", "\r", $string);
        $string = preg_replace("'\r'","", $string);
        $string = preg_replace("'\n '","", $string);
        $string = preg_replace("'\n '","", $string);
        $string = preg_replace("' '","", $string);
        $string = str_replace("\r\n"," ", $string);
        $string = str_replace("\r"," ", $string);
        $string = str_replace("\n"," ", $string);
        $string = preg_replace('/\s+/', ' ', $string);
        $string = trim($string);
        return $string;
    }

    public function isiOS()
    {
        if(empty($_SERVER['HTTP_USER_AGENT'])){
            return false;
        }
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $iPod    = stripos($user_agent,"iPod");
        $iPhone  = stripos($user_agent,"iPhone");
        $iPad    = stripos($user_agent,"iPad");
        $webOS   = stripos($user_agent,"webOS");
        if($iPod || $iPhone || $iPad || $webOS)
        {
            return true;
        }
        return false;
    }


}