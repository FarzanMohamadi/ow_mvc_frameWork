<?php
class FRMEVENTPLUS_CLASS_LabelValidator extends OW_Validator
{
    public function isValid( $label )
    {
        if ( $label === null )
        {
            return false;
        }

        $alreadyExist = FRMEVENTPLUS_BOL_CategoryDao::getInstance()->findIsExistLabel($label);

        if ( !isset($alreadyExist) )
        {
            return true;
        }
        return false;
    }
}
