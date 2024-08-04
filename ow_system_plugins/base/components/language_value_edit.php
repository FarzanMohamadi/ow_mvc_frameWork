<?php
/**
 * Language value edit component class. 
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_LanguageValueEdit extends OW_Component
{
    public $eventBased;
    
	public function __construct( $prefix, $key, $eventBased = false )
	{
		parent::__construct();

		$this->eventBased = $eventBased;
		
		$this->addForm(new LanguageValueEditForm($prefix, $key, $this));
	}

	public static function process( $prefix, $key )
	{
            $languageService = BOL_LanguageService::getInstance();
            $list = $languageService->findActiveList();
            $currentLanguageId = OW::getLanguage()->getCurrentId();
            $currentLangValue = "";

            foreach ( $list as $item )
            {
                    $keyDto = $languageService->findKey($prefix, $key);

                    if ( empty($keyDto) )
                    {
                        $prefixDto = $languageService->findPrefix($prefix);
                        $keyDto = $languageService->addKey($prefixDto->getId(), $key);
                    }

                    $value = trim($_POST['lang'][$item->getId()][$prefix][$key]);

            
                    if ( mb_strlen(trim($value)) == 0 || $value == json_decode('"\u00a0"') ) // stupid hack
                    {
                        $value = '&nbsp;';
                    }

                    $dto = $languageService->findValue($item->getId(), $keyDto->getId());

                    if ( $dto !== null )
                    {
                        $event = new OW_Event('admin.before_save_lang_value', array('dto'=>$dto));
                        OW::getEventManager()->trigger($event);

                            if ( $dto->getValue() !== $value )
                            {
                                $languageService->saveValue($dto->setValue($value));
                            }
                    }
                    else
                    {
                            $dto = $languageService->addValue($item->getId(), $prefix, $key, $value);
                    }

                if ( (int) $currentLanguageId === (int) $item->getId() )
                {
                    $currentLangValue = $value;
                }
            }

            exit(json_encode(array('result' => 'success', 'prefix' => $prefix, 'key' => $key, 'value' => $currentLangValue)));
	}
}

class LanguageValueEditForm extends Form
{
    /**
     * 
     * Constructor
     * @param $prefix
     * @param $key
     * @param BASE_CMP_LanguageValueEdit $parent
     */
	public function __construct( $prefix, $key, $parent )
	{
		parent::__construct('lang-values-edit');

		$this->setAjax(true);
		$this->setAction(OW::getRouter()->urlFor('ADMIN_CTRL_Languages', 'ajaxEditLangs')."?prefix={$prefix}&key={$key}");

		$languageService = BOL_LanguageService::getInstance();
		$list = $languageService->findActiveList();

		$parent->assign('langs', $list);
		$parent->assign('prefix', $prefix);
		$parent->assign('key', $key);

		foreach ( $list as $item )
		{
			$textArea = new Textarea("lang[{$item->getId()}][{$prefix}][{$key}]");
			$dto = $languageService->getValue($item->getId(), $prefix, $key);

			$value = ($dto !== null)? $dto->getValue(): '';

			$textArea->setValue($value);

			$this->addElement($textArea);
		}

		$submit = new Submit('submit');
		$submit->setValue(OW::getLanguage()->text('admin', 'save_btn_label'));
		$submit->addAttribute('class', 'ow_button ow_ic_save');

		if ( !$parent->eventBased )
		{
    		$jsString = 'owForms[{$formName}].bind("success", function(json){
                if ( json["result"] == "success") {
                    var fb = document.ajaxLangValueEditForms[ json["prefix"] +"-"+ json["key"] ];
                    var ff = document.ajaxLangValueEditForms[json["prefix"] +"-"+json["key"]+"callback"];
                    ff(json);
                    fb.close();
                }
            })';
		}
		else
		{
            $jsString = 'owForms[{$formName}].bind("success", function(json){
                if ( json["result"] == "success") {
                    OW.trigger("admin.language_key_edit_success", [json], this);
                }
            })';
		}
		
		$script = UTIL_JsGenerator::composeJsString($jsString, array(
		  'formName' => $this->getName()
		));
		
		OW::getDocument()->addOnloadScript($script);

		$this->addElement($submit);
	}
}
