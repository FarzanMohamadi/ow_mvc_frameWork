<?php
/**
 * coverphoto Service.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.coverphoto.bol
 * @since 1.0
 */
final class COVERPHOTO_BOL_Service
{
    /**
     * @var COVERPHOTO_BOL_CoverDao
     */
    private $coverDao;

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->coverDao = COVERPHOTO_BOL_CoverDao::getInstance();
    }
    /**
     * Singleton instance.
     *
     * @var COVERPHOTO_BOL_Service
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return COVERPHOTO_BOL_Service
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /***
     * @param $entityType
     * @param $entityId
     * @return bool
     */
    public function isOwner($entityType, $entityId){
        if( !OW::getUser()->isAuthenticated() ){
            return false;
        }
        if( OW::getUser()->isAdmin() || OW::getUser()->isAuthorized('base') ){
            return true;
        }
        if ($entityType=='user'){
            return $entityId==OW::getUser()->getId();
        }
        if ($entityType=='groups'){
            $group = GROUPS_BOL_Service::getInstance()->findGroupById($entityId);
            return GROUPS_BOL_Service::getInstance()->isCurrentUserCanEdit($group);
        }
        return false;
    }

    /***
     * @param $entityType
     * @param $entityId
     * @param $title
     * @param $input
     * @return array
     * @throws Exception
     */
    public function uploadNewCover($entityType, $entityId, $title, $input) {
        if(!$this->isOwner($entityType, $entityId)){
            return ['result'=>false, 'message'=>'not_authorized', 'code'=>'not_authorized'];
        }

        if ((int)$_FILES[$input]['error'] !== 0 || !is_uploaded_file($_FILES[$input]['tmp_name'])
            || !UTIL_File::validateImage($_FILES[$input]['name'])) {
            if (!is_uploaded_file($_FILES[$input]['tmp_name'])) {
                return ['result'=>false, 'code'=>'empty_image', 'message'=>OW::getLanguage()->text("coverphoto", "empty_image")];
            }
            return ['result'=>false, 'code'=>'not_valid_image', 'message'=>OW::getLanguage()->text('coverphoto', 'not_valid_image')];
        }

        $sizeValidator = UTIL_File::checkUploadedFile($_FILES[$input]);
        if(!$sizeValidator['result']){
            return ['result'=>false, 'code'=>'size_invalid', 'message'=>$sizeValidator['message']];
        }else if((int) $_FILES[$input]['size'] > (float) OW::getConfig()->getValue('base', 'tf_max_pic_size') * 1024 * 1024){
            return ['result'=>false, 'code'=>'size_invalid', 'message'=>OW::getLanguage()->text('base', 'upload_file_max_upload_filesize_error')];
        }

        //get configured file storage (Cloud files or file system drive, depends on settings in config file)
        $storage = OW::getStorage();

        $imagesDir = OW::getPluginManager()->getPlugin('coverphoto')->getUserFilesDir();
        $imageExt = UTIL_File::getExtension($_FILES[$input]['name']);
        $imageName = 'coverphoto_' . md5($_FILES[$input]['name']. time()) . '.' . $imageExt;
        $imagePath = $imagesDir . $imageName;

        if ($storage->fileExists($imagePath)) {
            $storage->removeFile($imagePath);
        }

        $image = new UTIL_Image($_FILES[$input]['tmp_name']);
        $image->saveImage($imagePath);
        OW::getStorage()->removeFile($_FILES[$input]['tmp_name']);

        $this->addCover($entityType, $entityId, $title, $imageName, time());
        return ['result'=>true, 'message'=>OW::getLanguage()->text("coverphoto", "database_record_saved_info")];
    }

    /***
     * @param $entityType
     * @param $input
     * @return array
     */
    public function uploadNewDefaultCover($entityType, $input) {
        if( !OW::getUser()->isAdmin() && !OW::getUser()->isAuthorized('base') ){
            return ['result'=>false, 'message'=>'not_authorized', 'code'=>'not_authorized'];
        }

        if ((int)$_FILES[$input]['error'] !== 0 || !is_uploaded_file($_FILES[$input]['tmp_name'])
            || !UTIL_File::validateImage($_FILES[$input]['name'])) {
            if (!is_uploaded_file($_FILES[$input]['tmp_name'])) {
                return ['result'=>false, 'code'=>'empty_image', 'message'=>OW::getLanguage()->text("coverphoto", "empty_image")];
            }
            return ['result'=>false, 'code'=>'not_valid_image', 'message'=>OW::getLanguage()->text('coverphoto', 'not_valid_image')];
        }

        $sizeValidator = UTIL_File::checkUploadedFile($_FILES[$input]);
        if(!$sizeValidator['result']){
            return ['result'=>false, 'code'=>'size_invalid', 'message'=>$sizeValidator['message']];
        }else if((int) $_FILES[$input]['size'] > (float) OW::getConfig()->getValue('base', 'tf_max_pic_size') * 1024 * 1024){
            return ['result'=>false, 'code'=>'size_invalid', 'message'=>OW::getLanguage()->text('base', 'upload_file_max_upload_filesize_error')];
        }

        //get configured file storage (Cloud files or file system drive, depends on settings in config file)
        $storage = OW::getStorage();

        $imagesDir = OW::getPluginManager()->getPlugin('coverphoto')->getUserFilesDir();
        $imagesUrl = OW::getPluginManager()->getPlugin('coverphoto')->getUserFilesUrl();
        $imageExt = UTIL_File::getExtension($_FILES[$input]['name']);
        $imageName = 'coverphoto_' . md5($_FILES[$input]['name']. time()) . '.' . $imageExt;
        $imagePath = $imagesDir . $imageName;
        $imageURL  = $imagesUrl . $imageName;

        if ($storage->fileExists($imagePath)) {
            $storage->removeFile($imagePath);
        }

        $image = new UTIL_Image($_FILES[$input]['tmp_name']);
        $image->saveImage($imagePath);
        OW::getStorage()->removeFile($_FILES[$input]['tmp_name']);

        OW::getConfig()->saveConfig('coverphoto', $entityType . '_default_cover', $imageURL);

        return ['result'=>true, 'message'=>OW::getLanguage()->text("coverphoto", "database_record_saved_info")];
    }

    /***
     * @param $entityType
     * @param $entityId
     * @return string
     */
    public function getPageURL($entityType, $entityId){
        if($entityType=='user'){
            $username = BOL_UserService::getInstance()->getUserName($entityId);
            return OW::getRouter()->urlForRoute('base_user_profile', array('username'=>$username));
        }
        if ($entityType=='groups'){
            $group = GROUPS_BOL_Service::getInstance()->findGroupById($entityId);
            return GROUPS_BOL_Service::getInstance()->getGroupUrl($group);
        }
    }

    /***
     * @param $entityType
     * @param $entityId
     * @param bool $originalSize
     * @return string
     */
    public function getCoverURL($entityType, $entityId, $originalSize = False)
    {
        $user_cover = COVERPHOTO_BOL_Service::getInstance()->getSelectedCover($entityType, $entityId);

        if (isset($user_cover)) {
            if ($originalSize) {
                return OW::getStorage()->getFileUrl(OW::getPluginManager()->getPlugin('coverphoto')->getUserFilesDir() . $user_cover->hash);
            } else {
                return OW::getStorage()->getFileUrl(OW::getPluginManager()->getPlugin('coverphoto')->getUserFilesDir() . $user_cover->croppedHash);
            }
        } else {
            if (OW::getConfig()->configExists('coverphoto', $entityType . '_default_cover')){
                return OW::getConfig()->getValue('coverphoto', $entityType . '_default_cover');
            }
            if ($originalSize) {
                return OW::getPluginManager()->getPlugin('coverphoto')->getStaticUrl() . 'img/' . 'empty_original_cover.jpg';
            } else {
                return OW::getPluginManager()->getPlugin('coverphoto')->getStaticUrl() . 'img/' . 'empty_cropped_cover.jpg';
            }
        }
    }

    /***
     * @param $entityType
     * @param $entityId
     * @param $title
     * @param $hash
     * @param $addDateTime
     * @param int $from_top
     * @param null $coverPhotoHeight
     * @return COVERPHOTO_BOL_Cover
     * @throws Exception
     */
    public function addCover($entityType, $entityId, $title, $hash, $addDateTime, $from_top = 0, $coverPhotoHeight= null)
    {
        if(!$this->isOwner($entityType, $entityId)){
            throw new Redirect404Exception();
        }

        $cover = new COVERPHOTO_BOL_Cover();
        $cover->entityType = $entityType;
        $cover->entityId = $entityId;
        $cover->hash = $hash;
        $cover->title = $title;
        $cover->addDateTime = $addDateTime;
        $cover->isCurrent = 1;

        $this->coverDao->unselectAllCovers($entityType, $entityId);

        $this->coverDao->save($cover);
        $this->addCroppedCover($cover, $from_top, $coverPhotoHeight);

        return $cover;
    }

    /***
     * @param $cover
     * @param $from_top
     * @param null $coverPhotoHeight
     * @return COVERPHOTO_BOL_Cover
     */
    public function addCroppedCover($cover, $from_top, $coverPhotoHeight = null)
    {
        $entityType = $cover->entityType;
        $entityId = $cover->entityId;

        if(!$this->isOwner($entityType, $entityId)){
            throw new Redirect404Exception();
        }

        $user_cover_hash = $cover->hash;
        $user_cover_old_path = OW::getPluginManager()->getPlugin('coverphoto')->getUserFilesDir() . $user_cover_hash;
        $default_cover_height = 150;//$imageInformationHeight/($imageInformationWidth/$default_cover_width);

        if($coverPhotoHeight!=null && $coverPhotoHeight<$default_cover_height){
            $from_top = ($from_top * $default_cover_height)/$coverPhotoHeight;
        }
        $default_cover_width = 918;
        $imageInformation = getimagesize(OW::getPluginManager()->getPlugin('coverphoto')->getUserFilesDir() . $user_cover_hash);
//            $imageInformationWidth = $imageInformation[0];
//            $imageInformationHeight = $imageInformation[1];
        $tb = new ThumbAndCrop();
        $tb->openImg($user_cover_old_path); //original cover image

        $newHeight = $tb->getRightHeight($default_cover_width);
        if($newHeight<$default_cover_height){
            $default_cover_height = $newHeight;
        }
        $tb->creaThumb($default_cover_width, $newHeight);
        $tb->setThumbAsOriginal();
        $tb->cropThumb($default_cover_width, $default_cover_height, 0, $from_top);

        $imagesDir = OW::getPluginManager()->getPlugin('coverphoto')->getUserFilesDir();
        $new_cover_name = 'coverphoto_' . md5(rand()) . '.jpg';
        $imagePath = $imagesDir . $new_cover_name;

        $tb->saveThumb($imagePath); //save cropped cover image
        $tb->resetOriginal();
        $tb->closeImg();

        $cover->croppedHash = $new_cover_name;
        $this->coverDao->save($cover);
        return $cover;
    }

    public function deleteDatabaseRecord($id)
    {
        $cover = $this->coverDao->findById($id);
        $entityType = $cover->entityType;
        $entityId = $cover->entityId;

        if(!$this->isOwner($entityType, $entityId)){
            throw new Redirect404Exception();
        }

        $this->coverDao->deleteById($id);
    }

    public function selectThisCover( $id )
    {
        $cover = $this->coverDao->findById($id);
        $entityType = $cover->entityType;
        $entityId = $cover->entityId;

        if(!$this->isOwner($entityType, $entityId)){
            throw new Redirect404Exception();
        }

        $this->coverDao->unselectAllCovers($entityType, $entityId);

        $cover->isCurrent = 1;
        $this->coverDao->save($cover);
    }

    public function getSelectedCover($entityType, $entityId )
    {
        return $this->coverDao->findCover( $entityType, $entityId );
    }

    public function findList( $entityType, $entityId )
    {
        return $this->coverDao->findListOrderedByTitle( $entityType, $entityId );
    }
}

class ThumbAndCrop
{

    private $handleimg;
    private $original = "";
    private $handlethumb;
    private $oldoriginal;

    /*
        Apre l'immagine da manipolare
    */
    public function openImg($file)
    {
        $this->original = $file;

        if ($this->extension($file) == 'jpg' || $this->extension($file) == 'jpeg') {
            $this->handleimg = imagecreatefromjpeg($file);
        } elseif ($this->extension($file) == 'png') {
            $this->handleimg = imagecreatefrompng($file);
        } elseif ($this->extension($file) == 'gif') {
            $this->handleimg = imagecreatefromgif($file);
        } elseif ($this->extension($file) == 'bmp') {
            $this->handleimg = imagecreatefromwbmp($file);
        }
    }

    /*
        Ottiene la larghezza dell'immagine
    */
    public function getWidth()
    {
        return imagesx($this->handleimg);
    }

    /*
        Ottiene la larghezza proporzionata all'immagine partendo da un'altezza
    */
    public function getRightWidth($newheight)
    {
        $oldw = $this->getWidth();
        $oldh = $this->getHeight();

        $neww = ($oldw * $newheight) / $oldh;

        return $neww;
    }

    /*
        Ottiene l'altezza dell'immagine
    */
    public function getHeight()
    {
        return imagesy($this->handleimg);
    }

    /*
        Ottiene l'altezza proporzionata all'immagine partendo da una larghezza
    */
    public function getRightHeight($newwidth)
    {
        $oldw = $this->getWidth();
        $oldh = $this->getHeight();

        $newh = ($oldh * $newwidth) / $oldw;

        return $newh;
    }

    /*
        Crea una miniatura dell'immagine
    */
    public function creaThumb($newWidth, $newHeight)
    {
        $oldw = $this->getWidth();
        $oldh = $this->getHeight();

        $this->handlethumb = imagecreatetruecolor($newWidth, $newHeight);

        return imagecopyresampled($this->handlethumb, $this->handleimg, 0, 0, 0, 0, $newWidth, $newHeight, $oldw, $oldh);
    }

    /*
        Ritaglia un pezzo dell'immagine
    */
    public function cropThumb($width, $height, $x, $y)
    {
        $oldw = $this->getWidth();
        $oldh = $this->getHeight();

        $this->handlethumb = imagecreatetruecolor($width, $height);

        return imagecopy($this->handlethumb, $this->handleimg, 0, 0, $x, $y, $width, $height);
    }

    /*
        Salva su file la Thumbnail
    */
    public function saveThumb($path, $qualityJpg = 100)
    {
        if ($this->extension($this->original) == 'jpg' || $this->extension($this->original) == 'jpeg') {
            return imagejpeg($this->handlethumb, $path, $qualityJpg);
        } elseif ($this->extension($this->original) == 'png') {
            return imagepng($this->handlethumb, $path);
        } elseif ($this->extension($this->original) == 'gif') {
            return imagegif($this->handlethumb, $path);
        } elseif ($this->extension($this->original) == 'bmp') {
            return imagewbmp($this->handlethumb, $path);
        }
    }

    /*
        Stampa a video la Thumbnail
    */
    public function printThumb()
    {
        if ($this->extension($this->original) == 'jpg' || $this->extension($this->original) == 'jpeg') {
            header("Content-Type: image/jpeg");
            imagejpeg($this->handlethumb);
        } elseif ($this->extension($this->original) == 'png') {
            header("Content-Type: image/png");
            imagepng($this->handlethumb);
        } elseif ($this->extension($this->original) == 'gif') {
            header("Content-Type: image/gif");
            imagegif($this->handlethumb);
        } elseif ($this->extension($this->original) == 'bmp') {
            header("Content-Type: image/bmp");
            imagewbmp($this->handlethumb);
        }
    }

    /*
        Distrugge le immagine per liberare le risorse
    */
    public function closeImg()
    {
        imagedestroy($this->handleimg);
        imagedestroy($this->handlethumb);
    }

    /*
        Imposta la thumbnail come immagine sorgente,
        in questo modo potremo combinare la funzione crea con la funzione crop
    */
    public function setThumbAsOriginal()
    {
        $this->oldoriginal = $this->handleimg;
        $this->handleimg = $this->handlethumb;
    }

    /*
        Resetta l'immagine originale
    */
    public function resetOriginal()
    {
        $this->handleimg = $this->oldoriginal;
    }

    /*
        Estrae l'estensione da un file o un percorso
    */
    private function extension($filePath)
    {
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        return strtolower($ext);
    }

}