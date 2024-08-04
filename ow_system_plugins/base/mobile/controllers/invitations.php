<?php
/**
 * Invitation Responder
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.mobile.controllers
 * @since 1.6.0
 */
class BASE_MCTRL_Invitations extends OW_MobileActionController
{
    public function command()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $invid = $_POST['invid'];
        $command = $_POST['command'];

        $event = new OW_Event('invitations.on_command', array(
            'command' => $command,
            'data' => $invid
        ));

        OW::getEventManager()->trigger($event);
        $result = $event->getData();

        echo json_encode($result);

        exit;
    }
}