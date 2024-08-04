<?php
/**
 * component class.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmshasta.classes
 * @since 1.0
 */
class FRMSHASTA_CMP_Files extends OW_Component
{
    /**
     * FRMSHASTA_CMP_Files constructor.
     * @param $params
     * @throws Redirect404Exception
     */
    public function __construct($params = array())
    {
        parent::__construct();
        $filesId = $params['fileIds'];
        $service = FRMSHASTA_BOL_Service::getInstance();

        $filesComponentIds = array();

        $files = $service->getFilesByIdList($filesId);

        $additionalId = 'default_widget';
        if (isset($params['additionalId'])) {
            $additionalId = $params['additionalId'];
        }

        foreach ($files as $file) {
            $filesComponentIds[] = 'fileId_' . $file->id;
            $this->addComponent('fileId_' . $file->id, new FRMSHASTA_CMP_File(array('fileId' => $file->id, 'additionalId' => $additionalId)));
        }

        $this->assign('files_component_ids', $filesComponentIds);
        FRMSHASTA_BOL_Service::getInstance()->addStaticFiles();
    }
}