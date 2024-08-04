<?php
/**
 * Created by PhpStorm.
 * User: MHeshmati
 * Date: 8/1/2018
 * Time: 10:45 AM
 */

class FRMUPDATESERVER_CLASS_LabelValidator extends OW_Validator
{
    public function isValid( $label )
    {
        if ( $label === null )
        {
            return false;
        }

        $alreadyExist = FRMUPDATESERVER_BOL_CategoryDao::getInstance()->findIsExistLabel($label);

        if ( !isset($alreadyExist) )
        {
            return true;
        }
        return false;
    }
}
