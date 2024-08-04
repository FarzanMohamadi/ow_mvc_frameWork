<?php
/**
 * FRM Graph
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgraph
 * @since 1.0
 */

class FRMGRAPH_BOL_Group extends OW_Entity
{
    public $groupId;
    public $time;

    public $gId;
    public $cluster_coe;
    public $eccentricity_cent;
    public $degree_cent;
    public $closeness_cent;
    public $betweenness_cent;
    public $page_rank;
    public $hub;
    public $authority;
    public $users_count;
    public $contents_count;
    public $files_count;
    public $users_interactions_count;
    public $all_activities_count;
}
