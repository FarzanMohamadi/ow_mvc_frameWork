<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BASE_CTRL_Flag extends OW_ActionController
{

    public function flag()
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            exit(json_encode(array(
                'result' => 'success',
                'js' => 'OW.error(' . json_encode(OW::getLanguage()->text('base', 'sing_in_to_flag')) . ')'
            )));
        }

        $entityType = $_POST["entityType"];
        $entityId = $_POST["entityId"];
        
        $data = BOL_ContentService::getInstance()->getContent($entityType, $entityId);
        $ownerId = $data["userId"];
        $userId = OW::getUser()->getId();
        
        if ( $ownerId == $userId )
        {
            exit(json_encode(array(
                'result' => 'success',
                'js' => 'OW.error("' . OW::getLanguage()->text('base', 'flag_own_content_not_accepted') . '")'
            )));
        }

        $reason = UTIL_HtmlTag::escapeHtml($_POST['reason']);
        $service = BOL_FlagService::getInstance();
        $service->addFlag($entityType, $entityId, $reason, $userId);
                
        exit(json_encode(array(
            'result' => 'success',
            'js' => 'OW.info("' . OW::getLanguage()->text('base', 'flag_accepted') . '")'
        )));
    }

    public function delete( $params )
    {
        if ( !(OW::getUser()->isAdmin() || BOL_AuthorizationService::getInstance()->isModerator()) )
        {
            throw new Redirect404Exception;
        }

        BOL_FlagService::getInstance()->deleteFlagById($params['id']);
        OW::getFeedback()->info(OW::getLanguage()->text('base', 'flags_deleted'));

        if ( !empty($_SERVER['HTTP_REFERER']) )
        {
            if(strpos( $_SERVER['HTTP_REFERER'], ":") === false ) {
                $this->redirect($_SERVER['HTTP_REFERER']);
            }
        }
        $this->redirect(OW::getRouter()->urlForRoute('base_index'));
    }
}