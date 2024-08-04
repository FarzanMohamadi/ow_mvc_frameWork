<?php
/**
 * Singleton. 'Flag' Data Access Object
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_Paging extends OW_Component
{

    function __construct( $page, $count, $range, $prefix = "",$baseUrl=null )
    {
        parent::__construct();

        $less = false;
        $more = false;

        $prev = $page > 1;
        $next = !($count == $page);

        if ( $count < 2 )
        {
            $this->setVisible(false);
            return;
        }

        if ( $count <= $range )
        {
            $start = 0;
            $range = $count;
        }
        else
        {
            if ( ceil($range / 2) > ($count - $page) )
            {
                $start = $count - $range;
                $less = true;
            }
            else
            {
                $more = true;

                if ( $page <= ceil($range / 2) )
                {
                    $start = 0;
                }
                else
                {
                    $start = $page - ceil($range / 2);
                    $less = true;
                }
            }
        }

        $this->assign('less', $less);
        $this->assign('more', $more);

        $this->assign('prev', $prev);
        $this->assign('next', $next);

        $this->assign('start', $start);

        $range = $range < $count ? $range : $count;

        $this->assign('page', $page);
        $this->assign('prefix', $prefix);
        $this->assign('page_shortcut_count', $range);
        $this->assign('count', $count);

        $this->assign('url', OW::getRequest()->buildUrlQueryString($baseUrl, array("{$prefix}page" => null)));
    }
}

?>