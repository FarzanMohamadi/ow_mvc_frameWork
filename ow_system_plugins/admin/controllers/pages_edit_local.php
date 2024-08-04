<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.controller
 * @since 1.0
 */
class ADMIN_CTRL_PagesEditLocal extends ADMIN_CTRL_Abstract
{

    public function __construct()
    {
        parent::__construct();

        $this->setPageHeading(OW::getLanguage()->text('admin', 'pages_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_gear_wheel');
        OW::getDocument()->getMasterPage()->getMenu(OW_Navigation::ADMIN_PAGES)->setItemActive('sidebar_menu_item_pages_manage');
        $this->assign('homeUrl', OW::getRouter()->getBaseUrl());
    }

    public function index( $params )
    {
        $id = (int) $params['id'];

        $this->assign('id', $id);

        $menu = BOL_NavigationService::getInstance()->findMenuItemById($id);

        if ( $menu === null )
        {
            throw new Redirect404Exception();
        }

        $navigationService = BOL_NavigationService::getInstance();

        $document = $navigationService->findDocumentByKey($menu->getDocumentKey());

        if ( $document === null )
        {
            $document = new BOL_Document();
            $document->setKey($menu->getDocumentKey());
            $document->setIsStatic(true);
        }

        $service = BOL_NavigationService::getInstance();

        $form = new EditLocalPageForm('edit-form', $menu);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $data = $form->getValues();
            $eventForEnglishFieldSupport = new OW_Event('frmmultilingualsupport.store.multilingual.data', array('entityId' => $id,'entityType'=>'page'));
            OW::getEventManager()->trigger($eventForEnglishFieldSupport);
//--
            $visibleFor = 0;
            $arr = !empty($data['visible-for']) ? $data['visible-for'] : array();
            foreach ( $arr as $val )
            {
                $visibleFor += $val;
            }

            $service->saveMenuItem(
                $menu->setVisibleFor($visibleFor)
            );

            $uri = str_replace(UTIL_String::removeFirstAndLastSlashes(OW::getRouter()->getBaseUrl()), '', UTIL_String::removeFirstAndLastSlashes($data['url']));
            $document->setUri(UTIL_String::removeFirstAndLastSlashes($uri));

            $navigationService->saveDocument($document);

            $languageService = BOL_LanguageService::getInstance();

            $plugin = OW::getPluginManager()->getPlugin('base');

//- name
            $langKey = $languageService->findKey(
                    $plugin->getKey(), $menu->getKey()
            );
            if ( !empty($langKey) )
            {
                $langValue = $languageService->findValue($languageService->getCurrent()->getId(), $langKey->getId());

                if ( $langValue === null )
                {
                    $langValue = new BOL_LanguageValue();
                    $langValue->setKeyId($langKey->getId());
                    $langValue->setLanguageId($languageService->getCurrent()->getId());
                }

                $languageService->saveValue(
                    $langValue->setValue($data['name'])
                );
            }
//- title

            $langKey = $languageService->findKey(
                    $plugin->getKey(), 'local_page_title_' . $menu->getKey()
            );


            if ( !empty($langKey) )
            {
                $langValue = $languageService->findValue(
                        $languageService->getCurrent()->getId(), $langKey->getId()
                );

                if ( $langValue === null )
                {
                    $langValue = new BOL_LanguageValue();
                    $langValue->setKeyId($langKey->getId());
                    $langValue->setLanguageId($languageService->getCurrent()->getId());
                }

                $languageService->saveValue(
                    $langValue->setValue($data['title'])
                );
            }
//- meta tags

            $langKey = $languageService->findKey($plugin->getKey(), 'local_page_meta_desc_' . $menu->getKey());

            if ( empty($langKey) )
            {
                $langKey = new BOL_LanguageKey();
                $langKey->setKey('local_page_meta_desc_' . $menu->getKey());
                $langKey->setPrefixId($languageService->findPrefixId($plugin->getKey()));

                $languageService->saveKey($langKey);
            }


            $langValue = $languageService->findValue($languageService->getCurrent()->getId(), $langKey->getId());

            if ( $langValue === null )
            {
                $langValue = new BOL_LanguageValue();
                $langValue->setKeyId($langKey->getId());
                $langValue->setLanguageId($languageService->getCurrent()->getId());
            }

            $languageService->saveValue($langValue->setValue($data['meta_desc']));
/*----------------------------------------------*/

            $langKey = $languageService->findKey($plugin->getKey(), 'local_page_meta_keywords_' . $menu->getKey());

            if ( empty($langKey) )
            {
                $langKey = new BOL_LanguageKey();
                $langKey->setKey('local_page_meta_keywords_' . $menu->getKey());
                $langKey->setPrefixId($languageService->findPrefixId($plugin->getKey()));

                $languageService->saveKey($langKey);
            }


            $langValue = $languageService->findValue($languageService->getCurrent()->getId(), $langKey->getId());

            if ( $langValue === null )
            {
                $langValue = new BOL_LanguageValue();
                $langValue->setKeyId($langKey->getId());
                $langValue->setLanguageId($languageService->getCurrent()->getId());
            }

            $languageService->saveValue($langValue->setValue($data['meta_keywords']));

//- content

            $langKey = $languageService->findKey(
                    $plugin->getKey(), 'local_page_content_' . $menu->getKey()
            );

            if ( !empty($langKey) )
            {
                $langValue = $languageService->findValue($languageService->getCurrent()->getId(), $langKey->getId());

                if ( $langValue === null )
                {
                    $langValue = new BOL_LanguageValue();
                    $langValue->setKeyId($langKey->getId());
                    $langValue->setLanguageId($languageService->getCurrent()->getId());
                }
                if(isset($_POST['content'])) {
                        $languageService->saveValue(
                            $langValue->setValue($_POST['content']));


                }
                else
                    $languageService->saveValue(
                        $langValue->setValue($data['content'])
                );
            }

//~
            $languageService->generateCache($languageService->getCurrent()->getId());

            $adminPlugin = OW::getPluginManager()->getPlugin('admin');

            OW::getFeedback()->info(OW::getLanguage()->text($adminPlugin->getKey(), 'updated_msg'));

            $this->redirect();

//--
        }

        $this->addForm($form, $menu);
        $code='';
        $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'receiverId'=>$id,'isPermanent'=>true,'activityType'=>'delete_page')));
        if(isset($frmSecuritymanagerEvent->getData()['code'])){
            $code = $frmSecuritymanagerEvent->getData()['code'];
        }
        $this->assign('code',$code);
    }

    public function delete( $params )
    {
        $id = 0;

        if ( empty($params['id'])
            || ( $id = (int) $params['id'] ) <= 0
        )
        {
            exit();
        }

        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            $code =$params['code'];
            if(!isset($code)){
                throw new Redirect404Exception();
            }
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => OW::getUser()->getId(), 'code'=>$code,'activityType'=>'delete_page')));
        }

        $menu = BOL_NavigationService::getInstance()->findMenuItemById($id);

        $navigationService = BOL_NavigationService::getInstance();

        $document = $navigationService->findDocumentByKey($menu->getDocumentKey());

        $navigationService->deleteDocument($document);

        $languageService = BOL_LanguageService::getInstance();

        $navigationService->deleteMenuItem($menu);

        $langKey = $languageService->findKey($menu->getPrefix(), $menu->getKey());
        $languageService->deleteKey($langKey->getId());

        $langKey = $languageService->findKey('base', 'local_page_meta_tags_' . $document->getKey());
        if ( $langKey !== null )
        {
            $languageService->deleteKey($langKey->getId());
        }

        $langKey = $languageService->findKey('base', 'local_page_title_' . $document->getKey());
        if ( $langKey !== null )
        {
            $languageService->deleteKey($langKey->getId());
        }

        $langKey = $languageService->findKey('base', 'local_page_content_' . $document->getKey());
        if ( $langKey !== null )
        {
            $languageService->deleteKey($langKey->getId());
        }

        $this->redirect(OW::getRouter()->urlForRoute('admin_pages_main'));
    }
}

class EditLocalPageForm extends Form
{

    public function __construct( $name, BOL_MenuItem $menu )
    {
        parent::__construct($name);

        $navigationService = BOL_NavigationService::getInstance();

        $document = $navigationService->findDocumentByKey($menu->getDocumentKey());

        if ( $document === null )
        {
            $document = new BOL_Document();
            $document->setKey($menu->getDocumentKey());
        }

        $language = OW_Language::getInstance();
        $languageService = BOL_LanguageService::getInstance();
        $currentLanguageId = $languageService->getCurrent()->getId();

        $plugin = OW::getPluginManager()->getPlugin('base');
        $adminPlugin = OW::getPluginManager()->getPlugin('admin');

        $nameTextField = new TextField('name');

        $langValueDto = $languageService->getValue($currentLanguageId, $plugin->getKey(), $menu->getKey());
        $langValue = $langValueDto === null ? '' : $language->text($plugin->getKey(), $menu->getKey());
        $this->addElement(
                $nameTextField->setValue($langValue)
                ->setLabel(OW::getLanguage()->text('admin', 'pages_edit_local_menu_name'))
                ->setRequired()
        );

        $titleTextField = new TextField('title');

        $langValueDto = $languageService->getValue($currentLanguageId, $plugin->getKey(), 'local_page_title_' . $menu->getKey());
        $langValue = $langValueDto === null ? '' : $language->text($plugin->getKey(), 'local_page_title_' . $menu->getKey());
        $this->addElement(
                $titleTextField->setValue($langValue)
                ->setLabel(OW::getLanguage()->text('admin', 'pages_edit_local_page_title'))
                ->setRequired(true)
        );

        $urlTextField = new TextField('url');
        $urlTextField->addValidator(new LocalPageUniqueValidator($document->getUri()));

        $this->addElement(
                $urlTextField->setValue($document->getUri())
                ->setLabel(OW::getLanguage()->text('admin', 'pages_edit_local_page_url'))
                ->setRequired(true)
        );

        $visibleForCheckboxGroup = new CheckboxGroup('visible-for');

        $visibleFor = $menu->getVisibleFor();

        $options = array(
            '1' => OW::getLanguage()->text('admin', 'pages_edit_visible_for_guests'),
            '2' => OW::getLanguage()->text('admin', 'pages_edit_visible_for_members')
        );

        $values = array();

        foreach ( $options as $value => $option )
        {
            if ( !($value & $visibleFor) )
                continue;

            $values[] = $value;
        }

        $this->addElement(
                $visibleForCheckboxGroup->setOptions($options)
                ->setValue($values)
                ->setLabel(OW::getLanguage()->text('admin', 'pages_edit_local_visible_for'))
        );

        $metaTagsTextarea = new Textarea('meta_keywords');

        $langValueDto = $languageService->getValue($currentLanguageId, $plugin->getKey(), 'local_page_meta_keywords_' . $menu->getKey());
        $langValue = $langValueDto === null ? '' : $language->text($plugin->getKey(), 'local_page_meta_keywords_' . $menu->getKey());
        $this->addElement(
            $metaTagsTextarea->setLabel(OW::getLanguage()->text("base", "pages_page_meta_keywords_label"))
                ->setValue($langValue)
                ->setDescription(OW::getLanguage()->text('base', 'pages_page_meta_keywords_desc'))
        );

        $metaTagsTextarea = new Textarea('meta_desc');

        $langValueDto = $languageService->getValue($currentLanguageId, $plugin->getKey(), 'local_page_meta_desc_' . $menu->getKey());
        $langValue = $langValueDto === null ? '' : $language->text($plugin->getKey(), 'local_page_meta_desc_' . $menu->getKey());
        $this->addElement(
            $metaTagsTextarea->setLabel(OW::getLanguage()->text("base", "pages_page_meta_desc_label"))
                ->setValue($langValue)
                ->setDescription(OW::getLanguage()->text('base', 'pages_page_meta_desc_desc'))
        );

        $contentTextArea = new Textarea('content');

        $contentTextArea->setDescription(
            OW::getLanguage()->text('admin', 'pages_page_field_content_desc', array(
                'src' => OW::getThemeManager()->getCurrentTheme()->getStaticImagesUrl() . 'question.png',
                'url' => '#'
                )
            )
        );

        $langValueDto = $languageService->getValue($currentLanguageId, $plugin->getKey(), 'local_page_content_' . $menu->getKey());
        $langValue = $langValueDto === null ? '' : $language->text($plugin->getKey(), 'local_page_content_' . $menu->getKey());
        $this->addElement(
                $contentTextArea->setLabel(OW::getLanguage()->text('admin', 'pages_edit_local_page_content'))
                ->setValue($langValue)
                ->setId('content')
        );


        $saveSubmit = new Submit('save');

        $this->addElement(
            $saveSubmit->setValue($language->text($adminPlugin->getKey(), 'save_btn_label'))
        );
    }
}

class LocalPageUniqueValidator extends OW_Validator
{
    private $uri;

    public function __construct( $uri )
    {
        $this->uri = $uri;
        $this->setErrorMessage(OW::getLanguage()->text('base', 'unique_local_page_error'));
    }

    public function isValid( $value )
    {
        $value = str_replace(UTIL_String::removeFirstAndLastSlashes(OW::getRouter()->getBaseUrl()), '', UTIL_String::removeFirstAndLastSlashes($value));

        if ( !trim($value) )
        {
            return false;
        }

        return ( $this->uri == $value || BOL_NavigationService::getInstance()->isDocumentUriUnique($value) );
    }
}