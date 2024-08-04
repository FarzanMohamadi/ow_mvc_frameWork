<?php
/**
 * @package ow_system_plugins.base.mobile.components
 * @since 1.6.0
 */
abstract class BASE_MCMP_AbstractButtonList extends OW_MobileComponent
{
    protected function prepareItem( $item, $defaultClass = "" )
    {
        $action = array();

        $action['label'] = $item["label"];
        $action['order'] = 999;

        $attrs = isset($item["attributes"]) && is_array($item["attributes"])
            ? $item["attributes"]
            : array();

        $attrs['class'] = empty($attrs['class']) 
                ? $defaultClass 
                : $defaultClass . " " . $attrs['class'];

        $attrs['href'] = isset($item["href"]) ? $item["href"] : 'javascript://';

        if ( isset($item["id"]) )
        {
            $attrs['id'] = $item["id"];
        }

        if ( isset($item["class"]) )
        {
            $attrs['class'] .= " " . $item["class"];
        }

        if ( isset($item["order"]) )
        {
            $action['order'] = $item["order"];
        }

        $_attrs = array();
        foreach ( $attrs as $name => $value )
        {
            $_attrs[] = $name . '="' . $value . '"';
        }

        $action['attrs'] = implode(' ', $_attrs);
        
        return $action;
    }
    
    protected function getSortedItems( $items )
    {
        usort($items, array($this, "itemsSorter"));
        
        return $items;
    }
    
    private function itemsSorter( $a, $b )
    {
        if ($a["order"] == $b["order"]) {
            return 0;
        }
        return ($a["order"] < $b["order"]) ? -1 : 1;
    }
}