<?php
class FRMTICKETING_CLASS_CategoryTitleValidator extends OW_Validator
{
    public function isValid( $title )
    {
        if ( $title === null )
        {
            return false;
        }

        $alreadyExist = FRMTICKETING_BOL_TicketCategoryDao::getInstance()->findIsExistTitle($title);

        if ( !isset($alreadyExist) )
        {
            return true;
        }
        return false;
    }
}
