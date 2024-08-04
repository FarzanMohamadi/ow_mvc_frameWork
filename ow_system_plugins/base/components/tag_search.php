<?php
/**
 * Tag search component. Works only for whole entity types.   
 * 
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_TagSearch extends OW_Component
{
    /**
     * @var string
     */
    private $url;
    /**
     * @var string
     */
    private $routeName;
    /**
     * @var string
     */
    private $box_label;
    /**
     * @var string
     */
    private $param_name;
    
    /**
     * BASE_CMP_TagSearch constructor.
     * @param null $url
     * @param string $label_lang_key
     */
    public function __construct( $url = null, $label_lang_key = 'base+tag_search', $param_name = 'tag', $show_tag_cloud=false,$entityType=false, $tagUrl=false , $tagsCount=false )
    {
        parent::__construct();
        $this->url = $url;
        $this->box_label = $label_lang_key;
        $this->param_name = $param_name;
        $this->show_tag_cloud = $show_tag_cloud;
        if($show_tag_cloud){
            $this->service = BOL_TagService::getInstance();
            $this->entityType = trim($entityType);
            $this->tagUrl = trim($tagUrl);
            $this->tagsCount = $tagsCount;
            $this->entityId = 0;
        }
    }

    /**
     * Sets route name for url generation. 
     * Route should be added to router and contain var - `tag`.
     * 
     * @param $routeName
     * @return BASE_CMP_TagSearch
     */
    public function setRouteName( $routeName )
    {
        $this->routeName = trim($routeName);
    }

    /**
     * @see OW_Renderable::onBeforeRender 
     */
    public function onBeforeRender()
    {
        $randId = rand(1, 100000);
        $formId = 'tag_search_form_' . $randId;
        $elId = 'tag_search_input_' . $randId;

        $this->assign('form_id', $formId);
        $this->assign('el_id', $elId);
        $this->assign('lang_label', $this->box_label);

        $mobileEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION,array('check' => true)));
        if(isset($mobileEvent->getData()['isMobileVersion'])&& $mobileEvent->getData()['isMobileVersion']==true) {
            $this->assign('isMobile', true);
        }

        $urlToRedirect = ($this->routeName === null) ? OW::getRequest()->buildUrlQueryString($this->url, array($this->param_name => '_tag_')) : OW::getRouter()->urlForRoute($this->routeName, array($this->param_name => '#tag#'));

        $script = "
			var tsVar" . $randId . " = '" . $urlToRedirect . "';
			
			$('#" . $formId . "').bind( 'submit', 
				function(){
					if( !$.trim( $('#" . $elId . "').val() ) )
					{
						OW.error(".  json_encode(OW::getLanguage()->text('base', 'tag_search_empty_value_error')).");
					}
					else
					{
						window.location = tsVar" . $randId . ".replace(/_tag_/, $('#" . $elId . "').val());
					}

					return false;  
				}
			);
		";

        OW::getDocument()->addOnloadScript($script);

    if($this->show_tag_cloud){

        $tagCloud = new BASE_CMP_EntityTagCloud($this->entityType, $this->tagUrl, $this->tagsCount);
        $tagCloud->setTemplate(OW::getPluginManager()->getPlugin('base')->getCmpViewDir() . 'big_tag_cloud.html');
        $this->assign('show_tag_cloud', $this->show_tag_cloud);
        $this->addComponent('tagCloud', $tagCloud);
    }

    }
}