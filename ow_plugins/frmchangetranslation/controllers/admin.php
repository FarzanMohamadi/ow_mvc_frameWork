<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 6/14/2017
 * Time: 11:12 AM
 */
class FRMCHANGETRANSLATION_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function index(array $params = array())
    {
        $language = OW::getLanguage();
        $this->setPageHeading($language->text('frmchangetranslation', 'admin_page_heading'));
        $this->setPageTitle($language->text('frmchangetranslation', 'admin_page_title'));

        $importLangForm = new Form('import');
        $importLangForm->setMethod('post');
        $importLangForm->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);
        $fileField = new FileField('file');
        $fileField->setLabel(OW::getLanguage()->text('frmchangetranslation', 'lang_file'));
        $importLangForm->addElement($fileField);
        $commandHidden = new HiddenField('command');
        $importLangForm->addElement($commandHidden->setValue('upload-lp'));
        $submit = new Submit('submit');
        $importLangForm->addElement($submit->setValue(OW::getLanguage()->text('frmchangetranslation', 'submit')));
        $importLangForm->setAction(OW::getRouter()->urlForRoute('frmchangetranslation.admin') . "#lang_import");
        $this->addForm($importLangForm);

        if ( isset($_POST['command']) && $_POST['command'] == 'upload-lp' ) {
            $this->import();
        }
    }

    public function import()
    {
        /** @var FRMCHANGETRANSLATION_BOL_Service $service */
        $service = FRMCHANGETRANSLATION_BOL_Service::getInstance();

        if (empty($_FILES['file']) || (int)$_FILES['file']['error'] !== 0 || !is_uploaded_file($_FILES['file']['tmp_name'])) {
            OW::getFeedback()->error(OW::getLanguage()->text('frmchangetranslation', 'import_failed'));
            $this->redirect();
        }

        $this->cleanImportDir($service->getImportPath());

        $tmpName = $_FILES['file']['tmp_name'];

        $uploadFilePath = $service->getImportPath() . $_FILES['file']['name'];
        OW::getStorage()->moveFile($tmpName, $uploadFilePath);

        if (OW::getStorage()->fileExists($tmpName)) {
            OW::getStorage()->removeFile($tmpName);
        }
        if (!$service->importUploadedFile($uploadFilePath)) {
            OW::getFeedback()->error(OW::getLanguage()->text('frmchangetranslation', 'import_failed'));
            $this->redirect();
        }
        OW::getFeedback()->info(OW::getLanguage()->text('frmchangetranslation', 'import_success'));
    }

    private function cleanImportDir($dir)
    {
        $dh = opendir($dir);

        while (false !== ($node = readdir($dh))) {
            if ($node == '.' || $node == '..')
                continue;

            if (OW::getStorage()->isDir($dir . $node)) {
                UTIL_File::removeDir($dir . $node);
                continue;
            }

            OW::getStorage()->removeFile($dir . $node);
        }
    }
}