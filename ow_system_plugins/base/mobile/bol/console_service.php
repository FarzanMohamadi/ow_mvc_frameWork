<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.mobile.bol
 * @since 1.6.0
 */
class MBOL_ConsoleService
{
    /**
     * @var MBOL_ConsoleService
     */
    private static $classInstance;

    const EVENT_COLLECT_CONSOLE_PAGES = 'mconsole.collect_pages';
    const EVENT_COLLECT_CONSOLE_PAGE_SECTIONS = 'mconsole.collect_page_sections';
    const EVENT_COLLECT_CONSOLE_PAGE_NEW_ITEMS = 'mconsole.collect_page_new_items';
    const EVENT_COUNT_CONSOLE_PAGE_NEW_ITEMS = 'mconsole.count_page_new_items';

    const SECTION_ITEMS_LIMIT = 3;

    /**
     * @return MBOL_ConsoleService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Constructor.
     */
    private function __construct()
    {

    }

    public function getPages()
    {
        // collect console pages
        $event = new BASE_CLASS_EventCollector(self::EVENT_COLLECT_CONSOLE_PAGES);
        OW::getEventManager()->trigger($event);
        $data = $event->getData();

        $pages = array();
        foreach ( $data as $item )
        {
            $cmp = $item['cmpClass'];
            $order = isset($item['order']) ? (int) $item['order'] : count($pages);
            $key = isset($item['key']) ? $item['key'] : FRMSecurityProvider::generateUniqueId();
            $counter = !empty($item['counter']) ? (int) $item['counter'] : null;

            $pages[$key] = array('order' => $order, 'key' => $key, 'counter' => $counter, 'cmpClass' => $cmp);
        }

        usort($pages, array($this, 'sortItems'));

        return $pages;
    }

    public function getPageSections( $page )
    {
        if ( !mb_strlen($page) )
        {
            return array();
        }

        $params = array('page' => $page);
        $event = new BASE_CLASS_EventCollector(
            MBOL_ConsoleService::EVENT_COLLECT_CONSOLE_PAGE_SECTIONS,
            $params
        );
        OW::getEventManager()->trigger($event);
        $items = $event->getData();

        $sections = array();
        foreach ( $items as $item )
        {
            $cmp = $item['component'];
            $order = isset($item['order']) ? (int) $item['order'] : count($sections);

            /** @var $cmp OW_MobileComponent */
            if ( is_subclass_of($cmp, 'OW_Renderable') && $cmp->isVisible() )
            {
                $sections[] = array(
                    'item' => $cmp->render(),
                    'order' => $order
                );
            }
        }

        usort($sections, array($this, 'sortItems'));

        return $sections;
    }

    public function countPageNewItems( $page )
    {
        if ( !mb_strlen($page) )
        {
            return null;
        }

        $params = array('page' => $page);
        $event = new BASE_CLASS_EventCollector(
            MBOL_ConsoleService::EVENT_COUNT_CONSOLE_PAGE_NEW_ITEMS,
            $params
        );
        OW::getEventManager()->trigger($event);
        $items = $event->getData();

        if ( !$items )
        {
            return 0;
        }

        $total = 0;
        foreach ( $items as $item )
        {
            $total += array_shift($item);
        }

        return $total;
    }

    public function countNewItems()
    {
        $pages = $this->getPages();

        $total = 0;
        foreach ( $pages as $page )
        {
            $total += $this->countPageNewItems($page['key']);
        }

        return $total;
    }

    public function getNewItems( $page, $timestamp )
    {
        $params = array('page' => $page, 'timestamp' => $timestamp);
        $event = new BASE_CLASS_EventCollector(
            MBOL_ConsoleService::EVENT_COLLECT_CONSOLE_PAGE_NEW_ITEMS,
            $params
        );
        OW::getEventManager()->trigger($event);
        $items = $event->getData();

        $result = array();
        if ( $items )
        {
            foreach ( $items as $item )
            {
                $key = key($item);
                $cmp = array_shift($item);

                if ( is_subclass_of($cmp, 'OW_Renderable') )
                {
                    $result[$key] = $cmp->render();
                }
            }
        }

        return $result;
    }

    public function sortItems( $p1, $p2 )
    {
        $a = (int) $p1['order'];
        $b = (int) $p2['order'];

        if ( $a == $b )
        {
            return 0;
        }

        return ($a > $b) ? 1 : -1;
    }
}