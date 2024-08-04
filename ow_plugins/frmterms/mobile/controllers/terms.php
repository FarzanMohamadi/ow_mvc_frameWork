<?php
class FRMTERMS_MCTRL_Terms extends OW_MobileActionController
{

    public function index($params)
    {
        OW::getDocument()->setHeading(OW::getLanguage()->text('frmterms','mobile_bottom_menu_item'));
        $service = FRMTERMS_BOL_Service::getInstance();
        $sectionId = -1;
        $firstFilledSection = $service->getFirstFilledSection();

        if (isset($params['sectionId'])) {
            $sectionId = $params['sectionId'];
        }else if($firstFilledSection!=-1){
            $sectionId = $firstFilledSection;
        }

        if($sectionId != -1) {
            $maxVersion = $service->getMaxVersion($sectionId);
            $items = $service->getItemsUsingVersion($maxVersion, $sectionId);
            $activeItems = array();
            $headersOfActiveItems = array();
            $lastModified = "";

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

            if($lastModified!=""){
                $formattedDate = UTIL_DateTime::formatSimpleDate($lastModified);
                $this->assign('lastModified', OW::getLanguage()->text('frmterms', 'release_date_label',array('value' => $formattedDate)));
            }
            $this->assign('sections', $service->getClientSections($sectionId));
            $this->assign("items", $activeItems);
            $cssDir = OW::getPluginManager()->getPlugin("frmterms")->getStaticCssUrl();
            OW::getDocument()->addStyleSheet($cssDir . "save-ajax-order-item.css");

            $this->addComponent('menu',$this->getMenu($sectionId));
            $this->assign('headerLabel', $service->getPageHeaderLabel($sectionId));
        }else{
            $this->assign('sections', array());
        }
    }

    /**
     * @param $sectionId
     * @return BASE_MCMP_ContentMenu
     */
    private function getMenu($sectionId)
    {
        $sections = FRMTERMS_BOL_Service::getInstance()->getClientSections($sectionId);

        $menuItems = array();
        $order = 0;
        foreach ( $sections as $section )
        {
            $item = new BASE_MenuItem();
            $item->setLabel($section['label']);
            $item->setUrl($section['url']);
            $item->setOrder($order);
            $item->setKey($section['sectionId']);
            if($section['sectionId'] == $sectionId) {
                $item->setActive(true);
            }
            array_push($menuItems, $item);
            $order++;
        }

        $menu = new BASE_MCMP_ContentMenu($menuItems);
        return $menu;
    }
}