<?php
/**
 * Contact us service.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcontactus.bol
 * @since 1.0
 */
class FRMCONTACTUS_BOL_Service
{

    private $departmentDao;

    /**
     * Singleton instance.
     *
     * @var FRMCONTACTUS_BOL_Service
     */
    private static $classInstance;


    private  $userinformationDao;
    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMCONTACTUS_BOL_Service
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
        $this->userinformationDao = FRMCONTACTUS_BOL_UserInformationDao::getInstance();
        $this->departmentDao = FRMCONTACTUS_BOL_DepartmentDao::getInstance();
    }

    public function getDepartmentLabel( $id )
    {
        return OW::getLanguage()->text('frmcontactus', $this->getDepartmentKey($id));
    }

    /***
     * @param $id
     * @return FRMCONTACTUS_BOL_Department
     */
    public function getDepartmentByID($id )
    {
        return FRMCONTACTUS_BOL_DepartmentDao::getInstance()->findById($id);
    }


    public function addDepartment( $email, $label )
    {
        $contact = new FRMCONTACTUS_BOL_Department();
        $contact->email = $email;
        $contact->label = $label;
        FRMCONTACTUS_BOL_DepartmentDao::getInstance()->save($contact);
    }

    public function deleteDepartment( $id )
    {
        $id = (int) $id;
        if ( $id > 0 )
        {
            FRMCONTACTUS_BOL_DepartmentDao::getInstance()->deleteById($id);
        }
    }

    private function getDepartmentKey( $name )
    {
        return 'dept_' . trim($name);
    }

    public function getDepartmentList()
    {
        return FRMCONTACTUS_BOL_DepartmentDao::getInstance()->findAll();
    }

    public function addUserInformation($subject , $useremail , $label , $message)
    {
        $userInfo = new FRMCONTACTUS_BOL_UserInformation();
        $userInfo->subject = $subject;
        $userInfo->useremail = $useremail;
        $userInfo->label = $label;
        $userInfo->message = $message;
        $userInfo->timeStamp = time();
        FRMCONTACTUS_BOL_UserInformationDao::getInstance()->save($userInfo);
    }

    public function deleteUserInformationBylabel( $label )
    {
        if ( isset($label) )
        {
            $this->userinformationDao->deleteByLabel($label);
        }
    }

    /**
     * @param $sectionId
     * @return array
     */
    public function getAdminSections($sectionId)
    {
        $sections = array();

        for ($i = 1; $i <= 2; $i++) {
            $sections[] = array(
                'sectionId' => $i,
                'active' => $sectionId == $i ? true : false,
                'url' => OW::getRouter()->urlForRoute('frmcontactus.admin.section-id', array('sectionId' => $i)),
                'label' => $this->getPageHeaderLabel($i)
            );
        }
        $sections[] = array(
            'sectionId' => 'new',
            'active' => $sectionId == 'new' ? true : false,
            'url' => OW::getRouter()->urlForRoute('frmcontactus.admin.section-id', array('sectionId' => 'new')),
            'label' => $this->getPageHeaderLabel('new')
        );
        return $sections;
    }

    public function getPageHeaderLabel($sectionId)
    {
        if ($sectionId == 1) {
            return OW::getLanguage()->text('frmcontactus', 'userInfo');
        } else if ($sectionId == 2) {
            return OW::getLanguage()->text('frmcontactus', 'department');
        }else if ($sectionId == 'new') {
            return OW::getLanguage()->text('frmcontactus', 'adminComment');
        }
    }
    public function getUserInformationList()
    {
        return  $this->userinformationDao->findAll();
    }

    public function getUserInformationListByLabel($department,$first,$count)
    {
        return $this->userinformationDao->findByLabel($department,$first,$count);
    }
    public function getCountByDepartment($department)
    {
        return $this->userinformationDao->getCountByDep($department);
    }

    public function isExistLabel($label)
    {

        if ( $label === null )
        {
            return false;
        }

        $department = FRMCONTACTUS_BOL_DepartmentDao::getInstance()->findIsExistLabel($label);

        if ( isset($department) )
        {
            return true;
        }

        return false;

    }

    public function editDepartment($id, $email, $label)
    {
        $item = $this->getDepartmentByID($id);
        if ($item == null) {
            return;
        }


        $item->label = $label;
        $item->email = $email;

        $this->departmentDao->save($item);
        return $item;
    }

    public function getDepartmentEditForm($id)
    {
        $item = $this->getDepartmentByID($id);
        $actionRoute = OW::getRouter()->urlForRoute('frmcontactus.admin.edit.item');
        $form = new Form('edit-item');
        $form->setAction($actionRoute);
        if ($item != null) {
            $idField = new HiddenField('id');
            $idField->setValue($item->id);
            $form->addElement($idField);
        }

        $emailField = new TextField('email');
        $emailField->setRequired();
        $emailField->addValidator(new EmailValidator());
        $emailField->setValue($item->email);
        $fieldLabel = new TextField('label');
        $fieldLabel->setRequired();
        $fieldLabel->setValue($item->label);
        $form->addElement($emailField);
        $form->addElement($fieldLabel);

        $submit = new Submit('submit', 'button');
        $submit->setValue(OW::getLanguage()->text('base', 'ow_ic_save'));
        $form->addElement($submit);

        return $form;
    }

}