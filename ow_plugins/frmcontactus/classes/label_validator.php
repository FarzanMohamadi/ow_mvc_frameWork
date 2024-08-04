<?php
class FRMCONTACTUS_CLASS_LabelValidator extends OW_Validator
{
    public function isValid( $label )
    {
        if ( $label === null )
        {
            return false;
        }

        $user = FRMCONTACTUS_BOL_DepartmentDao::getInstance()->findIsExistLabel($label);

        if ( !isset($user) )
        {
            return true;
        }
        return false;
    }
}
