<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.mobile.components
 * @since 1.6.0
 */
class BASE_MCMP_ProfileContentMenu extends OW_MobileComponent
{
    const EVENT_NAME = 'base.mobile.add_profile_content_menu';
    const DATA_KEY_LABEL = 'label';
    const DATA_KEY_THUMB = 'thumb';
    const DATA_KEY_LINK_ID = 'id';
    const DATA_KEY_LINK_CLASS = 'linkClass';
    const DATA_KEY_LINK_HREF = 'href';
    const DATA_KEY_LINK_ORDER = 'order';
    const DATA_KEY_LINK_ATTRIBUTES = 'attributes';

    /**
     *
     * @var BOL_User
     */
    protected $user;

    /**
     * Constructor.
     */
    public function __construct( BOL_User $user )
    {
        parent::__construct();

        $this->user = $user;
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $event = new BASE_CLASS_EventCollector(self::EVENT_NAME, array('userId' => $this->user->id));

        OW::getEventManager()->trigger($event);

        $addedData = $event->getData();

        if ( empty($addedData) )
        {
            $this->setVisible(false);

            return;
        }

        $this->initMenu($addedData);
    }

    public function initMenu( $items )
    {
        $tplActions = array();

        foreach ( $items as $item  )
        {
            $action = &$tplActions[];

            $action['label'] = $item[self::DATA_KEY_LABEL];
            $action['order'] = count($tplActions);

            $attrs = isset($item[self::DATA_KEY_LINK_ATTRIBUTES]) && is_array($item[self::DATA_KEY_LINK_ATTRIBUTES])
                ? $item[self::DATA_KEY_LINK_ATTRIBUTES]
                : array();

            $attrs['href'] = isset($item[self::DATA_KEY_LINK_HREF]) ? $item[self::DATA_KEY_LINK_HREF] : 'javascript://';

            if ( isset($item[self::DATA_KEY_LINK_ID]) )
            {
                $attrs['id'] = $item[self::DATA_KEY_LINK_ID];
            }

            if ( isset($item[self::DATA_KEY_LINK_CLASS]) )
            {
                $action['class'] = $item[self::DATA_KEY_LINK_CLASS];
            }

            if ( isset($item[self::DATA_KEY_LINK_ORDER]) )
            {
                $action['order'] = $item[self::DATA_KEY_LINK_ORDER];
            }

            if ( isset($item[self::DATA_KEY_THUMB]) )
            {
                $action['img'] = $item[self::DATA_KEY_THUMB];
            }

            $_attrs = array();
            foreach ( $attrs as $name => $value )
            {
                $_attrs[] = $name . '="' . $value . '"';
            }

            $action['attrs'] = implode(' ', $_attrs);
        }

        $this->assign('actions', $tplActions);
    }
}