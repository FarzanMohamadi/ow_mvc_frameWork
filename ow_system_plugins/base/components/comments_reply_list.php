<?php


class BASE_CMP_CommentsReplyList extends OW_Component
{

    public function __construct( $params )
    {
        parent::__construct();
        $tmp = $params["replyCommentId"];
        OW::getDocument()->addScriptDeclaration(
            "console.log('$tmp');"
        );
        $commentParams = $params['params'];
        $commentParams->setReplyCommentId($params['replyCommentId']);
        $this->addComponent('replyComments', new BASE_CMP_Comments($commentParams));

    }

}
