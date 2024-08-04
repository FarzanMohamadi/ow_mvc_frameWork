<?php
class FRMGROUPSPLUS_CLASS_LabelValidator extends OW_Validator
{
    public function isValid( $label )
    {
        if ( $label === null )
        {
            return false;
        }

        $alreadyExist = FRMGROUPSPLUS_BOL_CategoryDao::getInstance()->findIsExistLabel($label);

        if ( !isset($alreadyExist) )
        {
            return true;
        }
        return false;
    }
}
