<?php
/**
 *
 * @package ow_plugins.newsfeed.controllers
 * @since 1.0
 */
class NEWSFEED_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    /**
     * Default action
     */
    public function index()
    {
        $language = OW::getLanguage();

        $this->setPageHeading($language->text('newsfeed', 'admin_page_heading'));
        $this->setPageTitle($language->text('newsfeed', 'admin_page_title'));
        $this->setPageHeadingIconClass('ow_ic_comment');

        $configs = OW::getConfig()->getValues('newsfeed');
        $this->assign('configs', $configs);

        $form = new NEWSFEED_ConfigSaveForm($configs);

        $this->addForm($form);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            if ( $form->process($_POST) )
            {
                try {
                    $disableNewsfeedFromUserProfile = OW::getConfig()->getValue('newsfeed', 'disableNewsfeedFromUserProfile');
                    if (isset($disableNewsfeedFromUserProfile) && $disableNewsfeedFromUserProfile == "on") {
                        BOL_ComponentAdminService::getInstance()->deleteWidget('NEWSFEED_CMP_UserFeedWidget');
                    } else {
                        $widget = BOL_ComponentAdminService::getInstance()->addWidget('NEWSFEED_CMP_UserFeedWidget', false);
                        $widgetPlace = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentService::PLACE_PROFILE);
                        BOL_ComponentAdminService::getInstance()->addWidgetToPosition($widgetPlace, BOL_ComponentService::SECTION_RIGHT, 0);
                    }
                }catch (Exception $e)
                {

                }
                OW::getFeedback()->info($language->text('newsfeed', 'settings_updated'));
                $this->redirect(OW::getRouter()->urlForRoute('newsfeed_admin_settings'));
            }
        }

        $this->addComponent('menu', $this->getMenu());
    }

    public function customization()
    {
        $language = OW::getLanguage();

        $this->setPageHeading($language->text('newsfeed', 'admin_page_heading'));
        $this->setPageTitle($language->text('newsfeed', 'admin_page_title'));
        $this->setPageHeadingIconClass('ow_ic_comment');

        $types = NEWSFEED_BOL_CustomizationService::getInstance()->getActionTypes();

        $form = new NEWSFEED_CustomizationForm();
        $this->addForm($form);

        $processTypes = array();

        foreach ( $types as $type )
        {
            $field = new CheckboxField($type['activity'].'1');
            $field->setValue($type['active1']);
            $form->addElement($field);

            $field = new CheckboxField($type['activity'].'2');
            $field->setValue($type['active2']);
            $form->addElement($field);

            $field = new CheckboxField($type['activity'].'3');
            $field->setValue($type['active3']);
            $form->addElement($field);

            $processTypes[] = $type['activity'];
        }

        if ( OW::getRequest()->isPost() )
        {
            $result = $form->process($_POST, $processTypes);
            if ( $result )
            {
                OW::getFeedback()->info($language->text('newsfeed', 'customization_changed'));
            }
            else
            {
                OW::getFeedback()->warning($language->text('newsfeed', 'customization_not_changed'));
            }

            $this->redirect();
        }

        $this->assign('types', $types);
        $this->addComponent('menu', $this->getMenu());
    }

    private function getMenu()
    {
        $language = OW::getLanguage();

        $menuItems = array();

        $item = new BASE_MenuItem();
        $item->setLabel($language->text('newsfeed', 'admin_menu_item_settings'));
        $item->setUrl(OW::getRouter()->urlForRoute('newsfeed_admin_settings'));
        $item->setKey('newsfeed_settings');
        $item->setIconClass('ow_ic_gear_wheel ow_dynamic_color_icon');
        $item->setOrder(0);

        $menuItems[] = $item;

        $item = new BASE_MenuItem();
        $item->setLabel($language->text('newsfeed', 'admin_menu_item_customization'));
        $item->setUrl(OW::getRouter()->urlForRoute('newsfeed_admin_customization'));
        $item->setKey('newsfeed_customization');
        $item->setIconClass('ow_ic_files ow_dynamic_color_icon');
        $item->setOrder(1);

        $menuItems[] = $item;

        return new BASE_CMP_ContentMenu($menuItems);
    }
}

/**
 * Save photo configuration form class
 */
class NEWSFEED_ConfigSaveForm extends Form
{
    const COMMENT_COUNT=3;
    /**
     * Class constructor
     *
     */
    public function __construct( $configs )
    {
        parent::__construct('NEWSFEED_ConfigSaveForm');

        $language = OW::getLanguage();

        $field = new CheckboxField('showGroupChatForm');
        $field->setLabel(OW::getLanguage()->text('newsfeed', 'show_otp_form_group_label'));
        $field->setValue(OW::getConfig()->getValue('newsfeed', 'showGroupChatForm'));
        $this->addElement($field);

        $field = new CheckboxField('showDashboardChatForm');
        $field->setLabel(OW::getLanguage()->text('newsfeed', 'show_otp_form_dashboard_label'));
        $field->setValue(OW::getConfig()->getValue('newsfeed', 'showDashboardChatForm'));
        $this->addElement($field);

        $field = new CheckboxField('removeDashboardStatusForm');
        $field->setLabel(OW::getLanguage()->text('newsfeed', 'remove_status_form_dashboard_label'));
        $field->setValue(OW::getConfig()->getValue('newsfeed', 'removeDashboardStatusForm'));
        $this->addElement($field);

        $field = new CheckboxField('showFollowersAndFollowings');
        $field->setLabel(OW::getLanguage()->text('newsfeed', 'show_followers_and_followings'));
        $field->setValue(OW::getConfig()->getValue('newsfeed', 'showFollowersAndFollowings'));
        $this->addElement($field);

        $field = new CheckboxField('addReply');
        $field->setLabel(OW::getLanguage()->text('newsfeed', 'add_reply'));
        $field->setValue(OW::getConfig()->getValue('newsfeed', 'addReply'));
        $this->addElement($field);

        $field = new CheckboxField('disableComments');
        $field->setLabel(OW::getLanguage()->text('newsfeed', 'disable_comment'));
        $field->setValue(OW::getConfig()->getValue('newsfeed', 'disableComments'));
        $this->addElement($field);

        $field = new CheckboxField('disableLikes');
        $field->setLabel(OW::getLanguage()->text('newsfeed', 'disable_like'));
        $field->setValue(OW::getConfig()->getValue('newsfeed', 'disableLikes'));
        $this->addElement($field);

        $field = new CheckboxField('disableNewsfeedFromUserProfile');
        $field->setLabel(OW::getLanguage()->text('newsfeed', 'disable_newsfeed_from_user_profile'));
        $field->setValue(OW::getConfig()->getValue('newsfeed', 'disableNewsfeedFromUserProfile'));
        $pageUrl = OW::getRouter()->urlForRoute('admin_pages_user_profile');
        $field->setDescription(OW::getLanguage()->text('newsfeed','disable_newsfeed_from_user_profile_desktop_Desc',['pageUrl'=>$pageUrl]));
        $this->addElement($field);

        $field = new CheckboxField('allow_comments');
        $field->setLabel($language->text('newsfeed', 'admin_allow_comments_label'));
        $field->setValue($configs['allow_comments']);
        $this->addElement($field);

        $field = new CheckboxField('features_expanded');
        $field->setLabel($language->text('newsfeed', 'admin_features_expanded_label'));
        $field->setValue($configs['features_expanded']);
        $this->addElement($field);

        $field = new CheckboxField('index_status_enabled');
        $field->setLabel($language->text('newsfeed', 'admin_index_status_label'));
        OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_INDEX_STATUS_ENABLED, array('checkBoxField' => $field)));
        $field->setValue($configs['index_status_enabled']);
        $this->addElement($field);

        $field = new CheckboxField('allow_likes');
        $field->setLabel($language->text('newsfeed', 'admin_allow_likes_label'));
        $field->setValue($configs['allow_likes']);
        $this->addElement($field);

        $field = new TextField('comments_count');
        $field->setValue($configs['comments_count']);
        $field->setRequired(true);
        $validator = new IntValidator();
        $field->addValidator($validator);
        $field->setLabel($language->text('newsfeed', 'admin_comments_count_label'));
        $this->addElement($field);

        // submit
        $submit = new Submit('save');
        $submit->setValue($language->text('newsfeed', 'admin_save_btn'));
        $this->addElement($submit);
    }

    /**
     * Updates photo plugin configuration
     *
     * @return boolean
     */
    public function process( $data )
    {
        $config = OW::getConfig();

        $config->saveConfig('newsfeed', 'allow_likes', isset($data['allow_likes']) ? $data['allow_likes'] : null);
        $config->saveConfig('newsfeed', 'allow_comments', isset($data['allow_comments']) ? $data['allow_comments'] : null);
        $config->saveConfig('newsfeed', 'comments_count', isset($data['comments_count']) ? $data['comments_count'] : self::COMMENT_COUNT);
        $config->saveConfig('newsfeed', 'features_expanded', isset($data['features_expanded']) ? $data['features_expanded'] : null);
        $config->saveConfig('newsfeed', 'index_status_enabled', isset($data['index_status_enabled']) ? $data['index_status_enabled'] : null);
        $config->saveConfig('newsfeed', 'showGroupChatForm', isset($data['showGroupChatForm']) ? $data['showGroupChatForm'] : null);
        $config->saveConfig('newsfeed', 'showDashboardChatForm', isset($data['showDashboardChatForm']) ? $data['showDashboardChatForm'] : null);
        $config->saveConfig('newsfeed', 'removeDashboardStatusForm', isset( $data['removeDashboardStatusForm']) ?  $data['removeDashboardStatusForm']: null);
        $config->saveConfig('newsfeed', 'showFollowersAndFollowings', isset( $data['showFollowersAndFollowings']) ?  $data['showFollowersAndFollowings']: null);
        $config->saveConfig('newsfeed', 'disableNewsfeedFromUserProfile', isset($data['disableNewsfeedFromUserProfile']) ?  $data['disableNewsfeedFromUserProfile']: null);
        $config->saveConfig('newsfeed', 'addReply', isset($data['addReply']) ?  $data['addReply']: null);
        $config->saveConfig('newsfeed', 'disableComments', isset($data['disableComments']) ?  $data['disableComments']: null);
        $config->saveConfig('newsfeed', 'disableLikes', isset($data['disableLikes']) ?  $data['disableLikes']: null);
        return true;
    }
}

class NEWSFEED_CustomizationForm extends Form
{

    public function __construct(  )
    {
        parent::__construct('NEWSFEED_CustomizationForm');

        $language = OW::getLanguage();

        $btn = new Submit('save');
        $btn->setValue($language->text('newsfeed', 'save_customization_btn_label'));
        $this->addElement($btn);
    }

    public function process( $data, $types )
    {
        $changed = false;
        $typesToSave = array();

        foreach ( $types as $type ) {
            $val1 = isset($data[$type . '1'])? $data[$type . '1']=='on':false;
            $val2 = isset($data[$type . '2'])? $data[$type . '2']=='on':false;
            $val3 = isset($data[$type . '3'])? $data[$type . '3']=='on':false;
            $typesToSave[$type] = [$val1, $val2, $val3];
            $changed = true;
        }

        $jsonValue = json_encode($typesToSave);
        OW::getConfig()->saveConfig('newsfeed', 'disabled_action_types', $jsonValue);

        return $changed;
    }
}
