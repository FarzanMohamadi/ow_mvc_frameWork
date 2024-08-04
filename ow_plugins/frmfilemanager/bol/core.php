<?php
/**
 * frmfilemanager
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmfilemanager
 * @since 1.0
 */

class FRMFILEMANAGER_BOL_Core extends elFinder
{
    /**
     * Singleton instance.
     *
     * @var FRMFILEMANAGER_BOL_Core
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMFILEMANAGER_BOL_Core
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            // Documentation for connector options:
            // https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
            $opts = array(
                // 'debug' => true,
                'roots' => array(
                    array(
                        'driver'        => 'MySQL',
                        'host'          => OW_DB_HOST,
                        'port'          => OW_DB_PORT,
                        'user'          => OW_DB_USER,
                        'pass'          => OW_DB_PASSWORD,
                        'db'            => OW_DB_NAME,
                        //                    'accessControl' => 'accessControl',
                        'files_table'   => OW_DB_PREFIX . 'frmfilemanager_file',
                        'disabled'      => array('extract', 'archive', 'settings', 'zipdl'),
                        'path'          => 1,
                        'tmpPath'       => '/tmp'
                    ),
                )
            );
            self::$classInstance = new self($opts);
        }

        return self::$classInstance;
    }

    /**
     * Simple function to demonstrate how to control file access using "accessControl" callback.
     * This method will disable accessing files/folders starting from  '.' (dot)
     *
     * @param  string  $attr  attribute name (read|write|locked|hidden)
     * @param  string  $path  file path relative to volume root directory started with directory separator
     * @return bool|null
     **/
    public function accessControl($attr, $path, $data, $volume)
    {
        if($attr=='locked'){
            $attr = 'write';
        }

        $cwd_row = FRMFILEMANAGER_BOL_FileDao::getInstance()->findById($path);
        // no access to first two levels, for now!
        if($path <= 1 || $cwd_row->parent_id <=1) {
            return ($attr=='read');
        }

        //second level and third level directories
        $third_level_row = $cwd_row;
        $second_level_row = FRMFILEMANAGER_BOL_FileDao::getInstance()->findById($third_level_row->parent_id);
        $level = 3;
        while($second_level_row->parent_id > 1){
            $third_level_row = $second_level_row;
            $second_level_row = FRMFILEMANAGER_BOL_FileDao::getInstance()->findById($third_level_row->parent_id);
            $level += 1;
        }

        $entity_type =  FRMFILEMANAGER_BOL_Service::getInstance()->getEntityTypeFromName($third_level_row->name);
        $entity_id = FRMFILEMANAGER_BOL_Service::getInstance()->getEntityIdFromName($third_level_row->name);

        // check privacy for cwd
        $event = OW::getEventManager()->trigger(
            new OW_Event('frmfilemanager.check_privacy',
                ['level' => $level, 'type' => 'directory', 'name' => $cwd_row->name, 'is_parent_dir' => true,
                    'second' => $second_level_row->name, 'third' => $third_level_row->name,
                    'entityType' => $entity_type, 'entityId' => $entity_id],
                [$attr => true]
            )
        );
        return ($event->getData()[$attr]);
    }


    /**
     * Before opening a Folder
     * @param $args
     * @return array
     * @throws elFinderAbortException
     */
    protected function open($args)
    {
        $resp = parent::open($args);

        $cwd_id = (int)$this->realpath($resp['cwd']['hash']);
        $cwd_row = FRMFILEMANAGER_BOL_FileDao::getInstance()->findById($cwd_id);

        // no access to first two levels, for now!
        if($cwd_id <= 1 || $cwd_row->parent_id <=1) {
            return array('error' => $this->error(self::ERROR_OPEN, '', self::ERROR_PERM_DENIED));
        }

        //second level and third level directories
        $third_level_row = $cwd_row;
        $second_level_row = FRMFILEMANAGER_BOL_FileDao::getInstance()->findById($third_level_row->parent_id);
        $level = 3;
        while($second_level_row->parent_id > 1){
            $third_level_row = $second_level_row;
            $second_level_row = FRMFILEMANAGER_BOL_FileDao::getInstance()->findById($third_level_row->parent_id);
            $level += 1;
        }

        $entity_type =  FRMFILEMANAGER_BOL_Service::getInstance()->getEntityTypeFromName($third_level_row->name);
        $entity_id = FRMFILEMANAGER_BOL_Service::getInstance()->getEntityIdFromName($third_level_row->name);

        // check privacy for cwd
        $event = OW::getEventManager()->trigger(
            new OW_Event('frmfilemanager.check_privacy',
                ['level' => $level, 'type' => 'directory', 'name' => $cwd_row->name, 'is_parent_dir' => true,
                    'second' => $second_level_row->name, 'third' => $third_level_row->name,
                    'entityType' => $entity_type, 'entityId' => $entity_id],
                ['read' => true, 'write' => true]
            )
        );
        $cwd_read = ($event->getData()['read']);
        $cwd_write = ($event->getData()['write']);
        if(!$cwd_write){
            if(!$cwd_read){
                return array('error' => $this->error(self::ERROR_OPEN, $resp['cwd']['hash'], self::ERROR_PERM_DENIED));
            }
            $resp['cwd']['write'] = false;
        }

        // check privacy for sub-folders and sub-files
        // read for CWD is always true
        foreach($resp['files'] as $key=>$value){
            $file_id = (int)$this->realpath($value['hash']);

            $file = FRMFILEMANAGER_BOL_FileDao::getInstance()->findById($file_id);
            $content = $file->content;
            $type = ($file->mime=='directory')?'directory':'file';

            $event = OW::getEventManager()->trigger(
                new OW_Event('frmfilemanager.check_privacy',
                    ['level' => $level + 1, 'type' => $type, 'name' => $file->name, 'content' => $content,
                        'second' => $second_level_row->name, 'third' => $third_level_row->name,
                        'entityType' => $entity_type, 'entityId' => $entity_id],
                    ['read' => $cwd_read, 'write' => $cwd_write]
                )
            );

            if (isset($event->getData()['name'])) {
                $resp['files'][$key]['name'] = $event->getData()['name'];
            }
            if (!$event->getData()['write']) {
                $resp['files'][$key]['write'] = 0;
                $resp['files'][$key]['locked'] = 1;
            }
            if (!$event->getData()['read']) {
                $resp['files'][$key]['read'] = 0;
                $resp['files'][$key]['write'] = 0;
                $resp['files'][$key]['hidden'] = 1;
                $resp['files'][$key]['locked'] = 1;
            }
        }

        return $resp;
    }

    /**
     * Before saving uploaded files
     *
     * @param  array
     *
     * @return array
     * @throws elFinderAbortException
     */
    protected function upload($args)
    {
        $hash = $args['target'];
        $cwd_id = (int)$this->realpath($hash);

        $cwd_row = FRMFILEMANAGER_BOL_FileDao::getInstance()->findById($cwd_id);

        // no uploads to first two levels, for now!
        if($cwd_id <= 1 || $cwd_row->parent_id <=1) {
            return array('error' => $this->error(self::ERROR_UPLOAD));
        }

        //second level and third level directories
        $third_level_row = $cwd_row;
        $second_level_row = FRMFILEMANAGER_BOL_FileDao::getInstance()->findById($third_level_row->parent_id);
        $level = 3;
        while($second_level_row->parent_id > 1){
            $third_level_row = $second_level_row;
            $second_level_row = FRMFILEMANAGER_BOL_FileDao::getInstance()->findById($third_level_row->parent_id);
            $level += 1;
        }

        $entity_type =  FRMFILEMANAGER_BOL_Service::getInstance()->getEntityTypeFromName($third_level_row->name);
        $entity_id = FRMFILEMANAGER_BOL_Service::getInstance()->getEntityIdFromName($third_level_row->name);

        // check privacy for cwd
        $event = OW::getEventManager()->trigger(
            new OW_Event('frmfilemanager.check_privacy',
                ['level' => $level, 'type' => 'directory', 'name' => $cwd_row->name, 'is_parent_dir' => true,
                    'second' => $second_level_row->name, 'third' => $third_level_row->name,
                    'entityType' => $entity_type, 'entityId' => $entity_id],
                ['write' => true]
            )
        );
        $cwd_write = ($event->getData()['write']);
        if(!$cwd_write){
            return array('error' => $this->error(self::ERROR_UPLOAD));
        }

        // we have permission to upload
        // 1. upload the old way
            foreach($_FILES['upload']['name'] as $k => $v) {
                $item = [
                    'name' => $_FILES['upload']['name'][$k],
                    'type' => $_FILES['upload']['type'][$k],
                    'tmp_name' => $_FILES['upload']['tmp_name'][$k],
                    'error' => $_FILES['upload']['error'][$k],
                    'size' => $_FILES['upload']['size'][$k]
                ];

                if($entity_type == 'groups') {
                    $resp = FRMGROUPSPLUS_BOL_Service::getInstance()->manageAddFile($entity_id, $item);
                }elseif($entity_type == 'profile') {
                    $resp = BOL_UserService::getInstance()->manageAddFile($item);
                }

                if(isset($resp['dtoArr'])){
                    // replace content for insert
                    $filePath = OW::getPluginManager()->getPlugin('frmfilemanager')->getUserFilesDir() . 'tmp_' . $_FILES['upload']['name'][$k];
                    OW::getStorage()->fileSetContent($filePath, FRMFILEMANAGER_BOL_Service::getInstance()->contentForAttachment($resp['dtoArr']['dto']));
                    $args['FILES']['upload']['tmp_name'][$k] = $filePath;
                }else{
                    // remove file from inserting to the new table
                    foreach($args['FILES']['upload'] as $k2 => $v2) {
                        unset($args['FILES']['upload'][$k2][$k]);
                    }
                    unset($args['upload_path'][$k]);
                    unset($args['mtime'][$k]);
                }
            }

        // 2. insert new row to table
        return parent::upload($args);
    }

    /**
     * To download and view files
     *
     * @param $args
     * @return array|void
     */
    protected function file($args)
    {
        $err = array('error' => 'File not found', 'header' => 'HTTP/1.0 404 Not Found', 'raw' => true);

        $hash = $args['target'];
        $cwd_id = (int)$this->realpath($hash);

        $cwd_row = FRMFILEMANAGER_BOL_FileDao::getInstance()->findById($cwd_id);

        if ($cwd_row->mime == 'directory'){
            return $err;
        }

        // no downloads to first two levels, for now!
        if($cwd_id <= 1 || $cwd_row->parent_id <=1) {
            return $err;
        }

        //second level and third level directories
        $third_level_row = $cwd_row;
        $second_level_row = FRMFILEMANAGER_BOL_FileDao::getInstance()->findById($third_level_row->parent_id);
        $level = 3;
        while($second_level_row->parent_id > 1){
            $third_level_row = $second_level_row;
            $second_level_row = FRMFILEMANAGER_BOL_FileDao::getInstance()->findById($third_level_row->parent_id);
            $level += 1;
        }

        $entity_type =  FRMFILEMANAGER_BOL_Service::getInstance()->getEntityTypeFromName($third_level_row->name);
        $entity_id = FRMFILEMANAGER_BOL_Service::getInstance()->getEntityIdFromName($third_level_row->name);

        // check privacy for cwd
        $event = OW::getEventManager()->trigger(
            new OW_Event('frmfilemanager.check_privacy',
                ['level' => $level, 'type' => 'file', 'name' => $cwd_row->name,
                    'second' => $second_level_row->name, 'third' => $third_level_row->name,
                    'entityType' => $entity_type, 'entityId' => $entity_id],
                ['read' => true]
            )
        );

        if (!$event->getData()['read']) {
            return $err;
        }

        // download file
        $content = json_decode($cwd_row->content);
        $path = FRMFILEMANAGER_BOL_Service::getInstance()->getAttachmentPath($content->name);

        $result = parent::file($args);
        $result['pointer'] = @fopen($path,"rb");

        // Remove content-length: to fix the problem of download
        unset($result['header'][3]);

        return $result;
    }

    protected function rm($args)
    {
        // >> privacy for the dir
        $sample_id = (int)$this->realpath($args['targets'][0]);
        $sample_row = FRMFILEMANAGER_BOL_FileDao::getInstance()->findById($sample_id);
        $cwd_id = $sample_row->parent_id;
        $cwd_row = FRMFILEMANAGER_BOL_FileDao::getInstance()->findById($cwd_id);
        // no uploads to first two levels, for now!
        if($cwd_id <= 1 || $cwd_row->parent_id <=1) {
            return array('error' => $this->error(self::ERROR_UPLOAD));
        }
        //second level and third level directories
        $third_level_row = $cwd_row;
        $second_level_row = FRMFILEMANAGER_BOL_FileDao::getInstance()->findById($third_level_row->parent_id);
        $level = 3;
        while($second_level_row->parent_id > 1){
            $third_level_row = $second_level_row;
            $second_level_row = FRMFILEMANAGER_BOL_FileDao::getInstance()->findById($third_level_row->parent_id);
            $level += 1;
        }
        $entity_type =  FRMFILEMANAGER_BOL_Service::getInstance()->getEntityTypeFromName($third_level_row->name);
        $entity_id = FRMFILEMANAGER_BOL_Service::getInstance()->getEntityIdFromName($third_level_row->name);
        // check privacy for cwd
        $event = OW::getEventManager()->trigger(
            new OW_Event('frmfilemanager.check_privacy',
                ['level' => $level, 'type' => 'directory', 'name' => $cwd_row->name, 'is_parent_dir' => true,
                    'second' => $second_level_row->name, 'third' => $third_level_row->name,
                    'entityType' => $entity_type, 'entityId' => $entity_id],
                ['write' => true]
            )
        );
        $cwd_write = ($event->getData()['write']);

        // >> similar to parent::rm() code
        $targets = is_array($args['targets']) ? $args['targets'] : array();
        $result = array('removed' => array());

        if(!$cwd_write){
            $result['warning'] = $this->error(self::ERROR_LOCKED, $cwd_row->name);
            return $result;
        }

        foreach ($targets as $target) {
            elFinder::checkAborted();

            if (($volume = $this->volume($target)) == false) {
                $result['warning'] = $this->error(self::ERROR_RM, '#' . $target, self::ERROR_FILE_NOT_FOUND);
                break;
            }

            if ($this->itemLocked($target)) {
                $rm = $volume->file($target);
                $result['warning'] = $this->error(self::ERROR_LOCKED, $rm['name']);
                break;
            }

            // check privacy of file edit
            $file_id = (int)$this->realpath($target);
            $file = FRMFILEMANAGER_BOL_FileDao::getInstance()->findById($file_id);
            $content = $file->content;
            $type = ($file->mime=='directory')?'directory':'file';
            $event = OW::getEventManager()->trigger(
                new OW_Event('frmfilemanager.check_privacy',
                    ['level' => $level + 1, 'type' => $type, 'name' => $file->name, 'content' => $content,
                        'second' => $second_level_row->name, 'third' => $third_level_row->name,
                        'entityType' => $entity_type, 'entityId' => $entity_id],
                    ['write' => true]
                )
            );
            if(!$event->getData()['write']){
                $result['warning'] = $this->error($volume->error());
                break;
            }

            if (!$volume->rm($target)) {
                $result['warning'] = $this->error($volume->error());
                break;
            }

            // remove from tables of other plugins
            $attachment_id = json_decode($content)->a_id;
            OW::getEventManager()->trigger(
                new OW_Event('frmfilemanager.after_entity_remove',
                    ['level' => $level + 1, 'type' => $type, 'name' => $file->name,
                        'attachmentId' => $attachment_id, 'entityType' => $entity_type, 'entityId' => $entity_id]
                )
            );
        }

        return $result;
    }

}