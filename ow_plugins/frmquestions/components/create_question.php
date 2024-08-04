<?php
/**
 * Created by PhpStorm.
 * User: Seyed Ismail Mirvakili
 * Date: 3/4/18
 * Time: 8:52 AM
 */
class FRMQUESTIONS_CMP_CreateQuestion extends OW_Component
{
    const UNIQUE_ID_PREFIX = 'create_question_cmp';
    protected $needsPrivacy = true;
    protected $context;
    protected $contextId;

    public function __construct($context, $contextId)
    {
        parent::__construct();
        $this->context = $context;
        $this->contextId = $contextId;
        if (!OW::getUser()->isAuthenticated() || !FRMQUESTIONS_BOL_Service::getInstance()->canUserCreate(OW::getUser()->getId())) {
            $this->setVisible(false);
            return;
        }

        $template = OW::getPluginManager()->getPlugin('frmquestions')->getCmpViewDir() . 'create_question.html';
        $this->setTemplate($template);
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $uniqueId = self::UNIQUE_ID_PREFIX;
        $this->assign('uniqueId', $uniqueId);
        $url = OW::getRouter()->urlForRoute('frmquestion-create');
        $form = new FRMQUESTIONS_CLASS_CreateQuestionForm($url, $this->context, $this->contextId);
        if ($this->context == 'groups')
            $this->assign('addOptions',false);
        else
            $this->assign('addOptions',true);
        $this->addForm($form);
    }
}