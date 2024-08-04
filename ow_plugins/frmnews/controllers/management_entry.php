<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmnews.controllers
 * @since 1.0
 */
class FRMNEWS_CTRL_ManagementEntry extends OW_ActionController
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

        $this->setPageHeading(OW::getLanguage()->text('frmnews', 'management_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_write');

        $this->language = OW::getLanguage();
        $this->service = EntryService::getInstance();

        $this->addComponent('menu', new FRMNEWS_CMP_ManagementMenu());
    }

    public function index()
    {
        $userId = OW::getUser()->getId();

        $page = empty($_GET['page']) ? 1 : $_GET['page'];

        $rpp = (int) OW::getConfig()->getValue('frmnews', 'results_per_page');


        $first = ($page - 1) * $rpp;

        $count = $rpp;

        $data = $this->getData($userId, $first, $count);

        $list = $data['list'];

        $itemCount = $data['count'];

        $pageCount = ceil($itemCount / $rpp);

        $codes=array();
        foreach ($list as $entry){
            $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
                array('senderId'=>OW::getUser()->getId(),'receiverId'=>$entry->id,'isPermanent'=>true,'activityType'=>'delete_news')));
            if(isset($frmSecuritymanagerEvent->getData()['code'])){
                $code = $frmSecuritymanagerEvent->getData()['code'];
                $codes[$entry->id]=$code;
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
            case ( OW::getRouter()->getUri() == OW::getRouter()->uriForRoute('frmnews-manage-entrys') ):
                return 'entrys';

            case ( OW::getRouter()->getUri() == OW::getRouter()->uriForRoute('frmnews-manage-drafts') ):
                return 'drafts';
        }
    }
}