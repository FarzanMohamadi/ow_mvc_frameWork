<?php
class FRMTECHNOLOGY_CTRL_Order extends OW_ActionController
{
    private $service;
    private $isMobile;

    public function __construct()
    {
        $mobileEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION,array('check' => true)));
        if(isset($mobileEvent->getData()['isMobileVersion'])&& $mobileEvent->getData()['isMobileVersion']==true) {
            $this->isMobile=true;
        }
        else{
            $this->isMobile=false;
        }
        $this->service = FRMTECHNOLOGY_BOL_Service::getInstance();

    }

//    public function orderIndex()
//    {
//        if ( !OW::getUser()->isAuthorized('frmtechnology', 'view_order') )
//        {
//            throw new Redirect404Exception();
//        }
//        $configs = $this->service->getConfigs();
//        $page = ( empty($_GET['page']) || (int) $_GET['page'] < 0 ) ? 1 : (int) $_GET['page'];
//        if( isset($_GET['technologyStatus']) && (int)($_GET['technologyStatus']) > 0 ){
//            $orders = $this->service->findOrdersByTechnologyId((int)($_GET['technologyStatus']),$page);
//            $ordersCount = $this->service->findOrderCountByTechnologyId((int)($_GET['technologyStatus']));
//            $this->addComponent('paging', new BASE_CMP_Paging($page, ceil($ordersCount / $configs[FRMTECHNOLOGY_BOL_Service::CONF_ORDERS_COUNT_ON_PAGE]), 5));
//            if ( empty($orders) )
//            {
//                $this->assign('no_order', true);
//            }
//            $this->assign("orders",$this->service->getListingDataWithToolbarOrder($orders));
//            $this->assign('filterForm', true);
//            $this->assign('originalUrl', OW::getRouter()->urlForRoute('frmtechnology.orderIndex'));
//            $this->addForm($this->getOrderFilterForm(array('selectedTechnology' => (int)($_GET['technologyStatus']))));
//        }
//        else{
//            $orders = $this->service->findOrders($page);
//            $ordersCount = $this->service->findOrdersCount();
//            $this->addComponent('paging', new BASE_CMP_Paging($page, ceil($ordersCount / $configs[FRMTECHNOLOGY_BOL_Service::CONF_ORDERS_COUNT_ON_PAGE]), 5));
//            if ( empty($orders) )
//            {
//                $this->assign('no_order', true);
//            }
//            $ordersData=$this->service->getListingDataWithToolbarOrder($orders);
//            $this->assign("orders",$ordersData);
//            $this->assign('filterForm', true);
//            $this->assign('originalUrl', OW::getRouter()->urlForRoute('frmtechnology.orderIndex'));
//            $this->addForm($this->getOrderFilterForm(null));
//        }
//
//        $language = OW::getLanguage();
//        $this->setPageHeading($language->text('frmtechnology',  'order_index_page_heading'));
//        $this->setPageTitle($language->text('frmtechnology',  'order_index_page_title'));
//
//    }
//
//    public function orderSubmit($params = array())
//    {
//        $this->assign('isMobile',$this->isMobile);
//        if (OW::getRequest()->isAjax())
//        {
//            exit();
//        }
//
//       /* if ( !OW::getUser()->isAuthenticated() )
//        {
//            throw new AuthenticateException();
//        }*/
//
//        OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'frmtechnology', 'main_menu_item');
//
//
//        $this->setPageHeadingIconClass('ow_ic_write');
//
//        /*if (!OW::getUser()->isAuthorized('frmtechnology') && !OW::getUser()->isAuthorized('frmtechnology', 'add_technology') && !OW::getUser()->isAdmin() )
//        {
//            $status = BOL_AuthorizationService::getInstance()->getActionStatus('frmtechnology', 'add_technology');
//            throw new AuthorizationException($status['msg']);
//        }*/
//
//        $this->assign('authMsg', null);///?
//
//            $order = new FRMTECHNOLOGY_BOL_Order();
//            $order->setTechnologyId($params['technologyId']);
//        $form = new TechnologyOrderForm($order);
//        if ( OW::getRequest()->isPost() && (!empty($_POST['command']) && in_array($_POST['command'], array('save')) ) && $form->isValid($_POST) )
//        {
//            $form->process($this);
//        }
//        $this->assign('technologyUrl', OW::getRouter()->urlForRoute('frmtechnology.view', array('technologyId' => $order->getTechnologyId())));
//        $this->addForm($form);
//        $technologyTitle = $this->service->findTechnologyById($order->getTechnologyId())->getTitle();
//         $this->setPageHeading(OW::getLanguage()->text('frmtechnology', 'submit_order_page_heading', array('technology'=>$technologyTitle)));
//            OW::getDocument()->setTitle(OW::getLanguage()->text('frmtechnology', 'meta_title_submit_order'));
//            OW::getDocument()->setDescription(OW::getLanguage()->text('frmtechnology', 'meta_description_submit_order'));
//
//
//    }
//    public function orderView($params){
//        $orderId = (int) $params['orderId'];
//
//        if ( empty($orderId) )
//        {
//            throw new Redirect404Exception();
//        }
//
//        $order = $this->service->findOrderById($orderId);
//
//        if ( $order === null )
//        {
//            throw new Redirect404Exception();
//        }
//
//        $language = OW::getLanguage();
//
//        if ( !$this->service->isCurrentUserCanView() )
//        {
//            throw new Redirect404Exception();
//
//        }
//    $technologyTitle = $this->service->findTechnologyById($order->getTechnologyId())->getTitle();
////        OW::getDocument()->setTitle($language->text('frmtechnology', 'view_page_title', array(
////            'technology_title' => strip_tags($technology->title)
////        )));
//        $this->setPageHeading($language->text('frmtechnology', 'view_order_page_heading', array('technology'=>$technologyTitle)));
//        $infoArray = array(
//            'id' => $order->getId(),
//            'desc' => UTIL_HtmlTag::autoLink($order->getDescription()),
//            'name' => $order->getName(),
//            'phone' => $order->getPhone(),
//            'email' => $order->getEmail(),
//            'deleteUrl' => OW::getRouter()->urlFor('FRMTECHNOLOGY_CTRL_Order','delete', array('orderId' => $order->getId())),
//            'date' => UTIL_DateTime::formatSimpleDate($order->getTimeStamp(),true),
//            'companyName' => $order->getCompanyName(),
//            'companyWebsite' =>$order->getCompanyWebsite(),
//            'jobTitle' => $order->getJobTitle(),
//            'companyAddress'=> $order->getCompanyAddress(),
//            'companyActivityField' => $order->getCompanyActivityField()
//        );
//        /// add tech info
//        $this->assign('info', $infoArray);
//        $this->assign('orderIndexUrl', OW::getRouter()->urlForRoute('frmtechnology.orderIndex'));
//    }

//    public function delete( $params )
//    {
//        if ( empty($params['orderId']) )
//        {
//            throw new Redirect404Exception();
//        }
//
//        if ( !OW::getUser()->isAuthenticated() )
//        {
//            throw new AuthenticateException();
//        }
//        $orderId = $params['orderId'];
//
//        if ( !OW::getUser()->isAuthorized('frmtechnology','view_order') )
//        {
//            throw new Redirect404Exception();
//        }
//        $this->service->deleteOrder($orderId);
//            OW::getFeedback()->info(OW::getLanguage()->text('frmtechnology', 'delete_order_success_massage'));
//            $this->redirect(OW::getRouter()->urlForRoute('frmtechnology.orderIndex'));
//
//    }

    public function getOrderFilterForm($params){
        $form = new Form('orderFilterForm');
        if(isset($url)) {
            $form->setAction($url);
        }
        $form->setMethod(Form::METHOD_GET);
        $technologies = $this->service->findAllTechnologies();
        $technologyStatus = new Selectbox('technologies');
        $option = array();
        $option[null] = OW::getLanguage()->text('frmtechnology','select_technology');
        foreach ($technologies as $technology) {
            $option[$technology->getId()] = $technology->getTitle();
        }
        $technologyStatus->setHasInvitation(false);
        if(isset($params['selectedTechnology'])) {
            $technologyStatus->setValue($params['selectedTechnology']);
        }
        $technologyStatus->setOptions($option);
        $technologyStatus->addAttribute('id','technologyStatus');
        $form->addElement($technologyStatus);
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmtechnology')->getStaticJsUrl().'frmtechnology.js');
        return $form;

    }
}

class TechnologyOrderForm extends Form
{

    private $order;
    private $service;


    public function __construct( FRMTECHNOLOGY_BOL_Order $order, $tags = array() )
    {
        parent::__construct('save');

        $language = OW::getLanguage();
        $this->service = FRMTECHNOLOGY_BOL_Service::getInstance();
        $this->order = $order;
        $this->setMethod('post');
        $nameTextField = new TextField('name');
        $this->addElement($nameTextField->setLabel($language->text('frmtechnology', 'order_name'))->setRequired(true));
        $phoneTextField = new TextField('phone');
        $this->addElement($phoneTextField->setLabel($language->text('frmtechnology', 'order_phone'))->setRequired(true));
        $emailTextField = new TextField('email');
        $this->addElement($emailTextField->setLabel($language->text('frmtechnology', 'order_email'))->setRequired(true));
        $companyNameTextField = new TextField('companyName');
        $this->addElement($companyNameTextField->setLabel($language->text('frmtechnology', 'order_company_name'))->setRequired(true));
        $companyWebsiteTextField = new TextField('companyWebsite');
        $this->addElement($companyWebsiteTextField->setLabel($language->text('frmtechnology', 'order_company_website'))->setRequired(true));
        $jobTitleTextField = new TextField('jobTitle');
        $this->addElement($jobTitleTextField->setLabel($language->text('frmtechnology', 'order_job_title'))->setRequired(true));
        $companyAddressTextField = new TextField('companyAddress');
        $this->addElement($companyAddressTextField->setLabel($language->text('frmtechnology', 'order_company_address'))->setRequired(true));
        $companyActivityFieldTextField = new TextField('companyActivityField');
        $this->addElement($companyActivityFieldTextField->setLabel($language->text('frmtechnology', 'order_company_activity_field'))->setRequired(true));
        $buttons = array(
//            BOL_TextFormatService::WS_BTN_BOLD,
//            BOL_TextFormatService::WS_BTN_ITALIC,
//            BOL_TextFormatService::WS_BTN_UNDERLINE,
//            BOL_TextFormatService::WS_BTN_LINK,
//            BOL_TextFormatService::WS_BTN_ORDERED_LIST,
//            BOL_TextFormatService::WS_BTN_UNORDERED_LIST,
//            BOL_TextFormatService::WS_BTN_SWITCH_HTML,
//            BOL_TextFormatService::WS_BTN_HTML,
        );

        $mobileEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION,array('check' => true)));
        if(isset($mobileEvent->getData()['isMobileVersion'])&& $mobileEvent->getData()['isMobileVersion']==true) {
            $descriptionTextArea = new MobileWysiwygTextarea('description','frmtechnology');
        }else{
            $descriptionTextArea = new WysiwygTextarea('description','frmtechnology', $buttons);
            $descriptionTextArea->setSize(WysiwygTextarea::SIZE_L);
        }
        $descriptionTextArea->setLabel($language->text('frmtechnology', 'order_description'));
//        $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_AFTER_NEWSFEED_STATUS_STRING_READ,array('string' => $order->description)));
//        if(isset($stringRenderer->getData()['string'])){
//            $order->description = $stringRenderer->getData()['string'];
//        }
        $descriptionTextArea->setRequired(true);
        $this->addElement($descriptionTextArea);


            $text = $language->text('frmtechnology', 'save');

        $submit = new Submit('save');
        $submit->addAttribute('onclick', "$('#save_order_command').attr('value', 'save');");

        $this->addElement($submit->setValue($text));
        $this->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);
    }

    public function process( $ctrl )
    {
//        OW::getCacheManager()->clean( array( EntryDao::CACHE_TAG_POST_COUNT ));

        $service = FRMTECHNOLOGY_BOL_Service::getInstance();

        $data = $this->getValues();


        $data['name'] = UTIL_HtmlTag::escapeHtml(UTIL_HtmlTag::stripTagsAndJs($data['name']));
        $data['phone'] = UTIL_HtmlTag::escapeHtml(UTIL_HtmlTag::stripTagsAndJs($data['phone']));
        $data['email'] = UTIL_HtmlTag::escapeHtml(UTIL_HtmlTag::stripTagsAndJs($data['email']));
        $data['companyName'] = UTIL_HtmlTag::escapeHtml(UTIL_HtmlTag::stripTagsAndJs($data['companyName']));
        $data['companyWebsite'] = UTIL_HtmlTag::escapeHtml(UTIL_HtmlTag::stripTagsAndJs($data['companyWebsite']));
        $data['jobTitle'] = UTIL_HtmlTag::escapeHtml(UTIL_HtmlTag::stripTagsAndJs($data['jobTitle']));
        $data['companyAddress'] = UTIL_HtmlTag::escapeHtml(UTIL_HtmlTag::stripTagsAndJs($data['companyAddress']));
        $data['companyActivityField'] = UTIL_HtmlTag::escapeHtml(UTIL_HtmlTag::stripTagsAndJs($data['companyActivityField']));

        $text = UTIL_HtmlTag::sanitize($data['description']);
        $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_NEWSFEED_STATUS_STRING_WRITE,array('string' => $text)));
        if(isset($stringRenderer->getData()['string'])){
            $text = $stringRenderer->getData()['string'];
        }

        $this->order->setName($data['name']);
        $this->order->setPhone($data['phone']);
        $this->order->setEmail($data['email']);
        $this->order->setCompanyName($data['companyName']);
        $this->order->setCompanyWebsite($data['companyWebsite']);
        $this->order->setJobTitle($data['jobTitle']);
        $this->order->setCompanyAddress($data['companyAddress']);
        $this->order->setCompanyActivityField($data['companyActivityField']);
        $this->order->setDescription($text);
        $this->order->setTimeStamp(time());
        $service->saveOrder($this->order);
        OW::getFeedback()->info(OW::getLanguage()->text('frmtechnology', 'submit_order_success_msg'));
        $ctrl->redirect(OW::getRouter()->urlForRoute('frmtechnology.view', array('technologyId' => $this->order->getTechnologyId())));

    }
}