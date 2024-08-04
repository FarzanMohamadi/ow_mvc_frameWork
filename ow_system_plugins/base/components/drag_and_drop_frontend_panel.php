<?php
/**
 * Frontend widgets panel
 *
 * @package ow_system_plugins.base.components
 * @since 1.0
 */

class BASE_CMP_DragAndDropFrontendPanel extends BASE_CMP_DragAndDropPanel
{
    protected $customizeMode = false;
    protected $allowCustomize = false;
    protected $responderController = "BASE_CTRL_AjaxComponentAdminPanel";

    public function __construct( $placeName, array $componentList, $customizeMode, $componentTemplate, $responderController = null )
    {
        parent::__construct($placeName, $componentList, $componentTemplate);

        if ( !empty($responderController) )
        {
            $this->responderController = $responderController;
        }
        
        $this->customizeMode = (bool) $customizeMode;
        
        $this->assign('customizeMode', $this->customizeMode);
        $this->assign('allowCustomize', $this->allowCustomize);
        
        $this->setSettingsClassName("BASE_CMP_ComponentFrontendSettings");
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        if ( $this->customizeMode )
        {
            $this->initializeJs($this->responderController, 'OW_Components_DragAndDrop', $this->sharedData);

            $jsDragAndDropUrl = OW::getPluginManager()->getPlugin('BASE')->getStaticJsUrl() . 'drag_and_drop.js';
            OW::getDocument()->addScript($jsDragAndDropUrl);
        }
    }

    public function allowCustomize( $allowed = true )
    {
        $this->allowCustomize = $allowed;
        $this->assign('allowCustomize', $allowed);
    }

    public function customizeControlCunfigure( $customizeUrl, $normalUrl )
    {
        if ( $this->allowCustomize )
        {
            $js = new UTIL_JsGenerator();
            $js->newVariable('dndCustomizeUrl', $customizeUrl);
            $js->newVariable('dndNormalUrl', $normalUrl);
            $js->jQueryEvent('#goto_customize_btn', 'click', 'if(dndCustomizeUrl) window.location.href=dndCustomizeUrl;');
            $js->jQueryEvent('#goto_normal_btn', 'click', 'if(dndNormalUrl) window.location.href=dndNormalUrl;');
            OW::getDocument()->addOnloadScript($js);
        }
    }

    public function tplComponent( $params )
    {
        $uniqName = $params['uniqName'];
        $render = !empty($params['render']);

        $componentPlace = $this->componentList[$uniqName];
        $template = $this->customizeMode ? 'drag_and_drop_item_customize' : null;

        $viewInstance = new $this->itemClassName($uniqName, $this->isComponentClone($uniqName), $template, $this->sharedData);
        $eventForEnglishFieldSupport = new OW_Event('frmmultilingualsupport.find.multi.value.by.widget.unique.name',
            array('settings'=>$this->settingList[$uniqName],'uniqName'=>$uniqName));
        OW::getEventManager()->trigger($eventForEnglishFieldSupport);
        if(isset($eventForEnglishFieldSupport->getData()['multiSettings']))
        {
            $this->settingList[$uniqName]=$eventForEnglishFieldSupport->getData()['multiSettings'];
        }
        $viewInstance->setSettingList(empty($this->settingList[$uniqName]) ? array() : $this->settingList[$uniqName]);
        $viewInstance->componentParamObject->additionalParamList = $this->additionalSettingList;
        $viewInstance->componentParamObject->customizeMode = $this->customizeMode;

        if ( !empty($this->standartSettings[$componentPlace['className']]) )
        {
            $viewInstance->setStandartSettings($this->standartSettings[$componentPlace['className']]);
        }

        $viewInstance->setContentComponentClass($componentPlace['className']);

        if ( $render )
        {
            return $viewInstance->renderView();
        }

        return $viewInstance->renderScheme();
    }
}