<?php
/**
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_DeleteUser extends OW_Component
{

    /**
     * Constructor.
     */
    public function __construct( $params = array() )
    {
        parent::__construct();

        $userId = (int) $params['userId'];
        $code='';
        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            $code =$params['code'];
            if(!isset($code)){
                throw new Redirect404Exception();
            }
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => ow::getUser()->getId(), 'code'=>$code,'activityType'=>'userDelete_core')));
        }
        $showMessage = (bool) $params['showMessage'];

        $rspUrl = OW::getRouter()->urlFor('BASE_CTRL_User', 'deleteUser', array(
            'user-id' => $userId,
            'code'=>$code
        ));

        $rspUrl = OW::getRequest()->buildUrlQueryString($rspUrl, array(
            'showMessage' => (int) $showMessage
        ));

        $js = UTIL_JsGenerator::composeJsString('$("#baseDCButton").click(function()
        {
            var button = this;

            OW.inProgressNode(button);

            $.getJSON({$rsp}, function(r)
            {
                OW.activateNode(button);

                if ( _scope.floatBox )
                {
                    _scope.floatBox.close();
                }

                if ( _scope.deleteCallback )
                {
                    _scope.deleteCallback(r);
                }
            });
        });', array(
            'rsp' => $rspUrl
        ));

        OW::getDocument()->addOnloadScript($js);
    }
}