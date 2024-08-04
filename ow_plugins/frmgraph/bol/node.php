<?php
class FRMGRAPH_BOL_Node extends OW_Entity
{
    public $userId;
    public $cluster_coe;
    public $eccentricity_cent;
    public $degree_cent;
    public $closeness_cent;
    public $betweenness_cent;
    public $page_rank;
    public $hub;
    public $authority;
    public $groupId;
    public $time;
    public $all_done_likes_count;
    public $all_done_comments_count;
    public $user_all_likes_count;
    public $user_all_comments_count;

    public $contents_count;
    public $pictures_count;
    public $videos_count;
    public $news_count;
    public $all_contents_count;
    public $all_activities_count;
    public $all_done_activities_count;
}
