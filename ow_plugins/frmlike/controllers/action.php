<?php
class FRMLIKE_CTRL_Action extends OW_ActionController
{
    /**
     * @var BOL_VoteService
     */
    private $voteService;

    /**
     * Constructor.
     */
    public function __construct()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $this->voteService = BOL_VoteService::getInstance();
    }




}
