<?php
/**
 * FRM Graph
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgraph
 * @since 1.0
 */

class FRMGRAPH_CTRL_Graph extends OW_ActionController
{
    /**
     * FRMGRAPH_CTRL_Graph constructor.
     * @throws Redirect404Exception if the user has no access to view the FRMGraph pages
     */
    public function __construct()
    {
        if (!OW::getUser()->isAuthorized('frmgraph', 'graphshow') && !OW::getUser()->isAdmin())
            throw new Redirect404Exception();
        $this->setDocumentKey('frmgraph');
    }
    /* ============================ Components ============================ */

    public function index( array $params = array() )
    {
        $adminCmp = new FRMGRAPH_CMP_Admin($params, false);
        $this->addComponent('adminCmp',$adminCmp);
    }

    public function graph()
    {
        $graphCmp = new FRMGRAPH_CMP_AdminGraph(null, false);
        $this->addComponent('graphCmp',$graphCmp);
    }

    public function userAnalytics()
    {
        $userAnalyticsCmp = new FRMGRAPH_CMP_AdminUserAnalytics(null, false);
        $this->addComponent('userAnalyticsCmp',$userAnalyticsCmp);
     }

    public function groupAnalytics()
    {
        $groupAnalyticsCmp = new FRMGRAPH_CMP_AdminGroupAnalytics(null, false);
        $this->addComponent('groupAnalyticsCmp',$groupAnalyticsCmp);
    }

    public function userView( array $params = array() )
    {
        $userViewCmp = new FRMGRAPH_CMP_AdminUserView($params, false);
        $this->addComponent('userViewCmp',$userViewCmp);
    }

    public function groupView( array $params = array() )
    {
        $groupViewCmp = new FRMGRAPH_CMP_AdminGroupView($params, false);
        $this->addComponent('groupViewCmp',$groupViewCmp);
    }

    public function allGroups( array $params = array() )
    {
        $allGroupsCmp = new FRMGRAPH_CMP_AdminAllGroups($params, false);
        $this->addComponent('allGroupsCmp',$allGroupsCmp);
    }

    public function oneGroup( array $params = array() )
    {
        $oneGroupCmp = new FRMGRAPH_CMP_AdminOneGroup($params, false);
        $this->addComponent('oneGroupCmp',$oneGroupCmp);
    }

    public function allUsers( array $params = array() )
    {
        $allUsersCmp = new FRMGRAPH_CMP_AdminAllUsers($params);
        $this->addComponent('allUsersCmp',$allUsersCmp);
    }

    public function oneUser( array $params = array() )
    {
        $oneUserCmp = new FRMGRAPH_CMP_AdminOneUser($params, false);
        $this->addComponent('oneUserCmp',$oneUserCmp);
    }

    public function usersStatistics( array $params = array() )
    {
        $usersStatistics = new FRMGRAPH_CMP_AdminUsersStatistics($params, false);
        $this->addComponent('allUsersStatisticsCmp', $usersStatistics);
    }

    public function usersList( array $params = array() )
    {
        $users_list = new FRMGRAPH_CMP_UsersList($params);
        $this->addComponent('allUsersList', $users_list);
    }

    public function calculateAllInformation(){
        $service = FRMGRAPH_BOL_Service::getInstance();
        $service->calculateAllInformation();
        OW::getFeedback()->info(OW::getLanguage()->text('frmgraph', 'calculate_all_metrics_successfully'));
        $this->redirect(OW::getRouter()->urlForRoute('frmgraph.graph'));
    }
}
