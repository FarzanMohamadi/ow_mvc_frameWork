<?php
/**
 * 
 * 
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.photo.classes
 * @since 1.6.1
 */
class PHOTO_CLASS_HashtagFormElement extends Textarea
{
    public function __construct( $name )
    {
        parent::__construct($name);
        
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('photo')->getStaticCssUrl() . 'edit_photo.css');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('photo')->getStaticJsUrl() . 'codemirror.min.js');
    }
    
    public function renderInput($params = null)
    {
        OW::getDocument()->addOnloadScript('
            var _a = $("<a>", {class: "ow_hidden ow_content a"}).appendTo(document.body);
            OW.addCss(".cm-hashtag{cursor:pointer;color:" + _a.css("color") + "}");
            _a.remove();
        ');
        return parent::renderInput($params);
    }
    
    public function getElementJs()
    {
        $jsString = 'var formElement = new OwTextArea(' . json_encode($this->getId()) . ', ' . json_encode($this->getName()) . ', ' . json_encode(( $this->getHasInvitation() ? $this->getInvitation() : false)) . ');';

        $jsString .= '
            var editor = CodeMirror.fromTextArea(document.getElementById(' . json_encode($this->getId()) . '), {mode: "text/hashtag", lineWrapping: true, smartIndent: false, dragDrop: false});
            editor.setSize(360, 170);
            formElement.getValue = function()
            {
                return editor.getValue();
            };
            formElement.editor = editor;';

        return $jsString;
    }
}
