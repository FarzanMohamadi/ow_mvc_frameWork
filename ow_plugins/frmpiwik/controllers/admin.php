<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmaudio
 * @since 1.0
 */

class FRMPIWIK_CTRL_Admin extends ADMIN_CTRL_Abstract
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
        $this->setPageHeading($lang->text('frmpiwik', 'admin_settings_title'));
        $this->setPageTitle($lang->text('frmpiwik', 'admin_settings_title'));
        $this->setPageHeadingIconClass('ow_ic_gear_wheel');
    }

    /***
     *
     */
    public function settings()
    {


        $PiwikForm = new Form('PiwikForm');

        $lang = OW::getLanguage();
        $config = OW::getConfig();
        $configs = $config->getValues('frmpiwik');

        $piwikSiteId = new textfield('piwiSiteId');
        $piwikSiteId->setValue($configs['frmpiwik']);
        $PiwikForm->addElement($piwikSiteId);
        
        $saveSettings = new Submit('saveSettings');
        $saveSettings->setValue($lang->text('frmpiwik', 'admin_save_settings'));
        $PiwikForm->addElement($saveSettings);

        $this->addForm($PiwikForm);

        if ( OW::getRequest()->isPost())
        {
            if ( $PiwikForm->isValid($_POST) )
            {
                $config->saveConfig('frmpiwik', 'siteId', $PiwikForm->getElement('piwiSiteId')->getValue());
            }
        }
    }
}
