<?php
class FRMTERMS_CMP_TermsFloatBox extends OW_Component
{
    public function __construct($params)
    {
        parent::__construct();
        $service = FRMTERMS_BOL_Service::getInstance();
        $sectionId = -1;
        $firstFilledSection = $service->getFirstFilledSection();

        if (isset($params['sectionId'])) {
            $sectionId = $params['sectionId'];
        }else if($firstFilledSection!=-1){
            $sectionId = $firstFilledSection;
        }

        if($sectionId != -1) {
            if(OW::getConfig()->getValue('frmterms', 'terms' . $sectionId)==false){
                throw new Redirect404Exception();
            }

            $maxVersion = $service->getMaxVersion($sectionId);
            $items = $service->getItemsUsingVersion($maxVersion, $sectionId);
            $activeItems = array();
            $headersOfActiveItems = array();
            $lastModified = '';

            foreach ($items as $item) {
                $lastModified = $item->time;
                $activeItems[] = array(
                    'header' => $item->header,
                    'description' => $item->description,
                    'id' => 'header_terms_' . $item->id
                );
                if ($item->header != null) {
                    $headersOfActiveItems[] = array(
                        'name' => $item->header,
                        'id' => 'header_terms_' . $item->id
                    );
                }
            }

            $formattedDate = UTIL_DateTime::formatSimpleDate($lastModified);
            $this->assign('lastModified', OW::getLanguage()->text('frmterms', 'release_date_label',array('value' => $formattedDate)));

            $this->assign('sections', $service->getClientSections($sectionId));
            $this->assign("items", $activeItems);
            $this->assign("headersOfActiveItems", $headersOfActiveItems);

            $cssDir = OW::getPluginManager()->getPlugin("frmterms")->getStaticCssUrl();
            OW::getDocument()->addStyleSheet($cssDir . "save-ajax-order-item.css");
        }else{
            $this->assign('sections', array());
        }
    }
}