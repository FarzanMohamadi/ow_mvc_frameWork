<?php
class FRMTERMS_CTRL_Terms extends FRMTERMS_CLASS_ActionController
{

    public function index($params)
    {
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
            $this->assign('archiveUrl', OW::getRouter()->urlForRoute('frmterms.view-archives', array('sectionId' => $sectionId)));

            $cssDir = OW::getPluginManager()->getPlugin("frmterms")->getStaticCssUrl();
            OW::getDocument()->addStyleSheet($cssDir . "save-ajax-order-item.css");
        }else{
            $this->assign('sections', array());
        }
        $this->setDocumentKey("frmterms_page");
    }

    public function viewArchives($params)
    {
        $sectionId = 1;
        if (isset($params['sectionId'])) {
            $sectionId = $params['sectionId'];
        }

        $service = FRMTERMS_BOL_Service::getInstance();
        $maxVersion = $service->getMaxVersion($sectionId);

        $items = $service->getItemsAndVersions($sectionId);
        $versionMarked = array();
        $versions = array();

        foreach ($items as $item) {
            if (!in_array($item->version, $versionMarked)) {
                $versionMarked[] = $item->version;

//                $time_temp = date_create();
//                date_timestamp_set($time_temp, $item->time);
//                $time_temp = date_format($time_temp, 'Y-m-d H:i:s');
                $formattedDate = UTIL_DateTime::formatSimpleDate($item->time);

                $current = false;
                if ($item->version == $maxVersion) {
                    $current = true;
                }
                $versions[] = array(
                    'time' => $formattedDate,
                    'url' => OW::getRouter()->urlForRoute('frmterms.comparison-archive', array('sectionId' => $sectionId, 'version' => $item->version)),
                    'current' => $current
                );
            }
        }

        $this->assign('sections', $service->getClientSections($sectionId));
        $this->assign("header", $service->getPageHeaderLabel($sectionId));
        $this->assign("returnToTermsUrl", OW::getRouter()->urlForRoute('frmterms.index.section-id', array('sectionId' => $sectionId)));
        $this->assign("versions", $versions);
    }

    public function comparisonArchive($params)
    {
        $sectionId = 1;
        if (isset($params['sectionId'])) {
            $sectionId = $params['sectionId'];
        }

        $selectedVersion = 1;
        if (isset($params['version'])) {
            $selectedVersion = $params['version'];
        }

        $service = FRMTERMS_BOL_Service::getInstance();
        $maxVersion = $service->getMaxVersion($sectionId);

        $items = $service->getItemsUsingVersion($maxVersion, $sectionId);
        if (empty($items)) {
            $this->redirect(OW::getRouter()->urlForRoute('frmterms.view-archives', array('sectionId' => $sectionId)));
        }
        $currentItems = array();
        $lastModifiedCurrentVersion = '';
        foreach ($items as $item) {
            $lastModifiedCurrentVersion = $item->time;
            $currentItems[] = array(
                'header' => $item->header,
                'description' => $item->description
            );
        }

        $items = $service->getItemsUsingVersion($selectedVersion, $sectionId);
        if (empty($items)) {
            $this->redirect(OW::getRouter()->urlForRoute('frmterms.view-archives', array('sectionId' => $sectionId)));
        }
        $oldItems = array();
        $lastModifiedOldVersion = '';
        foreach ($items as $item) {
            $lastModifiedOldVersion = $item->time;
            $oldItems[] = array(
                'header' => $item->header,
                'description' => $item->description
            );
        }

//        $lastModifiedCurrentVersionTemp = date_create();
//        date_timestamp_set($lastModifiedCurrentVersionTemp, $lastModifiedCurrentVersion);
//        $lastModifiedCurrentVersionTemp = date_format($lastModifiedCurrentVersionTemp, 'Y-m-d H:i:s');
        $formattedDateCurrentVersion = UTIL_DateTime::formatSimpleDate($lastModifiedCurrentVersion);
        $this->assign('lastModifiedCurrentVersion', $formattedDateCurrentVersion);

//        $lastModifiedOldVersionTemp = date_create();
//        date_timestamp_set($lastModifiedOldVersionTemp, $lastModifiedOldVersion);
//        $lastModifiedOldVersionTemp = date_format($lastModifiedOldVersionTemp, 'Y-m-d H:i:s');
        $formattedDateOldVersion = UTIL_DateTime::formatSimpleDate($lastModifiedOldVersion);
        $this->assign('lastModifiedOldVersion', $formattedDateOldVersion);

        $this->assign('sections', $service->getClientSections($sectionId));
        $this->assign("oldItems", $oldItems);
        $this->assign("currentItems", $currentItems);
        $this->assign("header", $service->getPageHeaderLabel($sectionId));
        $this->assign("returnToArchiveUrl", OW::getRouter()->urlForRoute('frmterms.view-archives', array('sectionId' => $sectionId)));

        $cssDir = OW::getPluginManager()->getPlugin("frmterms")->getStaticCssUrl();
        OW::getDocument()->addStyleSheet($cssDir . "save-ajax-order-item.css");
    }
}