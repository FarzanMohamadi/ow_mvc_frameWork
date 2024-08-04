<?php


class BASE_MCMP_CommentsReplyList extends OW_Component
{

    public function __construct( $params )
    {
        parent::__construct();
        $commentParams = $params['params'];
        $commentParams->setReplyCommentId($params['replyCommentId']);
        $this->addComponent('replyComments', new BASE_MCMP_Comments($commentParams));

    }

}
