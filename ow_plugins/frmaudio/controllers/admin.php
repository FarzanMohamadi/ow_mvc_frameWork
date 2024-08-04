<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmaudio
 * @since 1.0
 */

class FRMAUDIO_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    /***
     * FRMAUDIO_CTRL_Admin constructor.
     */
    public function __construct()
    {
        parent::__construct();

        if ( OW::getRequest()->isAjax() )
        {
            return;
        }

        $lang = OW::getLanguage();
        $this->setPageHeading($lang->text('frmaudio', 'admin_settings_title'));
        $this->setPageTitle($lang->text('frmaudio', 'admin_settings_title'));
        $this->setPageHeadingIconClass('ow_ic_gear_wheel');
    }

    /***
     *
     */
    public function settings()
    {


        $AudioForm = new Form('AudioForm');

        $lang = OW::getLanguage();
        $config = OW::getConfig();
        $configs = $config->getValues('frmaudio');

        $dashbordaudio = new CheckboxField('audio_dashbord');
        $dashbordaudio->setValue($configs['audio_dashbord']);
        $AudioForm->addElement($dashbordaudio);

        $profileaudio = new CheckboxField('audio_profile');
        $profileaudio->setValue($configs['audio_profile']);
        $AudioForm->addElement($profileaudio);

        $forumaudio = new CheckboxField('audio_forum');
        $forumaudio->setValue($configs['audio_forum']);
        $AudioForm->addElement($forumaudio);

        $saveSettings = new Submit('saveSettings');
        $saveSettings->setValue($lang->text('frmaudio', 'admin_save_settings'));
        $AudioForm->addElement($saveSettings);

        $this->addForm($AudioForm);

        if ( OW::getRequest()->isPost())
        {
            if ( $AudioForm->isValid($_POST) )
            {
                $config->saveConfig('frmaudio', 'audio_dashbord', $AudioForm->getElement('audio_dashbord')->getValue());
                $config->saveConfig('frmaudio', 'audio_profile', $AudioForm->getElement('audio_profile')->getValue());
                $config->saveConfig('frmaudio', 'audio_forum', $AudioForm->getElement('audio_forum')->getValue());
            }
        }


    }
}
