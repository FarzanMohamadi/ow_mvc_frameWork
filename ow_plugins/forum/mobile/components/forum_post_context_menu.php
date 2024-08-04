<?php
/**
 * Forum post context menu class.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.mobile.components
 * @since 1.0
 */
class FORUM_MCMP_ForumPostContextMenu extends OW_MobileComponent
{
    /**
     * Post id
     * @var integer
     */
    protected $postId;

    /**
     * Class constructor
     * 
     * @param array $params
     *      integer topicId
     *      integer postId
     */
    public function __construct( array $params = array() )
    {
        parent::__construct();

        $this->topicId = !empty($params['topicId']) ? $params['topicId'] : -1;
        $this->postId = !empty($params['postId']) ? $params['postId'] : -1;
    }

    /**
     * Render component
     * 
     * @return type
     */
    public function render()
    {
        $items[] = array(
            'group' => 'forum',
            'label' => OW::getLanguage()->text('forum', 'edit'),
            'order' => 1,
            'class' => null,
            'href' => null,
            'id' => null,
            'attributes' => array(
                'class' => 'forum_edit_post',
                'data-id' => $this->postId,
            )
        );
        $postDeleteCode='';
        $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'receiverId'=>$this->topicId,'isPermanent'=>true,'activityType'=>'delete_post')));
        if(isset($frmSecuritymanagerEvent->getData()['code'])){
            $postDeleteCode = $frmSecuritymanagerEvent->getData()['code'];
        }
        $deletePostUrl = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('delete-post',
            array('topicId' => $this->topicId, 'postId' => $this->postId)),array('code' =>$postDeleteCode));
        $items[] = array(
            'group' => 'forum',
            'label' => OW::getLanguage()->text('forum', 'delete'),
            'order' => 1,
            'class' => null,
            'href' => $deletePostUrl,
            'id' => null,
            'attributes' => array(
                'class' => 'forum_delete_post',
            )
        );

        $menu = new BASE_MCMP_ContextAction($items);
        return $menu->render();
    }
}