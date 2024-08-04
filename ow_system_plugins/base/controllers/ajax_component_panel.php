<?php
/**
 * Ajax widget panel
 *
 * @package ow_system_plugins.base.controllers
 * @since 1.0
 */
abstract class BASE_CTRL_AjaxComponentPanel extends OW_ActionController
{
    private $actions = array();
    private $debug = array();

    /**
     * @see OW_ActionController::init()
     *
     */
    public function init()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $this->registerAction('saveComponentPlacePositions', array($this, 'saveComponentPlacePositions'));
        $this->registerAction('cloneComponent', array($this, 'cloneComponent'));
        $this->registerAction('deleteComponent', array($this, 'deleteComponent'));
        $this->registerAction('getSettingsMarkup', array($this, 'getSettingsMarkup'));
        $this->registerAction('saveSettings', array($this, 'saveSettings'));
        $this->registerAction('savePlaceScheme', array($this, 'savePlaceScheme'));
        $this->registerAction('moveComponentToPanel', array($this, 'moveComponentToPanel'));
        $this->registerAction('reload', array($this, 'reloadComponent'));
    }

    public function registerAction( $actionName, $actionCallback )
    {
        $this->actions[$actionName] = $actionCallback;
    }

    public function processQueue()
    {
        $requestQueue = json_decode(urldecode($_POST['request']), true);

        $responseQueue = array();
        $exception = false;

        foreach ( $requestQueue as $request )
        {
            if ( !isset($this->actions[$request['command']]) )
            {
                continue;
            }
            $command = $request['command'];
            $commandId = $request['commandId'];
            $data = empty($request['data']) ? array() : $request['data'];

            BASE_CLASS_Widget::setPlaceData($request['data']);

            $result = call_user_func($this->actions[$request['command']], $request['data']);
            $responseQueue[$commandId] = $result;
        }

        $response = array(
            'responseQueue' => $responseQueue,
            'debug' => $this->debug
        );

        echo json_encode($response);
        exit();
    }

    protected function debug( $var )
    {
        array_push($this->debug, $var);
    }

    private function checkComponentClass( $componentClassName )
    {
        $reflectionClass = new ReflectionClass($componentClassName);

        if ( !$reflectionClass->isSubclassOf('BASE_CLASS_Widget') )
        {
            throw new LogicException('Component is not configurable');
        }
    }

    protected function getComponentSettingList( $componentClassName, $params = array() )
    {
        $this->checkComponentClass($componentClassName);

        return call_user_func(array($componentClassName, 'getSettingList'), $params["componentId"]);
    }

    protected function getComponentAccess( $componentClassName, $params = array() )
    {
        $this->checkComponentClass($componentClassName);

        return call_user_func(array($componentClassName, 'getAccess'), $params["componentId"]);
    }

    protected function getComponentStandardSettingValueList( $componentClassName, $params = array() )
    {
        $this->checkComponentClass($componentClassName);

        return call_user_func(array($componentClassName, 'getStandardSettingValueList'), !empty($params["componentId"]) ? $params["componentId"] : null);
    }

    protected function validateComponentSettingList( $componentClassName, $settingList, $place, $params = array() )
    {
        $this->checkComponentClass($componentClassName);

        return call_user_func(array($componentClassName, 'validateSettingList'), $settingList, $place, $params["componentId"]);
    }

    protected function processSettingList( $componentClassName, $settingList, $place, $isAdmin, $params = array() )
    {
        $this->checkComponentClass($componentClassName);

        return call_user_func(array($componentClassName, 'processSettingList'), $settingList, $place, $isAdmin, $params["componentId"]);
    }

    protected function getComponentMarkup( BASE_CMP_DragAndDropItem $viewInstance, $renderView = false )
    {

        /* @var $document OW_AjaxDocument */
        $document = OW::getDocument();

        $responce = array();

        if ( $renderView )
        {
            $responce['content'] = $viewInstance->renderView();
        }
        else
        {
            $responce['content'] = $viewInstance->renderScheme();
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

    protected function getSettingFormMarkup( OW_Component $viewInstance )
    {
        /* @var $document OW_AjaxDocument */
        $document = OW::getDocument();

        $responce = array();
        $responce['content'] = $viewInstance->render();

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
