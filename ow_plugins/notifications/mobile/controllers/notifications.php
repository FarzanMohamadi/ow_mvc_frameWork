<?php
/**
 * Notifications
 *
 * @package ow_plugins.notifications.controllers
 * @since 1.0
 */
class NOTIFICATIONS_MCTRL_Notifications extends OW_MobileActionController
{
    /**
     *
     * @var NOTIFICATIONS_BOL_Service
     */
    private $service;
    private $userId;

    public function __construct()
    {
        parent::__construct();

        $this->service = NOTIFICATIONS_BOL_Service::getInstance();
        $this->userId = OW::getUser()->getId();
    }

    public function settings()
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }
        if(!OW_PluginManager::getInstance()->isPluginActive('frmprofilemanagement')) {
            throw new Redirect404Exception();
        }

        if(isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']!=null)
        {
            $this->assign('backUrl',$_SERVER['HTTP_REFERER']);
        }
        $contentMenu = new FRMPROFILEMANAGEMENT_MCMP_PreferenceContentMenu();
        $contentMenu->setItemActive('email_notifications');
        $this->addComponent('contentMenu', $contentMenu);

        OW::getDocument()->setHeading(OW::getLanguage()->text('notifications', 'setup_page_heading'));
        OW::getDocument()->setHeadingIconClass('ow_ic_mail');
        OW::getDocument()->setTitle(OW::getLanguage()->text('notifications', 'setup_page_title'));

        $actions = $this->service->collectActionList();
        $settings = $this->service->findRuleList($this->userId);

        $form = new NOTIFICATIONS_SettingForm();
        $this->addForm($form);

        $processActions = array();

        foreach ( $actions as $action )
        {
            $field = new CheckboxField($action['action']);
            $field->setValue(!empty($action['selected']));

            if ( isset($settings[$action['action']]) )
            {
                $field->setValue((bool) $settings[$action['action']]->checked);
            }

            $form->addElement($field);

            $processActions[] = $action['action'];
        }

        if ( OW::getRequest()->isPost() )
        {
            $result = $form->process($_POST, $processActions, $settings);
            if ( $result )
            {
                OW::getFeedback()->info(OW::getLanguage()->text('notifications', 'settings_changed'));
            }
            else
            {
                OW::getFeedback()->warning(OW::getLanguage()->text('notifications', 'settings_not_changed'));
            }

            $this->redirect();
        }

        $tplActions = array();

        foreach ( $actions as $action )
        {
            if ( empty($tplActions[$action['section']]) )
            {
                $tplActions[$action['section']] = array(
                    'label' => $action['sectionLabel'],
                    'icon' => empty($action['sectionIcon']) ? '' : $action['sectionIcon'],
                    'actions' => array()
                );
            }

            $tplActions[$action['section']]['actions'][$action['action']] = $action;
        }



        $this->assign('actions', $tplActions);
        OW::getEventManager()->trigger(new OW_Event('frm.on.before.profile.pages.view.render', array('pageType' => "preferences")));
    }

    public function unsubscribe( $params )
    {
        if ( isset($_GET['confirm-result']) && $_GET['confirm-result'] === "0" )
        {
            throw new RedirectException(OW_URL_HOME);
        }
        
        $code = $params['code'];
        $userId = $this->service->findUserIdByUnsubscribeCode($code);
        $lang = OW::getLanguage();

        if ( empty($userId) )
        {
            throw new RedirectAlertPageException($lang->text('notifications', 'unsubscribe_code_expired'));
        }

        if ( empty($_GET['confirm-result']) )
        {
            throw new RedirectConfirmPageException($lang->text('notifications', 'unsubscribe_confirm_msg'));
        }

        NOTIFICATIONS_BOL_Service::getInstance()->setSchedule($userId, NOTIFICATIONS_BOL_Service::SCHEDULE_NEVER);

        throw new RedirectAlertPageException($lang->text('notifications', 'unsubscribe_completed'));
    }

    public function test()
    {
        require_once dirname(dirname(__FILE__)) . DS . 'cron.php';

        $cron = new NOTIFICATIONS_Cron();
        //$cron->run();
        $cron->deleteExpired();
        exit;
    }

    public function apiUnsubscribe( $params )
    {
        if ( empty($params['emails']) || !is_array($params['emails']) )
        {
            throw new InvalidArgumentException('Invalid email list');
        }

        foreach ( $params['emails'] as $email )
        {
            $user = BOL_UserService::getInstance()->findByEmail($email);

            if ( $user === null )
            {
                throw new LogicException('User with email ' . $email . ' not found');
            }

            $userId = $user->getId();

            $activeActions = $this->service->collectActionList();
            $rules = $this->service->findRuleList($userId);

            $action = empty($params['action']) ? null : $params['action'];

            foreach ( $activeActions as $actionInfo )
            {
                if ( $action !== null && $actionInfo['action'] != $action )
                {
                    continue;
                }

                if ( empty($rules[$actionInfo['action']]) )
                {
                    $rule = new NOTIFICATIONS_BOL_Rule();
                    $rule->action = $actionInfo['action'];
                    $rule->userId = $userId;
                }
                else
                {
                    $rule = $rules[$actionInfo['action']];
                }

                $rule->checked = false;

                $this->service->saveRule($rule);
            }
        }
    }

    /***
     * List of all notifications of user
     *
     * @throws Redirect404Exception
     */
    public function notifications(){
        if(FRMSecurityProvider::checkPluginActive('frmmainpage', true) && !FRMMAINPAGE_BOL_Service::getInstance()->isDisabled('notifications')) {
            $this->redirect(OW::getRouter()->urlForRoute('frmmainpage.notifications'));
        }
        $cssUrl = OW::getPluginManager()->getPlugin('notifications')->getStaticCssUrl() . "notification.css";
        OW::getDocument()->addStyleSheet($cssUrl);
        $cmp = new BASE_MCMP_ConsoleNotificationsPage();
        $this->addComponent('cmp', $cmp);
    }
}

class NOTIFICATIONS_SettingForm extends Form
{

    public function __construct()
    {
        parent::__construct('notificationSettingForm');

        $language = OW::getLanguage();

        $field = new RadioField('schedule');

        $field->addOption(NOTIFICATIONS_BOL_Service::SCHEDULE_IMMEDIATELY, $language->text('notifications', 'schedule_immediately'));
        $field->addOption(NOTIFICATIONS_BOL_Service::SCHEDULE_AUTO, $language->text('notifications', 'schedule_automatic'));
        $field->addOption(NOTIFICATIONS_BOL_Service::SCHEDULE_NEVER, $language->text('notifications', 'schedule_never'));

        $schedule = NOTIFICATIONS_BOL_Service::getInstance()->getSchedule(OW::getUser()->getId());
        $field->setValue($schedule);
        $this->addElement($field);

        $btn = new Submit('save');
        $btn->setValue($language->text('notifications', 'save_setting_btn_label'));

        $this->addElement($btn);
    }

    public function process( $data, $actions, $dtoList )
    {
        $userId = OW::getUser()->getId();
        $result = 0;
        $service = NOTIFICATIONS_BOL_Service::getInstance();

        if ( !empty($data['schedule']) )
        {
            $result += (int) $service->setSchedule($userId, $data['schedule']);

            unset($data['schedule']);
        }

        foreach ( $actions as $action )
        {
            /* @var $dto NOTIFICATIONS_BOL_Rule */
            if ( empty($dtoList[$action]) )
            {
                $dto = new NOTIFICATIONS_BOL_Rule();
                $dto->userId = $userId;
                $dto->action = $action;
            }
            else
            {
                $dto = $dtoList[$action];
            }

            $checked = (int) !empty($data[$action]);

            if ( !empty($dto->id) && $dto->checked == $checked )
            {
                continue;
            }

            $dto->checked = $checked;
            $result++;

            $service->saveRule($dto);
        }

        return $result;
    }
}

