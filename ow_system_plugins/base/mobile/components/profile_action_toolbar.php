<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.mobile.components
 * @since 1.6.0
 */
class BASE_MCMP_ProfileActionToolbar extends BASE_MCMP_ButtonList
{
    const EVENT_NAME = 'base.mobile.add_profile_action_toolbar';

    protected $userId;

    /**
     * Constructor.
     */
    public function __construct( $userId )
    {
        parent::__construct(array());
        
        $this->userId = (int) $userId;
        
        $event = new BASE_CLASS_EventCollector(self::EVENT_NAME, array('userId' => $this->userId));

        OW::getEventManager()->trigger($event);

        $addedData = $event->getData();
        
        if ( empty($addedData) )
        {
            $this->setVisible(false);

            return;
        }
        
        $this->items = $addedData;
    }
}