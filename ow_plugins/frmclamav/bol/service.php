<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmclamav.bol
 * @since 1.0
 */

class FRMCLAMAV_BOL_Service
{

    private static $classInstance;
    
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /***
     * @param OW_Event $event
     * @return bool|void
     */
    public function isFileClean(OW_Event $event) {
        if(!OW::getUser()->isAuthorized('frmclamav','check_file'))
        {
            return;
        }
        $param = $event->getParams();
        $errorMessage=null;
        if (!isset($param['path']) || empty($param['path'])) {
            return;
        }
        $socketDisableDecision = OW::getConfig()->getValue('frmclamav','socket_disable_decision');
        $unknownFilePermission = OW::getConfig()->getValue('frmclamav','unknown_files_permission');
        $path = $param['path'];
        if (is_array($path) && sizeof($path) > 0) {
            $path = $path[0];
        }
        if (empty($path)) {
            return;
        }
        $clean = false;
        $webSocketEnable = false;
        $fileChecked = false;
        $fileCheckedResult = null;
        try {
            if (class_exists('JDecool\ClamAV\ClientFactory')) {
                $clientFactory = new JDecool\ClamAV\ClientFactory();

                $host = OW::getConfig()->getValue('frmclamav','socket_host');
                $port = OW::getConfig()->getValue('frmclamav','socket_port');

                $client = $clientFactory->create($host, $port);
                $analysis = $client->scan($path);
                if (isset($analysis) && isset($analysis->all()[$path])){
                    $webSocketEnable = true;
                    $fileCheckedResult = $analysis->all()[$path];
                    if ($fileCheckedResult) {
                        $fileChecked = true;
                        if ($fileCheckedResult->isClean()) {
                            $clean = true;
                        }
                    }
                }
            }
        }
        catch(InvalidDeamonResponse $e) {
            $errorMessage=$e->getMessage();
        }
        catch (RuntimeException $e) {
            $webSocketEnable = false;
            $errorMessage=$e->getMessage();
        }
        catch (Execption $e) {
            $errorMessage=$e->getMessage();
        }
        if (!$fileChecked && $unknownFilePermission) {
            $clean = true;
        }

        if(!$webSocketEnable && $socketDisableDecision) {
            $clean = true;
        }
        if(!$clean || isset($errorMessage))
        {
            OW::getLogger()->writeLog(OW_Log::NOTICE, 'frmclamav_notice', ['actionType'=>OW_Log::DELETE, 'enType'=>'frmclamav', 'error'=>isset($errorMessage)?$errorMessage:"", 'fileChecked'=>$fileChecked,
                'fileCheckedResult'=>print_r($fileCheckedResult, true),'webSocketEnable'=>$webSocketEnable]);
        }
        $event->setData(array('clean' => $clean, 'result' => $fileCheckedResult, 'webSocketEnable' => $webSocketEnable, 'fileChecked' => $fileChecked,'errorMessage'=>$errorMessage));
    }

    /***
     * @param OW_Event $event
     */
    public function addClamavStaticFiles(OW_Event $event){
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmclamav')->getStaticJsUrl() . 'frmclamav.js');
        OW::getDocument()->addStyleSheet(OW_PluginManager::getInstance()->getPlugin("frmclamav")->getStaticCssUrl() . 'frmclamav.css');
        OW::getLanguage()->addKeyForJs('frmclamav', 'virus_is_detected');
        OW::getLanguage()->addKeyForJs('frmclamav', 'file_is_clean');
    }

    /***
     * @param OW_Event $event
     */
    public function addFileUploadValidator(OW_Event $event) {
        $param = $event->getParams();
        if (!isset($param['FileField'])) {
            return;
        }
        $FileField=$param['FileField'];
        $FileField->addValidator(new FRMCLAMAV_CLASS_FileUploadValidator());
        $event->setData(array('FileField' =>  $FileField));
    }

    public function onCollectAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(
            array(
                'frmclamav' => array(
                    'label' => $language->text('frmclamav', 'auth_group_label'),
                    'actions' => array(
                        'check_file' => $language->text('frmclamav', 'auth_action_label_check_file')
                    )
                )
            )
        );
    }
}
