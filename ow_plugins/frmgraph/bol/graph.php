<?php
class FRMGRAPH_BOL_Graph extends OW_Entity
{
    public $groupId;
    public $time;
    public $adjacency_list;

    public $cluster_coe_avg;
    public $component_distr;
    public $degree_distr;
    public $average_distance;
    public $degree_average;
    public $diameter;
    public $distance_distr;
    public $edge_count;
    public $node_count;
    public $contents_count;
    public $pictures_count;
    public $videos_count;
    public $news_count;
    public $users_interactions_count;
    public $all_activities_count;

    public $g_adjacency_list;
    public $g_cluster_coe_avg;
    public $g_component_distr;
    public $g_degree_distr;
    public $g_average_distance;
    public $g_degree_average;
    public $g_distance_distr;
    public $g_edge_count;
    public $g_node_count;
    public $g_diameter;
    public $g_contents_count;
    public $g_files_count;
    public $g_users_interactions_count;
    public $g_all_activities_count;
}
