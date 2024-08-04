<?php
class FRMRSS_BOL_Service
{

    const ADD_RSS_COMPONENT = 'news.add.component';
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMRSS_BOL_Service
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {

    }

    public function getNewsForRSS($tag,$rss_items_count=10){
        $entries = array();
        if(FRMSecurityProvider::checkPluginActive('frmsaas', true)){
            $newsService = EntryService::getInstance();
            if(isset($tag)){
                $entries = EntryService::getInstance()->findListByTag(strip_tags(UTIL_HtmlTag::stripTags($tag)),0,$rss_items_count);
            }else {
                $entries = $newsService->findLatestPublicEntries(0, $rss_items_count);
            }
        }
        return $entries;
    }

    /***
     * @return Form
     */
    public function getRssForm(){
        $form = new Form('rss');

        $tagField = new Selectbox('tag');
        $option = array();
        $option[null] = OW::getLanguage()->text('frmrss','select_tag');

        $mostPopularTagsArray = BOL_TagService::getInstance()->findMostPopularTags('news-entry', 50);

        foreach ( $mostPopularTagsArray as $tag )
        {
            if (trim($tag['label']) != '') {
                $option[$tag['id']] = $tag['label'];
            }
        }
        $tagField->setHasInvitation(false);
        $tagField->setOptions($option);
        $form->addElement($tagField);

        return $form;
    }

    public function addRssComponent(OW_Event $event){
        $params = $event->getParams();
        if(isset($params['newsController'])){
            $params['newsController']->assign('hasRss',true);
            $plugin = OW::getPluginManager()->getPlugin('frmrss');
            OW::getLanguage()->addKeyForJs('frmrss', 'rss_float_box_title');
            OW::getDocument()->addScript($plugin->getStaticJsUrl() . 'frmrss.js');
            $staticCssUrl = OW::getPluginManager()->getPlugin('frmrss')->getStaticCssUrl();
            OW::getDocument()->addStyleSheet($staticCssUrl . 'frmrss.css');
        }
    }
}