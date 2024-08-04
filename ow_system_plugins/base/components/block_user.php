<?php
/**
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_BlockUser extends OW_Component
{

    /**
     * Constructor.
     */
    public function __construct( $params = array() )
    {
        parent::__construct();

        $userId = (int) $params['userId'];
        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            $jsonParams =json_decode($_POST['params']);
            $code = $jsonParams->code;
            if(!isset($code)){
                throw new Redirect404Exception();
            }
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => OW::getUser()->getId(), 'code'=>$code,'activityType'=>'userBlock_core')));
        }
        $js = UTIL_JsGenerator::composeJsString('$("#baseBlockButton").click(function(){
           _scope.confirmCallback();
        });');

        OW::getDocument()->addOnloadScript($js);
    }
}