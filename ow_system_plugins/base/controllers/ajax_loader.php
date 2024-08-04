<?php
/**
 * @package ow_system_plugins.base.controllers
 * @since 1.0
 */
class BASE_CTRL_AjaxLoader extends OW_ActionController
{
    public function init()
    {
        if( !OW::getRequest()->isAjax() )
        {
           throw new Redirect404Exception();
        }
    }

    public function component()
    {
        if ( empty($_GET['cmpClass']) )
        {
            exit;
        }
        if ( ! OW::getRequest()->isAjax() ){
            exit;
        }

        $cmpClass = trim($_GET['cmpClass']);
        if (strpos(strtoupper($cmpClass), '_CMP_')===false &&
            strpos(strtoupper($cmpClass), '_MCMP_')===false){
            exit(json_encode(['content'=>"403"]));
        }

        $params = empty($_POST['params']) ? array() : json_decode($_POST['params'], true);
        
        $eventManager = OW::getEventManager();
        $eventManager->trigger(new OW_Event('base.before.ajax_load.component', array(
            'cmpClass' => $cmpClass,
            'params' => $params
        )));

        $cmp = OW::getClassInstanceArray($cmpClass, $params);
        $responce = $this->getComponentMarkup($cmp);

        exit(json_encode($responce));
    }

    protected function getComponentMarkup( OW_Component $cmp )
    {

        /* @var $document OW_AjaxDocument */
        $document = OW::getDocument();

        $responce = array();

        $responce['content'] = trim($cmp->render());

        $beforeIncludes = $document->getScriptBeforeIncludes();
        if ( !empty($beforeIncludes) )
        {
            $responce['beforeIncludes'] = $beforeIncludes;
        }

        foreach ( $document->getScripts() as $script )
        {
            $responce['scriptFiles'][] = $script;
        }

        $onloadScript = $document->getOnloadScript();
        if ( !empty($onloadScript) )
        {
            $responce['onloadScript'] = $onloadScript;
        }

        $styleDeclarations = $document->getStyleDeclarations();
        if ( !empty($styleDeclarations) )
        {
            $responce['styleDeclarations'] = $styleDeclarations;
        }

        $styleSheets = $document->getStyleSheets();
        if ( !empty($styleSheets) )
        {
            $responce['styleSheets'] = $styleSheets;
        }

        return $responce;
    }
}