<?php
/**
 * frmslideshow
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmslideshow
 * @since 1.0
 */

class FRMSLIDESHOW_MCMP_ForumWidget extends BASE_CLASS_Widget
{
    /**
     * @param BASE_CLASS_WidgetParameter $paramObj
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();

        $forumService = FORUM_BOL_ForumService::getInstance();
        $userId = OW::getUser()->getId();
        $sectionGroupList = $forumService->getSectionGroupList($userId, null);
        $authors = $forumService->getSectionGroupAuthorList($sectionGroupList);

        // assign view variables
        $this->assign('sectionGroupList', $sectionGroupList);
        $this->assign('displayNames', BOL_UserService::getInstance()->getDisplayNamesForList($authors));

        $this->setTemplate(OW::getPluginManager()->getPlugin('frmslideshow')->getMobileCmpViewDir() . 'forum_widget.html');

        // import static files
        $jsDir = OW::getPluginManager()->getPlugin("frmslideshow")->getStaticJsUrl();
        OW::getDocument()->addScript($jsDir . "slick.js");
        OW::getDocument()->addScript($jsDir . "frmslideshow.js");

        $cssFile = OW::getPluginManager()->getPlugin('frmslideshow')->getStaticCssUrl() . 'frmslideshow.css';
        OW::getDocument()->addStyleSheet($cssFile);
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_TITLE => OW_Language::getInstance()->text('frmslideshow', 'forum_widget_title'),
            self::SETTING_ICON => self::ICON_USER
        );
    }
}
