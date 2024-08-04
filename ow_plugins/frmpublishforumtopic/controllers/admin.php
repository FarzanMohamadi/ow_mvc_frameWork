<?php
class FRMPUBLISHFORUMTOPIC_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function index()
    {
        $this->setPageHeading(OW::getLanguage()->text('frmpublishforumtopic', 'admin_settings_heading'));
        $this->setPageTitle(OW::getLanguage()->text('frmpublishforumtopic', 'admin_settings_title'));
        $config =  OW::getConfig();
        $language = OW::getLanguage();

        $form = new Form('form');
        $form->setAjax();
        $form->setAjaxResetOnSuccess(false);
        $form->setAction(OW::getRouter()->urlForRoute('frmpublishforumtopic.admin'));
        $form->bindJsFunction(Form::BIND_SUCCESS,'function( data ){ if(data && data.result){OW.info(\''.$language->text('frmpublishforumtopic', 'settings_updated').'\')  }  }');

        $destination = new RadioField('destination');
        $destination->setRequired();
        $destination->addOptions(
            array(
                'blog' => $language->text('frmpublishforumtopic', 'blog'),
                'news' => $language->text('frmpublishforumtopic', 'news')
            )
        );
        if($config->configExists('frmpublishforumtopic','publish_destination'))
        {
            $destination->setValue($config->getValue('frmpublishforumtopic','publish_destination'));
        }
        $destination->setLabel($language->text('frmpublishforumtopic', 'publish_destination'));
        $form->addElement($destination);


        $submit = new Submit('save');
        $form->addElement($submit);
        $this->addForm($form);

        if ( OW::getRequest()->isAjax() &&  OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $data = $form->getValues();
            $config->saveConfig('frmpublishforumtopic', 'publish_destination', $data['destination']);
            exit(json_encode(array('result' => true)));
        }
    }
}