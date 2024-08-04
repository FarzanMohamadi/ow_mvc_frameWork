<?php
/**
 * FRM Suggest Friend widget
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @since 1.0
 */
class FRMQUESTIONROLES_CMP_UsersDisapprovedWidget extends BASE_CLASS_Widget
{

    /**
     * FRMQUESTIONROLES_CMP_UsersDisapprovedWidget constructor.
     * @param BASE_CLASS_WidgetParameter $params
     */
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();
        $this->assignList($params);
    }

    private function assignList($params)
    {
        $service = FRMQUESTIONROLES_BOL_Service::getInstance();
        $usersInfo = $service->getDisApprovedUsers();
        if ($usersInfo['valid'] == false) {
            $this->assign('empty_list', true);
            OW::getDocument()->addStyleDeclaration('
                .dashboard-FRMQUESTIONROLES_CMP_UsersDisapprovedWidget {
                    display: none;
                }
		    ');
        } else {
            if (sizeof($usersInfo['users']) == 0) {
                $this->assign('empty_list', true);
            } else {
                $hasLoadMore = $usersInfo['hasLoadMore'];
                if ($hasLoadMore) {
                    $toolbars[] = array(
                        'href' => OW::getRouter()->urlForRoute('frmquestionroles.user_disapproved'),
                        'label' => OW::getLanguage()->text('base', 'view_all')
                    );
                    $this->assign('toolbars', $toolbars);
                }
                $this->addComponent('userList', new BASE_CMP_AvatarUserList($usersInfo['users']));
            }
        }
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_TITLE => OW_Language::getInstance()->text('frmquestionroles', 'users_disapproved'),
            self::SETTING_ICON => self::ICON_USER
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }
}