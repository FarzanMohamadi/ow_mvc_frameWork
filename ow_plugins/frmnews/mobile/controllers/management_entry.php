<?php
class FRMNEWS_MCTRL_ManagementEntry extends OW_MobileActionController
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
        OW::getDocument()->addStyleSheet(OW_PluginManager::getInstance()->getPlugin("frmnews")->getStaticCssUrl() . 'news.css');
        $this->setPageHeading(OW::getLanguage()->text('frmnews', 'management_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_write');

        $this->language = OW::getLanguage();
        $this->service = EntryService::getInstance();

        $this->addComponent('menu', new FRMNEWS_MCMP_ManagementMenu());
    }

    public function index()
    {
        $userId = OW::getUser()->getId();

        $page = empty($_GET['page']) ? 1 : $_GET['page'];

        $rpp = 5;


        $first = ($page - 1) * $rpp;

        $count = $rpp;

        $data = $this->getData($userId, $first, $count);

        $list = $data['list'];

        $itemCount = $data['count'];

        $pageCount = ceil($itemCount / $rpp);
        $codes=array();
        foreach ($list as $post){
            $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
                array('senderId'=>OW::getUser()->getId(),'receiverId'=>$post->id,'isPermanent'=>true,'activityType'=>'delete_news')));
            if(isset($frmSecuritymanagerEvent->getData()['code'])){
                $code = $frmSecuritymanagerEvent->getData()['code'];
                $codes[$post->id]=$code;
            }
        }
        $this->assign('codes', $codes);
        $this->assign('list', $list);
        $this->assign('status', $data['status']);

        $this->assign('thisUrl', OW_URL_HOME . OW::getRequest()->getRequestUri());

        $this->addComponent('paging', new BASE_CMP_PagingMobile($page, $pageCount, 5));
        $this->assign("urlForBack",OW::getRouter()->urlForRoute("frmnews"));

    }

    private function getData( $userId, $first, $count )
    {
        switch ( $this->getCase() )
        {
            case 'entrys':
                return array(
                    'status' => $this->language->text('frmnews', 'status_published'),
                    'list' => $this->service->findUserEntryList($userId, $first, $count),
                    'count' => $this->service->countUserEntry($userId),
                );

            case 'drafts':
                return array(
                    'status' => $this->language->text('frmnews', 'status_draft'),
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
            case ( OW::getRouter()->getUri() == OW::getRouter()->uriForRoute('news-manage-entrys') ):
                return 'entrys';

            case ( OW::getRouter()->getUri() == OW::getRouter()->uriForRoute('news-manage-drafts') ):
                return 'drafts';
        }
    }
}