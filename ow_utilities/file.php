<?php
/**
 * @package ow_utilities
 * @since 1.0
 */
class UTIL_File
{
    /**
     * Avaliable image extensions
     *
     * @var array
     */
    private static $imageExtensions = array('jpg', 'jpeg', 'png', 'gif');
    public static $VALID_PHOTO_SIZES = [ [40,40], [100,100], [256, 256], [512, 512], [800,800], [1280, 1280], [1960,1960]];

    /**
     * Avaliable video extensions
     *
     * @var array
     */
    private static $videoExtensions = array('avi', 'mpeg', 'wmv', 'flv', 'mov', 'mp4');

    /**
     * Copies whole directory from source to destination folder. The destionation folder will be created if it doesn't exist.
     * Array and callable can be passed as filter argument. Array should contain the list of file extensions to be copied.
     * Callable is more flexible way for filtering, it should contain one parameter (file/dir to be copied) and return bool 
     * value which indicates if the item should be copied.
     * 
     * @param string $sourcePath
     * @param string $destPath
     * @param mixed $filter
     * @param int $level
     */
    public static function copyDir( $sourcePath, $destPath, $filter = null, $level = -1 )
    {
        $sourcePath = self::removeLastDS($sourcePath);

        $destPath = self::removeLastDS($destPath);

        if ( !self::checkDir($sourcePath) )
        {
            return;
        }

        if ( !OW::getStorage()->fileExists($destPath) )
        {
            OW::getStorage()->mkdir($destPath);
//            OW::getStorage()->chmod($destPath, 0777);
        }

        $handle = opendir($sourcePath);

        if ( $handle !== false )
        {
            while ( ($item = readdir($handle)) !== false )
            {
                if ( $item === "." || $item === ".." )
                {
                    continue;
                }

                $path = $sourcePath . DS . $item;
                $dPath = $destPath . DS . $item;

                if ( is_callable($filter) && !call_user_func($filter, $path) )
                {
                    continue;
                }

                $copy = ($filter === null || (is_array($filter) && in_array(self::getExtension($item), $filter)) || is_callable($filter));

                if ( OW::getStorage()->isFile($path) && $copy )
                {
                    OW::getStorage()->copyFile($path, $dPath);
                }
                else if ( $level && OW::getStorage()->isDir($path) )
                {
                    self::copyDir($path, $dPath, $filter, ($level - 1));
                }
            }

            closedir($handle);
        }
    }

    /**
     * @param string $dirPath
     * @param array $fileTypes
     * @param integer $level
     * @return array
     */
    public static function findFiles( $dirPath, array $fileTypes = null, $level = -1 )
    {
        $dirPath = self::removeLastDS($dirPath);

        $resultList = array();

        $handle = opendir($dirPath);

        if ( $handle !== false )
        {
            while ( ($item = readdir($handle)) !== false )
            {
                if ( $item === '.' || $item === '..' )
                {
                    continue;
                }

                $path = $dirPath . DS . $item;

                if ( OW::getStorage()->isFile($path) && ( $fileTypes === null || in_array(self::getExtension($item), $fileTypes) ) )
                {
                    $resultList[] = $path;
                }
                else if ( $level && OW::getStorage()->isDir($path) )
                {
                    $resultList = array_merge($resultList, self::findFiles($path, $fileTypes, ($level - 1)));
                }
            }

            closedir($handle);
        }

        return $resultList;
    }

    /**
     * Removes directory with content
     *
     * @param string $dirPath
     * @param boolean $empty
     */
    public static function removeDir( $dirPath, $empty = false )
    {
//        OW::getLogger()->writeLog(OW_Log::NOTICE, 'remove_dir', ['actionType'=>OW_Log::DELETE, 'enType'=>'dir', 'dirPath' => $dirPath, 'empty' => $empty, 'trace'=>debug_backtrace()]);

        $dirPath = self::removeLastDS($dirPath);

        if ( !self::checkDir($dirPath) )
        {
            return;
        }

        $handle = opendir($dirPath);

        if ( $handle !== false )
        {
            while ( ($item = readdir($handle)) !== false )
            {
                if ( $item === '.' || $item === '..' )
                {
                    continue;
                }

                $path = $dirPath . DS . $item;

                if ( OW::getStorage()->isFile($path) )
                {
                    OW::getStorage()->removeFile($path);
                }
                else if ( OW::getStorage()->isDir($path) )
                {
                    self::removeDir($path);
                }
            }

            closedir($handle);
        }

        if ( $empty === false )
        {
            if ( !rmdir($dirPath) )
            {
                trigger_error("Cant remove directory `" . $dirPath . "`!", E_USER_WARNING);
            }
        }
    }

    /**
     * @param string $filename
     * @param bool $humanReadable
     * @return int|string
     */
    public static function getFileSize( $filename, $humanReadable = true )
    {
        $bytes = filesize($filename);

        if ( !$humanReadable )
        {
            return $bytes;
        }

        return self::convertBytesToHumanReadable($bytes);
    }

    /**
     * Returns file extension
     *
     * @param string $filenName
     * @return string
     */
    public static function getExtension( $filenName )
    {
        return strtolower(substr($filenName, (strrpos($filenName, '.') + 1)));
    }

    /**
     * Rteurns filename with stripped extension
     *
     * @param string $fileName
     * @return string
     */
    public static function stripExtension( $fileName )
    {
        if ( !strstr($fileName, '.') )
        {
            return trim($fileName);
        }

        return substr($fileName, 0, (strrpos($fileName, '.')));
    }

    /**
     * Returns path without last directory separator
     *
     * @param string $path
     * @return string
     */
    public static function removeLastDS( $path )
    {
        $path = trim($path);

        if ( substr($path, -1) === DS )
        {
            $path = substr($path, 0, -1);
        }

        return $path;
    }

    public static function checkDir( $path )
    {
        if ( !OW::getStorage()->fileExists($path) || !OW::getStorage()->isDir($path) )
        {
            //trigger_warning("Cant find directory `".$path."`!");

            return false;
        }

        if ( !is_readable($path) )
        {
            //trigger_warning('Cant read directory `'.$path.'`!');

            return false;
        }

        return true;
    }
    /* NEED to be censored */

    /**
     * Validates file
     *
     * @param string $fileName
     * @param array $avalia
     * bleExtensions
     * @return bool
     */
    public static function validate( $fileName, array $avaliableExtensions = array() )
    {
        if ( !( $fileName = trim($fileName) ) )
        {
            return false;
        }

        if ( empty($avaliableExtensions) )
        {
            $avaliableExtensions = array_merge(self::$imageExtensions, self::$videoExtensions);
        }

        $extension = self::getExtension($fileName);

        return in_array($extension, $avaliableExtensions);
    }

    /**
     * Validates image file
     *
     * @param string $fileName
     * @return bool
     */
    public static function validateImage( $fileName )
    {
        return self::validate($fileName, self::$imageExtensions);
    }

    /**
     * Validates video file
     *
     * @param string $fileName
     * @return bool
     */
    public static function validateVideo( $fileName )
    {
        return self::validate($fileName, self::$videoExtensions);
    }

    /**
     * Sanitizes a filename, replacing illegal characters
     *
     * @param string $fileName
     * @return string
     */
    public static function sanitizeName( $fileName )
    {
        if ( !( $fileName = trim($fileName) ) )
        {
            return false;
        }

        $specialChars = array("?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(",
            ")", "|", "~", "`", "!", "{", "}");
        $fileName = str_replace($specialChars, '', $fileName);
        $fileName = preg_replace('/[\s-]+/', '-', $fileName);
        $fileName = trim($fileName, '.-_');

        return $fileName;
    }

    /**
     * Checks if uploaded file is valid, if not returns localized error string.
     * 
     * @param int $errorCode
     * @return array
     */
    public static function checkUploadedFile( array $filesItem, $fileSizeLimitInBytes = null )
    {
        $language = OW::getLanguage();

        if ( empty($filesItem) || !array_key_exists("tmp_name", $filesItem) || !array_key_exists("size", $filesItem) )
        {
            return array("result" => false, "message" => $language->text("base", "upload_file_fail"));
        }

        if ( $fileSizeLimitInBytes == null )
        {
            $fileSizeLimitInBytes = self::getFileUploadServerLimitInBytes();
        }

        if ( $filesItem["error"] != UPLOAD_ERR_OK )
        {
            switch ( $filesItem["error"] )
            {
                case UPLOAD_ERR_INI_SIZE:
                    $errorString = $language->text("base", "upload_file_max_upload_filesize_error",
                        array("limit" => ($fileSizeLimitInBytes / 1024 / 1024) . "MB"));
                    break;

                case UPLOAD_ERR_PARTIAL:
                    $errorString = $language->text("base", "upload_file_file_partially_uploaded_error");
                    break;

                case UPLOAD_ERR_NO_FILE:
                    $errorString = $language->text("base", "upload_file_no_file_error");
                    break;

                case UPLOAD_ERR_NO_TMP_DIR:
                    $errorString = $language->text("base", "upload_file_no_tmp_dir_error");
                    break;

                case UPLOAD_ERR_CANT_WRITE:
                    $errorString = $language->text("base", "upload_file_cant_write_file_error");
                    break;

                case UPLOAD_ERR_EXTENSION:
                    $errorString = $language->text("base", "upload_file_invalid_extention_error");
                    break;

                default:
                    $errorString = $language->text("base", "upload_file_fail");
            }

            return array("result" => false, "message" => $errorString);
        }

        if ( $filesItem['size'] > $fileSizeLimitInBytes )
        {
            return array("result" => false, "message" => $language->text("base",
                    "upload_file_max_upload_filesize_error",
                    array("limit" => ($fileSizeLimitInBytes / 1024 / 1024) . "MB")));
        }

        if ( !is_uploaded_file($filesItem["tmp_name"]) )
        {
            return array("result" => false, "message" => $language->text("base", "upload_file_fail"));
        }

        return array("result" => true);
    }

    /**
     * Returns server file upload limit in bytes
     * 
     * @return int
     */
    public static function getFileUploadServerLimitInBytes()
    {
        $uploadMaxFilesize = self::convertHumanReadableToBytes(ini_get("upload_max_filesize"));
        $postMaxSize = self::convertHumanReadableToBytes(ini_get("post_max_size"));

        return $uploadMaxFilesize < $postMaxSize ? $uploadMaxFilesize : $postMaxSize;
    }

    /**
     * Converts human readable (10Mb, 20Kb...) in bytes
     * 
     * @param string $value
     * @return int
     */
    public static function convertHumanReadableToBytes( $value )
    {
        $value = trim($value);
        $lastChar = strtolower($value[strlen($value) - 1]);
        $value = floatval($value);

        switch ( $lastChar )
        {
            case "g":
                $value *= 1024;
            case "m":
                $value *= 1024;
            case "k":
                $value *= 1024;
        }

        return intval($value);
    }

    /**
     * Converts bytes in human readable string
     * 
     * @param int $bytes
     * @param int $decimals
     * @return string
     */
    public static function convertBytesToHumanReadable( $bytes, $decimals = 2 )
    {
        $size = array("B", "kB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");

        $factor = (int) floor((strlen($bytes) - 1) / 3);

        if ( isset($size[$factor]) )
        {
            return sprintf("%.{$decimals}f ", $bytes / pow(1024, $factor)) . $size[$factor];
        }

        return $bytes;
    }


    /***
     * @param $path
     * @param $width
     * @param $height
     * @return string
     */
    private static function getPhotoPathForSize($path, $width, $height) {
        $ext_i = strrpos($path, '.');
        return substr($path, 0, $ext_i) . '-' . $width . 'x' . $height . substr($path, $ext_i);
    }

    public static function getCustomPath($originalPath, $uniqueName, $sizeWidth=false, $sizeHeight=false, $sizeType='min') {
        $path = $originalPath;

        if ($sizeWidth && $sizeHeight && in_array(UTIL_File::getExtension($originalPath), array('jpg', 'jpeg', 'png', 'bmp'))) {
            list($actual_width, $actual_height) = @getimagesize($originalPath);

            if($actual_width > 0 && $actual_height > 0) {
                $chosenSize = [0, 0];
                foreach (self::$VALID_PHOTO_SIZES as $validSize) {
                    $chosenSize = $validSize;
                    if ($chosenSize[0] >= $sizeWidth && $chosenSize[1] >= $sizeHeight) {
                        break;
                    }
                }

                //calculate exact size
                if ($chosenSize[0] / $chosenSize[1] > $actual_width / $actual_height) {
                    $chosenSize[1] = intval($chosenSize[0] * ($actual_height / $actual_width));

                } else {
                    $chosenSize[0] = intval($chosenSize[1] * ($actual_width / $actual_height));
                }

                if ($chosenSize[0] < $actual_width && $chosenSize[1] < $actual_height) {
                    //get requested size
                    $path = self::getPhotoPathForSize($path, $chosenSize[0], $chosenSize[1]);

                    //check if exists with requested size from db
                    $photoDao = BOL_PhotoSizeDao::getInstance();
                    $photoFromDb = $photoDao->findPhoto($uniqueName, $chosenSize[0], $chosenSize[1]);
                    if (!isset($photoFromDb)) {
                        $item = new BOL_PhotoSize();
                        $item->originalPath = $uniqueName;
                        $item->width = $chosenSize[0];
                        $item->height = $chosenSize[1];
                        $photoDao->save($item);

                        $image = new UTIL_Image($originalPath);
                        $image->resizeImage($chosenSize[0], $chosenSize[1]);
                        $image->saveImage($path);
                    }
                }
            }
        }
        return $path;
    }

    /***
     *
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     *
     * @param $originalPath
     * @param $uniqueName
     * @param bool $sizeWidth
     * @param bool $sizeHeight
     * @param string $sizeType
     * @param $params
     * @return string
     */
    public static function getFileUrl($originalPath, $uniqueName, $sizeWidth=false, $sizeHeight=false, $sizeType='min', $params = array())
    {
        $path = self::getCustomPath($originalPath, $uniqueName, $sizeWidth, $sizeHeight, $sizeType);
        return OW::getStorage()->getFileUrl($path, false, $params);
    }

    /***
     * Clears file cache
     *
     * @param $path
     */
    public static function clearCache($path){
        if (function_exists('opcache_invalidate') && strlen(ini_get("opcache.restrict_api")) < 1) {
            opcache_invalidate($path, true);
        } elseif (function_exists('apc_compile_file')) {
            apc_compile_file($path);
        }
    }


    /***
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param $filename
     * @return mixed|string
     */
    public static function getMimeTypeByFileName($filename){
        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $parts = explode('.',$filename);
        $ext = strtolower(array_pop($parts));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        }
        return 'application/octet-stream';
    }
}
