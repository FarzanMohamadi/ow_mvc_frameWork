<?php
/**
 * component class.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmshasta.classes
 * @since 1.0
 */
class FRMSHASTA_CMP_File extends OW_Component
{
    /**
     * FRMSHASTA_CMP_File constructor.
     * @param $params
     * @throws Redirect404Exception
     */
    public function __construct($params = array())
    {
        parent::__construct();
        $fileId = $params['fileId'];
        $service = FRMSHASTA_BOL_Service::getInstance();

        $file = $service->getFile($fileId);
        $filesData = $service->preparedFileItem($file);

        $this->assign('file', $filesData);
        $additionalId = 'default_widget';
        if (isset($params['additionalId'])) {
            $additionalId = $params['additionalId'];
        }
        $this->assign('additionalId', $additionalId);
        FRMSHASTA_BOL_Service::getInstance()->addStaticFiles();
    }
}