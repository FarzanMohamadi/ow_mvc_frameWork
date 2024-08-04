<?php
/**
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_IndexWallWidget extends BASE_CLASS_Widget
{

    /**
     * @return Constructor.
     */
    public function __construct( $paramObj )
    {
        parent::__construct();

        // check if comments are empty and user can't add comments
        if( (int)BOL_CommentService::getInstance()->findCommentCount('base_index_wall', 1) === 0 )
        {
            if( !OW::getUser()->isAuthenticated() )
            {
                $this->setVisible(false);
            }
        }
        
        $params = $paramObj->customParamList;

        $commentParams = new BASE_CommentsParams('base', 'base_index_wall');

        if ( isset($params['comments_count']) )
        {
            $commentParams->setCommentCountOnPage($params['comments_count']);
        }

        $commentParams->setDisplayType(BASE_CommentsParams::DISPLAY_TYPE_TOP_FORM_WITH_PAGING);
        $commentParams->setWrapInBox(false);

        $this->addComponent('comments', new BASE_CMP_Comments($commentParams));
    }

    public static function getSettingList()
    {
        $settingList = array();
        $settingList['comments_count'] = array(
            'presentation' => self::PRESENTATION_SELECT,
            'label' => OW::getLanguage()->text('base', 'cmp_widget_wall_comments_count'),
            'optionList' => array('3' => 3, '5' => 5, '10' => 10, '20' => 20, '50' => 50),
            'value' => 10
        );

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => false,
            self::SETTING_TITLE => OW::getLanguage()->text('base', 'comments_widget_label'),
            self::SETTING_WRAP_IN_BOX => false
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}