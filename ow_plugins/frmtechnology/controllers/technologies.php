<?php
class FRMTECHNOLOGY_CTRL_Technologies extends OW_ActionController
{

    private $service;
    private $isMobile;
    /**
     * @var BASE_CMP_ContentMenu
     */
    private $menu;

    public function __construct()
    {
        $mobileEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION, array('check' => true)));
        if (isset($mobileEvent->getData()['isMobileVersion']) && $mobileEvent->getData()['isMobileVersion'] == true) {
            $this->isMobile = true;
        } else {
            $this->isMobile = false;
        }
        $this->service = FRMTECHNOLOGY_BOL_Service::getInstance();
        $this->menu = $this->getTechnologyListMenu();
        if (!OW::getRequest()->isAjax()) {
            if ($this->isMobile) {

            } else {
                $mainMenuItem = OW::getDocument()->getMasterPage()->getMenu(OW_Navigation::MAIN)->getElement('main_menu_item', 'frmtechnology');
                if ($mainMenuItem !== null) {
                    $mainMenuItem->setActive(true);
                }
            }
        }
    }

    public function index($params)
    {
        if (empty($params['listType'])) {
            $params['listType'] = 'latest';
        }
        $listType = $params['listType'];
        $this->assign('isMobile', $this->isMobile);
        $validList = array('deactivate', 'latest', 'tags');
        $this->addComponent('menu', $this->menu);
        if (!in_array($listType, $validList)) {
            $this->redirect(OW::getRouter()->urlForRoute('frmtechnology.view-list', array('listType' => 'latest')));
        } else {
            $this->assign('listType', $listType);
        }
        switch ($listType) {
            case 'deactivate':
                if (OW::getUser()->isAuthorized('frmtechnology', 'manage-technology') || OW::getUser()->isAdmin()) {
                    $this->deactivateTechnologyList();
                } else {
                    throw new Redirect404Exception();
                }
                break;
            case 'latest':
                $this->LatestTechnologyList();
                break;
            case 'tags':
                $this->tagsTechnologyList();
                break;
            default:
                throw new Redirect404Exception();
        }
//        if(OW::getUser()->isAuthenticated() && $listType == 'my') {
//            $this->myTechnologyList();
//        }else{
//            $this->LatestTechnologyList();
//        }
    }
//    public function myTechnologyList(){
//        if ( OW::getUser()->isAuthorized('frmtechnology','add_technology') )
//        {
//            $this->assign('allowAdd', true);
//            $this->assign('url_new_entry', OW::getRouter()->urlForRoute('frmtechnology.add'));
//        }
//
//        $configs = $this->service->getConfigs();
//        $page = ( empty($_GET['page']) || (int) $_GET['page'] < 0 ) ? 1 : (int) $_GET['page'];
//        if( isset($_GET['technologyStatus']) ){
//            $searchTitle = UTIL_HtmlTag::escapeHtml(UTIL_HtmlTag::stripTagsAndJs($_GET['technologyStatus']));
//            $technologies = $this->service->findTechnologiesByFiltering($searchTitle,$page,OW::getUser()->getId());
//            $technologiesCount = $this->service->findTechnologiesByFilteringCount($searchTitle,OW::getUser()->getId());
//            $this->addComponent('paging', new BASE_CMP_Paging($page, ceil($technologiesCount / $configs[FRMTECHNOLOGY_BOL_Service::CONF_TECHNOLOGIES_COUNT_ON_PAGE]), 5));
//            if ( empty($technologies) )
//            {
//                $this->assign('no_technology', true);
//            }
//            $this->assign('technologies', $this->service->getListingDataWithToolbarTechnology($technologies));
//            $this->assign('filterForm', true);
//            $this->addForm($this->getTechnologyFilterForm(array('searchTitle' => $searchTitle)));
//
//        }else{
//            $technologies = $this->service->findMyTechnologies($page);
//            $technologiesCount = $this->service->findMyTechnologiesCount();
//
//            $this->addComponent('paging', new BASE_CMP_Paging($page, ceil($technologiesCount / $configs[FRMTECHNOLOGY_BOL_Service::CONF_TECHNOLOGIES_COUNT_ON_PAGE]), 5));
//            if ( empty($technologies) )
//            {
//                $this->assign('no_technology', true);
//            }
//            $this->assign('page', $page);
//            $this->assign('technologies', $this->service->getListingDataWithToolbarTechnology($technologies));
//            $this->assign('filterForm', true);
//            $this->addForm($this->getTechnologyFilterForm(null));
//        }
//
//        $language = OW::getLanguage();
//        $this->setPageHeading($language->text('frmtechnology',  'technology_main_page_heading'));
//        $this->setPageTitle($language->text('frmtechnology',  'technology_main_page_title'));
//        $templatePath = OW::getPluginManager()->getPlugin('frmtechnology')->getCtrlViewDir() . 'technologies_index.html';
//        $this->setTemplate($templatePath);
//        $this->assign('originalUrl', OW::getRouter()->urlForRoute('frmtechnology.view-list', array('listType' => 'my')));
//
//
//        OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'frmtechnology', 'main_menu_item');
//    }
    public function tagsTechnologyList()
    {
        $this->menu->setItemActive('tags');
        $language = OW::getLanguage();
        $this->setPageHeading($language->text('frmtechnology', 'technology_main_page_heading'));
        $this->setPageTitle($language->text('frmtechnology', 'technology_main_page_title'));
        $this->assign('url_new_entry', OW::getRouter()->urlForRoute('frmtechnology.add'));
        $tagSearch = new BASE_CMP_TagSearch(OW::getRouter()->urlForRoute('frmtechnology.view-list', array('listType' => 'tags')));
        $this->addComponent('tagSearch', $tagSearch);
        $tagCount = 1000;
        $tagCloud = new BASE_CMP_EntityTagCloud('technology-description', OW::getRouter()->urlForRoute('frmtechnology.view-list', array('listType' => 'tags')), $tagCount);
        $configs = $this->service->getConfigs();
        $tag = !(empty($_GET['tag'])) ? strip_tags(UTIL_HtmlTag::stripTags($_GET['tag'])) : null;
        $this->assign('tag', $tag);
        $showList = !empty($tag);
        if(!$showList){
            $tagCloud->setTemplate(OW::getPluginManager()->getPlugin('base')->getCmpViewDir() . 'big_tag_cloud.html');
        }
        $this->addComponent('tagCloud', $tagCloud);
        $this->assign('showList', $showList);
        $tagsLabel = array();
        $items = $this->service->findTechnologiesOrderedList(null);
        $page = (empty($_GET['page']) || (int)$_GET['page'] < 0) ? 1 : (int)$_GET['page'];
        if (isset($_GET['tag'])) {
            $searchTag = UTIL_HtmlTag::escapeHtml(UTIL_HtmlTag::stripTagsAndJs($_GET['tag']));
            $technologies = $this->service->findTechnologiesByFilteringTag($searchTag, $page);
            $technologiesCount = $this->service->findTechnologiesByFilteringCount($searchTag);
            $this->addComponent('paging', new BASE_CMP_Paging($page, ceil($technologiesCount / $configs[FRMTECHNOLOGY_BOL_Service::CONF_TECHNOLOGIES_COUNT_ON_PAGE]), 5));
            if (empty($technologies)) {
                $this->assign('no_technology', true);
            }else{
                $this->assign('technologies', $this->service->getListingDataWithToolbarTechnology($technologies));
            }
        }
        foreach ($items as $item){
            if($item->getStatus()== FRMTECHNOLOGY_BOL_Service::STATUS_DEACTIVATE){
                continue;
            }
            $tags = BOL_TagService::getInstance()->findEntityTags($item->getId(),'technology-description');
            if(sizeof($tags)>0){
                $labels = " ";
                $comma = OW::getLanguage()->text('frmtechnology', 'null').' ';
                foreach($tags as $tag)
                {
                    $labels .= '<a href="'.OW::getRouter()->urlForRoute('frmtechnology.view-list', array('listType'=>'tags')) . "?tag=".$tag->getLabel().'">'.$tag->getLabel().'</a>'.$comma;
                }
                $labels = rtrim($labels, $comma);
                $tagsLabel[$item->getId()]=$labels;
            }
        }
        $this->assign('tags', $tagsLabel);
        $templatePath = OW::getPluginManager()->getPlugin('frmtechnology')->getCtrlViewDir() . 'technologies_tags.html';
        $this->setTemplate($templatePath);
    }

    public function latestTechnologyList()
    {
        $this->menu->setItemActive('latest');
        //  if ( OW::getUser()->isAuthorized('frmtechnology','add_technology') )
        //  {
        $this->assign('allowAdd', true);
        $this->assign('url_new_entry', OW::getRouter()->urlForRoute('frmtechnology.add'));
        //  }

        $configs = $this->service->getConfigs();
        $page = (empty($_GET['page']) || (int)$_GET['page'] < 0) ? 1 : (int)$_GET['page'];
//        if (isset($_GET['technologyStatus'])) {
//            $searchTitle = UTIL_HtmlTag::escapeHtml(UTIL_HtmlTag::stripTagsAndJs($_GET['technologyStatus']));
//            $technologies = $this->service->findTechnologiesByFiltering($searchTitle, $page);
//            $technologiesCount = $this->service->findTechnologiesByFilteringCount($searchTitle);
//            $this->addComponent('paging', new BASE_CMP_Paging($page, ceil($technologiesCount / $configs[FRMTECHNOLOGY_BOL_Service::CONF_TECHNOLOGIES_COUNT_ON_PAGE]), 5));
//            if (empty($technologies)) {
//                $this->assign('no_technology', true);
//            }
//            $this->assign('technologies', $this->service->getListingDataWithToolbarTechnology($technologies));
//            $this->assign('filterForm', true);
//            $this->addForm($this->getTechnologyFilterForm(array('searchTitle' => $searchTitle)));
//
//        } else {
        $technologies = $this->service->findTechnologiesOrderedList($page);
        $technologiesCount = $this->service->findTechnologiesOrderedListCount();

        $this->addComponent('paging', new BASE_CMP_Paging($page, ceil($technologiesCount / $configs[FRMTECHNOLOGY_BOL_Service::CONF_TECHNOLOGIES_COUNT_ON_PAGE]), 5));
        if (empty($technologies)) {
            $this->assign('no_technology', true);
        }
        $this->assign('page', $page);
        $this->assign('technologies', $this->service->getListingDataWithToolbarTechnology($technologies));
        $this->assign('filterForm', true);
        $this->addForm($this->getTechnologyFilterForm(null));
//        }
//        $tagCount = 1000;
//        $tagSearch = new BASE_CMP_TagSearch(OW::getRouter()->urlForRoute('frmtechnology.view-list',
//            array('list'=>'browse-by-tag')),'base+tag_search','tag' ,true,
//            'news-entry', OW::getRouter()->urlForRoute('frmtechnology.view-list', array('list'=>'browse-by-tag')), $tagCount);
//        $this->addComponent('tagSearch', $tagSearch);
//
//        $entrySearch = new BASE_CMP_TagSearch(OW::getRouter()->urlForRoute('frmtechnology.view-list', array('list'=>'search-results')),
//            'frmtechnology+search_entries','q');

//        $this->addComponent('entrySearch', $entrySearch);
        $language = OW::getLanguage();
        $this->setPageHeading($language->text('frmtechnology', 'technology_main_page_heading'));
        $this->setPageTitle($language->text('frmtechnology', 'technology_main_page_title'));
        $templatePath = OW::getPluginManager()->getPlugin('frmtechnology')->getCtrlViewDir() . 'technologies_index.html';
        $this->setTemplate($templatePath);
        $this->assign('originalUrl', OW::getRouter()->urlForRoute('frmtechnology.view-list', array('listType' => 'latest')));
    }

    public function deactivateTechnologyList()
    {
        $configs = $this->service->getConfigs();
        $page = (empty($_GET['page']) || (int)$_GET['page'] < 0) ? 1 : (int)$_GET['page'];
        $technologies = $this->service->findDeactivateTechnologiesOrderedList($page);
        $technologiesCount = $this->service->findDeactivateTechnologiesOrderedListCount();
        $this->addComponent('paging', new BASE_CMP_Paging($page, ceil($technologiesCount / $configs[FRMTECHNOLOGY_BOL_Service::CONF_TECHNOLOGIES_COUNT_ON_PAGE]), 5));
        if (empty($technologies)) {
            $this->assign('no_technology', true);
        }
        $this->assign('page', $page);
        $this->assign('technologies', $this->service->getListingDataWithToolbarDeactivateTechnology($technologies));

        $language = OW::getLanguage();
        $this->setPageHeading($language->text('frmtechnology', 'deactivate_technologies_page_heading'));
        $this->setPageTitle($language->text('frmtechnology', 'deactivate_technologies_page_title'));
        $templatePath = OW::getPluginManager()->getPlugin('frmtechnology')->getCtrlViewDir() . 'technologies_deactivate.html';
        $this->setTemplate($templatePath);
    }

    public function view($params)
    {
        $this->assign('isMobile', $this->isMobile);
        $technologyId = (int)$params['technologyId'];

        if (empty($technologyId)) {
            throw new Redirect404Exception();
        }

        $technology = $this->service->findTechnologyById($technologyId);

        if ($technology === null) {
            throw new Redirect404Exception();
        }

        if ($technology->status == FRMTECHNOLOGY_BOL_Service::STATUS_DEACTIVATE) {
            if (!OW::getUser()->isAdmin() && !OW::getUser()->isAuthorized('frmtechnology', 'manage-technology')) {
                throw new Redirect404Exception();
            }
        }

        $language = OW::getLanguage();

        $allowEdit = OW::getUser()->isAuthorized('frmtechnology', 'manage-technology') || OW::getUser()->isAdmin(); //|| $technology->getUserId() == OW::getUser()->getId();
        $this->assign('allowEdit', $allowEdit);
        $isActive = $technology->getStatus() == FRMTECHNOLOGY_BOL_Service::STATUS_ACTIVE ? true : false;
        $this->assign('isActive', $isActive);
        OW::getDocument()->setTitle($language->text('frmtechnology', 'view_page_title', array(
            'technology_title' => strip_tags($technology->getTitle())
        )));
        $this->setPageHeading($technology->getTitle());
        $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_AFTER_NEWSFEED_STATUS_STRING_READ, array('string' => $technology->getDescription())));
        if (isset($stringRenderer->getData()['string'])) {
            $technology->setDescription(($stringRenderer->getData()['string']));
        }
        if ($technology->getStatus() == FRMTECHNOLOGY_BOL_Service::STATUS_ACTIVE) {
            $title = UTIL_String::truncate(strip_tags($technology->getTitle()), 300, "...");
        } else {
            $title = OW::getLanguage()->text('frmtechnology', 'deactivate_technology_title', array('title' => UTIL_String::truncate(strip_tags($technology->getTitle()), 300, "...")));
        }

        $tags = BOL_TagService::getInstance()->findEntityTags($technology->getId(),'technology-description');
        if(count($tags)>=1){
            $tagLabels = "<span class='ow_wrap_normal'>";
            foreach ( $tags as $tag )
            {
                $tag = $tag->getLabel();
                $tagLabels .='<a href="' . OW::getRouter()->urlForRoute('blogs.list', array('list'=>'browse-by-tag')) . "?tag={$tag}" . "\">{$tag}</a>, ";
            }

            $tagLabels = mb_substr($tagLabels, 0, mb_strlen($tagLabels) - 2);
            $tagLabels .= "</span>";
        }else{
            $tagLabels = $language->text('frmtechnology','null');
        }
        $infoArray = array(
            'id' => $technology->getId(),
            'imgUrl1' => ($technology->getImage1() ? $this->service->generateImageUrl($technology->getImage1(), false) : null),
            'imgUrl2' => ($technology->getImage2() ? $this->service->generateImageUrl($technology->getImage2(), false) : null),
            'imgUrl3' => ($technology->getImage3() ? $this->service->generateImageUrl($technology->getImage3(), false) : null),
            'imgUrl4' => ($technology->getImage4() ? $this->service->generateImageUrl($technology->getImage4(), false) : null),
            'imgUrl5' => ($technology->getImage5() ? $this->service->generateImageUrl($technology->getImage5(), false) : null),
            'desc' => UTIL_HtmlTag::autoLink($technology->getDescription()),
            'title' => $title,
            'date' => UTIL_DateTime::formatSimpleDate($technology->getTimeStamp(), true),
            'userFullName' => $technology->getUserFullName(),
            'email' => $technology->getEmail(),
            'pn' => $technology->getPhoneNumber(),
            'tags' => $tagLabels,
            'editUrl' => OW::getRouter()->urlForRoute('frmtechnology.edit', array('technologyId' => $technology->getId())),
            'delete' => $allowEdit ? array(
                'url' => OW::getRouter()->urlFor('FRMTECHNOLOGY_CTRL_Save', 'delete', array('technologyId' => $technology->getId())),
                'confirmMessage' => OW::getLanguage()->text('frmtechnology', 'delete_technology_confirm_message')
            ) : null,
            'deactivateUrl' => ($technology->getStatus() == FRMTECHNOLOGY_BOL_Service::STATUS_ACTIVE ? OW::getRouter()->urlFor('FRMTECHNOLOGY_CTRL_Save', 'updateActivationStatus', array('technologyId' => $technology->getId(), 'status' => 'deactivate')) : null),
            'activateUrl' => ($technology->getStatus() == FRMTECHNOLOGY_BOL_Service::STATUS_DEACTIVATE ? OW::getRouter()->urlFor('FRMTECHNOLOGY_CTRL_Save', 'updateActivationStatus', array('technologyId' => $technology->getId(), 'status' => 'active')) : null),
            'position' => $technology->getPosition(),
            'grade' => $technology->getGrade(),
            'sn' => $technology->getStudentNumber(),
            'org' => $technology->getOrganization(),
            'area' => $technology->getArea()
            //'orderUrl'=> OW::getRouter()->urlForRoute('frmtechnology.submit', array('technologyId' => $technology->getId()))
        );
        OW::getDocument()->setTitle($language->text('frmtechnology', 'view_page_title', array('technology_title' => $title)));
        $this->setPageHeading($title);
//        $supporterList = array();
//        $technologySupporters = $this->service->findSupporterList($technologyId);
//        foreach ($technologySupporters as $supporter)
//            $supporterList[] = $supporter->getUserId();
//        if( ($technology->getWhoCanInviteSupporter() == FRMTECHNOLOGY_BOL_Service::WCIS_CREATOR && $technology->getUserId()== OW::getUser()->getId())
//        || ($technology->getWhoCanInviteSupporter() == FRMTECHNOLOGY_BOL_Service::WCIS_MEMBERS && OW::getUser()->isAuthenticated())) {
//            $this->assign('allowInvite', true);
//            $users = array();
//            $userDto = BOL_UserService::getInstance()->findRecentlyActiveList(0, 1000);
//
//            foreach ($userDto as $u) {
//                if ($u->id != OW::getUser()->getId()) {
//                    $users[] = $u->id;
//                }
//            }
//            $idList = array();
//            if (!empty($users)) {
//                foreach ($users as $uid) {
//                    if (in_array($uid, $supporterList)) {
//                        continue;
//                    }
//
//                    $idList[] = $uid;
//                }
//            }
//            $options = array(
//                'technologyId' => $technologyId,
//                'userList' => $idList,
//                'floatBoxTitle' => $language->text('frmtechnology', 'invite_supporter_fb_title'),
//                'inviteResponder' => OW::getRouter()->urlFor('FRMTECHNOLOGY_CTRL_Technologies', 'inviteSupporter')
//            );
//
//            $js = UTIL_JsGenerator::newInstance()->callFunction('FRMTECHNOLOGY_InitInviteButton', array($options));
//            OW::getDocument()->addOnloadScript($js);
//
//        }
//        allow Not Support codes
//        if (in_array(OW::getUser()->getId(), $supporterList) && OW::getUser()->getId() != $technology->getUserId()) {
//            $infoArray['deleteSupporterUrl'] = OW::getRouter()->urlFor('FRMTECHNOLOGY_CTRL_Technologies' , 'deleteSupporter',array('technologyId' => $technology->getId()));
//            $this->assign('allowNotSupport', true);
//        }

//        supporter widget codes
//        $place = 'frmtechnology';
//        $componentAdminService = BOL_ComponentAdminService::getInstance();
//        $schemeList = $componentAdminService->findSchemeList();
//        $defaultScheme = $componentAdminService->findSchemeByPlace($place);
//        if ( empty($defaultScheme) && !empty($schemeList) )
//        {
//            $defaultScheme = reset($schemeList);
//        }
//        $template ='drag_and_drop_entity_panel';
//
//        if ( !$componentAdminService->isCacheExists($place) )
//        {
//            $state = array();
//            $state['defaultComponents'] = $componentAdminService->findPlaceComponentList($place);
//            $state['defaultPositions'] = $componentAdminService->findAllPositionList($place);
//            $state['defaultSettings'] = $componentAdminService->findAllSettingList();
//            $state['defaultScheme'] = $defaultScheme;
//
//            $componentAdminService->saveCache($place, $state);
//        }
//
//        $state = $componentAdminService->findCache($place);
//        $defaultComponents = $state['defaultComponents'];
//        $defaultPositions = $state['defaultPositions'];
//        $defaultSettings = $state['defaultSettings'];
//        $defaultScheme = $state['defaultScheme'];
//        $customize = false;
//        $componentPanel = new BASE_CMP_DragAndDropEntityPanel($place, $technology->getId(), $defaultComponents, $customize, $template);
//        $componentPanel->setAdditionalSettingList(array(
//            'technologyId' => $technology->getId(),
//            'entity' => 'frmtechnology'
//        ));
//
//        $componentPanel->setSchemeList($schemeList);
//        $componentPanel->setPositionList($defaultPositions);
//        $componentPanel->setSettingList($defaultSettings);
//        $componentPanel->setScheme($defaultScheme);
//
//
//        $this->assign('componentPanel', $componentPanel->render());
        $this->assign('info', $infoArray);

        if ($technology->status == FRMTECHNOLOGY_BOL_Service::STATUS_DEACTIVATE) {
            $this->assign('technologiesListUrl', OW::getRouter()->urlForRoute('frmtechnology.view-list', array('listType' => 'deactivate')));
        } else {
            $this->assign('technologiesListUrl', OW::getRouter()->urlForRoute('frmtechnology.index'));
        }
    }

//    public function inviteSupporter()
//    {
//        if ( !OW::getRequest()->isAjax() )
//        {
//            throw new Redirect404Exception();
//        }
//
//        $userId = OW::getUser()->getId();
//
//        if ( empty($userId) )
//        {
//            throw new AuthenticateException();
//        }
//
//        $response = array();
//        $userIds = json_decode($_POST['userIdList']);
//        $technologyId = $_POST['technologyId'];
//        $allIdList = json_decode($_POST['allIdList']);
//
//        $technology = $this->service->findTechnologyById($technologyId);
//
//        if( ($technology->getWhoCanInviteSupport() == FRMTECHNOLOGY_BOL_Service::WCIS_CREATOR && $technology->getUserId()!= OW::getUser()->getId())
//            || ($technology->getWhoCanInviteSupport() == FRMTECHNOLOGY_BOL_Service::WCIS_MEMBERS && !OW::getUser()->isAuthenticated()))
//            {
//            exit(json_encode(array('result ' => false, 'error' => 'error')));
//            }
//            $count = 0;
//            foreach ($userIds as $uid) {
//                if ($userId == $uid) {
//                    continue;
//                }
//                $this->service->inviteSupporter($technologyId, $uid);
//                    $count++;
//                }
//            $response['added'] = true;
//            $response['url'] = OW::getRouter()->urlForRoute('frmtechnology.view', array('technologyId' => $technologyId));
//            $response['messageType'] = 'info';
//            $response['allIdList'] = array_diff($allIdList, $userIds);
//            OW::getFeedback()->info(OW::getLanguage()->text('frmtechnology', 'supporter_invite_success_message', array('count' => $count)));
//
//            exit(json_encode($response));
//        }
//
//        public function deleteSupporter ($params){
//            $this->service->deleteSupporter($params['technologyId']);
//
//            $redirectUrl = OW::getRouter()->urlForRoute('frmtechnology.view', array('technologyId' => $params['technologyId']));
//            OW::getFeedback()->info(OW::getLanguage()->text('frmtechnology', 'feed_not_support_complete_msg'));
//            $this->redirect($redirectUrl);
//        }
//

    public function getTechnologyFilterForm($params)
    {
        $form = new Form('technologyFilterForm');
        if (isset($url)) {
            $form->setAction($url);
        }
        $form->setMethod(Form::METHOD_GET);
        $searchTitle = new TextField('searchTitle');
        $searchTitle->addAttribute('placeholder', OW::getLanguage()->text('frmtechnology', 'search_technology_title'));
        if (isset($params['searchTitle'])) {
            $searchTitle->setValue($params['searchTitle']);
        }
        $searchTitle->addAttribute('id', 'technologyStatus');
//        if($searchedTitle!=null) {
//            $searchTitle->setValue($searchedTitle);
//        }
        $searchTitle->setHasInvitation(false);
        $form->addElement($searchTitle);
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmtechnology')->getStaticJsUrl() . 'frmtechnology.js');
        return $form;

    }

    private function getTechnologyListMenu()
    {
        $language = OW::getLanguage();
        $items = array();
        $items[0] = new BASE_MenuItem();
        $items[0]->setLabel($language->text('frmtechnology', 'technology_list_menu_item_latest'))
            ->setKey('latest')
            ->setUrl(OW::getRouter()->urlForRoute('frmtechnology.view-list', array('listType' => 'latest')))
            ->setOrder(1)
            ->setIconClass('ow_ic_clock');
        $items[2] = new BASE_MenuItem();
        $items[2]->setLabel($language->text('frmtechnology', 'technology_list_tags'))
            ->setKey('tags')
            ->setUrl(OW::getRouter()->urlForRoute('frmtechnology.view-list', array('listType' => 'tags')))
            ->setOrder(2)
            ->setIconClass('ow_ic_tag');
        if ($this->isMobile) {
            return new BASE_MCMP_ContentMenu($items);
        } else {
            return new BASE_CMP_ContentMenu($items);
        }
    }

}