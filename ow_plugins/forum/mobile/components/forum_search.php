<?php
/**
 * Forum search form class.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.mobile.components
 * @since 1.0
 */
class FORUM_MCMP_ForumSearch extends OW_MobileComponent
{
    private $scope;
    private $id;

    public function __construct( array $params )
    {
        parent::__construct();

        $this->scope = !empty($params['scope']) ? $params['scope'] : 'all_forum';
        $this->id = !empty($params['id']) ? $params['id'] : null;

        switch ( $this->scope )
        {
            case 'topic':
                $location = OW::getRouter()->
                        urlForRoute('forum_search_topic', array('topicId' => $this->id));
                break;

            case 'group':
                $location = OW::getRouter()->
                        urlForRoute('forum_search_group', array('groupId' => $this->id));
                break;

            case 'section':
                $location = OW::getRouter()->
                        urlForRoute('forum_search_section', array('sectionId' => $this->id));
                break;

            default:
                $location = OW::getRouter()->urlForRoute('forum_search');
                break;
        }

        $invitation = OW::getLanguage()->text('forum', 'search_invitation_' . $this->scope);

        // add form       
        $this->addForm(new FORUM_MCLASS_SearchForm("search_form", $invitation, $location));

        // assign view variables
        $this->assign('invitation', $invitation);
        $this->assign('location', $location);
    }
}