<?php
/**
 * Search component class.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmadvancesearch.classes
 * @since 1.0
 */
class FRMADVANCESEARCH_CMP_Search extends OW_Component
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $isGuestAllowed = (boolean)OW::getConfig()->getValue('frmadvancesearch','show_search_to_guest');
        if(!OW::getUser()->isAuthenticated() && !$isGuestAllowed) {
            exit();
        }
        parent::__construct();
        $resultData = array();
        $event = OW::getEventManager()->trigger(new OW_Event('frmadvancesearch.on_collect_search_items',
            array('q' => 'collecting plugin names', 'maxCount' => 10, 'do_query' => false), $resultData));
        $resultData = $event->getData();

        $pluginNames[] =OW_Language::getInstance()->text('frmadvancesearch','all_sections');
        foreach($resultData as $key => $value){
            $tmpFieldKey = 'search_allowed_'.$key;
            if(OW::getConfig()->configExists('frmadvancesearch',$tmpFieldKey)){
                $isAllowed = OW::getConfig()->getValue('frmadvancesearch',$tmpFieldKey);
                if($isAllowed){
                    $pluginNames[] = $value["label"];
                }
            }
            else
                $pluginNames [] = $value["label"];
        }
        $this->assign('pluginNames' ,$pluginNames);

        $searchActionUrl = OW::getRouter()->urlForRoute('frmadvancesearch.search');
        if (strpos($searchActionUrl, 'INVALID_URI') !== false) {
            $this->assign('invalidUrl', true);
        }

        if(OW::getConfig()->configExists('frmadvancesearch','show_entity_author')){
            $isAllowedSowAuthor = OW::getConfig()->getValue('frmadvancesearch','show_entity_author');
            if(!$isAllowedSowAuthor){
                OW::getDocument()->addStyleDeclaration('.result_search_item a.label {display: none;}');
            }
        }

        $this->assign('searchActionUrl', $searchActionUrl);
    }
}