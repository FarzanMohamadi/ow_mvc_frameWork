<?php
class FRMTICKETING_CMP_TicketPostQuote extends OW_Component
{
    /**
     * Class constructor
     * 
     * @param array $params
     *      integer quoteId
     */
    public function __construct(array $params = array())
    {
        parent::__construct();

        $quoteId = !empty($params['quoteId']) 
            ? $params['quoteId'] 
            : null;
        $ticketService = FRMTICKETING_BOL_TicketService::getInstance();
        $postDto = $ticketService->findPostById($quoteId);

        if (!$postDto) 
        {
            $this->setVisible(false);
            return;
        }

        // assign view variables
        $this->assign('postFrom', BOL_UserService::getInstance()->getDisplayName($postDto->userId));
        $this->assign('postText', $postDto->text);
    }
}