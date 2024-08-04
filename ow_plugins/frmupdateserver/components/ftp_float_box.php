<?php
/**
 * Ftp Float Box
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @since 1.0
 */
class FRMUPDATESERVER_CMP_FtpFloatBox extends OW_Component
{

    /**
     * FRMUPDATESERVER_CMP_FtpFloatBox constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $params = json_decode($_POST['params']);
        $isPublic = false;
        if(!in_array($params->type, array('core', 'plugins', 'themes'))){
            $files = FRMUPDATESERVER_BOL_Service::getInstance()->getPublicFilesOfSource($params->type);
            $isPublic = true;
        }else{
            $files = FRMUPDATESERVER_BOL_Service::getInstance()->getFilesOfSource($params->type, $params->key);
        }
        $this->assign('isPublic',$isPublic);
        $this->assign('files',$files['files']);
        $this->assign('dirs',$files['dirs']);
        $this->assign('type', $params->type);
        $this->assign('returnIconUrl', $files['returnIconUrl']);
        $this->assign('returnLabel', $files['returnLabel']);
        $this->assign('urlOfDownload', $files['urlOfDownload']);
        $this->assign('preloader_img_url', OW::getThemeManager()->getThemeImagesUrl() . 'ajax_preloader_content.gif');
        $this->assign('data_post_url', OW::getRouter()->urlForRoute('frmupdateserver.data-post-url'));
    }
}