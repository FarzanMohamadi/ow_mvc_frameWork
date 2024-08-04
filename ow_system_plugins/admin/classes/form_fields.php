<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.admin.class
 * @since 1.0
 */
class ColorField extends FormElement
{

    // need to remake with getElementJs method
    public function __construct( $name )
    {
        parent::__construct($name);
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('admin')->getStaticJsUrl() . 'color_picker.js');
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

        $output = '<div class="color_input"><input type="text" id="colorh_' . $this->getId() . '" name="' . $this->getName() . '" ' . ( $this->getValue() !== null ? '" value="' . $this->getValue() . '"' : '' ) . ' />' .
            '&nbsp;<input type="button" class="color_button" id="color_' . $this->getId() . '" style="background:' . ( $this->getValue() !== null ? $this->getValue() : '' ) . '" />
        <div style="display:none;"><div id="colorcont_' . $this->getId() . '"></div></div></div>';

        $varName = rand(10, 100000);

        $js = "var callback" . $varName . " = function(color){
            $('#colorh_" . $this->getId() . "').attr('value', color);
            $('#color_" . $this->getId() . "').css({backgroundColor:color});
            window.colorPickers['" . $this->getId() . "'].close();
        };
        new ColorPicker($('#colorcont_" . $this->getId() . "'), callback" . $varName . ", '" . $this->getValue() . "');
        $('#color_" . $this->getId() . "').click(
            function(){
                if( !window.colorPickers )
                {
                    window.colorPickers = {};
                }
                window.colorPickers['" . $this->getId() . "'] = new OW_FloatBox({\$contents:$('#colorcont_" . $this->getId() . "'), \$title:'Color Picker'});
            }
        );";

        OW::getDocument()->addOnloadScript($js);

        return $output;
    }
}

class addValueField extends FormElement
{
    protected $tag;
    protected $disabled;

    // need to remake with getElementJs method
    public function __construct( $name )
    {
        parent::__construct($name);
        
        $tagFieldName = 'input_'  . $this->getName() . '_tag_field';
        $this->tag = new TagsInputField($tagFieldName);
        $this->tag->setMinChars(1);
        $this->value = array();
    }

    public function setValue( $value )
    {
        $values = array();
        
        if ( is_array($value) )
        {
            $this->setArrayValue($value);

            /* if ( isset($value['values']) && is_array($value['values']) )
            {
                $this->setArrayValue($value['values']);
            }
            else
            {
                $this->setArrayValue($value);
            }*/
        }
        else if ( is_string($value) )
        {
            $valueList = json_decode($value, true);

            $result = array();
            
            if ( empty($valueList) )
            {
                return;
            }

            ksort($valueList);
            
            foreach ( $valueList as $order => $val )
            {
                foreach ( $val as $k => $v )
                {
                    $result[$k] = $v;
                }
            }
                
            $this->setArrayValue($result);
        }

        return $this;
    }


    protected function setArrayValue( $value )
    {
        $values = array();

        if ( !empty($value) )
        {
            $count = 0;

            foreach ( $value as $key => $label )
            {
                if ( !empty($key) && isset($label) )
                {
                    $values[$key] = $label;
                    $count++;
                }

                if ( $count > 65 )
                {
                    break;
                }
            }
        }

        $this->value = $values;
    }


    public function setDisabled( $disabled = true )
    {
        $this->disabled = $disabled;
    }
    /* public function getElementJs()
    {
        $jsString = parent::getElementJs();
        $jsString .= " " . $this->tag->getElementJs();
        return $jsString;
    } */

    /**
     * @see FormElement::renderInput()
     *
     * @param array $params
     * @return string
     */
    public function renderInput( $params = null )
    {
        if ( $this->disabled )
        {
            $attributes = $this->attributes;

            unset($attributes['name']);

            $message = OW::getLanguage()->text('admin', 'possible_values_disable_message');

            $event = new OW_Event('admin.get.possible_values_disable_message', array('name' => $this->getName(), 'id' => $this->getId() ), $message);
            OW::getEventManager()->trigger($event);

            $message = $event->getData();

            return UTIL_HtmlTag::generateTag('div', $attributes, true, $message);
        }

        parent::renderInput($params);

        $label = OW::getLanguage()->text('admin', 'remove_value');
        $label = preg_replace('/\\\u([0-9A-Fa-f]+)/', '&#x$1;', $label);
        $label = html_entity_decode($label, ENT_COMPAT, 'UTF-8');
        $template = '
                        <div class="clearfix question_value_block" style="cursor:move;">
                                <span class="tag">
                                    <input type="hidden" value="{$value}">
                                    <span class="label" style="max-width:250px;overflow:hidden;">{$label}</span>
                                    <a title="'.$label.'" class="remove" href="javascript://"></a>
                                </span>
                        </div>';

        $template = UTIL_String::replaceVars($template, array('label' => '', 'value' => 0));
        
        $addButtonName = $this->getName() . '_add_button';
        $addButtonId = $addButtonName . '_' . rand(0, 10000);

        $jsDir = OW::getPluginManager()->getPlugin("admin")->getStaticJsUrl();
        OW::getDocument()->addScript($jsDir . "questions.js");
        
        $json = json_encode(array( 'tagFieldId' => $this->tag->getId(), 'dataFieldId' => $this->getId(), 'value' =>  $this->value, 'order' =>  array_keys($this->value), 'template' => $template ));
        
        OW::getDocument()->addOnloadScript("
            if ( !window.addQuestionValues )
            {
                window.addQuestionValues = {};
            }

            window.addQuestionValues[".json_encode($this->getId())."] = new questionValuesField(" . $json . "); ");

        OW::getLanguage()->addKeyForJs('admin', 'questions_edit_delete_value_confirm_message');
 
        $inputValues = array();
                
        foreach ( $this->value as $key => $val )
        {
            $inputValues[] = array($key => $val);
        }
        
        $html =
            "<script>
            $('#".$this->tag->getId()."').on('keypress', function (e) {
                if(e.keyCode == 13)
                {
                    e.preventDefault();  
                    e.returnValue = false;
                    e.cancelBubble = true;
                    $('#$addButtonId').click();
                    return false;
                }
            });
            </script>"
            .
            '<div class="values_list">
                </div>
                <input type="hidden" id='.json_encode($this->getId()).' name='.json_encode($this->getName()).' value=' . json_encode($inputValues) . ' />
                <input type="hidden" id='.json_encode($this->getId()."_deleted_values").' name='.json_encode($this->getName() . "_deleted_values").' value="" />
                <div style="padding-left: 4px;" class="ow_smallmargin">'.OW::getLanguage()->text('admin', 'add_question_value_description').'</div>
                <div class="clearfix">
                    <div class="ow_left" style="width: 260px;">'.($this->tag->renderInput()).'</div>
                    <div class="ow_right">
                        <span class="ow_button">
                            <span class="ow_ic_add">
                                <input type="button" value='.OW::getLanguage()->text('admin', 'add_button').' class="ow_ic_add" name="'.$addButtonName.'" id="'.$addButtonId.'">
                            </span>
                        </span>
                    </div>
                </div>';
                
        return $html;
    }
}

class infiniteValueField extends addValueField
{
    protected function setArrayValue( $value )
    {
        $values = array();

        if ( !empty($value) )
        {
            $count = 0;

            foreach ( $value as $key => $label )
            {
                if ( isset($label) )
                {
                    $values[$key] = $label;
                    $count++;
                }
            }
        }
        $this->value = $values;
    }

    /**
     * @see FormElement::renderInput()
     *
     * @param array $params
     * @return string
     */
    public function renderInput( $params = null )
    {
        $html = parent::renderInput($params);
        $label = OW::getLanguage()->text('admin', 'remove_value');
        $label = preg_replace('/\\\u([0-9A-Fa-f]+)/', '&#x$1;', $label);
        $label = html_entity_decode($label, ENT_COMPAT, 'UTF-8');
        $template = '
                        <div class="clearfix question_value_block" style="cursor:move;">
                                <span class="tag">
                                    <input type="hidden" value="{$value}">
                                    <span class="label" style="max-width:250px;overflow:hidden;">{$label}</span>
                                    <a title="'.$label.'" class="remove" href="javascript://"></a>
                                </span>
                        </div>';

        $template = UTIL_String::replaceVars($template, array('label' => '', 'value' => 0));

        $json = json_encode(
            array(
                'tagFieldId' => $this->tag->getId(),
                'dataFieldId' => $this->getId(),
                'value' =>  $this->value,
                'order' =>  array_keys($this->value),
                'template' => $template
            )
        );

        OW::getDocument()->addOnloadScript("
            if ( !window.addInfiniteQuestionValues )
            {
                window.addInfiniteQuestionValues = {};
            }

            window.addInfiniteQuestionValues[".json_encode($this->getId())."] = new infiniteQuestionValuesField(" . $json . "); ");

        return $html;
    }

}
