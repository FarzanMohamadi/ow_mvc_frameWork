<?php
/**
 * Admin content statistics component
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.7.6
 */
class ADMIN_CMP_ContentStatistic extends OW_Component
{
    /**
     * Default content group
     * @var string
     */
    protected $defaultContentGroup;

    /**
     * Default period
     * @var string
     */
    protected $defaultPeriod;

    /**
     * Class constructor
     *
     * @param array $params
     */
    public function __construct( $params )
    {
        parent::__construct();

        $this->defaultContentGroup = !empty($params['defaultContentGroup'])
            ? $params['defaultContentGroup']
            : null;

        $this->defaultPeriod = !empty($params['defaultPeriod'])
            ? $params['defaultPeriod']
            : BOL_SiteStatisticService::PERIOD_TYPE_TODAY;

    }

    /**
     * On before render
     *
     * @return void
     */
    public function onBeforeRender()
    {
        // get all registered content groups
        $contentGroups = BOL_ContentService::getInstance()->getContentGroups();

        // check the received group
        if (!array_key_exists($this->defaultContentGroup, $contentGroups) )
        {
            $this->setVisible(false);
            return false;
        }

        // get all group's entity types
        // e.g. forum-topic, forum-post, etc
        $entityTypes = $contentGroups[$this->defaultContentGroup]['entityTypes'];

        // TODO: Delete me or fix in a next version!!!
        if ( in_array('forum-post', $entityTypes) )
        {
            $key = array_search('forum-post', $entityTypes);
            unset($entityTypes[$key]);
        }

        // get detailed content types info
        $contentTypes = BOL_ContentService::getInstance()->getContentTypes();

        // get entity labels
        $entityLabels = array();
        foreach ($entityTypes as $entityType)
        {
            $entityLabels[$entityType] = $contentTypes[$entityType]['entityLabel'];
        }

        // register components
        $this->addComponent('statistics',
                new BASE_CMP_SiteStatistic('content-statistics-chart', $entityTypes, $entityLabels, $this->defaultPeriod));
    }
}

