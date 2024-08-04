<?php
class FRMRULES_CLASS_EventHandler
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
            case 'rule_list' :
                $sectionId = FRMRULES_BOL_Service::getInstance()->getGuideLineSectionName();
                $urls[] = OW::getRouter()->urlForRoute('frmrules.index.section-id', array(
                    'sectionId' => $sectionId
                ));
                break;
        }
        if ($urls) {
            $event->setData($urls);
        }
    }

    public function init()
    {
        $this->genericInit();
    }
    public function genericInit()
    {
        OW::getEventManager()->bind("base.sitemap.get_urls", array($this, "onSitemapGetUrls"));
    }

}