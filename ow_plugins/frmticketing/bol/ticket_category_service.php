<?php
/**
 *
 */

/**
 * frmticketing Service.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmticketing.bol
 * @since 1.0
 */
class FRMTICKETING_BOL_TicketCategoryService
{

    /**
     * @var frmterms_BOL_ItemDao
     */
    private $categoryDao;

    /**
     * Constructor.
     */
    protected function __construct()
    {
        $this->categoryDao = FRMTICKETING_BOL_TicketCategoryDao::getInstance();
    }

    /**
     * Singleton instance.
     *
     * @var FRMTICKETING_BOL_TicketCategoryService
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMTICKETING_BOL_TicketCategoryService
     */
    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function getTicketCategoryList()
    {
        return $this->categoryDao->findAll();
    }

    public function addCategory($title)
    {
        $categoryEntity = new FRMTICKETING_BOL_TicketCategory();
        $categoryEntity->title = $title;
        $this->categoryDao->save($categoryEntity);
    }

    public function getItemForm($id)
    {
        $item = $this->getCategoryById($id);
        $formName = 'edit-item';
        $submitLabel = 'edit';
        $actionRoute = OW::getRouter()->urlFor('FRMTICKETING_CTRL_Admin', 'editCategoryItem');

        $form = new Form($formName);
        $form->setAction($actionRoute);

        if ($item != null) {
            $idField = new HiddenField('id');
            $idField->setValue($item->id);
            $form->addElement($idField);
        }

        $fieldTitle = new TextField('title');
        $fieldTitle->setRequired();
        $fieldTitle->setInvitation(OW::getLanguage()->text('frmticketing', 'title_category_label'));
        $fieldTitle->setValue($item->title);
        $fieldTitle->setHasInvitation(true);
        $validator = new FRMTICKETING_CLASS_CategoryTitleValidator();
        $language = OW::getLanguage();
        $validator->setErrorMessage($language->text('frmticketing', 'title_error_already_exist'));
        $fieldTitle->addValidator($validator);
        $form->addElement($fieldTitle);

        $submit = new Submit('submit', 'button');
        $submit->setValue(OW::getLanguage()->text('base', 'ow_ic_save'));
        $form->addElement($submit);

        return $form;
    }

    public function getCategoryById($id)
    {
        return $this->categoryDao->findById($id);
    }

    public function editCategoryItem($id, $title)
    {
        $item = $this->getCategoryById($id);
        if ($item == null) {
            return;
        }
        if ($title == null) {
            $title = false;
        }
        $item->title = $title;

        $this->categoryDao->save($item);
        return $item;
    }

    public function deleteCategory( $categoryId )
    {
        $categoryId = (int)$categoryId;
        if ($categoryId > 0) {

            $ticketService= FRMTICKETING_BOL_TicketService::getInstance();
            $ticketService->deleteTicketCategoryInformation($categoryId);

            $this->categoryDao->deleteById($categoryId);
        }
    }

    public function findCategoryById($categoryId)
    {
        return $this->categoryDao->findById($categoryId);
    }

}