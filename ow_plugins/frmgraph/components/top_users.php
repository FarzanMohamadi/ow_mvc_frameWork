<?php
/**
 * FRM Graph
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgraph
 * @since 1.0
 */

class FRMGRAPH_CMP_TopUsers extends OW_Component
{

    /**
     * FRMGRAPH_CMP_TopUsers constructor.
     * @param bool $widget
     * @param int $numberOfAllUsers
     * @param bool $pagination
     * @param int $numberOfResultRows
     */
    public function __construct($widget, $numberOfAllUsers, $pagination, $numberOfResultRows)
    {
        parent::__construct();
        $service = FRMGRAPH_BOL_Service::getInstance();
        $service->findTopUsers($this, $numberOfAllUsers, $pagination, $numberOfResultRows, true);
        if ($widget) {
            $toolbarArray[] = array('href' => OW::getRouter()->urlForRoute('frmgraph.top_users'), 'label' => OW::getLanguage()->text('base', 'view_all'));
            $this->assign('toolbars', $toolbarArray);
        }

        if($service->checkUserPermission() && OW::getUser()->isAuthenticated()) {
            $this->assign('top_users_hint', OW::getLanguage()->text('frmgraph', 'top_users_hint'));
        }

        if (!$widget) {
            $this->assign('top_users_header', OW::getLanguage()->text('frmgraph', 'top_users_widget'));
        }
    }

}