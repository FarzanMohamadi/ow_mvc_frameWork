<?php
/**
 * Created by Mohammad Aghaabbasloo
 * Time: 4:12 PM
 */
class FRMEMAILCONTROLLER_BOL_Service
{
    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     *
     */
    public function addJsFile()
    {
        $jsDir = OW::getPluginManager()->getPlugin("frmemailcontroller")->getStaticJsUrl();
        OW::getDocument()->addScript($jsDir . "frmemailcontroller.js");
    }

    public function onBeforeJoinFormRender(OW_Event $event){
        OW::getLanguage()->addKeyForJs('frmemailcontroller', 'valid_email_provider_information_title');
        $params = $event->getParams();
        $parentEmail =$params['form']->getElement('parentEmail');
        if($parentEmail!=null){
            $validator = new FRMEMAILCONTROLLER_CLASS_EmailProviderValidator();
            $params['form']->getElement('parentEmail')->addValidator($validator);
            $this->addJsFile();
        }
    }

    public function checkEmailFields( OW_Event $event )
    {
        OW::getLanguage()->addKeyForJs('frmemailcontroller', 'valid_email_provider_information_title');
        $params = $event->getParams();
        if(isset($params['element'])) {
            $element = $params['element'];
            if($element->getAttribute('name')!=null && $element->getAttribute('name')=='email'){
                $cloneElement = clone $element;
                $cloneElement->setValue('abcdefgh@ijklmnopqrst.com');
                if($cloneElement->isValid()) {
                    $validator = new FRMEMAILCONTROLLER_CLASS_EmailProviderValidator();
                    $element->addValidator($validator);
                    $this->addJsFile();
                }
            }
        }
    }

}