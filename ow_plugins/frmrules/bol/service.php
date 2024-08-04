<?php
/**
 * frmrules
 */

/**
 * frmrules Service.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmrules.bol
 * @since 1.0
 */
final class FRMRULES_BOL_Service
{
    private $sections = ['security' => 1, 'privacy' => 2, 'country' => 3];

    /**
     * @var FRMRULES_BOL_ItemDao
     */
    private $itemDao;

    /**
     * @var FRMRULES_BOL_CategoryDao
     */
    private $categoryDao;

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->itemDao = FRMRULES_BOL_ItemDao::getInstance();
        $this->categoryDao = FRMRULES_BOL_CategoryDao::getInstance();
    }

    /**
     * Singleton instance.
     *
     * @var FRMRULES_BOL_Service
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMRULES_BOL_Service
     */
    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }


    /***
     * @param $name
     * @param $icon
     * @param $sectionId
     * @return FRMRULES_BOL_Category
     */
    public function saveCategory($name, $icon, $sectionId){
        return $this->categoryDao->saveCategory($name, $icon, $sectionId);
    }

    /***
     * @param $catId
     * @param $nameValue
     * @param $descriptionValue
     * @param $tag
     * @param $icon
     * @return FRMRULES_BOL_Item
     */
    public function saveItem($catId, $nameValue, $descriptionValue, $tag, $icon){
        $order = $this->getMaxOrderOfItem($catId) +1;
        return $this->itemDao->saveItem($catId,  $nameValue, $descriptionValue, $order, $tag, $icon);
    }

    /***
     * @param $item
     */
    public function saveItemUsingObject($item)
    {
        $this->itemDao->save($item);
    }

    /***
     * @param $categoryId
     */
    public function deleteCategory($categoryId){
        $items = $this->getItemsByCategory($categoryId);
        foreach($items as $item){
            $this->itemDao->deleteById($item->id);
        }
        $this->categoryDao->deleteById($categoryId);
    }

    /***
     * @param $id
     */
    public function deleteItem($id){
        $this->itemDao->deleteById($id);
    }

    /***
     * @param $categoryId
     * @return FRMRULES_BOL_Category
     */
    public function getCategory($categoryId){
        return $this->categoryDao->getCategory($categoryId);
    }

    /***
     * @param $categoryId
     * @param $name
     * @param $icon
     * @return FRMRULES_BOL_Category
     */
    public function updateCategory($categoryId, $name, $icon){
        return $this->categoryDao->update($categoryId, $name, $icon);
    }

    /***
     * @param $id
     * @return FRMRULES_BOL_Item
     */
    public function getItemById($id)
    {
        return $this->itemDao->getItemById($id);
    }


    /***
     * @param $itemId
     * @param $catId
     * @param $nameValue
     * @param $descriptionValue
     * @param $tag
     * @param $icon
     * @return mixed
     */
    public function updateItem($itemId, $catId,  $nameValue, $descriptionValue, $tag, $icon){
        $order = $this->getMaxOrderOfItem($catId) +1;
        return $this->itemDao->update($itemId, $catId,  $nameValue, $descriptionValue, $tag, $icon, $order);
    }

    /***
     * @param $id
     * @return FRMRULES_BOL_Item
     */
    public function getItem($id){
        return $this->itemDao->getItem($id);
    }

    /***
     * @param $catId
     * @return int|mixed
     */
    public function getMaxOrderOfItem($catId){
        $category = $this->getCategory($catId);
        $categories = $this->getAllCategories($category->sectionId);
        $categoriesIds = array();
        foreach($categories as $category){
            $categoriesIds[] = $category->id;
        }
        return $this->itemDao->getMaxOrder($categoriesIds);
    }

    /***
     * @param $sectionId
     * @param bool $isAdmin
     * @return BASE_CMP_ContentMenu
     */
    public function getSections($sectionId, $isAdmin = true)
    {
        
        $menu = new BASE_CMP_ContentMenu();

        //guidline page
        $menuItem = new BASE_MenuItem();
        $menuItem->setLabel($this->getPageHeaderLabel($this->getGuideLineSectionName()));
        $menuItem->setIconClass($this->getPageHeaderIcon($this->getGuideLineSectionName()));
        $menuItem->setUrl(OW::getRouter()->urlForRoute('frmrules.admin.section-id', array('sectionId' => $this->getGuideLineSectionName())));
        if(!$isAdmin){
            $menuItem->setUrl(OW::getRouter()->urlForRoute('frmrules.index.section-id', array('sectionId' => $this->getGuideLineSectionName())));
        }
        $menuItem->setKey($this->getGuideLineSectionName());
        $menuItem->setActive($sectionId == $this->getGuideLineSectionName() ? true : false);
        $menuItem->setOrder(0);
        $menu->addElement($menuItem);

        for ($i = 1; $i <= 3; $i++) {
            $menuItem = new BASE_MenuItem();
            $menuItem->setLabel($this->getPageHeaderLabel($i));
            $menuItem->setIconClass($this->getPageHeaderIcon($i));
            $menuItem->setUrl(OW::getRouter()->urlForRoute('frmrules.admin.section-id', array('sectionId' => $i)));
            if(!$isAdmin){
                $menuItem->setUrl(OW::getRouter()->urlForRoute('frmrules.index.section-id', array('sectionId' => $i)));
            }
            $menuItem->setKey($i);
            $menuItem->setOrder($i);
            $menuItem->setActive($sectionId == $i ? true : false);
            $menu->addElement($menuItem);
        }

        return $menu;
    }

    /***
     * @param $sectionId
     * @return string
     */
    public function getSectionsHeader($sectionId)
    {
        return OW::getLanguage()->text('frmrules', $this->getPageHeaderKey($sectionId).'_header');
    }

    /***
     * @param $sectionId
     * @return string
     */
    public function getPageHeaderIcon($sectionId){
        if ($sectionId == 1) {
            return 'ow_ic_lock ow_dynamic_color_icon';
        } else if ($sectionId == 2) {
            return 'ow_ic_groups ow_dynamic_color_icon';
        } else if ($sectionId == 3) {
            return 'ow_ic_bookmark ow_dynamic_color_icon';
        }else if ($sectionId == $this->getGuideLineSectionName()) {
            return 'ow_ic_info ow_dynamic_color_icon';
        }
    }

    /***
     * @param $sectionId
     * @return mixed|null|string
     */
    public function getPageHeaderLabel($sectionId)
    {
        return OW::getLanguage()->text('frmrules', $this->getPageHeaderKey($sectionId));
    }

    /***
     * @param $sectionId
     * @return string
     */
    public function getPageHeaderKey($sectionId)
    {
        if ($sectionId == 1) {
            return 'security';
        } else if ($sectionId == 2) {
            return 'privacy';
        } else if ($sectionId == 3) {
            return 'country';
        }else if ($sectionId == $this->getGuideLineSectionName()) {
            return $this->getGuideLineSectionName();
        }
    }

    /***
     * @param $iconName
     * @return string
     */
    public function getIconUrl($iconName){
        return OW::getPluginManager()->getPlugin('frmrules')->getStaticUrl() . 'images/'.$iconName.'.png';
    }

    /***
     * @return array
     */
    public function getIconOptionList(){
        $icons = array();
        $icons[''] = OW::getLanguage()->text('frmrules', 'without_icon');
        $icons['world'] = OW::getLanguage()->text('frmrules', 'icon_world');
        $icons['iran'] = OW::getLanguage()->text('frmrules', 'icon_iran');
        $icons['administrative'] = OW::getLanguage()->text('frmrules', 'icon_administrative');
        $icons['technical'] = OW::getLanguage()->text('frmrules', 'icon_technical');
        $icons['iso'] = OW::getLanguage()->text('frmrules', 'icon_iso');
        return $icons;
    }

    /***
     * @param $action
     * @param $sectionId
     * @param null $nameValue
     * @param null $iconValue
     * @return Form
     */
    public function getCategoryForm($action, $sectionId, $nameValue = null, $iconValue = null){
        $form = new Form('category');
        $form->setAction($action);
        $form->setMethod(Form::METHOD_POST);

        $name = new TextField('name');
        $name->setRequired();
        $name->setLabel(OW::getLanguage()->text('frmrules', 'name'));
        $name->setValue($nameValue);
        $name->setHasInvitation(false);
        $form->addElement($name);

        $iconField = new Selectbox('icon');
        $iconField->setLabel(OW::getLanguage()->text('frmrules', 'icon'));
        $iconField->setOptions($this->getIconOptionList());
        $iconField->setValue($iconValue);
        $iconField->setHasInvitation(false);
        $form->addElement($iconField);

        $sectionIdField = new HiddenField('sectionId');
        $sectionIdField->setValue($sectionId);

        $submit = new Submit('submit');
        $form->addElement($submit);

        return $form;
    }


    /***
     * @param $sectionId
     * @param $action
     * @param null $nameValue
     * @param null $descriptionValue
     * @param null $iconValue
     * @param null $categoryValue
     * @param null $tagValue
     * @return Form
     */
    public function getItemForm($sectionId, $action, $nameValue = null, $descriptionValue = null, $iconValue = null, $categoryValue=null, $tagValue=null){
        $form = new Form('item');
        $form->setAction($action);
        $form->setMethod(Form::METHOD_POST);

        $name = new TextField('name');
        $name->setRequired();
        $name->setValue($nameValue);
        $name->setLabel(OW::getLanguage()->text('frmrules', 'name'));
        $name->setHasInvitation(false);
        $form->addElement($name);

        $description = new Textarea('description');
        $description->setRequired();
        $description->setValue($descriptionValue);
        $description->setLabel(OW::getLanguage()->text('frmrules', 'description'));
        $description->setHasInvitation(false);
        $form->addElement($description);

        $iconField = new Selectbox('icon');
//        $iconField->setRequired();
        $iconField->setLabel(OW::getLanguage()->text('frmrules', 'icon'));
        $iconField->setOptions($this->getIconOptionList());
        $iconField->setValue($iconValue);
        $iconField->setHasInvitation(false);
        $form->addElement($iconField);

        $categoryField = new Selectbox('categoryId');
        $categoryField->setRequired();
        $categoryField->setLabel(OW::getLanguage()->text('frmrules', 'category'));
        $options = array();
        $categories = FRMRULES_BOL_Service::getInstance()->getAllCategories($sectionId);
        foreach($categories as $category){
            $options[$category->id] = $category->name;
        }
        $categoryField->setOptions($options);
        $categoryField->setValue($categoryValue);
        $categoryField->setHasInvitation(false);
        $form->addElement($categoryField);



        $tagField = new TextField('tag');
//        $tagField->setRequired();
        $tagField->setLabel(OW::getLanguage()->text('frmrules', 'tag'));
        $tagField->setValue($tagValue);
        $tagField->setHasInvitation(false);
        $form->addElement($tagField);

        $submit = new Submit('submit');
        $form->addElement($submit);

        return $form;
    }

    /**
     *
     * @param int $sectionId
     * @return array
     */
    public function getAllCategories($sectionId)
    {
        return $this->categoryDao->getAllCategories($sectionId);
    }

    /**
     *
     * @param int $sectionId
     * @return array
     */
    public function getAllItems($sectionId)
    {
        $categories = $this->categoryDao->getAllCategories($sectionId);
        $categoriesId = array();
        foreach($categories as $category){
            $categoriesId[] = $category->id;
        }
        return $this->itemDao->getAllItems($categoriesId);
    }

    /***
     * @param $categoryId
     * @return array
     */
    public function getItemsByCategory($categoryId){
        return $this->itemDao->getItemsByCategory($categoryId);
    }

    /***
     * @param $sectionId
     * @return bool
     */
    public function isCountryRuleSection($sectionId){
        if($sectionId==3){
            return true;
        }

        return false;
    }

    /***
     * @return string
     */
    public function getGuideLineSectionName(){
        return 'guideline';
    }

}