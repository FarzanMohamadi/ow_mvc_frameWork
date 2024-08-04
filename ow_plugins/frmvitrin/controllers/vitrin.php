<?php
class FRMVITRIN_CTRL_Vitrin extends OW_ActionController
{

    public function index($params)
    {
        $service = FRMVITRIN_BOL_Service::getInstance();
        $items = $service->getItems();
        $itemsArray = array();
        foreach ($items as $item) {
            $description = explode("<!--more-->", BASE_CMP_TextFormatter::fromBBtoHtml($item->description));
            $itemsArray[] = array(
                'title'=> $item->title,
                'description' => $description[0],
                'language' => $item->language,
                'businessModel' => $item->businessModel,
                'url' => $item->url,
                'logo' => $service->getFileUrl($item->logo),
                'targetMarket' => $item->targetMarket,
                'vendor' => $item->vendor,
                'viewUrl' => OW::getRouter()->urlForRoute('frmvitrin.item', array('id' => $item->id))
            );
        }

        $this->assign('business_model_icon',$service->getIconUrl('business_model.svg'));
        $this->assign('target_icon',$service->getIconUrl('target.svg'));
        $this->assign('developer_icon',$service->getIconUrl('developer.svg'));
        $this->assign('language_icon',$service->getIconUrl('language.svg'));
        if(sizeof($itemsArray)==0){
            $this->assign('emptyItems', true);
        }

        $this->assign('items', $itemsArray);

        $cssDir = OW::getPluginManager()->getPlugin("frmvitrin")->getStaticCssUrl();
        OW::getDocument()->addStyleSheet($cssDir . "frmvitrin.css");

        $vitrinDescription = OW::getConfig()->getValue('frmvitrin', 'description');
        if($vitrinDescription!=null && $vitrinDescription!=""){
            $this->assign('vitrinDescription', $vitrinDescription);
        }
    }

    public function item($params){
        if(!isset($params['id'])){
            $this->redirect(OW::getRouter()->urlForRoute('frmvitrin.index'));
        }
        $service = FRMVITRIN_BOL_Service::getInstance();
        $item = $service->getItem($params['id']);
        if($item==null){
            $this->redirect(OW::getRouter()->urlForRoute('frmvitrin.index'));
        }
        $description = $item->description;
        $itemArray = array(
            'title'=> $item->title,
            'description' => $description,
            'language' => $item->language,
            'businessModel' => $item->businessModel,
            'targetMarket' => $item->targetMarket,
            'vendor' => $item->vendor,
            'url' => $item->url,
            'logo' => $service->getFileUrl($item->logo)
        );

        $this->assign('business_model_icon',$service->getIconUrl('business_model.svg'));
        $this->assign('target_icon',$service->getIconUrl('target.svg'));
        $this->assign('developer_icon',$service->getIconUrl('developer.svg'));
        $this->assign('language_icon',$service->getIconUrl('language.svg'));

        $this->assign('item', $itemArray);
        $this->assign('returnToVitrin', OW::getRouter()->urlForRoute('frmvitrin.index'));
        $cssDir = OW::getPluginManager()->getPlugin("frmvitrin")->getStaticCssUrl();
        OW::getDocument()->addStyleSheet($cssDir . "frmvitrin.css");
    }

}