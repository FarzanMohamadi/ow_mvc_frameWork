<?php
/***
 * Class BASE_CMP_CommentRequestChangeMessage
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */

class BASE_CMP_CommentRequestChangeMessage extends OW_Component
{
    /**
     * BASE_CMP_CommentRequestChangeMessage constructor.
     */
    public function __construct( $params )
    {
        parent::__construct();
        $userId = $params['userId'];
        $hasAccessToApproveUser = BOL_UserService::getInstance()->hasAccessToApproveUser($userId);
        if (!$hasAccessToApproveUser['valid']) {
            throw new Redirect404Exception();
        }

        $userService = BOL_UserService::getInstance();

        if ( $user = $userService->findUserById($userId) )
        {
            if ( $userService->isApproved($userId) )
            {
                throw new Redirect404Exception();
            }
        }

        $form = new Form('message');
        $form->setAction(OW::getRouter()->urlFor("BASE_CTRL_WaitForApproval", "requestChangeFormSubmit",
            ['id'=>$userId]));

        $textarea = new Textarea('message');
        $textarea->setRequired();

        $form->addElement($textarea);

        $submit = new Submit('submit');
        $submit->setLabel(OW::getLanguage()->text('base', 'submit'));

        $form->addElement($submit);

        $this->addForm($form);
    }

}
