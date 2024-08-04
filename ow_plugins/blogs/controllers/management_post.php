<?php
/**
 * @package ow_plugins.blogs.controllers
 * @since 1.0
 */
class BLOGS_CTRL_ManagementPost extends OW_ActionController
{
    private
    $language,
    $service;

    public function __construct()
    {

        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $this->setPageHeading(OW::getLanguage()->text('blogs', 'management_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_write');
        $this->setDocumentKey('my_blog_management_page');

        $this->language = OW::getLanguage();
        $this->service = PostService::getInstance();

        $this->addComponent('menu', new BLOGS_CMP_ManagementMenu());
    }

    public function index()
    {
        $userId = OW::getUser()->getId();

        $page = empty($_GET['page']) ? 1 : $_GET['page'];

        $rpp = (int) OW::getConfig()->getValue('blogs', 'results_per_page');


        $first = ($page - 1) * $rpp;

        $count = $rpp;

        $data = $this->getData($userId, $first, $count);

        $list = $data['list'];

        $itemCount = $data['count'];

        $pageCount = ceil($itemCount / $rpp);
        $codes=array();
        foreach ($list as $post){
            $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
                array('senderId'=>OW::getUser()->getId(),'receiverId'=>$post->id,'isPermanent'=>true,'activityType'=>'delete_blog')));
            if(isset($frmSecuritymanagerEvent->getData()['code'])){
                $code = $frmSecuritymanagerEvent->getData()['code'];
                $codes[$post->id]=$code;
            }
        }
        $this->assign('codes', $codes);
        $this->assign('list', $list);
        $this->assign('status', $data['status']);

        $this->assign('thisUrl', OW_URL_HOME . OW::getRequest()->getRequestUri());

        $this->addComponent('paging', new BASE_CMP_Paging($page, $pageCount, 5));
    }

    private function getData( $userId, $first, $count )
    {
        switch ( $this->getCase() )
        {
            case 'posts':
                return array(
                    'status' => $this->language->text('blogs', 'status_published'),
                    'list' => $this->service->findUserPostList($userId, $first, $count),
                    'count' => $this->service->countUserPost($userId),
                );

            case 'drafts':
                return array(
                    'status' => $this->language->text('blogs', 'status_draft'),
                    'list' => $this->service->findUserDraftList($userId, $first, $count),
                    'count' => $this->service->countUserDraft($userId),
                );
        }

        return array();
    }

    private function getCase()
    {
        switch ( true )
        {
            case ( OW::getRouter()->getUri() == OW::getRouter()->uriForRoute('blog-manage-posts') ):
                return 'posts';

            case ( OW::getRouter()->getUri() == OW::getRouter()->uriForRoute('blog-manage-drafts') ):
                return 'drafts';
        }
    }
}