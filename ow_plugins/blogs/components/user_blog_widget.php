<?php
/**
 * @package ow_plugins.blogs.components
 * @since 1.0
 */
class BLOGS_CMP_UserBlogWidget extends BASE_CLASS_Widget
{

    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();

        $service = PostService::getInstance();

        if ( empty($params->additionalParamList['entityId']) )
        {
            
        }

        $userId = $params->additionalParamList['entityId'];

        
        if ( $userId != OW::getUser()->getId() && !OW::getUser()->isAuthorized('blogs', 'view') )
        {
            $this->setVisible(false);
            return;
        }
        
        /* Check privacy permissions */
        $eventParams = array(
            'action' => PostService::PRIVACY_ACTION_VIEW_BLOG_POSTS,
            'ownerId' => $userId,
            'viewerId' => OW::getUser()->getId()
        );

        try
        {
            OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
        }
        catch ( RedirectException $ex )
        {
            $this->setVisible(false);
            return;
        }
        /* */

        if ( $service->countUserPost($userId) == 0 && $service->countUserDraft($userId) == 0 && !$params->customizeMode )
        {
            $this->setVisible(false);
            return;
        }

        $this->assign('displayname', BOL_UserService::getInstance()->getDisplayName($userId));
        $this->assign('username', BOL_UserService::getInstance()->getUserName($userId));

        $list = array();

        $count = $params->customParamList['count'];

        $userPostList = $service->findUserPostList($userId, 0, $count);

        foreach ( $userPostList as $id => $item )
        {
            /* Check privacy permissions */
            if ( $item->authorId != OW::getUser()->getId() && !OW::getUser()->isAuthorized('blogs') )
            {
                $eventParams = array(
                    'action' => PostService::PRIVACY_ACTION_VIEW_BLOG_POSTS,
                    'ownerId' => $item->authorId,
                    'viewerId' => OW::getUser()->getId()
                );

                try
                {
                    OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
                }
                catch ( RedirectException $ex )
                {
                    continue;
                }
            }
            /* */

            $list[$id] = $item;
            $list[$id]->setPost(strip_tags($item->getPost()));

            $idList[] = $item->id;
        }

        $commentInfo = array();

        if ( !empty($idList) )
        {
            $commentInfo = BOL_CommentService::getInstance()->findCommentCountForEntityList('blog-post', $idList);
            $tb = array();
            foreach ( $list as $key => $item )
            {

                $sentenceCorrected = false;
                if ( mb_strlen($item->getPost()) > 170 )
                {
                    $sentence = $item->getPost();
                    $event = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::PARTIAL_HALF_SPACE_CODE_DISPLAY_CORRECTION, array('sentence' => $sentence, 'trimLength' => 170)));
                    if(isset($event->getData()['correctedSentence'])){
                        $sentence = $event->getData()['correctedSentence'];
                        $sentenceCorrected=true;
                    }
                    $event = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::PARTIAL_SPACE_CODE_DISPLAY_CORRECTION, array('sentence' => $sentence, 'trimLength' => 170)));
                    if(isset($event->getData()['correctedSentence'])){
                        $sentence = $event->getData()['correctedSentence'];
                        $sentenceCorrected=true;
                    }
                }
                if($sentenceCorrected){
                    $list[$key]->setPost($sentence.'...');
                }
                else{
                    $list[$key]->setPost(UTIL_String::truncate($item->getPost(), 170, '...'));
                }
                if ( mb_strlen($item->getTitle()) > 350 )
                {
                    $list[$key]->setTitle(UTIL_String::truncate(UTIL_HtmlTag::stripTagsAndJs($item->getTitle()), 350, '...'));
                }                
                if ( $commentInfo[$item->getId()] == 0 )
                {
                    $comments_tb_link = array('label' => '', 'href' => '');
                }
                else
                {
                    $comments_tb_link = array(
                        'label' => '<span class="ow_txt_value">' . $commentInfo[$item->getId()] . '</span> ' . OW::getLanguage()->text('blogs', 'toolbar_comments'),
                        'href' => OW::getRouter()->urlForRoute('post', array('id' => $item->getId()))
                    );
                }

                $tb[$item->getId()] = array(
                    $comments_tb_link,
                    array(
                        'label' => UTIL_DateTime::formatDate($item->getTimestamp()),
                        'class' => 'ow_ic_date'
                    )
                );
            }

            $this->assign('tb', $tb);
        }

        $itemList = array();
        foreach($list as $post)
        {
            $itemList[] = array(
                'dto' => $post,
                'titleHref' => OW::getRouter()->urlForRoute('user-post', array('id'=>$post->getId()))
            );
        }

        $this->assign('list', $itemList);
        $this->assign('my_drafts_url', OW::getRouter()->urlForRoute('blog-manage-drafts'));
        $user = BOL_UserService::getInstance()->findUserById($userId);
        if($service->countUserPost($userId)!=0) {
            $this->setSettingValue(
                self::SETTING_TOOLBAR, array(
                    array(
                        'label' => OW::getLanguage()->text('base', 'view_all_with_count', array('count' => $service->countUserPost($userId))),
                        'href' => OW::getRouter()->urlForRoute('user-blog', array('user' => $user->getUsername()))
                    )
                )
            );
        }
    }

    public static function getSettingList()
    {
        $settingList = array();

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

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW::getLanguage()->text('blogs', 'blog'),
            self::SETTING_ICON => 'ow_ic_write',
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_SHOW_TITLE => true
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}