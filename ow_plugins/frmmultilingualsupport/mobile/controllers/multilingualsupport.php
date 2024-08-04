<?php
class FRMMULTILINGUALSUPPORT_MCTRL_MultilingualSupport extends OW_MobileActionController
{
    public function index()
    {
        $language = OW::getLanguage();
        $languageService = BOL_LanguageService::getInstance();
        $langDao = BOL_LanguageDao::getInstance();
        $enLanguage = $langDao->findByTag('en');
        if(!isset($enLanguage) || $enLanguage->getStatus()!='active')
        {
            throw new Redirect404Exception();
        }
        $faLanguage = $langDao->findByTag('fa-IR');
        if(!isset($faLanguage) || $faLanguage->getStatus()!='active')
        {
            throw new Redirect404Exception();
        }
        $form = new Form('select_language');
        $languageTag = new RadioField('languages');
        $languageTag->setRequired();
        $languageTag->addOptions(array($faLanguage->getId() => 'فا', $enLanguage->getId() => 'en'));
        $languageTag->setLabel($language->text('frmmultilingualsupport', 'select_language'));
        $form->addElement($languageTag);

        $submit = new Submit('save');
        $submit->setValue($language->text('frmmultilingualsupport', 'save'));
        $form->addElement($submit);


        $this->addForm($form);
        if ( OW::getRequest()->isPost() ) {
            if ($form->isValid($_POST)) {
                $data = $form->getValues();
                $language = $languageService->findById((int)$data['languages']);
                $url = OW::getRequest()->buildUrlQueryString(null, array( "language_id"=>$language->id ) );
                $this->redirect($url);
            }
        }

        if($languageService->getCurrent()->getTag()=='fa-IR'){
            $languageTag->setValue($faLanguage->getId());
        }else if($languageService->getCurrent()->getTag()=='en'){
            $languageTag->setValue($enLanguage->getId());
        }
    }

}


