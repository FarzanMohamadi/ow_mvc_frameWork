<?php
/**
 * FRM Rules
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmtelegram
 */

class FRMRULES_MCTRL_Rules extends OW_MobileActionController
{
    public function index($params)
    {
        OW::getDocument()->setHeading(OW::getLanguage()->text('frmrules','rules_mobile'));
        $service = FRMRULES_BOL_Service::getInstance();
        $sectionId = $service->getGuideLineSectionName();
        if (isset($params['sectionId'])) {
            $sectionId = $params['sectionId'];
        }
        $this->assign('sectionId', $sectionId);
        $this->addComponent('menu',$this->getMenu($sectionId));

        if($sectionId == $service->getGuideLineSectionName()) {
            $frmrules_guidline = OW::getConfig()->getValue('frmrules', 'frmrules_guidline');
            if($frmrules_guidline==null){
                $frmrules_guidline = '';
            }
            $this->assign('frmrules_guidline', $frmrules_guidline);
        }else {
            $allItems = $service->getAllItems($sectionId);
            $items = array();
            $categories = array();
            $categoryMarked = array();
            $tags = array();
            $count = 0;
            foreach ($allItems as $item) {
                $count++;
                $category = $service->getCategory($item->categoryId);
                $itemInformation = array(
                    'name' => $item->name,
                    'categoryId' => $item->categoryId,
                    'categoryName' => $category->name,
                    'tag' => $item->tag,
                    'description' => $this->parsRuleDescription($item->description),
                    'numberingLabel' => OW::getLanguage()->text('frmrules', 'numberingLabel', array('value' => $count))
                );
                if ($service->isCountryRuleSection($sectionId)) {
                    $itemInformation['numberingLabel'] = OW::getLanguage()->text('frmrules', 'numberingRuleLabel', array('value' => $count));
                }
                $categoryInformation = array(
                    'name' => $category->name,
                    'id' => $category->id
                );
                if (!empty($item->icon)) {
                    $itemInformation['icon'] = $service->getIconUrl($item->icon);
                }

                if (!empty($category->icon)) {
                    $itemInformation['categoryIcon'] = $service->getIconUrl($category->icon);
                    $categoryInformation['icon'] = $service->getIconUrl($category->icon);
                }

                $explodedTags = explode('.', $item->tag);
                foreach ($explodedTags as $explodedTag) {
                    if (!empty($explodedTag) && !in_array($explodedTag, $tags)) {
                        $tags[] = $explodedTag;
                    }
                }
                $items[] = $itemInformation;
                if (!in_array($category->id, $categoryMarked)) {
                    $categoryMarked[] = $category->id;
                    $categories[] = $categoryInformation;
                }
            }

            $this->assign('itemFloatCss', BOL_LanguageService::getInstance()->getCurrent()->getRtl() ? 'float: right;margin-left: 10px;' : 'float: left;margin-right: 10px;');
            $this->assign('sectionsHeader', $service->getSectionsHeader($sectionId));
            $this->assign('items', $items);
        }
    }

    /**
     * @param $sectionId
     * @return BASE_MCMP_ContentMenu
     */
    private function getMenu($sectionId)
    {
        $service = FRMRULES_BOL_Service::getInstance();
        $menu = new BASE_MCMP_ContentMenu();

        //guideline page
        $menuItem = new BASE_MenuItem();
        $menuItem->setLabel($service->getPageHeaderLabel($service->getGuideLineSectionName()));
        $menuItem->setIconClass($service->getPageHeaderIcon($service->getGuideLineSectionName()));
        $menuItem->setUrl(OW::getRouter()->urlForRoute('frmrules.index.section-id', array('sectionId' => $service->getGuideLineSectionName())));
        $menuItem->setKey($service->getGuideLineSectionName());
        $menuItem->setActive($sectionId == $service->getGuideLineSectionName() ? true : false);
        $menuItem->setOrder(0);
        $menu->addElement($menuItem);

        for ($i = 1; $i <= 3; $i++) {
            $menuItem = new BASE_MenuItem();
            $menuItem->setLabel($service->getPageHeaderLabel($i));
            $menuItem->setIconClass($service->getPageHeaderIcon($i));
            $menuItem->setUrl(OW::getRouter()->urlForRoute('frmrules.index.section-id', array('sectionId' => $i)));
            $menuItem->setKey($i);
            $menuItem->setOrder($i);
            $menuItem->setActive($sectionId == $i ? true : false);
            $menu->addElement($menuItem);
        }
        return $menu;
    }

    public function parsRuleDescription($description)
    {
        $description = str_replace('*','<img style="width: 16px;" src="'.FRMRULES_BOL_Service::getInstance()->getIconUrl('star').'" />',$description);
        return $description;
    }
}