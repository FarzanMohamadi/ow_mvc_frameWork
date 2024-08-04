<?php
header('Content-type: application/xml');
class FRMRSS_CTRL_Rss extends OW_ActionController
{

    /**
     * Generate RSS 2.0 feed
     *
     * @return string RSS 2.0 xml
     */
    public function index($params)
    {
        $tag=null;
        if(isset($params['tag']) && $params['tag']!=''){
            $tag=$params['tag'];
            $tag = urldecode($tag);
        }

        //$_POST['tag']
        $siteName = OW::getConfig()->getValue('base', 'site_name');
        $siteDescription = OW::getConfig()->getValue('base', 'site_description') != null ? OW::getConfig()->getValue('base', 'site_description') : '';

        $xml = '<?xml version="1.0" encoding="utf-8"?>' . "\n";

        $xml .= '<rss version="2.0" >' . "\n";

        // channel required properties
        $xml .= '<channel>' . "\n";
        $xml .= '<title>' . $siteName . '</title>' . "\n";
        $xml .= '<link>' . OW::getRouter()->getBaseUrl() . '</link>' . "\n";
        $xml .= '

       /*
        * get only News Rss
        */
        if(FRMSecurityProvider::checkPluginActive('frmnews', true)) {
            $newsService = EntryService::getInstance();
            $rss_items = FRMRSS_BOL_Service::getInstance()->getNewsForRSS($tag,20);
            foreach ($rss_items as $rss_item) {
                $url = OW::getRouter()->urlForRoute('entry', array(
                    'id' => $rss_item->id
                ));
                $mainDescription='';
                $description = nl2br(UTIL_String::truncate(strip_tags($rss_item->entry), 300, '...'));
                if (mb_strlen($rss_item->entry) > 300) {
                    $sentence = $rss_item->entry;
                    $event = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::PARTIAL_HALF_SPACE_CODE_DISPLAY_CORRECTION, array('sentence' => $sentence, 'trimLength' => 300)));
                    if (isset($event->getData()['correctedSentence'])) {
                        $sentence = $event->getData()['correctedSentence'];
                        $sentenceCorrected = true;
                    }
                    $event = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::PARTIAL_SPACE_CODE_DISPLAY_CORRECTION, array('sentence' => $sentence, 'trimLength' => 300)));
                    if (isset($event->getData()['correctedSentence'])) {
                        $sentence = $event->getData()['correctedSentence'];
                        $sentenceCorrected = true;
                    }
                }
                if (isset($sentenceCorrected) && $sentenceCorrected ) {
                    $description = nl2br($sentence . '...');
                }

                if ($rss_item->image) {
                    $imgsrc= $newsService->generateImageUrl($rss_item->image, true);
                    $imageDesc = '<a href="'.$url.'" target="_blank"> <img src="' . $imgsrc . '" hspace="5" vspace="5" class="rssImage" align="left"/> </a>';
                    $mainDescription = $mainDescription.$imageDesc;
                }
                $mainDescription = $mainDescription.$description;
                $xml .= '<item>' . "\n";
                $xml .= '<title><![CDATA[' . $rss_item->title . ']]></title>' . "\n";
                $xml .= '<link>' . $url . '</link>' . "\n";
                $xml .= '
                $xml .= '<pubDate>' . date("F j, Y, g:i a",$rss_item->timestamp ). '</pubDate>' . "\n";
                $xml .= '</item>' . "\n";
            }
        }
        $xml .= '</channel>';

        $xml .= '</rss>';

        exit($xml);

    }

    public function createWithTag(){
        if (OW::getRequest()->isAjax()) {
            $url = OW::getRouter()->urlForRoute('rss_without_parameter');
            if (isset($_POST['tag']) && !empty($_POST['tag'])) {
                $url = OW::getRouter()->urlForRoute('rss_with_parameter', array('tag' => $_POST['tag']));
            }
            exit(json_encode(array('url' => urldecode($url), 'result' => true)));
        }
    }

}


