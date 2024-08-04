<?php
/**
 * User: Elahe
 * Date: 2/27/2019
 * Time: 9:09 PM
 */


class FRMTICKETING_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function __construct()
    {
        parent::__construct();
        $this->setPageHeadingIconClass('ow_ic_gear_wheel');
    }

    /**
     * Default action
     */

    const CATEGORY_SECTION=1;
    const ORDER_SECTION=2;
    const Category_Users_SECTION=3;
    const CATEGORY_TYPE='category';
    const ORDER_TYPE='order';
    const CATEGORY_USERS_TYPE='category_users';
    public function index(array $params = array())
    {
        $language = OW::getLanguage();
        $this->setPageHeading($language->text('frmticketing', 'settings_page_heading'));
        $this->setPageTitle($language->text('frmticketing', 'settings_page_title'));
        $currentSectionFromParams = null;
        if(isset($params['currentSection'])) {
            $currentSectionFromParams = $params['currentSection'];
        }
        $sectionsInformation = FRMTICKETING_BOL_TicketService::getInstance()->getSections($currentSectionFromParams);
        $sections = $sectionsInformation['sections'];
        $currentSection = $sectionsInformation['currentSection'];
        $this->assign('sections',$sections);
        $this->assign('currentSection',$currentSection);

        $plugin = OW::getPluginManager()->getPlugin('frmticketing');
        OW::getDocument()->addScript($plugin->getStaticJsUrl() . "ticket.js");

        if($currentSection==1) {
            $categoryService= FRMTICKETING_BOL_TicketCategoryService::getInstance();
            $language = OW::getLanguage();
            $form = $this->generateSettingsForm('categorySettings', $currentSection);
            $this->addForm($form);

            # Active Categories Table
            $activeTicketListCategory=array();
            $activeTicketCategories = $categoryService->getTicketCategoryListByStatus('active');
            $deactivateUrls = array();
            $editUrls = array();
            foreach ($activeTicketCategories as $ticketCategory) {
                $editUrls[$ticketCategory->id] =  "OW.ajaxFloatBox('FRMTICKETING_CMP_EditCategoryFloatBox', {id: ".$ticketCategory->id."} , {iconClass: 'ow_ic_edit', title: '".$language->text('frmticketing', 'edit_item')."'})";

                $activeTicketListCategory[$ticketCategory->id]['name'] = $ticketCategory->id;
                $activeTicketListCategory[$ticketCategory->id]['title'] = $ticketCategory->title;
                $deactivateUrls[$ticketCategory->id] = OW::getRouter()->urlFor(__CLASS__, 'deactivateItem', array('id' => $ticketCategory->id));
            }
            $this->assign('activeTicketListCategory', $activeTicketListCategory);
            $this->assign('deactivateUrls', $deactivateUrls);
            $this->assign('editUrls',$editUrls);

            # Inactive Categories Table
            $inactiveTicketListCategory= array();
            $inactiveTicketCategories = $categoryService->getTicketCategoryListByStatus('inactive');
            $deletePermanentUrls = array();
            $activateUrls = array();
            foreach ($inactiveTicketCategories as $ticketCategory) {
                $inactiveTicketListCategory[$ticketCategory->id]['name'] = $ticketCategory->id;
                $inactiveTicketListCategory[$ticketCategory->id]['title'] = $ticketCategory->title;

                $activateUrls[$ticketCategory->id] = OW::getRouter()->urlFor(__CLASS__, 'activateItem', array('id' => $ticketCategory->id));
                $deletePermanentUrls[$ticketCategory->id] = OW::getRouter()->urlFor(__CLASS__, 'deleteItem', array('id' => $ticketCategory->id,'type'=>self::CATEGORY_TYPE));
            }
            $this->assign('inactiveTicketListCategory', $inactiveTicketListCategory);
            $this->assign('permanentlyDeleteUrls', $deletePermanentUrls);
            $this->assign('activateUrls',$activateUrls);

            if (OW::getRequest()->isPost()) {
                if ($form->isValid($_POST)) {
                    $data = $form->getValues();
                    $categoryService->addCategory($data['title']);
                    OW::getFeedback()->info($language->text('frmticketing', 'settings_saved_successfully'));
                    $this->redirect();
                }
            }
        }
        else if($currentSection==2) {
            $orderService= FRMTICKETING_BOL_TicketOrderService::getInstance();
            $language = OW::getLanguage();
            $form = $this->generateSettingsForm('orderSettings', $currentSection);
            $this->addForm($form);

            $deleteUrls = array();
            $ticketListOrder=array();
            $ticketOrders = $orderService->getTicketOrderList();
            $editUrls = [];
            foreach ($ticketOrders as $ticketOrder) {
                $editUrls[$ticketOrder->id] =  "OW.ajaxFloatBox('FRMTICKETING_CMP_EditOrderFloatBox', {id: ".$ticketOrder->id."} , {iconClass: 'ow_ic_edit', title: '".$language->text('frmticketing', 'edit_item_page_title')."'})";

                $ticketListOrder[$ticketOrder->id]['name'] = $ticketOrder->id;
                $ticketListOrder[$ticketOrder->id]['title'] = $ticketOrder->title;
                $deleteUrls[$ticketOrder->id] = OW::getRouter()->urlFor(__CLASS__, 'deleteItem', array('id' => $ticketOrder->id,'type'=>self::ORDER_TYPE));
            }
            $this->assign('ticketListOrder', $ticketListOrder);
            $this->assign('deleteUrls', $deleteUrls);
            $this->assign('editUrls',$editUrls);

            if (OW::getRequest()->isPost()) {
                if ($form->isValid($_POST)) {
                    $data = $form->getValues();
                    $orderService->addOrder($data['title']);
                    OW::getFeedback()->info($language->text('frmticketing', 'settings_saved_successfully'));
                    $this->redirect();
                }
            }
        }
        else if($currentSection==3){
            $categoryUserService = FRMTICKETING_BOL_TicketCategoryUserService::getInstance();
            $language = OW::getLanguage();
            $form = $this->generateSettingsForm('categoryUserSetting', $currentSection);
            $this->addForm($form);

            $this->assign('autocompleteUrl',OW::getRouter()->urlFor(__CLASS__, 'autoCompleteUsernames'));
            $activeCategoryUsers = $categoryUserService->findAllCategoryUsersByStatus('active');
            $this->assign('activeCategoryUsersList', $activeCategoryUsers);

            $inactiveCategoryUsers = $categoryUserService->findAllCategoryUsersByStatus('inactive');
            $this->assign('inactiveCategoryUsersList', $inactiveCategoryUsers);

            $this->assign('deleteUserForCategoryUrl', OW::getRouter()->urlFor(__CLASS__, 'deleteItem',array('type'=>self::CATEGORY_USERS_TYPE)));
            if (OW::getRequest()->isPost()) {
                if ($form->isValid($_POST)) {
                    $data = $form->getValues();
                    $categoryUserService->addUserToCategory($data['username'], $data['category']);
                    OW::getFeedback()->info($language->text('frmticketing', 'settings_saved_successfully'));
                    $this->redirect();
                }
            }
        }
    }

    /**
     * Generates Order Settings form.
     *
     * @param $currentSection
     * @param $name
     * @return Form
     */
    private function generateSettingsForm( $name, $currentSection )
    {
        switch ($name)
        {
            case 'categorySettings':
                $form = new FRMTICKETING_CLASS_CategorySettingsForm($name);
                break;
            case 'orderSettings':
                $form = new FRMTICKETING_CLASS_OrderSettingsForm($name);
                break;
            case 'categoryUserSetting':
                $form = new FRMTICKETING_CLASS_CategoryUserSettingsForm($name);
                break;
            default:
                return null;
        }
        $form->setAjax(false);
        $form->setAction(
            OW::getRouter()->urlForRoute(
                'frmticketing.admin-currentSection',array('currentSection'=>$currentSection)
            )
        );
        return $form;
    }

    public function deleteItem( $params )
    {
        switch ($params['type'])
        {
            case self::CATEGORY_TYPE:
                if ( isset($params['id']))
                {
                    FRMTICKETING_BOL_TicketCategoryService::getInstance()->deleteCategory((int) $params['id']);
                }
                OW::getFeedback()->info(OW::getLanguage()->text('frmticketing', 'database_record_deleted'));
                $this->redirect(OW::getRouter()->urlForRoute('frmticketing.admin-currentSection',array('currentSection'=>self::CATEGORY_SECTION)));
                break;
            case self::ORDER_TYPE:
                if ( isset($params['id']))
                {
                    FRMTICKETING_BOL_TicketOrderService::getInstance()->deleteOrder((int) $params['id']);
                }
                OW::getFeedback()->info(OW::getLanguage()->text('frmticketing', 'database_record_deleted'));
                $this->redirect(OW::getRouter()->urlForRoute('frmticketing.admin-currentSection',array('currentSection'=>self::ORDER_SECTION)));
                break;
            case self::CATEGORY_USERS_TYPE:
                if ( isset($params['catId']) && isset($params['username']))
                {
                    FRMTICKETING_BOL_TicketCategoryUserService::getInstance()->deleteByCategoryIdAndUsername($params['catId'],$params['username']);
                }
                OW::getFeedback()->info(OW::getLanguage()->text('frmticketing', 'database_record_deleted'));
                $this->redirect(OW::getRouter()->urlForRoute('frmticketing.admin-currentSection',array('currentSection'=>self::Category_Users_SECTION)));
                break;
        }
    }

    public function activateItem( $params )
    {
        if ( isset($params['id']))
        {
            FRMTICKETING_BOL_TicketCategoryService::getInstance()->activateCategory((int) $params['id']);
        }
        OW::getFeedback()->info(OW::getLanguage()->text('frmticketing', 'category_activated_successfully'));
        $this->redirect(OW::getRouter()->urlForRoute('frmticketing.admin-currentSection',array('currentSection'=>self::CATEGORY_SECTION)));
    }

    public function deactivateItem( $params )
    {
        if ( isset($params['id']))
        {
            FRMTICKETING_BOL_TicketCategoryService::getInstance()->deactivateCategory((int) $params['id']);
        }
        OW::getFeedback()->info(OW::getLanguage()->text('frmticketing', 'category_deactivated_successfully'));
        $this->redirect(OW::getRouter()->urlForRoute('frmticketing.admin-currentSection',array('currentSection'=>self::CATEGORY_SECTION)));
    }

    public function editCategoryItem()
    {
        $form =  FRMTICKETING_BOL_TicketCategoryService::getInstance()->getItemForm($_POST['id']);
        if ( $form->isValid($_POST) ) {
            FRMTICKETING_BOL_TicketCategoryService::getInstance()->editCategoryItem($form->getElement('id')->getValue(), $form->getElement('title')->getValue());
            OW::getFeedback()->info(OW::getLanguage()->text('frmticketing', 'database_record_edit'));
            $this->redirect(OW::getRouter()->urlForRoute('frmticketing.admin-currentSection',array('currentSection'=>self::CATEGORY_SECTION)));
        }else{
            if($form->getErrors()['label'][0]!=null) {
                OW::getFeedback()->error($form->getErrors()['label'][0]);
            }
            $this->redirect(OW::getRouter()->urlForRoute('frmticketing.admin-currentSection',array('currentSection'=>self::CATEGORY_SECTION)));
        }
    }

    public function editOrderItem()
    {
        $form =  FRMTICKETING_BOL_TicketOrderService::getInstance()->getItemForm($_POST['id']);
        if ( $form->isValid($_POST) ) {
            FRMTICKETING_BOL_TicketOrderService::getInstance()->editOrderItem($form->getElement('id')->getValue(), $form->getElement('title')->getValue());
            OW::getFeedback()->info(OW::getLanguage()->text('frmticketing', 'database_record_edit'));
            $this->redirect(OW::getRouter()->urlForRoute('frmticketing.admin-currentSection',array('currentSection'=>self::ORDER_SECTION)));
        }else{
            if($form->getErrors()['label'][0]!=null) {
                OW::getFeedback()->error($form->getErrors()['label'][0]);
            }
            $this->redirect(OW::getRouter()->urlForRoute('frmticketing.admin-currentSection',array('currentSection'=>1)));
        }
    }

    public function autoCompleteUsernames(){
        if (!OW::getRequest()->isAjax()) {
            exit(json_encode(array('error' => 'only ajax is allowed.')));
        }

        if(!isset($_POST['username']))
        {
            exit(json_encode(array('error' => 'data is incomplete.')));
        }

        $users = BOL_UserService::getInstance()->findByUsernameStartsWith($_POST['username']);
        exit(json_encode(array_column($users,'username')));
    }
}
