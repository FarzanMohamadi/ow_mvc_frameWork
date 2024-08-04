<?php
/**
 * Forum post quote class.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.components
 * @since 1.0
 */
class FORUM_CMP_ForumPostQuote extends OW_Component
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

        $postDto = FORUM_BOL_ForumService::getInstance()->findPostById($quoteId);

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