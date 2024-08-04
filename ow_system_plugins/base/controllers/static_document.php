<?php
/**
 * @package ow_system_plugins.base.controllers
 * @since 1.0
 */
class BASE_CTRL_StaticDocument extends OW_ActionController
{
    /**
     * @var BOL_NavigationService
     */
    private $navService;

    public function __construct()
    {
        parent::__construct();
        $this->navService = BOL_NavigationService::getInstance();
    }

    public function index( $params )
    {
        if ( empty($params['documentKey']) )
        {
            throw new Redirect404Exception();
        }

        $language = OW::getLanguage();
        $documentKey = $params['documentKey'];

        $document = $this->navService->findDocumentByKey($documentKey);

        if ( $document === null )
        {
            throw new Redirect404Exception();
        }

        $menuItem = $this->navService->findMenuItemByDocumentKey($document->getKey());

        if ( $menuItem !== null )
        {
            if ( !$menuItem->getVisibleFor() || ( $menuItem->getVisibleFor() == BOL_NavigationService::VISIBLE_FOR_GUEST && OW::getUser()->isAuthenticated() ) )
            {
                throw new Redirect404Exception();
            }

            if ( $menuItem->getVisibleFor() == BOL_NavigationService::VISIBLE_FOR_MEMBER && !OW::getUser()->isAuthenticated() )
            {
                throw new AuthenticateException();
            }
        }

        $this->assign('content', $language->text('base', "local_page_content_{$document->getKey()}"));
        $this->setPageHeading($language->text('base', "local_page_title_{$document->getKey()}"));
        $this->setPageTitle($language->text('base', "local_page_title_{$document->getKey()}"));
        $this->documentKey = $document->getKey();

        $this->setDocumentKey($document->getKey());

        OW::getEventManager()->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($this, 'setCustomMetaInfo'));
        $eventForEnglishFieldSupport = new OW_Event('frmmultilingualsupport.show.data.in.multilingual', array('display'=>'Content','pageController' => $this,'entityType'=>'page','entityId'=>$menuItem->getId()));
        OW::getEventManager()->trigger($eventForEnglishFieldSupport);
    }

    public function setCustomMetaInfo()
    {
        OW::getDocument()->setDescription(null);

        if ( OW::getLanguage()->valueExist('base', "local_page_meta_desc_{$this->getDocumentKey()}") )
        {
            OW::getDocument()->setDescription(OW::getLanguage()->text('base', "local_page_meta_desc_{$this->getDocumentKey()}"));
        }

        if ( OW::getLanguage()->valueExist('base', "local_page_meta_keywords_{$this->getDocumentKey()}") )
        {
            OW::getDocument()->setKeywords(OW::getLanguage()->text('base', "local_page_meta_keywords_{$this->getDocumentKey()}"));
        }
    }
}
