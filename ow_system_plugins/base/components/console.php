<?php
/**
 * User console component class.
 *
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_Console extends OW_Component
{

    const EVENT_NAME = 'console.collect_items';
    const RENDER_EVENT_NAME = 'console.before_item_render';
    const AFTER_CONSOLE_ITEM_COLLECTED = 'after.console.item.collected';

    const ALIGN_LEFT = -1;
    const ALIGN_RIGHT = 0;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $event = new BASE_CLASS_ConsoleItemCollector(self::EVENT_NAME);
        OW::getEventManager()->trigger($event);
        $items = $event->getData();

        $event = new BASE_CLASS_ConsoleItemCollector(self::AFTER_CONSOLE_ITEM_COLLECTED, array('items' => $items));
        OW::getEventManager()->trigger($event);
        if(isset($event->getData()[0])){
            $items = $event->getData()[0];
        }

        $resultItems = array();

        foreach ( $items as $item )
        {
            $itemCmp = null;
            $order = self::ALIGN_LEFT;
            if ( is_array($item) )
            {
                if ( empty($item['item']) )
                {
                    continue;
                }

                $itemCmp = $item['item'];

                $order = isset($item['order']) ? $item['order'] : self::ALIGN_LEFT;
            }
            else
            {
                $itemCmp = $item;
            }

            if ( $order == self::ALIGN_LEFT )
            {
                $order = count($resultItems);
            }

            $resultItem = array(
                "item" => $itemCmp,
                "order" => $order
            );

            $renderEvent = new OW_Event(self::RENDER_EVENT_NAME, $resultItem, $resultItem);
            OW::getEventManager()->trigger($renderEvent);
            $resultItem = $renderEvent->getData();

            $itemCmp = $resultItem['item'];
            $order = $resultItem['order'];

            if ( is_subclass_of($itemCmp, 'OW_Renderable') && $itemCmp->isVisible() )
            {
                $resultItems[] = array(
                    'item' => $itemCmp->render(),
                    'order' => $order
                );
            }
        }

        usort($resultItems, array($this, '_sortItems'));

        $tplItems = array();

        foreach ( $resultItems as $item )
        {
            $tplItems[] = $item['item'];
        }

        $this->assign('items', $tplItems);


        $jsUrl = OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'console.js';
        OW::getDocument()->addScript($jsUrl);

        $event = new OW_Event(BASE_CTRL_Ping::PING_EVENT . '.consoleUpdate');
        OW::getEventManager()->trigger($event);

        $params = array(
            'pingInterval' => FRMSecurityProvider::getDefaultPingIntervalInSeconds() * 1000
        );

        $js = UTIL_JsGenerator::newInstance();
        $js->newObject(array('OW', 'Console'), 'OW_Console', array($params, $event->getData()));

        OW::getDocument()->addOnloadScript($js, 900);
    }

    public function _sortItems( $item1, $item2 )
    {
        $a = (int) $item1['order'];
        $b = (int) $item2['order'];

        if ($a == $b)
        {
            return 0;
        }

        return ($a > $b) ? -1 : 1;
    }




    /* Deprecated Block */

    const DATA_KEY_ICON_CLASS = 'icon_class';
    const DATA_KEY_URL = 'url';
    const DATA_KEY_ID = 'id';
    const DATA_KEY_BLOCK = 'block';
    const DATA_KEY_BLOCK_ID = 'block_id';
    const DATA_KEY_ITEMS_LABEL = 'block_items_count';
    const DATA_KEY_BLOCK_CLASS = 'block_class';
    const DATA_KEY_TITLE = 'title';
    const DATA_KEY_HIDDEN_CONTENT = 'hidden_content';

    const VALUE_BLOCK_CLASS_GREEN = 'ow_mild_green';
    const VALUE_BLOCK_CLASS_RED = 'ow_mild_red';

}