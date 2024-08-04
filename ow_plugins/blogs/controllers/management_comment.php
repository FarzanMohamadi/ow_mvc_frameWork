<?php
/**
 * @package ow_plugins.blogs.controllers
 * @since 1.0
 */
class BLOGS_CTRL_ManagementComment extends OW_ActionController
{

    public function index()
    {

        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $this->setPageHeading(OW::getLanguage()->text('blogs', 'management_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_write');
        $this->setDocumentKey('my_blog_User_comments');

        $this->addComponent('menu', new BLOGS_CMP_ManagementMenu());

        $service = PostService::getInstance();

        $userId = OW::getUser()->getId();

        $page = empty($_GET['page']) ? 1 : $_GET['page'];

        $this->assign('thisUrl', OW_URL_HOME . OW::getRequest()->getRequestUri());

        $rpp = (int) OW::getConfig()->getValue('blogs', 'results_per_page');

        $first = ($page - 1) * $rpp;
        $count = $rpp;

        $list = $service->findUserPostCommentList($userId, $first, $count);
        $authorIdList = array();
        $postList = array();
        $imageInfoList = array();
        foreach ( $list as $id => $item )
        {
            $message=$item['message'];
            $decodedMessage=urldecode($message);
            $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_RENDER_STRING, array('string' => $decodedMessage)));
            if (isset($stringRenderer->getData()['string'])) {
                $decodedMessage = ($stringRenderer->getData()['string']);
            }
            $list[$id]['message']=$decodedMessage;

            $list[$id]['url'] = OW::getRouter()->urlForRoute('user-post', array('id'=>$item['entityId']));
            $postList[$item['entityId']] = $service->findById($item['entityId']);
            $authorIdList[] = $item['userId'];
        }

        $usernameList = array();
        $displayNameList = array();
        $avatarUrlList = array();

        if ( !empty($authorIdList) )
        {
            $userService = BOL_UserService::getInstance();

            $usernameList = $userService->getUserNamesForList($authorIdList);
            $displayNameList = $userService->getDisplayNamesForList($authorIdList);
            $avatarUrlList = BOL_AvatarService::getInstance()->getAvatarsUrlList($authorIdList);
            for ($loop_index = 0; $loop_index < sizeof($authorIdList); $loop_index++){
                $imageInfoList[$authorIdList[$loop_index]]= BOL_AvatarService::getInstance()->getAvatarInfo($authorIdList[$loop_index], $avatarUrlList[$authorIdList[$loop_index]]);
            }
        }

        $this->assign('postList', $postList);
        $this->assign('usernameList', $usernameList);
        $this->assign('displaynameList', $displayNameList);
        $this->assign('avatarUrlList', $avatarUrlList);
        $this->assign('imageInfoList', $imageInfoList);

        $this->assign('list', $list);

        $itemCount = $service->countUserPostComment($userId);

        $pageCount = ceil($itemCount / $rpp);

        $this->addComponent('paging', new BASE_CMP_Paging($page, $pageCount, 5));
    }

    public function deleteComment( $params )
    {

        if ( empty($params['id']) || intval($params['id']) <= 0 )
        {
            throw new InvalidArgumentException();
        }

        $id = (int) $params['id'];

        $isAuthorized = true; // TODO: Authorization needed

        if ( !$isAuthorized )
        {
            exit;
        }

        BOL_CommentService::getInstance()->deleteComment($id);

        OW::getFeedback()->info(OW::getLanguage()->text('blogs', 'manage_page_comment_deleted_msg'));

        if ( !empty($_GET['back-to']) )
        {
            if(strpos( $_GET['back-to'], ":") === false ) {
                $this->redirect($_GET['back-to']);
            }
        }
        $this->redirect(OW::getRouter()->urlForRoute('blog-manage-comments'));
    }
}