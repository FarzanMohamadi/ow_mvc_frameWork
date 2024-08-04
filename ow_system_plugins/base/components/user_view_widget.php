<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_UserViewWidget extends BASE_CLASS_Widget
{
    const USER_VIEW_PRESENTATION_TABS = 'tabs';

    const USER_VIEW_PRESENTATION_TABLE = 'table';

    /**
     * @param BASE_CLASS_WidgetParameter $params
     * @return \BASE_CMP_UserViewWidget
     */
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();

        $userId = $params->additionalParamList['entityId'];

        // $ownerMode = $userId == $viewerId;
        $adminMode = OW::getUser()->isAuthorized('base','edit_user_profile');
        // if($ownerMode){
        //     $url =  OW::getRouter()->urlForRoute('base_edit');
        //     $changePassword = new BASE_CMP_ChangePassword($url);
        //     $this->addComponent("changePassword", $changePassword);
        // }
        $user = BOL_UserService::getInstance()->findUserById($userId);
        $accountType = $user->accountType;
        $questionService = BOL_QuestionService::getInstance();

        $questions = self::getUserViewQuestions($userId, $adminMode);
        
        if ( empty($questions['questions']) && $adminMode )
        {
            $list = BOL_QuestionService::getInstance()->getRequiredQuestionsForNewAccountType();
            
            $questions = self::getUserViewQuestions($userId, $adminMode, array_keys($list) );
        }

        $sectionsHtml = $questions['sections'];

        $sections = array_keys($sectionsHtml);

        $template = OW::getPluginManager()->getPlugin('base')->getViewDir() . 'components' . DS . 'user_view_widget_table.html';

        $userViewPresntation = OW::getConfig()->getValue('base', 'user_view_presentation');

        if ( $userViewPresntation === self::USER_VIEW_PRESENTATION_TABS )
        {
            $template = OW::getPluginManager()->getPlugin('base')->getViewDir() . 'components' . DS . 'user_view_widget_tabs.html';

            OW::getDocument()->addOnloadScript(" view = new UserViewWidget(); ");

            $jsDir = OW::getPluginManager()->getPlugin("base")->getStaticJsUrl();
            OW::getDocument()->addScript($jsDir . "user_view_widget.js");

            $this->addMenu($sections);
        }

        $script = ' $(".profile_hidden_field").hover(function(){OW.showTip($(this), {timeout:150, show: "'.OW::getLanguage()->text('base', 'base_invisible_profile_field_tooltip').'"})}, function(){OW.hideTip($(this))});';

        OW::getDocument()->addOnloadScript($script);

        $cmp = OW::getClassInstance("BASE_CMP_ProfileActionToolbar", $userId);
        $this->addComponent('profileActionToolbar', $cmp);

        $this->setTemplate($template);


        if ( !isset($sections[0]) )
        {
            $sections[0] = 0;
        }

        $this->assign('firstSection', $sections[0]);
        $this->assign('sectionsHtml', $sectionsHtml);


        $additionalWidgetEvent = OW::getEventManager()->trigger(new OW_Event('on.before.profile.view.widget.render', array( 'userId' => (int) $userId)));
        if(isset($additionalWidgetEvent->getData()['cmp'])){
            $additionalWidget = $additionalWidgetEvent->getData()['cmp'];
            $this->assign('additionalWidget', $additionalWidget );
        }
        $this->assign('avatarUrl', BOL_AvatarService::getInstance()->getAvatarUrl($userId) );
        $this->assign('displayName', BOL_UserService::getInstance()->getDisplayName($userId) );
        if(OW::getConfig()->getValue('base', 'mandatory_user_approve')){
            $isApproved = BOL_UserService::getInstance()->isApproved($userId);
            $this->assign('isApproved', $isApproved);
            $hasAccessToApproveUser = BOL_UserService::getInstance()->hasAccessToApproveUser($userId);
            if (!$isApproved && $hasAccessToApproveUser['valid']){
                $moderator_note = BOL_UserApproveDao::getInstance()->getRequestedNotes($userId);
                if (!empty($moderator_note)){
                    $note = $moderator_note['admin_message'];
                    $note = str_replace("\n", '<br />', $note);
                    $this->assign('moderator_note', $note);
                }
            }
        }else{
            $this->assign('isApproved', 'approveIsNotActive');
        }

        //$this->assign('questionLabelList', $questionLabelList);
        $this->assign('userId', $userId);
    }

    public static function getStandardSettingValueList()
    {
        $language = OW::getLanguage();
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_TITLE => $language->text('base', 'view_index'),
            self::SETTING_FREEZE => true
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }

    public function addMenu( $sections )
    {
        $menuItems = array();

        foreach ( $sections as $key => $section )
        {
            $item = new BASE_MenuItem();

            $item->setLabel(BOL_QuestionService::getInstance()->getSectionLang($section))
                ->setKey($section)
                ->setUrl('javascript://')
                ->setPrefix('menu')
                ->setOrder($key);

            if ( $key == 0 )
            {
                $item->setActive(true);
            }

            $menuItems[] = $item;
            $script = '$(\'li.menu_' . $section . '\').click(function(){view.showSection(\'' . $section . '\');});';
            OW::getDocument()->addOnloadScript($script);
        }

        $this->addComponent('menu', new BASE_CMP_ContentMenu($menuItems));
    }

    public static function getUserViewQuestions( $userId, $adminMode = false, $questionNames = array(), $sectionNames = null )
    {
        $questions = BOL_UserService::getInstance()->getUserViewQuestions($userId, $adminMode, $questionNames, $sectionNames);

        if ( !empty($questions['data'][$userId]) )
        {
            $data = array();
            foreach ( $questions['data'][$userId] as $key => $value )
            {
                if ( is_array($value) )
                {
                    $questions['data'][$userId][$key] = implode(', ', $value);
                }
            }
        }

        $sectionList = array();

        $userViewPresntation = OW::getConfig()->getValue('base', 'user_view_presentation');

        if ( !empty($questions['questions']) )
        {
            $sections = array_keys($questions['questions']);
            $count = 0;

            $isHidden = false;

            foreach ( $sections as $section )
            {
                if ( $userViewPresntation === self::USER_VIEW_PRESENTATION_TABS && $count != 0 )
                {
                    $isHidden = true;
                }

                $sectionQuestions = !empty($questions['questions'][$section]) ? $questions['questions'][$section] : array();
                $data = !empty($questions['data'][$userId]) ? $questions['data'][$userId] : array();
                $component = OW::getClassInstance( 'BASE_CMP_UserViewSection', $section, $sectionQuestions, $data, $questions['labels'], $userViewPresntation, $isHidden, array('userId' => $userId) );

                if ( !empty($component) )
                {
                    $sectionList[$section] = $component->render();
                }
                $count++;
            }
        }

        $questions['sections'] = $sectionList;

        return $questions;
    }
}
