<?php
/**
 * User preference
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.controllers
 * @since 1.0
 */
class BASE_CTRL_Preference extends OW_ActionController
{
    private $preferenceService;
    private $userService;

    public function __construct()
    {
        parent::__construct();

        $this->preferenceService = BOL_PreferenceService::getInstance();
        $this->userService = BOL_UserService::getInstance();

        $contentMenu = new BASE_CMP_PreferenceContentMenu();
        $contentMenu->setItemActive('preference');

        $this->addComponent('contentMenu', $contentMenu);
    }

    public function index( $params )
    {
        $userId = OW::getUser()->getId();

        if ( OW::getRequest()->isAjax() )
        {
            exit;
        }
        
        if ( !OW::getUser()->isAuthenticated() || $userId === null )
        {
            throw new AuthenticateException();
        }

        $language = OW::getLanguage();

        $this->setPageHeading($language->text('base', 'preference_index'));
        $this->setPageHeadingIconClass('ow_ic_gear_wheel');

        // -- Preference form --
        
        $preferenceForm = new Form('preferenceForm');
        $preferenceForm->setId('preferenceForm');

        $preferenceSubmit = new Submit('preferenceSubmit');
        $preferenceSubmit->addAttribute('class', 'ow_ic_save');

        $preferenceSubmit->setValue($language->text('base', 'preference_submit_button'));
        
        $preferenceForm->addElement($preferenceSubmit);

        // --

        $sectionList = BOL_PreferenceService::getInstance()->findAllSections();
        $preferenceList = BOL_PreferenceService::getInstance()->findAllPreference();

        $preferenceNameList = array();
        foreach( $preferenceList as $preference )
        {
            $preferenceNameList[$preference->key] = $preference->key;
        }

        $preferenceValuesList = BOL_PreferenceService::getInstance()->getPreferenceValueListByUserIdList($preferenceNameList, array($userId));

        $formElementEvent = new BASE_CLASS_EventCollector( BOL_PreferenceService::PREFERENCE_ADD_FORM_ELEMENT_EVENT, array( 'values' => $preferenceValuesList[$userId] ) );
        OW::getEventManager()->trigger($formElementEvent);
        $data = $formElementEvent->getData();
        
        $formElements = empty($data) ? array() : call_user_func_array('array_merge', $data);

        $formElementList = array();

        foreach( $formElements as $formElement )
        {
            /* @var $formElement FormElement */

            $formElementList[$formElement->getName()] = $formElement;
        }
        
        $resultList = array();

        foreach( $sectionList as $section )
        {
            foreach( $preferenceList as $preference )
            {
                if( $preference->sectionName === $section->name && !empty( $formElementList[$preference->key] ) )
                {
                    $resultList[$section->name][$preference->key] = $preference->key;

                    $element = $formElementList[$preference->key];
                    $preferenceForm->addElement($element);
                }
            }
        }

        if ( OW::getRequest()->isPost() )
        {
            if( $preferenceForm->isValid($_POST) )
            {
                $values = $preferenceForm->getValues();
                $restul = BOL_PreferenceService::getInstance()->savePreferenceValues($values, $userId);

                if ( $restul )
                {
                    OW::getFeedback()->info($language->text('base', 'preference_preference_data_was_saved'));
                }
                else
                {
                    OW::getFeedback()->warning($language->text('base', 'preference_preference_data_not_changed'));
                }
                
                $this->redirect();
            }
        }

        $this->addForm($preferenceForm);

        $data = array();
        $sectionLabelEvent = new BASE_CLASS_EventCollector( BOL_PreferenceService::PREFERENCE_SECTION_LABEL_EVENT );
        OW::getEventManager()->trigger($sectionLabelEvent);
        $data = $sectionLabelEvent->getData();
        
        $sectionLabels = empty($data) ? array() : call_user_func_array('array_merge', $data);

        $this->assign('preferenceList', $resultList);
        $this->assign('sectionLabels', $sectionLabels);

        $this->setDocumentKey("profile_preferences");
    }


}