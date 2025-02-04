<?php
class FRMNEWSFEEDPIN_MCTRL_Pin extends OW_MobileActionController
{

    public function addPinByEntity(array $params = array())
    {
        $result = array(
            'error' => true,
            'msg' => OW_Language::getInstance()->text('frmnewsfeedpin', 'add_fail')
        );
        if (isset($_POST['entityId']) && isset($_POST['entityType'])) {
            $pin = new FRMNEWSFEEDPIN_BOL_Pin();
            $pin->setCreateDate(time());
            $pin->setEntityId($_POST['entityId']);
            $pin->setEntityType($_POST['entityType']);
            FRMNEWSFEEDPIN_BOL_PinDao::getInstance()->save($pin);
            $result['error'] = false;
            $result['msg'] = OW_Language::getInstance()->text('frmnewsfeedpin', 'add_success');
            $result['button_value'] = OW_Language::getInstance()->text('frmnewsfeedpin', 'un_pin_button_label');
        }
        exit(json_encode($result));
    }

    public function deletePin(array $params = array())
    {
        $result = array(
            'error' => true,
            'msg' => OW_Language::getInstance()->text('frmnewsfeedpin', 'delete_fail')
        );
        try {
            if (isset($_POST['className'])) {
                preg_match_all('/(.*)frmnewsfeedpin_pin_id_\[(.+)\]_\[(\d*)\](.*)/u', $_POST['className'], $matches);
                if((isset($matches[2][0]) && !empty($matches[2][0])) && (isset($matches[3][0]) && !empty($matches[3][0]))) {
                    FRMNEWSFEEDPIN_BOL_PinDao::getInstance()->deleteByEntityIdAndEntityType((int) $matches[3][0],$matches[2][0]);
                    $pin = FRMNEWSFEEDPIN_BOL_PinDao::getInstance()->findByEntityIdAndEntityType((int) $matches[3][0],$matches[2][0]);
                    if (!isset($pin)) {
                        $result['error'] = false;
                        $result['msg'] = OW_Language::getInstance()->text('frmnewsfeedpin', 'delete_success');
                        $result['button_value'] = OW_Language::getInstance()->text('frmnewsfeedpin', 'pin_button_label');
                    }
                }
            }
        } catch (Exception $ignored) {
        }
        exit(json_encode($result));
    }
}
