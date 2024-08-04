<?php
class FRMUSERLOGIN_CTRL_Iisuserlogin extends OW_ActionController
{

    public function index($params)
    {
        if(OW::getConfig()->configExists('frmuserlogin','update_active_details') && OW::getConfig()->getValue('frmuserlogin','update_active_details')) {
            $this->redirect(OW::getRouter()->urlForRoute('frmuserlogin.active'));
        }
        $this->redirect(OW::getRouter()->urlForRoute('frmuserlogin.login'));
    }
    public function login($params)
    {
        $this->setPageHeading(OW::getLanguage()->text('frmuserlogin', 'login_details_header'));
        $this->setDocumentKey('active_sessions_page');
        if(!OW::getUser()->isAuthenticated()){
            throw new Redirect404Exception();
        }
        $service = FRMUSERLOGIN_BOL_Service::getInstance();
        $items = array();
        $details = $service->getUserLoginDetails(OW::getUser()->getId());
        if($details != null) {
            foreach ($details as $detail) {
                $items[] = array(
                    'time' => UTIL_DateTime::formatSimpleDate($detail->time),
                    'browser' => $detail->browser,
                    'ip' => $detail->ip
                );
            }
        }
        $this->assign("items", $items);
        $menu = new BASE_CMP_ContentMenu($service->getMenu(0));
        $this->addComponent('menu', $menu);
    }

    /**
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     */
    public function active($params)
    {
        $service = FRMUSERLOGIN_BOL_Service::getInstance();
        $this->setPageHeading(OW::getLanguage()->text('frmuserlogin', 'bottom_menu_item'));
        $this->setDocumentKey('active_sessions_page');
        $uId = OW::getUser()->getId();
        if(!OW::getUser()->isAuthenticated()){
            throw new Redirect404Exception();
        }
        if(!OW::getConfig()->configExists('frmuserlogin','update_active_details') || !OW::getConfig()->getValue('frmuserlogin','update_active_details')) {
            throw new Redirect404Exception();
        }
        $js = '
        function terminateDevice(id){
            $.ajax({
                url: "'.OW::getRouter()->urlForRoute('frmuserlogin.terminate_device').'",
                type: "POST",
                data: {deviceId: id},
                dataType: "json",
                success: function (data) {
                    if(data.result){
                        $("#device_"+data.id).hide(500, function() {$(this).remove();});;
                    }
                }
            });
        }
        function terminateAllDevices(){
            if(confirm("'.OW::getLanguage()->text('frmuserlogin','are_you_sure').'")){
                $.ajax({
                    url: "'.OW::getRouter()->urlForRoute('frmuserlogin.terminate_device').'",
                    type: "POST",
                    data: {deviceId: -1},
                    dataType: "json",
                    success: function (data) {
                        if(data.result){
                            location.reload(true);
                        }
                    }
                });
            }
        }
        ';
        OW::getDocument()->addScriptDeclarationBeforeIncludes($js);

        //paging
        $rpp = 10;
        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;
        $itemsCount = $service->getUserActiveDetailsCount($uId);
        if($itemsCount>$rpp){
            $paging = new BASE_CMP_Paging($page, ceil((int)$itemsCount / $rpp), 5);
            $this->addComponent('paging', $paging);
        }
        if(intval($page)<=0 || $page>ceil((int)$itemsCount / $rpp)) {
            $page = 1;
        }

        $items = array();
        $details = $service->getUserActiveDetails($uId, $page, $rpp);
        if($details != null) {
            foreach ($details as $detail) {
                if(session_id() == $detail->sessionId){
                    $actions = OW::getLanguage()->text('frmuserlogin','current_device');
                }else{
                    $actions = '<a class="ow_lbutton" href="javascript://" onclick="terminateDevice('.$detail->id.')">'. OW::getLanguage()->text('frmuserlogin','terminate_device').'</a>';
                }
                $items[] = array(
                    'time' => UTIL_DateTime::formatSimpleDate($detail->time),
                    'browser' => $detail->browser,
                    'ip' => $detail->ip,
                    'actions' => $actions,
                    'id' => $detail->id
                );
            }
        }
        $this->assign("items", $items);
        $menu = new BASE_CMP_ContentMenu($service->getMenu(1));
        $this->addComponent('menu', $menu);
    }

    /**
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     */
    public function terminateDevice($params){
        if(!isset($_POST['deviceId'])){
            exit(json_encode(array('id' => 0 , 'result' => false)));
        }
        $deviceId = $_POST['deviceId'];
        $userId = OW::getUser()->getId();
        $service = FRMUSERLOGIN_BOL_Service::getInstance();
        if($deviceId==-1){
            $result = $service->terminateAllOtherDevices($userId);
        }else {
            $result = $service->terminateDevice($deviceId, $userId);
        }
        exit(json_encode(array('id' => $deviceId , 'result' => $result)));
    }
}