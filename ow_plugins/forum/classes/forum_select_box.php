<?php
/**
 * Forum selectbox field class.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.classes
 * @since 1.0
 */
class ForumSelectBox extends Selectbox
{

    /**
     * Class constructor
     *
     * @param array $params
     */
    public function __construct( $params = null )
    {
        parent::__construct($params);
    }

    /**
     * @see FormElement::renderInput()
     *
     * @param array $params
     * @return string
     */
    public function renderInput( $params = null )
    {
        $optionsString = '';

        foreach ( $this->getOptions() as $option )
        {
            $attrs = (!is_null($this->value) && $option['value'] == $this->value) ? array('selected' => 'selected') : array();

            $attrs['value'] = $option['value'];

            if ( $option['disabled'] )
            {
                $attrs['disabled'] = $option['disabled'];
                $attrs['class'] = 'disabled_option';
            }

            $optionsString .= UTIL_HtmlTag::generateTag('option', $attrs, true, trim($option['label']));
        }

        return UTIL_HtmlTag::generateTag('select', $this->attributes, true, $optionsString);
    }
}
