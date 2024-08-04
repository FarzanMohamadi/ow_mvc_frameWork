<?php
/**
 * frmfilemanager
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmfilemanager
 * @since 1.0
 */

class FRMFILEMANAGER_BOL_Service
{

    /***
     * @var FRMFILEMANAGER_BOL_FileDao
     */
    private $fileDao;

    /**
     * Singleton instance.
     *
     * @var FRMFILEMANAGER_BOL_Service
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMFILEMANAGER_BOL_Service
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
        $this->fileDao = FRMFILEMANAGER_BOL_FileDao::getInstance();
    }

    /***
     * WARNING: this function resets tables and re-checks all files
     */
    public function reset_and_import(){
        OW::getDbo()->query("
            TRUNCATE `" . OW_DB_PREFIX . "frmfilemanager_file`;
            INSERT INTO `" . OW_DB_PREFIX . "frmfilemanager_file`
                (`id`, `parent_id`, `name`, `content`, `size`, `mtime`, `mime`,  `read`, `write`, `locked`, `hidden`, `width`, `height`) VALUES 
                ('1' ,         '0', 'root',        '',    '0',     '0','directory', '1',     '0',      '0',      '0',      '0',     '0')
            ;
        ");

        OW::getEventManager()->trigger(new OW_Event('frmfilemanager.import_files'));
    }

    /***
     * WARNING: this function resets tables of all user files
     */
    public function reset_only_groups(){
        $row = $this->getByPath('frm:groups');
        if(isset($row)) {
            $this->deleteDirById($row->id);
        }
        OW::getEventManager()->trigger(new OW_Event('frmfilemanager.import_files', ['type'=>'groups']));
    }

    /***
     * WARNING: this function resets tables of all private user files
     */
    public function reset_only_profile(){
        $row = $this->getByPath('frm:profile');
        if(isset($row)) {
            $this->deleteDirById($row->id);
        }
        OW::getEventManager()->trigger(new OW_Event('frmfilemanager.import_files', ['type'=>'profile']));
    }

    public function insert($name, $parent, $type, $time, $content='', $write=true, $locked=false, $size=0){
        if (empty($parent)){
            return 0;
        }
        $dto = new FRMFILEMANAGER_BOL_File();
        $dto->name = $name;
        $dto->parent_id = $parent;
        $dto->content = $content;
        $dto->size = $size;
        $dto->mtime = $time;
        $dto->mime = $type;
        $dto->read = '1';
        $dto->write = $write?'1':'0';
        $dto->locked = $locked?'1':'0';
        $dto->hidden = '0';
        $dto->width = $dto->height = '0';
        $this->fileDao->save($dto);
        return $dto->id;
    }

    /***
     * @param $a_id
     * @return FRMFILEMANAGER_BOL_File
     */
    public function findByAttachmentId($a_id){
        $ex = new OW_Example();
        $ex->andFieldLike('content', '{"a_id":"'.$a_id.'"%');
        return $this->fileDao->findObjectByExample($ex);
    }

    /***
     * @param $a_id
     * @param $newName
     * @param $newParent
     */
    public function editFileByAttachmentId($a_id, $newName, $newParent){
        $dto = $this->findByAttachmentId($a_id);
        if(empty($dto)){
            return;
        }
        if(!empty($newName)) {
            $dto->name = $newName;
        }
        if(!empty($newParent)) {
            $dto->parent_id = $newParent;
        }
        $this->fileDao->save($dto);
    }

    /***
     * @param $id
     * @param $newName
     * @param $newParent
     */
    public function editDirById($id, $newName, $newParent){
        $dto = $this->fileDao->findById($id);
        if(empty($dto)){
            return;
        }
        if(!empty($newName)) {
            $dto->name = $newName;
        }
        if(!empty($newParent)) {
            $dto->parent_id = $newParent;
        }
        $this->fileDao->save($dto);
    }

    /***
     * @param $a_id
     * @return int
     */
    public function deleteFileByAttachmentId($a_id){
        $ex = new OW_Example();
        $ex->andFieldLike('content', '"a_id":"'.$a_id.'"');
        return $this->fileDao->deleteByExample($ex);
    }

    /***
     * @param $name
     * @return array
     */
    public function deleteDirByName($name){
        $id = $this->getIdFromName($name);
        return $this->deleteDirById($id);
    }

    /***
     * @param $id
     * @return array|null
     */
    public function deleteDirById($id){
        $ids = [];

        // Delete all sub folders, ...
        $dir = $this->fileDao->findById($id);
        if (empty($dir)) {
            return null;
        }
        $stack = [$dir->id];
        while(!empty($stack)){
            $curId = array_pop($stack);
            $ids[] = $curId;
            $stack = array_merge($stack, $this->fileDao->getSubIds($curId));
        }

        return $this->fileDao->deleteByIdList($ids);
    }

    /***
     * Are they in the same lineage
     * @param $parent_id
     * @param $child_id
     * @return array
     */
    public function hasRelationship($parent_id, $child_id){
        $cur_row = FRMFILEMANAGER_BOL_FileDao::getInstance()->findById($child_id);
        $distance = 0;
        while($cur_row->id < $parent_id){
            $cur_row = FRMFILEMANAGER_BOL_FileDao::getInstance()->findById($cur_row->parent_id);
            $distance += 1;
        }
        if($cur_row->id == $parent_id){
            return [true, $distance];
        }
        return [false];
    }

    /***
     * @param $name
     * @return mixed
     */
    public function getIdFromName($name){
        $ex = new OW_Example();
        $ex->andFieldLike('name', $name);
        $ex->andFieldEqual('mime', 'directory');
        $ex->setOrder('parent_id ASC');
        $ex->setLimitClause(0,1);
        return $this->fileDao->findIdByExample($ex);
    }

    /***
     * @param $code
     * @return mixed|string
     */
    public function getEntityTypeFromName($code){
        if (is_numeric($code)){
            return '';
        }
        $parts = explode(':', $code);
        if ($parts[0] == 'frm' && count($parts)>1){
            return $parts[1];
        }
        return '';
    }

    /***
     * @param $code
     * @return int
     */
    public function getEntityIdFromName($code){
        if (is_numeric($code)){
            return (int)$code;
        }
        $parts = explode(':', $code);
        if ($parts[0] == 'frm' && count($parts)>1){
            return (int)$parts[count($parts)-1];
        }
        return 0;
    }

    /***
     * @param BOL_Attachment $attachmentDto
     * @return string
     */
    public function contentForAttachment($attachmentDto){
        $content = ['a_id' => $attachmentDto->id, 'name' => $attachmentDto->getFileName()];
        return json_encode($content);
    }

    /***
     * @param string $fileName
     * @return string
     */
    public function getAttachmentPath($fileName){
        return BOL_AttachmentService::getInstance()->getAttachmentsDir() . $fileName;
    }

    /***
     * @param $path
     * @return FRMFILEMANAGER_BOL_File
     * example path: /frm:groups/frm:groups:1
     */
    public function getByPath($path){
        $parts = explode('/', $path);
        $row = $this->fileDao->findById(1);

        $parent_id = 1;
        foreach($parts as $name){
            if(empty($name)){
                continue;
            }
            $ex = new OW_Example();
            $ex->andFieldEqual('parent_id', $parent_id);
            $ex->andFieldEqual('name', $name);
            $row = $this->fileDao->findObjectByExample($ex);
            if(empty($row)){
                return null;
            }
            $parent_id = $row->id;
        }

        return $row;
    }

    /***
     * @param $id
     * @return string
     */
    public function getHashById($id){
        $volumeId = 'm1_';
        //       return 'm1_Mg';
        return $volumeId . rtrim(strtr(base64_encode($id), '+/=', '-_.'), '.');
    }

    /***
     * @param $entityId
     * @return bool
     */
    public function hasProfileAccess($entityId = null){
        if (!OW::getUser()->isAuthenticated())
            return false;
        $entityId = isset($entityId)?$entityId:OW::getUser()->getId();
        if ($entityId != OW::getUser()->getId())
            return false;
        $path = "frm:profile/frm:profile:".$entityId;
        $dir = $this->getByPath($path);
        return !(empty($dir));
    }

    /***
     * @param $entityType
     * @param $entityId
     * @return array
     */
    public function getSubfolders($entityType, $entityId){
        $path = "frm:{$entityType}/frm:{$entityType}:{$entityId}";
        $dir = $this->getByPath($path);
        if(empty($dir)){
            return [];
        }

        $result = ['root'=>$dir->id];
        $stack = [$dir->id];
        while(!empty($stack)){
            $cur = array_pop($stack);
            $result[$cur] = [];

            $newDirs = $this->fileDao->getSubDirs($cur);
            foreach ($newDirs as $newDir){
                /*** @var FRMFILEMANAGER_BOL_File $newDir*/
                $stack[] = $newDir->id;
                $result[$cur][] = ['id'=>$newDir->id, 'name'=>$newDir->name,
                    'created_at'=>$newDir->mtime, 'parent_id'=>$newDir->parent_id];
            }
        }
        return $result;
    }

    /***
     * @param $entityType
     * @param $entityId
     * return array
     */
    public function getSubfiles($entityType, $entityId){
        $path = "frm:{$entityType}/frm:{$entityType}:{$entityId}";
        $dir = $this->getByPath($path);
        if(empty($dir)){
            return [];
        }

        $fileList = [];
        $stack = [$dir];
        while(!empty($stack)){
            $curItem = array_pop($stack);
            if(!isset($curItem->mime)){
                continue;
            }
            if ($curItem->mime == 'directory') {
                $stack = array_merge($stack, $this->fileDao->getSubFiles($curItem->id, true));
            }else{
                $content = $curItem->content;
                $attachment_id = json_decode($content)->a_id;
                $attch = BOL_AttachmentDao::getInstance()->findById($attachment_id);
                if(isset($attch)){
                    $fileList[$attachment_id] = $attch;
                    $fileList[$attachment_id]->parent_id = $curItem->parent_id;
                }
            }
        }

        return $fileList;
    }

    /***
     * @param $id
     * @return bool
     */
    public function moveToMyProfile($id){
        if (!OW::getUser()->isAuthenticated()){
            return false;
        }
        if(empty($id)){
            return false;
        }
        $myProfile = $this->getByPath('frm:profile/frm:profile:'.OW::getUser()->getId());
        if(empty($myProfile)){
            return false;
        }

        $file = $this->fileDao->findById($id);
        /* @var $file FRMFILEMANAGER_BOL_File */
        if (!isset($file) || $file->mime == 'directory'){
            return false;
        }
        $content = json_decode($file->content);
        $attchId = $content->a_id;

        // duplicate file attachment and insert to my profile
        $attach = BOL_AttachmentService::getInstance()->duplicateAttachmentById($attchId);
        if(!isset($attach)){
            return false;
        }

        // new file row
        $content->a_id = $attach->id;
        $this->insert($file->name, $myProfile->id, $file->mime, time(), json_encode($content));

        return true;
    }

    /***
     * @param $hash
     * @return int
     */
    public function getIdByHash($hash){
        $core = FRMFILEMANAGER_BOL_Core::getInstance();
        return (int)$core->realpath($hash);
    }
}