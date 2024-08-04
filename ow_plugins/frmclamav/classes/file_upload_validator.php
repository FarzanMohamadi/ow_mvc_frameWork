<?php
class FRMCLAMAV_CLASS_FileUploadValidator extends OW_Validator
{
    public function isValid($value)
    {
        if(!empty($_FILES)) {
            $values=reset($_FILES);
            $filePath = $values['tmp_name'];
            $fileName = $values['name'];
            $checkAnotherExtensionEvent = OW::getEventManager()->trigger(new OW_Event('frmclamav.is_file_clean', array('path' => $filePath)));
            if (isset($checkAnotherExtensionEvent->getData()['clean'])) {
                $isClean = $checkAnotherExtensionEvent->getData()['clean'];
                if (!$isClean) {
                    $this->setErrorMessage(OW::getLanguage()->text('frmclamav', 'virus_file_found', array('file' => $fileName)));
                    return false;
                }
            }
        }
        return true;
    }
}
