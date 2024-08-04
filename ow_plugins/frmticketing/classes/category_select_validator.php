<?php
class FRMTICKETING_CLASS_CategorySelectValidator extends OW_Validator
{
    public function isValid( $category)
    {
        if ( $category === null )
        {
            return false;
        }
        $categoryData = explode('_',$category);
        $categoryDataValidationEvent= OW::getEventManager()->trigger(new OW_Event('ticket.validate.category.data',array('categoryData'=>$categoryData,'userId'=>OW::getUser()->getId())));
        if(isset($categoryDataValidationEvent->getData()['violationOccured']))
        {
            return false;
        }
        return true;
    }
}
