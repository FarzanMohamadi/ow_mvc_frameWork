<?php
/**
 * FRM Advance Search
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmadvancesearch
 * @since 1.0
 */

class FRMADVANCESEARCH_MCTRL_AllUsersList extends OW_MobileActionController
{
    private $usersPerPage;

    public function __construct()
    {
        parent::__construct();
        $this->setPageHeading(OW::getLanguage()->text('base', 'users_browse_page_heading'));
        $this->setPageTitle(OW::getLanguage()->text('base', 'users_browse_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_user');
        $this->usersPerPage = (int)OW::getConfig()->getValue('base', 'users_count_on_page');
    }

    public function index( $params )
    {
        $type = isset($params['type'])?$params['type']:'all';
        if(!in_array($type, array('all', 'new', 'friends'))){
            throw new Redirect404Exception();
        }

        OW::getLanguage()->addKeyForJs('base', 'more');
        $jsDir = OW::getPluginManager()->getPlugin("frmadvancesearch")->getStaticJsUrl();
        OW::getDocument()->addScript($jsDir . "frmadvancesearch-mobile.js");
        OW::getDocument()->addOnloadScript(';frmadvancesearch_search_users(\''.OW::getRouter()->urlForRoute('frmadvancesearch.search_users', array('type'=>$type, 'key' => '')).'\', "#frmadvancedsearch_search_all_users", 30, true);');


        //setting back url
        if(isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']!=null){
            $this->assign('backUrl',$_SERVER['HTTP_REFERER']);
        }
        else {
            if (FRMSecurityProvider::checkPluginActive('frmmainpage', true) && !FRMMAINPAGE_BOL_Service::getInstance()->isDisabled('friends')) {
                $backUrl = OW::getRouter()->urlForRoute('frmmainpage.friends');
            } else {
                $backUrl = OW::getRouter()->urlForRoute('index');
            }
            $this->assign('backUrl',$backUrl);
        }

    }
}