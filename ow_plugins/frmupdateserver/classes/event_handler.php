<?php
class FRMUPDATESERVER_CLASS_EventHandler
{

    public function __construct()
    {
        
    }

    /**
     * Get sitemap urls
     *
     * @param OW_Event $event
     * @return void
     */
    public function onSitemapGetUrls( OW_Event $event )
    {
        $params = $event->getParams();

        $urls   = array();

        switch ( $params['entity'] ) {
            case 'frmupdateserver_download' :
                $urls[] = OW::getRouter()->urlForRoute('frmupdateserver.index');
                break;
        }
        if ($urls) {
            $event->setData($urls);
        }

    }

    public function onBeforeDocumentRender( OW_Event $event )
    {
        $cssDir = OW::getPluginManager()->getPlugin("frmupdateserver")->getStaticCssUrl();
        OW::getDocument()->addStyleSheet($cssDir . "frmupdateserver.css");

        $jsDir = OW::getPluginManager()->getPlugin("frmupdateserver")->getStaticJsUrl().'frmupdateserver.js';
        OW::getDocument()->addScript($jsDir);

        OW::getLanguage()->addKeyForJs('frmupdateserver', 'download_directly');
        OW::getLanguage()->addKeyForJs('frmupdateserver', 'wait_for_download');
        OW::getLanguage()->addKeyForJs('frmupdateserver', 'files');
    }

    public function onBeforePostRequestFailForCSRF(OW_Event $event){
        $url = $_SERVER['REQUEST_SCHEME'] . '://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $passPathsKey = array();
        $passPathsKey[] = 'server';
        $passPathsKey[] = 'server.get_item_info';
        $passPathsKey[] = 'server.get_item';
        $passPathsKey[] = 'server.platform_info';
        $passPathsKey[] = 'server.download_platform';
        $passPathsKey[] = 'server.download_full_platform';
        $passPathsKey[] = 'server.get_items_update_info';
        $passPathsKey[] = 'server.update_static_files';
        $passPathsKey[] = 'server.check_all_for_update';
        $passPathsKey[] = 'frmupdateserver.data-post-url';
        $passPathsKey[] = 'frmupdateserver.download-file';

        foreach ($passPathsKey as $passPath){
            $passPath = OW::getRouter()->urlForRoute($passPath);
            if(strpos($url, $passPath)==0){
                $event->setData(array('pass' => true));
                return;
            }
        }
    }

    public function init()
    {
        $this->genericInit();
    }
    public function genericInit()
    {
        OW::getEventManager()->bind("base.sitemap.get_urls", array($this, "onSitemapGetUrls"));
        OW::getEventManager()->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($this, "onBeforeDocumentRender"));
        OW::getEventManager()->bind("on.before.post.request.fail.for.csrf", array($this, "onBeforePostRequestFailForCSRF"));
    }

}