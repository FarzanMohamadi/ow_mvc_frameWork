<?php
/**
 * frmfilemanager
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmfilemanager
 * @since 1.0
 */
class FRMFILEMANAGER_CTRL_Backend extends OW_ActionController
{

    public function connector($params){
        // run elFinder
        $connector = new FRMFILEMANAGER_BOL_CoreConnector(FRMFILEMANAGER_BOL_Core::getInstance());
        $connector->run();
        exit('error');
    }

    public function saveToProfile($params){
        if(!OW::getRequest()->isAjax()){
            throw new Redirect404Exception();
        }
        if(empty($_POST['hashes'])){
            exit(json_encode(array('result' => false,
                'message' => OW::getLanguage()->text("frmfilemanager", "failed_to_relocate"))));
        }
        foreach($_POST['hashes'] as $hash) {
            $id = FRMFILEMANAGER_BOL_Service::getInstance()->getIdByHash($hash);
            $res = FRMFILEMANAGER_BOL_Service::getInstance()->moveToMyProfile($id);
            if (!$res) {
                exit(json_encode(array('result' => false,
                    'message' => OW::getLanguage()->text("frmfilemanager", "failed_to_relocate"))));
            }
        }

        exit(json_encode(array('result' => true,
            'message' => OW::getLanguage()->text("frmfilemanager", "saved_successfully"))));
    }
}
