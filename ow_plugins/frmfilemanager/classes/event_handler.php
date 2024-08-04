<?php
/**
 * frmfilemanager
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmfilemanager
 * @since 1.0
 */
class FRMFILEMANAGER_CLASS_EventHandler
{
    private static $classInstance;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }


    private function __construct()
    {
    }

    public function init()
    {
        $this->genericInit();
    }

    public function genericInit(){
        $eventManager = OW::getEventManager();
        $eventManager->bind('frmfilemanager.after_file_upload', array($this, 'afterFileUpload'));
        $eventManager->bind('base.attachment.delete', array($this, 'afterFileDelete'));
        $eventManager->bind('frmfilemanager.insert', array($this, 'insertEntity'));
        $eventManager->bind('frmfilemanager.remove', array($this, 'removeEntity'));
        $eventManager->bind(OW_EventManager::ON_USER_REGISTER, array($this, 'onUserRegistered'));
        $eventManager->bind(OW_EventManager::ON_USER_UNREGISTER, array($this, 'onUserDelete'));
    }

    public function afterFileUpload(OW_Event $event)
    {
        $service = FRMFILEMANAGER_BOL_Service::getInstance();
        $params = $event->getParams();
        $dto = $params['dto'];
        $entityType = $params['entityType'];
        $entityId = $params['entityId'];

        $path = "frm:{$entityType}/frm:{$entityType}:{$entityId}";
        $dir = $service->getByPath($path);
        if(empty($dir)){
            return;
        }

        // check if specified a subfolder
        $parent_id = $dir->id;
        if(isset($_POST['parent_id']) && $service->hasRelationship($dir->id, (int)$_POST['parent_id'])){
            $parent_id = (int)$_POST['parent_id'];
        }

        $content = $service->contentForAttachment($dto);
        $service->insert($dto->origFileName, $parent_id, 'file', time(), $content);
    }

    public function afterFileDelete(OW_Event $event){
        $params = $event->getParams();
        $id = $params['id'];
        FRMFILEMANAGER_BOL_Service::getInstance()->deleteFileByAttachmentId($id);
    }

    public function insertEntity(OW_Event $event){
        $params = $event->getParams();
        $service = FRMFILEMANAGER_BOL_Service::getInstance();

        $parent = $params['parent'];
        $pid = $service->getIdFromName($parent);

        $service->insert($params['name'], $pid, $params['mime'], $params['time'],
            $params['content'], $params['write'], $params['locked']);
    }

    public function removeEntity(OW_Event $event){
        $params = $event->getParams();
        FRMFILEMANAGER_BOL_Service::getInstance()->deleteDirByName($params['name']);
    }

    public function onUserRegistered(OW_Event $event)
    {
        $params = $event->getParams();
        if (isset($params['userId'])) {
            $uId = $params['userId'];
            $user = BOL_UserService::getInstance()->findUserById($uId);
            if(isset($user)){
                $dir0Id = FRMFILEMANAGER_BOL_Service::getInstance()->getIdFromName('frm:profile');
                FRMFILEMANAGER_BOL_Service::getInstance()->insert('frm:profile:'.$uId, $dir0Id,
                    'directory', time(), '', true, true);
            }
        }
    }

    public function onUserDelete( OW_Event $event )
    {
        $params = $event->getParams();

        if (empty($params['deleteContent'])) {
            return;
        }

        $userId = $params['userId'];
        $dir = FRMFILEMANAGER_BOL_Service::getInstance()->getByPath('frm:profile/frm:profile:'.$userId);
        if(isset($dir)){
            FRMFILEMANAGER_BOL_Service::getInstance()->deleteDirById($dir->id);
        }
    }
}