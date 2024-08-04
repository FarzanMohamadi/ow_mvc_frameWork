<?php
class FRMSECUREFILEURL_CTRL_Url extends OW_ActionController
{

    public function index($params)
    {
        if (!isset($params['hash'])) {
            throw new Redirect404Exception();
        }else{
            $hash = $params['hash'];
            $service = FRMSECUREFILEURL_BOL_Service::getInstance();
            $url = $service->existUrlByHash($hash);
            if($url != null){
                $path = OW_DIR_ROOT . $url->path;
                header('Content-Description: File Transfer');
                if(function_exists('mime_content_type')){
                    $type = mime_content_type($path);
                    if($type == 'video/mp4'){
                        $service->bufferVideo($path);
                    }
                    header('Content-Type: ' . $type);
                }else{
                    header('Content-Type: application/octet-stream');
                }
                header('Content-Disposition: inline; filename=' . basename($path));
                header('Content-Transfer-Encoding: binary');
                header('Pragma: public');
                header('Cache-Control: max-age=864000');
                header('Expires: '. gmdate('D, d M Y H:i:s \G\M\T', time() + 864000));
                header('Content-Length: ' . filesize($path));
                ob_clean();

                // These 2 lines works for files with small size. (But not working for large one).
//                flush();
//                @readfile($path);

                set_time_limit(0);
                $file = @fopen($path,"rb");
                if ( $file !== false ) {
                    while (!feof($file)) {
                        print(@fread($file, 1024 * 8));
                        ob_flush();
                        flush();
                    }
                }
            }
            exit();
        }
    }
}