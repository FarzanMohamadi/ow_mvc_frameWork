<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.frmnews.mobile.controllers
 * @since 1.6.0
 */
class FRMNEWS_MCTRL_News extends OW_MobileActionController
{
    /**
     * @param $params
     * @throws Redirect404Exception
     */
    public function index($params)
    {
        if(!isset($params['list'])){
            $params['list'] = 'latest';
        }
        $this->setPageTitle(OW::getLanguage()->text('frmnews', 'index_page_title'));
        $this->setPageHeading(OW::getLanguage()->text('frmnews', 'index_page_heading'));
        OW::getDocument()->addStyleSheet(OW_PluginManager::getInstance()->getPlugin("frmnews")->getStaticCssUrl() . 'news.css');
        OW::getDocument()->setHeading(OW::getLanguage()->text('frmnews', 'index_page_heading'));
        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;

        if ( !OW::getUser()->isAdmin() && !OW::getUser()->isAuthorized('frmnews', 'view') && !OW::getUser()->isAuthorized('frmnews'))
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('frmnews', 'view');
            throw new AuthorizationException($status['msg']);
        }
        $addNew_promoted = false;
        $addNew_isAuthorized = false;
        if (OW::getUser()->isAuthenticated())
        {
            if (OW::getUser()->isAuthorized('frmnews', 'add') || OW::getUser()->isAdmin())
            {
                $addNew_isAuthorized = true;
                $this->assign('my_drafts_url', OW::getRouter()->urlForRoute('news-manage-drafts'));
            }
            else
            {
                $status = BOL_AuthorizationService::getInstance()->getActionStatus('frmnews', 'add');
                if ($status['status'] == BOL_AuthorizationService::STATUS_PROMOTED)
                {
                    $addNew_promoted = true;
                    $addNew_isAuthorized = true;
                    $script = '$("#btn-add-new-entry").click(function(){
                        OW.authorizationLimitedFloatbox('.json_encode($status['msg']).');
                        return false;
                    });';
                    OW::getDocument()->addOnloadScript($script);
                }
                else
                {
                    $addNew_isAuthorized = false;
                }
            }
        }
        $addNew_isAuthorized = false;
        if(OW::getUser()->isAuthorized('frmnews', 'add') || OW::getUser()->isAdmin()){
            $addNew_isAuthorized = true;
        }
        $this->assign('addNew_isAuthorized', $addNew_isAuthorized);
        $this->assign('addNew_promoted', $addNew_promoted);


        $service = EntryService::getInstance();
        $rpp = (int) OW::getConfig()->getValue('frmnews', 'results_per_page');

        $first = ($page - 1) * $rpp;
        $count = $rpp;
        $case = $params['list'];
        if ( !in_array($case, array( 'latest', 'browse-by-tag', 'most-discussed', 'top-rated', 'search-results' )) )
        {
            throw new Redirect404Exception();
        }
        $showList = true;
        $isBrowseByTagCase = $case == 'browse-by-tag';
        $isSearchResultsCase = $case == 'search-results';

        $contentMenu = $this->getContentMenu();
        if(!$isSearchResultsCase) {
            $contentMenu->setItemActive($case);
        }
        $this->addComponent('menu', $contentMenu );
        $this->assign('listType', $case);

        $this->assign('isBrowseByTagCase', $isBrowseByTagCase);
        $this->assign('isSearchResultsCase', $isSearchResultsCase);
        if($isSearchResultsCase) {
            $q = UTIL_HtmlTag::escapeHtml($_GET['q']);
            $this->assign('q', $q );
        }

        $tagSearch = new BASE_MCMP_Search(OW::getRouter()->urlForRoute('frmnews.list', array('list'=>'browse-by-tag')),
            'base+tag_search', 'tag',OW::getLanguage()->text('frmnews', 'search_by_tag_placeholder'));
        $this->addComponent('tagSearch', $tagSearch);

        $entrySearch = new BASE_MCMP_Search(OW::getRouter()->urlForRoute('frmnews.list', array('list'=>'search-results')),
            'frmnews+search_entries', 'q', OW::getLanguage()->text('frmnews', 'search_by_entry_placeholder'));
        $this->addComponent('entrySearch', $entrySearch);

        $tagCount = null;
        if ( $isBrowseByTagCase )
        {
            $tagCount = 1000;
        }

        $tagCloud = new BASE_CMP_EntityTagCloud('news-entry', OW::getRouter()->urlForRoute('frmnews.list', array('list'=>'browse-by-tag')), $tagCount);

        if ( $isBrowseByTagCase )
        {
            $tagCloud->setTemplate(OW::getPluginManager()->getPlugin('base')->getCmpViewDir() . 'big_tag_cloud.html');

            $tag = !(empty($_GET['tag'])) ? strip_tags(UTIL_HtmlTag::stripTags($_GET['tag'])) : '';
            $this->assign('tag', $tag );

            if (empty($tag))
            {
                $showList = false;
            }
        }
        $this->addComponent('tagCloud', $tagCloud);


        $this->assign('showList', $showList);

        $list = array();
        $itemsCount = 0;

        list($list, $itemsCount) = $service->getEntryList($case, $first, $count);

        $entrys = array();

        $toolbars = array();

        $userService = BOL_UserService::getInstance();

        $authorIdList = array();

        $previewLength = 50;
        $tagsLabel =  array();
        foreach ( $list as $item )
        {
            $dto = $item['dto'];
            $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_AFTER_NEWSFEED_STATUS_STRING_READ,array('string' => $dto->getEntry())));
            if(isset($stringRenderer->getData()['string'])){
                $dto->setEntry($stringRenderer->getData()['string']);
            }
            $dto->setEntry($dto->getEntry());
            $dto->setTitle( UTIL_String::truncate( strip_tags($dto->getTitle()), 350, '...' )  );
            $authorDisplayName = $userService->getDisplayName($dto->getAuthorId());

            $text = explode("<!--more-->", $dto->getEntry());

            $isPreview = count($text) > 1;

            if ( !$isPreview )
            {
                $text = explode('<!--page-->', $text[0]);
                $showMore = count($text) > 1;
            }
            else
            {
                $showMore = true;
            }

            $text = $text[0];
            $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_RENDER_STRING, array('string' => $text)));
            if (isset($stringRenderer->getData()['string'])) {
                $text = ($stringRenderer->getData()['string']);
            }

            $commentService = BOL_CommentService::getInstance();
            $commentCount = $commentService->findCommentCount('news-entry',$dto->getId());
            $userUrl = $userService->getUserUrl($dto->getAuthorId());
            $tags = BOL_TagService::getInstance()->findEntityTags($dto->getId(),'news-entry');
            if(sizeof($tags)>0){
                $labels = " ";
                $comma = OW::getLanguage()->text('base', 'comma').' ';
                foreach($tags as $tag)
                {
                    $labels .= '<a href="'.OW::getRouter()->urlForRoute('frmnews.list', array('list'=>'browse-by-tag')) . "?tag=".$tag->getLabel().'">'.$tag->getLabel().'</a>'.$comma;
                }
                $labels = rtrim($labels, $comma);
                $tagsLabel[$dto->getId()]=$labels;
            }
            $imageSource =  $service->generateImageUrl($dto->image, true);
            $entrys[] = array(
                'dto' => $dto,
                'text' => $text,
                'showMore' => $showMore,
                'url' => OW::getRouter()->urlForRoute('user-entry', array('id'=>$dto->getId())),
                'author' =>$authorDisplayName,
                'commentCount' =>$commentCount,
                'userUrl'=>$userUrl,
                'imageSrc' => $imageSource,
                'imageInfo' => BOL_AvatarService::getInstance()->getAvatarInfo($dto->id, $imageSource),
                'imageTitle' => $dto->getTitle()
            );

            $authorIdList[] = $dto->authorId;
            $idList[] = $dto->getId();
        }
        $this->assign('tags', $tagsLabel);
        if ( !empty($idList) )
        {
            $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($authorIdList, true, false);
            $this->assign('avatars', $avatars);

            $nlist = array();
            foreach ( $avatars as $userId => $avatar )
            {
                $nlist[$userId] = $avatar['title'];
            }
            $urls = BOL_UserService::getInstance()->getUserUrlsForList($authorIdList);
            $this->assign('toolbars', $this->getToolbar($idList, $list, $urls, $nlist));
        }

        $this->assign('list', $entrys);
        $this->assign('url_new_entry', OW::getRouter()->urlForRoute('entry-save-new'));

        $paging = new BASE_CMP_PagingMobile($page, ceil($itemsCount / $rpp), 5);

        $this->addComponent('paging', $paging);

        OW::getEventManager()->trigger(new OW_Event(EntryService::ON_BEFORE_NEWS_LIST_VIEW_RENDER, array('newsView' => $this)));
    }

    /**
     * @return BASE_MCMP_ContentMenu
     */
    private function getContentMenu()
    {
        $menuItems = array();

        $listNames = array(
            'latest' => array('iconClass' => 'ow_ic_clock'),
//            'most-discussed' => array('iconClass' => 'ow_ic_comment'),
//            'top-rated' => array('iconClass' => 'ow_ic_star'),
            'browse-by-tag' => array('iconClass' => 'ow_ic_tag')
        );

        $i=0;
        foreach ( $listNames as $listKey => $listArr )
        {
            $menuItem = new BASE_MenuItem();
            $menuItem->setKey($listKey);
            $menuItem->setUrl(OW::getRouter()->urlForRoute('frmnews.list', array('list' => $listKey)));
            $menuItemKey = explode('-', $listKey);
            $listKey = "";
            foreach ($menuItemKey as $key)
            {
                $listKey .= strtoupper(substr($key, 0, 1)).substr($key, 1);
            }

            $menuItem->setLabel(OW::getLanguage()->text('frmnews', 'menuItem'.$listKey));
            $menuItem->setIconClass($listArr['iconClass']);
            $menuItem->setOrder($i++);
            $menuItems[] = $menuItem;
        }

        return new BASE_MCMP_ContentMenu($menuItems);
    }

    private function getToolbar( $idList, $list, $ulist, $nlist )
    {
        if ( empty($idList) )
        {
            return array();
        }

        $info = array();

        $info['comment'] = BOL_CommentService::getInstance()->findCommentCountForEntityList('news-entry', $idList);

        $info['rate'] = BOL_RateService::getInstance()->findRateInfoForEntityList('news-entry', $idList);

        $info['tag'] = BOL_TagService::getInstance()->findTagListByEntityIdList('news-entry', $idList);

        $toolbars = array();

        foreach ( $list as $item )
        {
            $id = $item['dto']->id;

            $toolbars[$id] = array(
                array(
                    'class' => 'ow_ipc_date',
                    'label' => UTIL_DateTime::formatDate($item['dto']->timestamp)
                ),
            );

            if ( $info['rate'][$id]['avg_score'] > 0 )
            {
                $toolbars[$id][] = array(
                    'label' => OW::getLanguage()->text('frmnews', 'rate') . ' <span class="ow_txt_value">' . ( ( $info['rate'][$id]['avg_score'] - intval($info['rate'][$id]['avg_score']) == 0 ) ? intval($info['rate'][$id]['avg_score']) : sprintf('%.2f', $info['rate'][$id]['avg_score']) ) . '</span>',
                );
            }

            if ( !empty($info['comment'][$id]) )
            {
                $toolbars[$id][] = array(
                    'label' => OW::getLanguage()->text('frmnews', 'comments') . ' <span class="ow_txt_value">' . $info['comment'][$id] . '</span>',
                );
            }


            if ( empty($info['tag'][$id]) )
            {
                continue;
            }

            $value = "<span class='ow_wrap_normal'>" . OW::getLanguage()->text('frmnews', 'tags') . ' ';

            foreach ( $info['tag'][$id] as $tag )
            {
                $value .='<a href="' . OW::getRouter()->urlForRoute('frmnews.list', array('list'=>'browse-by-tag')) . "?tag={$tag}" . "\">{$tag}</a>, ";
            }

            $value = mb_substr($value, 0, mb_strlen($value) - 2);
            $value .= "</span>";
            $toolbars[$id][] = array(
                'label' => $value,
            );
        }

        return $toolbars;
    }
}

