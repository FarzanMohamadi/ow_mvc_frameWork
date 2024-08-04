<?php
/**
 * Created by PhpStorm.
 * User: Seyed Ismail Mirvakili
 * Date: 3/3/18
 * Time: 10:58 AM
 */
class FRMQUESTIONS_CLASS_UrlMapping
{
    public function init(){
        OW::getRouter()->addRoute(new OW_Route('frmquestions-index', 'frmquestions', 'FRMQUESTIONS_CTRL_Question', 'questionHome'));
        OW::getRouter()->addRoute(new OW_Route('frmquestions-home', 'frmquestion_home/:type', 'FRMQUESTIONS_CTRL_Question', 'questionHome'));
        OW::getRouter()->addRoute(new OW_Route('frmquestion-create', 'frmquestions/create', 'FRMQUESTIONS_CTRL_Question', 'createQuestion'));
        OW::getRouter()->addRoute(new OW_Route('frmquestion-reload', 'frmquestions/reload', 'FRMQUESTIONS_CTRL_Question', 'reloadQuestion'));
        OW::getRouter()->addRoute(new OW_Route('frmquestion-edit', 'frmquestions/edit', 'FRMQUESTIONS_CTRL_Question', 'editQuestion'));
        OW::getRouter()->addRoute(new OW_Route('frmquestion-info', 'frmquestions/info', 'FRMQUESTIONS_CTRL_Question', 'getQuestionInfo'));
        OW::getRouter()->addRoute(new OW_Route('frmquestion-answer', 'frmquestions/answer', 'FRMQUESTIONS_CTRL_Question', 'answer'));
        OW::getRouter()->addRoute(new OW_Route('frmoption-add', 'frmquestions/options/add', 'FRMQUESTIONS_CTRL_Question', 'addOption'));
        OW::getRouter()->addRoute(new OW_Route('frmoption-delete', 'frmquestions/options/delete', 'FRMQUESTIONS_CTRL_Question', 'deleteOption'));
        OW::getRouter()->addRoute(new OW_Route('frmoption-edit', 'frmquestions/options/edit', 'FRMQUESTIONS_CTRL_Question', 'editOption'));
        OW::getRouter()->addRoute(new OW_Route('frmquestion-subscribe', 'frmquestions/subscribe', 'FRMQUESTIONS_CTRL_Question', 'subscribe'));
        OW::getRouter()->addRoute(new OW_Route('frmquestion-delete', 'frmquestions/delete', 'FRMQUESTIONS_CTRL_Question', 'deleteQuestion'));
    }

    public function mobileInit(){
        OW::getRouter()->addRoute(new OW_Route('frmquestions-index', 'frmquestions', 'FRMQUESTIONS_MCTRL_Question', 'questionHome'));
        OW::getRouter()->addRoute(new OW_Route('frmquestions-home', 'frmquestion_home/:type', 'FRMQUESTIONS_MCTRL_Question', 'questionHome'));
        OW::getRouter()->addRoute(new OW_Route('frmquestion-create', 'frmquestions/create', 'FRMQUESTIONS_MCTRL_Question', 'createQuestion'));
        OW::getRouter()->addRoute(new OW_Route('frmquestion-reload', 'frmquestions/reload', 'FRMQUESTIONS_MCTRL_Question', 'reloadQuestion'));
        OW::getRouter()->addRoute(new OW_Route('frmquestion-edit', 'frmquestions/edit', 'FRMQUESTIONS_MCTRL_Question', 'editQuestion'));
        OW::getRouter()->addRoute(new OW_Route('frmquestion-info', 'frmquestions/info', 'FRMQUESTIONS_MCTRL_Question', 'getQuestionInfo'));
        OW::getRouter()->addRoute(new OW_Route('frmquestion-answer', 'frmquestions/answer', 'FRMQUESTIONS_MCTRL_Question', 'answer'));
        OW::getRouter()->addRoute(new OW_Route('frmoption-add', 'frmquestions/options/add', 'FRMQUESTIONS_MCTRL_Question', 'addOption'));
        OW::getRouter()->addRoute(new OW_Route('frmoption-delete', 'frmquestions/options/delete', 'FRMQUESTIONS_MCTRL_Question', 'deleteOption'));
        OW::getRouter()->addRoute(new OW_Route('frmoption-edit', 'frmquestions/options/edit', 'FRMQUESTIONS_MCTRL_Question', 'editOption'));
        OW::getRouter()->addRoute(new OW_Route('frmquestion-subscribe', 'frmquestions/subscribe', 'FRMQUESTIONS_MCTRL_Question', 'subscribe'));
        OW::getRouter()->addRoute(new OW_Route('frmquestion-delete', 'frmquestions/delete', 'FRMQUESTIONS_MCTRL_Question', 'deleteQuestion'));
    }
}