<?php
/**
 * Avatar field form element.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_system_plugins.base.bol
 * @since 1.7.2
 */
class BASE_CLASS_AvatarField extends FormElement
{
    /**
     * @param string $name
     */
    public function __construct( $name, $changeUserAvatar = true )
    {
        parent::__construct($name);

        $this->changeUserAvatar = $changeUserAvatar;
        $this->addAttribute('type', 'file');
    }

    /**
     * @see FormElement::renderInput()
     *
     * @param array $params
     * @return string
     */
    public function renderInput( $params = null )
    {
        parent::renderInput($params);

        $deleteLabel = OW::getLanguage()->text('base', 'delete');

        if ( $this->value )
        {
            // hide the input
            $this->attributes = array_merge($this->attributes, array(
                'style' => 'display:none'
            ));
        }

        $markup = '<div class="ow_avatar_field">';
        $markup .= UTIL_HtmlTag::generateTag('input', $this->attributes);

        if ( !$this->value )
        {
            $markup .= '<div class="ow_avatar_field_preview" style="display: none;"><img src="" alt="" /><span title="'.$deleteLabel.'"></span></div>';
        }
        else 
        {
            $markup .= '<div class="ow_avatar_field_preview" style="display: block;"><img src="' . $this->value . '" alt="" /><span title="'.$deleteLabel.'"></span></div>';            
            $markup .= '<input type="hidden" id="' . $this->getId() . '_preload_avatar" name="avatarPreloaded" value="1" />';
        }
        $markup .= '<input type="hidden" id="' . $this->getId() . '_update_avatar" name="avatarUpdated" value="0" />';
        $markup .= '<input type="hidden" name="' . $this->attributes['name'] . '" value="' . $this->value . '" class="ow_avatar_field_value" />';
        $markup .= '</div>';

        return $markup;
    }

    public function getElementJs()
    {
        $params = array(
            'ajaxResponder' => OW::getRouter()->urlFor('BASE_CTRL_Avatar', 'ajaxResponder'),
            'changeUserAvatar' => $this->changeUserAvatar
        );
        $jsString = "var formElement = new OwAvatarField(" . json_encode($this->getId()) . ", " . json_encode($this->getName()) . ", ".json_encode($params).");";

        $jsString .= $this->generateValidatorAndFilterJsCode("formElement");

        $jsString .= "
			formElement.getValue = function(){

                var value = $(this.input).closest('.ow_avatar_field').find('.ow_avatar_field_value').val();

		        return value;
			};

			formElement.resetValue = function(){
                $(this.input).closest('.ow_avatar_field').find('.ow_avatar_field_value').val('');
                $(this.input).closest('.ow_avatar_field').find('input[name^=\'avatarUpdated\']').val(0);
            };

			formElement.setValue = function(value){
			    $(this.input).closest('.ow_avatar_field').find('.ow_avatar_field_value').val(value);
			    $(this.input).closest('.ow_avatar_field').find('input[name^=\'avatarUpdated\']').val(1);
			};
		";

        return $jsString;
    }
}