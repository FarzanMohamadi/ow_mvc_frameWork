<?php
class FRMGRAPH_CTRL_Public extends OW_ActionController
{
    /**
     * FRMGRAPH_CTRL_Graph constructor.
     * @throws Redirect404Exception if the user has no access to view the FRMGraph pages
     */
    public function __construct()
    {
        $this->setDocumentKey('frmgraph');
    }

    public function topUsers() {
        $topUsersCmp = new FRMGRAPH_CMP_TopUsers(false, 100, true, 20);
        $this->addComponent('topUsers', $topUsersCmp);

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmgraph')->getStaticJsUrl() . 'countUp.js', 'text/javascript', (-100));
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('frmgraph')->getStaticCssUrl() . 'countup.css');
    }
}
