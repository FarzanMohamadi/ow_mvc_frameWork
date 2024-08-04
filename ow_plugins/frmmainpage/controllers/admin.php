<?php
class FRMMAINPAGE_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function __construct()
    {
        parent::__construct();

        $this->setPageHeading(OW::getLanguage()->text('frmmainpage', 'admin_settings_heading'));
        $this->setPageHeadingIconClass('ow_ic_gear_wheel');
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('frmmainpage')->getStaticCssUrl() . 'frmmainpage.css');
    }

    public function index($params)
    {
        OW::getDocument()->setTitle(OW::getLanguage()->text('frmmainpage', 'admin_settings_heading'));

        $service = FRMMAINPAGE_BOL_Service::getInstance();
        $list = $service->getMenuOrder();
        $disables = $service->getDisabledList();
        if(OW::getConfig()->configExists('frmmainpage', 'defaultPage')) {
            $defaultPage = OW::getConfig()->getValue('frmmainpage', 'defaultPage');
        }
        $list_result = array();
        foreach ($list as $item){
            $data=array();
            $enable = true;
            if(!empty($disables) && ($key = array_search($item, $disables)) !== false)
            {
                $enable=false;
            }
            if(isset($defaultPage) && $defaultPage==$item)
            {
                $data['defaultPage']=true;
            }
            $data['id']=$item;
            $data['label']=$service->getLableOfMenu($item);
            $data['enable']=$enable;
            $list_result[] = $data;
        }

        $this->assign('list', $list_result);
    }

    public function ajaxSaveOrder()
    {
        if (!empty($_POST['list']) && is_array($_POST['list'])) {
            $service = FRMMAINPAGE_BOL_Service::getInstance();
            $list = array();
            foreach ($_POST['list'] as $index => $id) {
                $list[] = $id;
            }
            $service->savePageOrdered($list);
            exit(json_encode(array('result ' => true)));
        }
        exit(json_encode(array('result ' => false)));
    }

    public function ajaxSaveDisable()
    {
        if (!empty($_POST['enable']) && !empty($_POST['id'])) {
            $service = FRMMAINPAGE_BOL_Service::getInstance();
            $enabled_list = 0; $disabled_list = 0;
            if($_POST['enable']=='false')
            {
                $service->addToDisableList($_POST['id']);
                $disabled_list++;
            }
            else if($_POST['enable']=='true')
            {
                $service->removeFromDisableList($_POST['id']);
                $enabled_list++;
            }
            exit(json_encode(array('result ' => true, 'enabled_list'=>$enabled_list, 'disabled_list'=>$disabled_list)));
        }
        exit(json_encode(array('result ' => false)));
    }

    public function ajaxSaveDefault()
    {
        if (!empty($_POST['enable']) && !empty($_POST['id'])) {
            $service = FRMMAINPAGE_BOL_Service::getInstance();
            $service->addAsDefault($_POST['id']);
            exit(json_encode(array('result ' => true, 'defaultPage'=>$_POST['id'])));
        }
        exit(json_encode(array('result ' => false)));
    }
}
