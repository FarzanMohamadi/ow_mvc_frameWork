<?php
class BASE_CTRL_Console extends OW_ActionController
{
    public function listRsp()
    {
        $request = json_decode($_POST['request'], true);

        $event = new BASE_CLASS_ConsoleListEvent('console.load_list', $request, $request['data']);
        OW::getEventManager()->trigger($event);

        $responce = array();
        $responce['items'] = $event->getList();

        $responce['data'] = $event->getData();
        $responce['markup'] = array();

        /* @var $document OW_AjaxDocument */
        $document = OW::getDocument();

        $responce['markup']['scriptFiles'] = $document->getScripts();
        $responce['markup']['onloadScript'] = $document->getOnloadScript();
        $responce['markup']['styleDeclarations'] = $document->getStyleDeclarations();
        $responce['markup']['styleSheets'] = $document->getStyleSheets();
        $responce['markup']['beforeIncludes'] = $document->getScriptBeforeIncludes();

        echo json_encode($responce);

        exit;
    }

    /***
     * @throws Redirect404Exception
     */
    public function fetchConsoleData(){
        if (!OW::getRequest()->isAjax()){
            throw new Redirect404Exception();
        }
        OW::getEventManager()->trigger(new OW_Event('console.fetch'));
        echo json_encode("");

        exit;
    }
}