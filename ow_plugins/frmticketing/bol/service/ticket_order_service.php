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
final class FRMTICKETING_BOL_TicketOrderService
{

    /**
     * @var FRMTICKETING_BOL_TicketOrderDao
     */
    private $orderDao;


    /**
     * Constructor.
     */
    protected function __construct()
    {
        $this->orderDao = FRMTICKETING_BOL_TicketOrderDao::getInstance();
    }

    /**
     * Singleton instance.
     *
     * @var frmterms_BOL_Service
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMTICKETING_BOL_TicketOrderService
     */
    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function getTicketOrderList()
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('status','active');
        return $this->orderDao->findListByExample($ex);
    }
    public function addOrder($title)
    {
        $orderEntity = new FRMTICKETING_BOL_TicketOrder();
        $orderEntity->title = $title;
        $this->orderDao->save($orderEntity);
    }

    public function getItemForm($id)
    {
        $item = $this->getOrderById($id);
        $formName = 'edit-item';
        $submitLabel = 'edit';
        $actionRoute = OW::getRouter()->urlFor('FRMTICKETING_CTRL_Admin', 'editOrderItem');

        $form = new Form($formName);
        $form->setAction($actionRoute);

        if ($item != null) {
            $idField = new HiddenField('id');
            $idField->setValue($item->id);
            $form->addElement($idField);
        }

        $fieldTitle = new TextField('title');
        $fieldTitle->setRequired();
        $fieldTitle->setInvitation(OW::getLanguage()->text('frmticketing', 'title_order_label'));
        $fieldTitle->setValue($item->title);
        $fieldTitle->setHasInvitation(true);
        $validator = new FRMTICKETING_CLASS_OrderTitleValidator();
        $language = OW::getLanguage();
        $validator->setErrorMessage($language->text('frmticketing', 'title_error_already_exist'));
        $fieldTitle->addValidator($validator);
        $form->addElement($fieldTitle);

        $submit = new Submit('submit', 'button');
        $submit->setValue(OW::getLanguage()->text('base', 'ow_ic_save'));
        $form->addElement($submit);

        return $form;
    }

    public function getOrderById($id)
    {
        return $this->orderDao->findById($id);
    }

    public function editOrderItem($id, $title)
    {
        $item = $this->getOrderById($id);
        if ($item == null) {
            return;
        }
        if ($title == null) {
            $title = false;
        }
        $item->title = $title;

        $this->orderDao->save($item);
        return $item;
    }

    public function deleteOrder( $orderId )
    {
        $orderId = (int)$orderId;
        if ($orderId > 0) {
            $this->orderDao->softDeleteOrderById($orderId);
        }
    }

    public function findOrderById($orderId)
    {
        return $this->orderDao->findById($orderId);
    }
}