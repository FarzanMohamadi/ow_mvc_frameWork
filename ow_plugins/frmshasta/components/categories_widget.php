<?php
/**
 * FRMSHASTA widget
 *
 * @since 1.0
 */
class FRMSHASTA_CMP_CategoriesWidget extends BASE_CLASS_Widget
{

    /**
     * FRMSHASTA_CMP_CategoriesWidget constructor.
     * @param BASE_CLASS_WidgetParameter $params
     */
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();
        $this->assignList($params);
        FRMSHASTA_BOL_Service::getInstance()->addStaticFiles();
    }

    private function assignList($params)
    {

        if (!OW::getUser()->isAuthenticated()) {
            return null;
        }
        $service = FRMSHASTA_BOL_Service::getInstance();
        $categoriesId = array();

        $userCategoriesValue = $service->getUserCategories(OW::getUser()->getId());

        foreach ($userCategoriesValue as $cat) {
            $categoriesId[] = $cat->id;
        }

        if ($userCategoriesValue == null || sizeof($userCategoriesValue) == 0) {
            $allCategories = $service->getAllCategories();
            foreach ($allCategories as $cat) {
                $categoriesId[] = $cat->id;
            }
        }

        $categoryComponents = array();

        foreach ($categoriesId as $categoryId) {
            $categoryCmp = new FRMSHASTA_CMP_Category(array('categoryId' => $categoryId));
            $this->addComponent('cat'.$categoryId, $categoryCmp);
            $categoryComponents[] = 'cat'.$categoryId;
        }

        $this->assign('categoryComponents', $categoryComponents);

        $service->addStaticFiles();

        $this->assign('addCategoryUrl', OW::getRouter()->urlForRoute('frmshasta_add_category'));

        $service = FRMSHASTA_BOL_Service::getInstance();
        if ($service->hasUserAccessManager()) {
            $this->assign('addCompany', true);
            $this->assign('customizeSpecialCategory', true);
            $this->assign('reportsUrl', OW::getRouter()->urlForRoute('frmshasta_reports'));
            $this->assign('addCategory',true);
        }
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_TITLE => OW_Language::getInstance()->text('frmshasta', 'categories'),
            self::SETTING_ICON => self::ICON_USER
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }
}