<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 6/11/2017
 * Time: 11:41 AM
 */

$maxUploadMaxFilesize = BOL_FileService::getInstance()->getUploadMaxFilesize();
$config = OW::getConfig();
if ( !$config->configExists('frmvideoplus', 'maximum_video_file_upload'))
{
    $config->addConfig('frmvideoplus', 'maximum_video_file_upload',$maxUploadMaxFilesize);
}