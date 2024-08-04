<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugin.mailbox.classes
 * @since 1.6.1
 * */
class MAILBOX_CLASS_SearchField extends TextField
{
    public $showCloseButton = true;

    /**
     * @see FormElement::renderInput()
     *
     * @param array $params
     * @return string
     */
    public function renderInput( $params = null )
    {
        $tag = parent::renderInput($params);

        if ($this->showCloseButton)
        {
            $tag .= '<a href="javascript://" class="ow_btn_close_search" id="'.$this->attributes['name'].'_close_btn_search"></a>';
        }

        return $tag;
    }
}