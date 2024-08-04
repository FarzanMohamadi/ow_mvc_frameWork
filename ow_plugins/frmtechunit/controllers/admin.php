<?php
/**
 * Created by PhpStorm.
 * User: Seyed Ismail Mirvakili
 * Date: 7/2/18
 * Time: 10:45 AM
 */

class FRMTECHUNIT_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    /**
     * @param $params
     * @throws Redirect404Exception
     */
    public function index($params)
    {
        if (!OW::getUser()->isAuthenticated() || !OW::getUser()->isAdmin())
            throw new Redirect404Exception();
        OW::getDocument()->setTitle(OW::getLanguage()->text('frmtechunit', 'main_menu_item'));

        $form = new Form('section');
        $form->setAction(OW::getRouter()->urlForRoute('frmtechunit.admin'));
        $form->setMethod(Form::METHOD_POST);
        $form->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);

        $title = new TextField('title');
        $title->setLabel(OW::getLanguage()->text('frmtechunit', 'field_title'));
        $title->setRequired();
        $title->setHasInvitation(false);
        $form->addElement($title);

        $active = new CheckboxField('required');
        $active->setLabel(OW::getLanguage()->text('frmtechunit', 'field_required'));
        $form->addElement($active);

        $submit = new Submit('submit');
        $form->addElement($submit);

        $this->addForm($form);

        if (OW::getRequest()->isPost()) {
            if ($form->isValid($_POST)) {
                $data = $form->getValues();
                $title = $data['title'];
                $required = $data['required'];
                $section = new FRMTECHUNIT_BOL_Section();
                $section->name = FRMSecurityProvider::generateUniqueId('section_');
                $section->title = $title;
                $section->required = isset($required) && $required;
                FRMTECHUNIT_BOL_SectionDao::getInstance()->save($section);
                if (OW::getConfig()->configExists('frmtechunit', 'orders')) {
                    $orderedList = json_decode(OW::getConfig()->getValue('frmtechunit', 'orders'));
                    $orderedList[] = $section->id;
                    FRMTECHUNIT_BOL_Service::getInstance()->savePageOrdered($orderedList);
                }
                OW::getFeedback()->info(OW::getLanguage()->text('frmtechunit', 'success'));
                $this->redirect();
            }
        }
        $page = !empty($_GET['page']) ? $_GET['page'] : 1;
        $count = 20;
        $sections = FRMTECHUNIT_BOL_SectionDao::getInstance()->all(($page - 1) * $count, $count);

        $sectionsCount = FRMTECHUNIT_BOL_SectionDao::getInstance()->countAll();
        $paging = new BASE_CMP_Paging($page, ceil($sectionsCount / $count), $count);
        $this->assign('paging', $paging->render());

        $sectionsArray = array();
        if (OW::getConfig()->configExists('frmtechunit', 'orders')) {
            $orderedList = json_decode(OW::getConfig()->getValue('frmtechunit', 'orders'));
            foreach ($orderedList as $item) {
                foreach ($sections as $section) {
                    if ($section->id == $item) {
                        $sectionInf = array(
                            'title' => $section->title,
                            'required' => $section->required,
                            'id' => $section->id,
                            'editUrl' => OW::getRouter()->urlForRoute('frmtechunit.admin.edit', array('id' => $section->id)),
                            'deleteUrl' => "if(confirm('" . OW::getLanguage()->text('frmtechunit', 'delete_item_warning') . "')){location.href='" . OW::getRouter()->urlForRoute('frmtechunit.admin.delete', array('id' => $section->id)) . "';}",
                        );
                        $sectionsArray[] = $sectionInf;
                    }
                }
            }
        } else {
            foreach ($sections as $section) {
                $sectionInf = array(
                    'title' => $section->title,
                    'required' => $section->required,
                    'id' => $section->id,
                    'editUrl' => OW::getRouter()->urlForRoute('frmtechunit.admin.edit', array('id' => $section->id)),
                    'deleteUrl' => "if(confirm('" . OW::getLanguage()->text('frmtechunit', 'delete_item_warning') . "')){location.href='" . OW::getRouter()->urlForRoute('frmtechunit.admin.delete', array('id' => $section->id)) . "';}",
                );
                $sectionsArray[] = $sectionInf;
            }
        }
        $this->assign('sections', $sectionsArray);
    }

    /**
     * @param $params
     * @throws Redirect404Exception
     */
    public function edit($params)
    {
        if (!OW::getUser()->isAuthenticated() || !OW::getUser()->isAdmin())
            throw new Redirect404Exception();
        if (!isset($params['id']))
            throw new Redirect404Exception();
        $id = $params['id'];
        $section = FRMTECHUNIT_BOL_SectionDao::getInstance()->findById($id);
        if (!isset($section))
            throw new Redirect404Exception();

        $form = new Form('section');
        $form->setAction(OW::getRouter()->urlForRoute('frmtechunit.admin.edit', array('id' => $id)));
        $form->setMethod(Form::METHOD_POST);
        $form->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);

        $title = new TextField('title');
        $title->setLabel(OW::getLanguage()->text('frmtechunit', 'field_title'));
        $title->setRequired();
        $title->setValue($section->title);
        $title->setHasInvitation(false);
        $form->addElement($title);

        $field = new HiddenField('id');
        $field->setValue($id);
        $form->addElement($field);

        $active = new CheckboxField('required');
        $active->setLabel(OW::getLanguage()->text('frmtechunit', 'field_required'));
        $active->setValue($section->required);
        $form->addElement($active);

        $submit = new Submit('submit');
        $form->addElement($submit);

        $this->addForm($form);

        if (OW::getRequest()->isPost()) {
            if ($form->isValid($_POST)) {
                $data = $form->getValues();
                $title = $data['title'];
                $required = $data['required'];
                $id = $data['id'];
                $section = FRMTECHUNIT_BOL_SectionDao::getInstance()->findById($id);
                $section->title = $title;
                $section->required = isset($required) && $required;
                FRMTECHUNIT_BOL_SectionDao::getInstance()->save($section);
                OW::getFeedback()->info(OW::getLanguage()->text('frmtechunit', 'success'));
                $this->redirect(OW::getRouter()->urlForRoute('frmtechunit.admin'));
            }
        }
    }

    /**
     * @param $params
     * @throws Redirect404Exception
     */
    public function delete($params)
    {
        if (!OW::getUser()->isAuthenticated() || !OW::getUser()->isAdmin())
            throw new Redirect404Exception();
        if (!isset($params['id']))
            throw new Redirect404Exception();
        $id = $params['id'];
        FRMTECHUNIT_BOL_SectionDao::getInstance()->deleteById($id);
        FRMTECHUNIT_BOL_UnitSectionDao::getInstance()->deleteUnitSectionsBySection($id);
        FRMTECHUNIT_BOL_Service::getInstance()->resetPageOrdered();
        OW::getFeedback()->info(OW::getLanguage()->text('frmtechunit', 'success'));
        $this->redirect(OW::getRouter()->urlForRoute('frmtechunit.admin'));
    }

    /**
     * @param $params
     * @throws Redirect404Exception
     */
    public function ajaxSaveOrder($params)
    {
        if (!OW::getUser()->isAuthenticated() || !OW::getUser()->isAdmin())
            throw new Redirect404Exception();
        if (!empty($_POST['list']) && is_array($_POST['list'])) {
            $service = FRMTECHUNIT_BOL_Service::getInstance();
            $list = array();
            foreach ($_POST['list'] as $index => $id) {
                $list[] = $id;
            }
            $service->savePageOrdered($list);
            exit(json_encode(array('result ' => true)));
        }
        exit(json_encode(array('result ' => false)));
    }
}