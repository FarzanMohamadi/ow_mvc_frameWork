<?php
/**
 * @package ow_plugins.blogs.components
 * @since 1.0
 */
class BLOGS_CMP_BlogWidget extends BASE_CLASS_Widget
{

    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();

        $service = PostService::getInstance();

        $count = $params->customParamList['count'];
        $previewLength = $params->customParamList['previewLength'];

        $list = $service->findListByUser(0, $count);

        if ( (empty($list) || (false && !OW::getUser()->isAuthorized('blogs', 'add') && !OW::getUser()->isAuthorized('blogs', 'view') && !OW::getUser()->isAuthorized('blogs') && !OW::getUser()->isAdmin())) && !$params->customizeMode )
        {
            $this->setVisible(false);

            return;
        }

        $posts = array();

        $userService = BOL_UserService::getInstance();

        $postIdList = array();
        foreach ( $list as $dto )
        {
            /* @var $dto Post */

            if ( mb_strlen($dto->getTitle()) > 100 )
            {
                $dto->setTitle(UTIL_String::truncate(UTIL_HtmlTag::stripTagsAndJs($dto->getTitle()), 350, '...'));
            }
            $text = $service->processPostText($dto->getPost());

            $posts[] = array(
                'dto' => $dto,
                'text' => UTIL_String::truncate($text, $previewLength),
                'truncated' => ( mb_strlen($text) > $previewLength ),
                'url' => OW::getRouter()->urlForRoute('user-post', array('id'=>$dto->getId()))
            );

            $idList[] = $dto->getAuthorId();
            $postIdList[] = $dto->id;
        }

        $commentInfo = array();

        if ( !empty($idList) )
        {
            $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($idList, true, true);
            $this->assign('avatars', $avatars);

            $urls = BOL_UserService::getInstance()->getUserUrlsForList($idList);

            $commentInfo = BOL_CommentService::getInstance()->findCommentCountForEntityList('blog-post', $postIdList);

            $toolbars = array();

            foreach ( $list as $dto )
            {
                $toolbars[$dto->getId()] = array(
                    array(
                        'class' => 'ow_icon_control ow_ic_user',
                        'href' => isset($urls[$dto->getAuthorId()]) ? $urls[$dto->getAuthorId()] : '#',
                        'label' => isset($avatars[$dto->getAuthorId()]['title']) ? $avatars[$dto->getAuthorId()]['title'] : ''
                    ),
                    array(
                        'class' => 'ow_remark ow_ipc_date',
                        'label' => UTIL_DateTime::formatDate($dto->getTimestamp())
                    )
                );
            }
            $this->assign('tbars', $toolbars);
        }

        $this->assign('commentInfo', $commentInfo);
        $this->assign('list', $posts);


        if ( $service->countPosts() > 0 )
        {
            $toolbar = array();

            if ( OW::getUser()->isAuthorized('blogs', 'add'))
            {
                $toolbar[] = array(
                        'label' => OW::getLanguage()->text('blogs', 'add_new'),
                        'href' => OW::getRouter()->urlForRoute('post-save-new')
                    );
            }

            if ( OW::getUser()->isAuthorized('blogs', 'view') )
            {
                $toolbar[] = array(
                    'label' => OW::getLanguage()->text('blogs', 'go_to_blog'),
                    'href' => OW::getRouter()->urlForRoute('blogs')
                    );
            }

            if (!empty($toolbar))
            {
                $this->setSettingValue(self::SETTING_TOOLBAR, $toolbar);
            }

        }
    }

    public static function getSettingList()
    {

        $options = array();

        for ( $i = 3; $i <= 10; $i++ )
        {
            $options[$i] = $i;
        }

        $settingList['count'] = array(
            'presentation' => self::PRESENTATION_SELECT,
            'label' => OW::getLanguage()->text('blogs', 'cmp_widget_post_count'),
            'optionList' => $options,
            'value' => 3,
        );
        $settingList['previewLength'] = array(
            'presentation' => self::PRESENTATION_TEXT,
            'label' => OW::getLanguage()->text('blogs', 'blog_widget_preview_length_lbl'),
            'value' => 200,
        );

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        $list = array(
            self::SETTING_TITLE => OW::getLanguage()->text('blogs', 'main_menu_item'),
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_ICON => 'ow_ic_write'
        );

        return $list;
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}

