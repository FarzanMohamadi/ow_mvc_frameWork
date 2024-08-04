<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmaudio
 * @since 1.0
 */
class FRMAUDIO_CMP_Audio extends OW_Component
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        FRMAUDIO_BOL_Service::getInstance()->getAudioJS();
        $form = FRMAUDIO_BOL_Service::getInstance()->getAddAudioForm();
        // privacy on floatbox
/*        $event = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_CREATE_FORM_USING_FIELD_PRIVACY, array('privacyKey' => 'add_audio')));
        if(isset($event->getData()['privacyElement'])){
            $form->addElement($event->getData()['privacyElement']);
            $this->assign('statusPrivacy', true);
        }*/
        $this->assign('dataUrl',OW::getRouter()->urlForRoute('frmaudio-audio-save-temp-item'));
        $this->assign('dataBlobUrl',OW::getRouter()->urlForRoute('frmaudio-audio-save-blob-item'));
        $this->addForm($form);
    }
}