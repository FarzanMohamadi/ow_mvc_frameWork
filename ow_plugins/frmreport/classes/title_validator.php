<?php
class FRMREPORT_CLASS_TitleValidator extends OW_Validator{

    function isValid($value)
    {
        if($value === null){
            return false;
        }
        $activityType = FRMREPORT_BOL_Service::getInstance()->findActivityType($value);
        if(isset($activityType)){
            return false;
        }
        return true;
    }
}