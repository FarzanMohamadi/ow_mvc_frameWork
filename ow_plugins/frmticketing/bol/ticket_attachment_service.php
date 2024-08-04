<?php
/**
 * FRMTICKETING Ticket Attachment Service Class
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.bol
 * @since 1.0
 */
final class FRMTICKETING_BOL_TicketAttachmentService
{
    /**
     * @var FRMTICKETING_BOL_TicketAttachmentService
     */
    private static $classInstance;

    /**
     * @var FRMTICKETING_BOL_TicketAttachmentDao
     */
    private $attachmentDao;

    /**
     * Class constructor
     */
    protected function __construct()
    {
        $this->attachmentDao = FRMTICKETING_BOL_TicketAttachmentDao::getInstance();
    }

    /**
     * Returns class instance
     *
     * @return FRMTICKETING_BOL_TicketAttachmentService
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
            self::$classInstance = new self();

        return self::$classInstance;
    }

    /**
     * @param $id
     * @return FRMTICKETING_BOL_TicketAttachment
     */
    public function findTicketAttachmentById( $id )
    {
        return $this->attachmentDao->findById($id);
    }

    public function findAllAttachments()
    {
        return $this->attachmentDao->findAll();
    }

    /**
     * Returns attachments list
     * 
     * @param array $ticketIds
     * @return array
     */
    public function findAttachmentsByEntityIdList( $entityIds,$entityType )
    {
        if ( !count($entityIds) )
        {
            return array();
        }

        $attmList = $this->attachmentDao->findAttachmentsByEntityIdList($entityIds,$entityType);

        $list = array();

        if ( $attmList )
        {
            foreach ( $attmList as $attm )
            {
                $attm['fileSize'] = round($attm['fileSize'] / 1024, 2);
                $ext = UTIL_File::getExtension($attm['fileName']);
                $attm['downloadUrl'] = $this->getAttachmentFileUrl($attm['id'], $attm['hash'], $ext, $attm['fileNameClean']);
                $list[$attm['entityId']][] = $attm;
            }
        }

        return $list;
    }

    public function addAttachment( FRMTICKETING_BOL_TicketAttachment $attachment, $file )
    {
        $this->attachmentDao->save($attachment);

        $attId = $attachment->id;
        $ext = UTIL_File::getExtension($attachment->fileName);

        $filePath = $this->getAttachmentFilePath($attId, $attachment->hash, $ext, $attachment->fileNameClean);
        $pluginFilesPath = $this->getAttachmentPluginFilesPath($attId, $attachment->hash, $ext, $attachment->fileNameClean);

        $storage = OW::getStorage();
        
        if ( $storage->fileExists($file) && $storage->renameFile($file, $filePath) )
        {
            //$storage->copyFile($pluginFilesPath, $filePath);
            OW::getStorage()->removeFile($pluginFilesPath, true);

            return true;
        }
        else
        {
            $this->attachmentDao->deleteById($attId);
            return false;
        }
    }

    public function deleteAttachment( $attId )
    {
        /* @var FRMTICKETING_BOL_TicketAttachment $attachment */
        $attachment = $this->findTicketAttachmentById($attId);

        if ( !$attachment )
        {
            return true;
        }

        $ext = UTIL_File::getExtension($attachment->fileName);
        $path = $this->getAttachmentFilePath($attId, $attachment->hash, $ext, $attachment->fileNameClean);

        if ( OW::getStorage()->fileExists($path) )
        {
            $attachment->fileNameClean = ('deleted_' . FRMSecurityProvider::generateUniqueId() . '_' . $attachment->fileNameClean);
            $this->attachmentDao->save($attachment);
            $newPath = $this->getAttachmentFilePath($attId, $attachment->hash, $ext, $attachment->fileNameClean);

            OW::getStorage()->renameFile($path, $newPath);
//            OW::getStorage()->removeFile($path);
        }

        $this->attachmentDao->deleteById($attId);

        return true;
    }

    public function deleteAttachmentsByTypeAndId($entityType,$entityId)
    {
        if ( !$entityId ||  !$entityType)
        {
            return false;
        }

        $attachments = $this->attachmentDao->findAttachmentsByTypeAndId($entityType,$entityId);

        foreach ( $attachments as $file )
        {
            $this->deleteAttachment($file['id']);
        }

        return false;
    }

    public function getAttachmentFileName( $attId, $hash, $ext, $name )
    {
        return 'attachment_' . $attId . '_' . $hash . (mb_strlen($name) ? '_' . $name : (mb_strlen($ext) ? '.' . $ext : ''));
    }

    public function getAttachmentFilePath( $attId, $hash, $ext, $name = null )
    {
        $userFilesDir = OW::getPluginManager()->getPlugin('frmticketing')->getUserFilesDir();

        return $userFilesDir . $this->getAttachmentFileName($attId, $hash, $ext, $name);
    }

    public function getAttachmentFileUrl( $attId, $hash, $ext, $name = null )
    {
        $userFilesDir = OW::getPluginManager()->getPlugin('frmticketing')->getUserFilesDir();
        $storage = OW::getStorage();

        return $storage->getFileUrl($userFilesDir . $this->getAttachmentFileName($attId, $hash, $ext, $name));
    }

    public function getAttachmentPluginFilesPath( $attId, $hash, $ext, $name = null )
    {
        $dir = OW::getPluginManager()->getPlugin('frmticketing')->getPluginFilesDir();

        return $dir . $this->getAttachmentFileName($attId, $hash, $ext, $name);
    }

    public function countAttachments()
    {
        return $this->attachmentDao->countAll();
    }
}
