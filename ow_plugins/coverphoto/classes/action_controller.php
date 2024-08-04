<?php
class COVERPHOTO_CLASS_ActionController extends OW_ActionController
{
    /**
     *
     * @var COVERPHOTO_BOL_Service
     */
    protected $service;

    public function init()
    {
        $this->service = COVERPHOTO_BOL_Service::getInstance();

        OW::getNavigation()->activateMenuItem('main', 'coverphoto', 'main_menu_item');

        if (!OW::getUser()->isAuthenticated())
        {
            $this->redirect(OW::getRouter()->urlForRoute('static_sign_in'));
        }
    }
}

