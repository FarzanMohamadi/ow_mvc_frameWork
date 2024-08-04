<?php
/**
 * Invitation Responder
 *
 * @package ow_system_plugins.base.controllers
 * @since 1.0
 */
class BASE_CTRL_Invitation extends OW_ActionController
{
    public function ajax()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $command = $_POST['command'];
        $data = json_decode($_POST['data'], true);

        $event = new OW_Event('invitations.on_command', array(
            'command' => $command,
            'data' => $data
        ));

        OW::getEventManager()->trigger($event);
        $result = $event->getData();

        echo json_encode(array(
            'script' => (string) $result
        ));

        exit;
    }
}