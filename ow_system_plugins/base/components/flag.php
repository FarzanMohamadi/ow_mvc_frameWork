<?php
/**
 * Singleton. 'Flag' Data Access Object
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_Flag extends OW_Component
{

    public function __construct( $entityType, $entityId )
    {
        parent::__construct();

        $this->addForm(new FlagForm($entityType, $entityId));
    }
}

class FlagForm extends Form
{

    public function __construct( $entityType, $entityId )
    {
        parent::__construct('flag');

        $this->setAjax(true);

        $this->setAction(OW::getRouter()->urlFor('BASE_CTRL_Flag', 'flag'));

        $element = new HiddenField('entityType');
        $element->setValue($entityType);
        $this->addElement($element);
        
        $element = new HiddenField('entityId');
        $element->setValue($entityId);
        $this->addElement($element);
        

        $element = new RadioField('reason');
        $element->setOptions(array(
            'spam' => OW::getLanguage()->text('base', 'flag_spam'),
            'offence' => OW::getLanguage()->text('base', 'flag_offence'),
            'illegal' => OW::getLanguage()->text('base', 'flag_illegal'))
        );

        $flagDto = BOL_FlagService::getInstance()->findFlag($entityType, $entityId, OW::getUser()->getId());
        
        if ( $flagDto !== null )
        {
            $element->setValue($flagDto->reason);
        }

        $this->addElement($element);

        OW::getDocument()->addOnloadScript(
            "owForms['{$this->getName()}'].bind('success', function(json){
                if (json['result'] == 'success') {
                    _scope.floatBox && _scope.floatBox.close();
                    OW.addScript(json.js);
                }
            })");
        OW::getDocument()->addStyleDeclaration("ul.ow_radio_group li{
        padding: 5px 5px 2px 5px;
        margin-bottom: 5px;
        border: 1px solid #dfdedeba;
        background-color: #dfdede47;
        -webkit-border-radius: 5px;
        -moz-border-radius: 5px;
        border-radius: 5px;
        width: 91% !important;
        margin-right: 5%;
        };"
       );
    }
}