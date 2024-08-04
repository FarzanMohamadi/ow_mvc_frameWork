<?php
/**
 * User statistics component
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.7.6
 */
class ADMIN_CMP_UserStatistic extends OW_Component
{
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
        if(!OW::getUser()->isAuthenticated() || !OW::getUser()->isAdmin()){
            throw new Redirect404Exception();
        }

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
        $entityTypes = array(
            'user_join',
            'user_login'
        );

        $entityLabels = array(
            'user_join' => OW::getLanguage()->text('admin', 'site_statistics_user_registrations'),
            'user_login' => OW::getLanguage()->text('admin', 'site_statistics_user_logins')
        );

        // register components
        $this->addComponent('statistics',
                new BASE_CMP_SiteStatistic('user-statistics-chart', $entityTypes, $entityLabels, $this->defaultPeriod));
    }
}

