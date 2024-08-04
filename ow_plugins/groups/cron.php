<?php
/**
 * Groups cron job.
 *
 * @package ow.ow_plugins.groups.bol
 * @since 1.0
 */
class GROUPS_Cron extends OW_Cron
{
    const GROUPS_DELETE_LIMIT = 50;

    public function getRunInterval()
    {
        return 1;
    }

    public function run()
    {
        $config = OW::getConfig();

        // check if uninstall is in progress
        if ( !$config->getValue('groups', 'uninstall_inprogress') )
        {
            return;
        }

        if ( !$config->configExists('groups', 'uninstall_cron_busy') )
        {
            $config->addConfig('groups', 'uninstall_cron_busy', 0);
        }

        // check if cron queue is not busy
        if ( $config->getValue('groups', 'uninstall_cron_busy') )
        {
            return;
        }

        $config->saveConfig('groups', 'uninstall_cron_busy', 1);
        $service = GROUPS_BOL_Service::getInstance();

        try
        {
            $groups = $service->findLimitedList(self::GROUPS_DELETE_LIMIT);

            if ( empty($groups) )
            {
                BOL_PluginService::getInstance()->uninstall('groups');
                OW::getApplication()->setMaintenanceMode(false);
                $config->saveConfig('groups', 'uninstall_inprogress', 0);

                return;
            }

            foreach ( $groups as $group )
            {
                $service->deleteGroup($group->id);
            }

            OW::getEventManager()->trigger(new OW_Event(GROUPS_BOL_Service::EVENT_UNINSTALL_IN_PROGRESS));
        } finally {
            $config->saveConfig('groups', 'uninstall_cron_busy', 0);
        }
    }
}