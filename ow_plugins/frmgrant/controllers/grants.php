<?php
class FRMGRANT_CTRL_Grants extends OW_ActionController
{
    private $service;
    public function __construct()
    {
        $this->service = FRMGRANT_BOL_Service::getInstance();
    }
    public function index()
    {
        if ( OW::getUser()->isAuthorized('frmgrant','manage-grant') )
        {
            $this->assign('url_new_entry', OW::getRouter()->urlForRoute('frmgrant.add'));
        }else{
            $this->assign('url_new_entry', null);
        }
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('frmgrant')->getStaticCssUrl() . 'frmgrant.css');

        $configs = $this->service->getConfigs();
        $page = (empty($_GET['page']) || (int)$_GET['page'] < 0) ? 1 : (int)$_GET['page'];
        $grants = $this->service->findGrantsOrderedList($page);
        $grantsCount = $this->service->findGrantsOrderedListCount();
        $this->addComponent('paging', new BASE_CMP_Paging($page, ceil($grantsCount / $configs[FRMGRANT_BOL_Service::CONF_GRANTS_COUNT_ON_PAGE]), 5));
        if (empty($grants)) {
            $this->assign('no_grant', true);
        }
        $this->assign('page', $page);
        $this->assign('grants', $this->service->getListingDataGrant($grants));

        $language = OW::getLanguage();
        $this->setPageHeading($language->text('frmgrant', 'grant_main_page_heading'));
        $this->setPageTitle($language->text('frmgrant', 'grant_main_page_title'));

    }
    public function view($params)
    {
        $grantId = (int)$params['grantId'];
        if (empty($grantId)) {
            throw new Redirect404Exception();
        }
        $grant = $this->service->findGrantById($grantId);
        if ($grant === null) {
            throw new Redirect404Exception();
        }
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('frmgrant')->getStaticCssUrl() . 'frmgrant.css');
        $language = OW::getLanguage();
        $allowEdit = OW::getUser()->isAuthorized('frmgrant', 'manage-grant') || OW::getUser()->isAdmin();
        $this->assign('allowEdit', $allowEdit);
        $title = UTIL_String::truncate(strip_tags($grant->getTitle()), 300, "...");
        $infoArray = array(
            'id' => $grant->getId(),
            'desc' => $grant->getDescription()== null ? null : UTIL_HtmlTag::autoLink($grant->getDescription()),
            'title' => $title,
            'date' => UTIL_DateTime::formatSimpleDate($grant->getTimeStamp(), true),
            'prof' => $grant->getProfessor(),
            'college' => $grant->getCollegeAndField(),
            'lab' => $grant->getLaboratory(),
            'started' => $grant->getStartedYear(),
            'delete' => $allowEdit ? array(
                'url' => OW::getRouter()->urlFor('FRMGRANT_CTRL_Save', 'delete', array('grantId' => $grant->getId())),
                'confirmMessage' => OW::getLanguage()->text('frmgrant', 'delete_grant_confirm_message')
            ) : null,
            'editUrl' => OW::getRouter()->urlForRoute('frmgrant.edit', array('grantId' => $grant->getId()))
        );
        $this->setPageTitle($language->text('frmgrant', 'view_page_title', array('grant_title' => $title)));
        $this->setPageHeading($title);
        $this->assign('info', $infoArray);
        $this->assign('grantsListUrl', OW::getRouter()->urlForRoute('frmgrant.index'));
    }
}