<?php
/**
 * Created by PhpStorm.
 * User: Seyed Ismail Mirvakili
 * Date: 7/2/18
 * Time: 10:45 AM
 */

class FRMTECHUNIT_MCTRL_Unit extends OW_MobileActionController
{
    const COUNT = 10;

    public $service;

    public function __construct()
    {
        $this->service = FRMTECHUNIT_BOL_Service::getInstance();
    }

    /**
     * @param $params
     * @throws Redirect404Exception
     */
    public function index($params)
    {
        if (!FRMTECHUNIT_BOL_Service::getInstance()->hasViewAccess())
            throw new Redirect404Exception();
        if (isset($_GET['search']))
            $q = $_GET['search'];
        $form = new Form('search');
        $form->setMethod(Form::METHOD_GET);
        $form->setAction(OW_Router::getInstance()->urlForRoute('frmtechunit.units'));

        $field = new TextField('search');
        $field->addValidator(new StringValidator(3));
        $field->setLabel(OW_Language::getInstance()->text('frmtechunit', 'search'));
        $field->setInvitation(OW_Language::getInstance()->text('frmtechunit', 'search'));
        $field->setHasInvitation(true);
        if (isset($q))
            $field->setValue($q);
        $form->addElement($field);

        $field = new Submit('submit');
        $field->setLabel(OW_Language::getInstance()->text('frmtechunit', 'field_submit'));
        $form->addElement($field);

        if (isset($q)) {
            $unitsCount = FRMTECHUNIT_BOL_UnitDao::getInstance()->searchCount($q);
            $page = (!empty($_GET['page']) && intval($_GET['page']) > 0) ? $_GET['page'] : 1;
            $units = FRMTECHUNIT_BOL_Service::getInstance()->searchUnits($q, ($page - 1) * self::COUNT, self::COUNT);
            $this->loadUnits($units, $unitsCount, $page);
        } else {
            $unitsCount = FRMTECHUNIT_BOL_UnitDao::getInstance()->countAll();
            $page = (!empty($_GET['page']) && intval($_GET['page']) > 0) ? $_GET['page'] : 1;
            $units = FRMTECHUNIT_BOL_Service::getInstance()->loadUnits(($page - 1) * self::COUNT, self::COUNT);
            $this->loadUnits($units, $unitsCount, $page);
        }
        if (FRMTECHUNIT_BOL_Service::getInstance()->hasAddAccess()) {
            $this->assign('add_url', OW::getRouter()->urlForRoute('frmtechunit.add_unit'));
        }
        $this->addForm($form);
        $this->assign('originalUrl', OW::getRouter()->urlForRoute('frmtechunit.search'));
        OW::getDocument()->addOnloadScript('
            window.searchUnits = function(url) {
                var query = $(\'#unitSearch\')[0].value;
                var filter = "?search="+query;
                url = url + filter;
                window.location = url;
            }
        ');
    }

    public function loadUnits($units, $unitsCount, $page)
    {
        $paging = new BASE_CMP_PagingMobile($page, ceil($unitsCount / self::COUNT), 5);
        $this->addComponent('paging', $paging);
        $unitArray = array();
        /** @var FRMTECHUNIT_BOL_Unit $unit */
        foreach ($units as $unit) {
            $websiteUrl = $unit->website;
            if (strpos($unit->website,'http://') === false || strpos($unit->website,'https://') === false){
                $websiteUrl = 'http://'.$unit->website;
            }
            $content =
                OW::getLanguage()->text('frmtechunit','field_manager').': '.$unit->manager.'<br/>'.
                OW::getLanguage()->text('frmtechunit','field_phone').': '.$unit->phone.'<br/>'.
                OW::getLanguage()->text('frmtechunit','field_website').': '.'<a href="'.$websiteUrl.'" target="_blank">'.$unit->website.'</a>';
            $unitArray[] = array(
                'id' => $unit->id,
                'url' => OW::getRouter()->urlForRoute('frmtechunit.unit', array('id' => $unit->id)),
                'title' => $unit->name,
                'imageTitle' => $unit->name,
                'content' => $content,
                'time' => UTIL_DateTime::formatDate($unit->timestamp),
                'imageSrc' => FRMTECHUNIT_BOL_Service::getInstance()->getImageUrl($unit, $unit->image)
            );
        }
        $this->assign('list', $unitArray);
    }

    /**
     * @param $params
     * @throws Redirect404Exception
     */
    public function addUnit($params)
    {
        if (!FRMTECHUNIT_BOL_Service::getInstance()->hasAddAccess())
            throw new Redirect404Exception();
        $form = new Form('add_unit');
        $form->setMethod(Form::METHOD_POST);
        $form->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);
        $form->setAction(OW_Router::getInstance()->urlForRoute('frmtechunit.add_unit'));

        $field = new TextField('name');
        $field->addValidator(new StringValidator(3, 128));
        $field->setLabel(OW_Language::getInstance()->text('frmtechunit', 'field_name'));
        $field->setRequired(true);
        $form->addElement($field);

        $field = new FileField('image');
        $field->setLabel(OW_Language::getInstance()->text('frmtechunit', 'field_image'));
        $field->setRequired(false);
        $form->addElement($field);

        $field = new FileField('qr_code');
        $field->setLabel(OW_Language::getInstance()->text('frmtechunit', 'field_qr_code'));
        $field->setRequired(false);
        $form->addElement($field);

        $field = new TextField('manager');
        $field->addValidator(new StringValidator(3, 128));
        $field->setLabel(OW_Language::getInstance()->text('frmtechunit', 'field_manager'));
        $field->setRequired(true);
        $form->addElement($field);

        $field = new TextField('address');
        $field->addValidator(new StringValidator(3, 512));
        $field->setLabel(OW_Language::getInstance()->text('frmtechunit', 'field_address'));
        $field->setRequired(true);
        $form->addElement($field);

        $field = new TextField('phone');
        $field->addValidator(new StringValidator(3, 15));
        $field->setLabel(OW_Language::getInstance()->text('frmtechunit', 'field_phone'));
        $field->setRequired(true);
        $form->addElement($field);

        $field = new TextField('email');
        $field->addValidator(new EmailValidator());
        $field->setLabel(OW_Language::getInstance()->text('frmtechunit', 'field_email'));
        $field->setRequired(true);
        $form->addElement($field);

        $field = new TextField('website');
        $field->addValidator(new StringValidator(3, 256));
        $field->setLabel(OW_Language::getInstance()->text('frmtechunit', 'field_website'));
        $field->setRequired(true);
        $form->addElement($field);

        if (OW::getConfig()->configExists('frmtechunit', 'orders')) {
            $orderedList = json_decode(OW::getConfig()->getValue('frmtechunit', 'orders'));
        } else {
            $orderedList = FRMTECHUNIT_BOL_SectionDao::getInstance()->findIdListByExample(new OW_Example());
        }
        $sectionData = array();
        foreach ($orderedList as $item) {
            $section = FRMTECHUNIT_BOL_SectionDao::getInstance()->findById($item);
            $field = new MobileWysiwygTextarea($section->name,'frmtechunit');
            $field->setLabel($section->title);
            $field->setRequired($section->required);
            $form->addElement($field);
            $sectionData[] = $section->name;
        }

        $field = new Submit('submit');
        $field->setLabel(OW_Language::getInstance()->text('frmtechunit', 'field_submit'));
        $form->addElement($field);

        if (OW::getRequest()->isPost() && $form->isValid($_POST)) {
            $data = $form->getValues();

            $image = null;
            if (!empty($_FILES['image']['name'])) {
                if ((int)$_FILES['image']['error'] !== 0 || !is_uploaded_file($_FILES['image']['tmp_name']) || !UTIL_File::validateImage($_FILES['image']['name'])) {
                    OW::getFeedback()->error(OW::getLanguage()->text('base', 'not_valid_image'));
                    OW::getApplication()->redirect();
                } else {
                    $image = $this->service->addImage($_FILES['image']['tmp_name']);
                }
            }
            $qr_code = null;
            if (!empty($_FILES['qr_code']['name'])) {
                if ((int)$_FILES['qr_code']['error'] !== 0 || !is_uploaded_file($_FILES['qr_code']['tmp_name']) || !UTIL_File::validateImage($_FILES['qr_code']['name'])) {
                    OW::getFeedback()->error(OW::getLanguage()->text('base', 'not_valid_image'));
                    OW::getApplication()->redirect();
                } else {
                    $qr_code = $this->service->addImage($_FILES['qr_code']['tmp_name']);
                }
            }

            $sectionArray = array();
            foreach ($orderedList as $item) {
                $section = FRMTECHUNIT_BOL_SectionDao::getInstance()->findById($item);
                $sectionArray[$section->id] = $data[$section->name];
            }
            FRMTECHUNIT_BOL_Service::getInstance()->saveUnit($data['name'], $data['manager'], $image, $qr_code, $data['address'], $data['phone'], $data['email'], $data['website'], $sectionArray);
            OW::getFeedback()->info(OW::getLanguage()->text('frmtechunit', 'success'));
            $this->redirect(OW::getRouter()->urlForRoute('frmtechunit.units'));
        }
        $this->addForm($form);
        $this->assign('sections', $sectionData);
    }

    /**
     * @param $params
     * @throws Redirect404Exception
     */
    public function unit($params)
    {
        if (!FRMTECHUNIT_BOL_Service::getInstance()->hasViewAccess())
            throw new Redirect404Exception();
        if (!isset($params['id']))
            throw new Redirect404Exception();
        $id = $params['id'];
        $unit = FRMTECHUNIT_BOL_UnitDao::getInstance()->findById($id);
        $unitCmp = new FRMTECHUNIT_MCMP_Unit($unit);
        $this->addComponent('unitCmp', $unitCmp);
    }

    /**
     * @param $params
     * @throws Redirect404Exception
     */
    public function editUnit($params)
    {
        if (!FRMTECHUNIT_BOL_Service::getInstance()->hasAddAccess())
            throw new Redirect404Exception();
        if (!isset($params['id']))
            throw new Redirect404Exception();
        $id = $params['id'];
        /** @var FRMTECHUNIT_BOL_Unit $unit */
        $unit = FRMTECHUNIT_BOL_UnitDao::getInstance()->findById($id);

        $form = new Form('edit_unit');
        $form->setMethod(Form::METHOD_POST);
        $form->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);
        $form->setAction(OW_Router::getInstance()->urlForRoute('frmtechunit.edit_unit', array('id' => $id)));

        $field = new TextField('name');
        $field->addValidator(new StringValidator(3, 128));
        $field->setLabel(OW_Language::getInstance()->text('frmtechunit', 'field_name'));
        $field->setRequired(true);
        $field->setValue($unit->name);
        $form->addElement($field);

        $field = new FileField('image');
        $field->setLabel(OW_Language::getInstance()->text('frmtechunit', 'field_image'));
        $field->setRequired(false);
        $form->addElement($field);

        $field = new FileField('qr_code');
        $field->setLabel(OW_Language::getInstance()->text('frmtechunit', 'field_qr_code'));
        $field->setRequired(false);
        $form->addElement($field);

        $field = new TextField('manager');
        $field->addValidator(new StringValidator(3, 128));
        $field->setLabel(OW_Language::getInstance()->text('frmtechunit', 'field_manager'));
        $field->setRequired(true);
        $field->setValue($unit->manager);
        $form->addElement($field);

        $field = new TextField('address');
        $field->addValidator(new StringValidator(3, 512));
        $field->setLabel(OW_Language::getInstance()->text('frmtechunit', 'field_address'));
        $field->setRequired(true);
        $field->setValue($unit->address);
        $form->addElement($field);

        $field = new TextField('phone');
        $field->addValidator(new StringValidator(3, 15));
        $field->setLabel(OW_Language::getInstance()->text('frmtechunit', 'field_phone'));
        $field->setRequired(true);
        $field->setValue($unit->phone);
        $form->addElement($field);

        $field = new TextField('email');
        $field->addValidator(new EmailValidator());
        $field->setLabel(OW_Language::getInstance()->text('frmtechunit', 'field_email'));
        $field->setRequired(true);
        $field->setValue($unit->email);
        $form->addElement($field);

        $field = new TextField('website');
        $field->addValidator(new StringValidator(3, 256));
        $field->setLabel(OW_Language::getInstance()->text('frmtechunit', 'field_website'));
        $field->setRequired(true);
        $field->setValue($unit->website);
        $form->addElement($field);

        if (OW::getConfig()->configExists('frmtechunit', 'orders')) {
            $orderedList = json_decode(OW::getConfig()->getValue('frmtechunit', 'orders'));
        } else {
            $orderedList = FRMTECHUNIT_BOL_SectionDao::getInstance()->findIdListByExample(new OW_Example());
        }
        $sectionData = array();
        foreach ($orderedList as $item) {
            $section = FRMTECHUNIT_BOL_SectionDao::getInstance()->findById($item);
            $field = new MobileWysiwygTextarea($section->name,'frmtechunit');
            $field->setLabel($section->title);
            $field->setRequired($section->required);
            $unitSections = FRMTECHUNIT_BOL_UnitSectionDao::getInstance()->getUnitSections($unit->id, $section->id);
            foreach ($unitSections as $unitSection) {
                $field->setValue($unitSection->content);
                break;
            }
            $form->addElement($field);
            $sectionData[] = $section->name;
        }

        $field = new Submit('submit');
        $field->setLabel(OW_Language::getInstance()->text('frmtechunit', 'field_submit'));
        $form->addElement($field);

        if (isset($unit->image)) {
            $this->assign('image', FRMTECHUNIT_BOL_Service::getInstance()->getImageUrl($unit, $unit->image));
            $field = new CheckboxField('delete_image');
            $field->setLabel(OW_Language::getInstance()->text('frmtechunit', 'delete_image'));
            $field->setRequired(false);
            $field->setValue(false);
            $form->addElement($field);
        }
        if (isset($unit->qr_code)) {
            $this->assign('qr_code', FRMTECHUNIT_BOL_Service::getInstance()->getImageUrl($unit, $unit->qr_code));
            $field = new CheckboxField('delete_qr');
            $field->setLabel(OW_Language::getInstance()->text('frmtechunit', 'delete_image'));
            $field->setRequired(false);
            $field->setValue(false);
            $form->addElement($field);
        }
        $this->assign('sections', $sectionData);
        $this->addForm($form);

        if (OW::getRequest()->isPost() && $form->isValid($_POST)) {
            $data = $form->getValues();

            $image = null;
            if (!empty($_FILES['image']['name'])) {
                if ((int)$_FILES['image']['error'] !== 0 || !is_uploaded_file($_FILES['image']['tmp_name']) || !UTIL_File::validateImage($_FILES['image']['name'])) {
                    OW::getFeedback()->error(OW::getLanguage()->text('base', 'not_valid_image'));
                    OW::getApplication()->redirect();
                } else {
                    $image = $this->service->addImage($_FILES['image']['tmp_name']);
                }
            }
            $qr_code = null;
            if (!empty($_FILES['qr_code']['name'])) {
                if ((int)$_FILES['qr_code']['error'] !== 0 || !is_uploaded_file($_FILES['qr_code']['tmp_name']) || !UTIL_File::validateImage($_FILES['qr_code']['name'])) {
                    OW::getFeedback()->error(OW::getLanguage()->text('base', 'not_valid_image'));
                    OW::getApplication()->redirect();
                } else {
                    $qr_code = $this->service->addImage($_FILES['qr_code']['tmp_name']);
                }
            }

            $sectionArray = array();
            foreach ($orderedList as $item) {
                $section = FRMTECHUNIT_BOL_SectionDao::getInstance()->findById($item);
                $sectionArray[$section->id] = $data[$section->name];
            }
            $deleteImage = isset($data['delete_image']) && $data['delete_image'];
            $deleteQr = isset($data['delete_qr']) && $data['delete_qr'];
            FRMTECHUNIT_BOL_Service::getInstance()->editUnit($id, $data['name'], $data['manager'], $image, $deleteImage, $qr_code, $deleteQr, $data['address'], $data['phone'], $data['email'], $data['website'], $sectionArray);
            OW::getFeedback()->info(OW::getLanguage()->text('frmtechunit', 'success'));
            $this->redirect(OW::getRouter()->urlForRoute('frmtechunit.unit', array('id' => $id)));
        }
    }

    /**
     * @param $params
     * @throws Redirect404Exception
     */
    public function deleteUnit($params)
    {
        if (!FRMTECHUNIT_BOL_Service::getInstance()->hasAddAccess())
            throw new Redirect404Exception();
        if (!isset($params['id']))
            throw new Redirect404Exception();
        $id = $params['id'];
        FRMTECHUNIT_BOL_UnitDao::getInstance()->deleteById($id);
        FRMTECHUNIT_BOL_UnitSectionDao::getInstance()->deleteUnitSectionsByUnit($id);
        OW::getFeedback()->info(OW::getLanguage()->text('frmtechunit', 'success'));
        $this->redirect(OW::getRouter()->urlForRoute('frmtechunit.units'));
    }
}