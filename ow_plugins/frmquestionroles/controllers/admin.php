<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmquestionroles
 * @since 1.0
 */

class FRMQUESTIONROLES_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function __construct()
    {
        parent::__construct();
    }

    public static function getMenu(){
        $pageActions = array(
            array('name' => 'users', 'iconClass' => 'ow_ic_user ow_dynamic_color_icon',
                'label'=>OW::getLanguage()->text('admin', 'sidebar_menu_item_permission_moders'), 'route'=>'admin_permissions_moderators'),
            array('name' => 'questions', 'iconClass' => 'ow_ic_files ow_dynamic_color_icon',
                'label'=>OW::getLanguage()->text('frmquestionroles', 'admin_title'), 'route'=>'frmquestionroles.index'),
        );

        $menuItems = array();
        foreach ( $pageActions as $key => $item )
        {
            $menuItem = new BASE_MenuItem();
            $menuItem->setKey($item['name'])->setLabel($item['label'])
                ->setOrder($key)->setUrl(OW::getRouter()->urlForRoute( $item['route']));
            $menuItem->setIconClass($item['iconClass']);
            $menuItems[] = $menuItem;
        }

        return new BASE_CMP_ContentMenu($menuItems);
    }

    /**
     * @param null $params
     * @throws Redirect404Exception
     */
    public function index($params = NULL )
    {
        $service = FRMQUESTIONROLES_BOL_Service::getInstance();
        if (!$service->hasAccessToRolesManagement()) {
            throw new Redirect404Exception();
        }

        $this->setPageHeading(OW::getLanguage()->text('frmquestionroles', 'admin_title'));
        # --------------------- MENU
        $menu = self::getMenu();
        $menu->setItemActive('questions');
        $this->addComponent('menu', $menu);
        # ---------------------

        $systemRolesInfo = $service->getAllSystemRoles();

        $lang = OW::getLanguage();
        $form = new Form('roles');

        # ---------------- MODERATORS
        $systemRolesOptions = array();
        foreach ($systemRolesInfo['roles'] as $role){
            $systemRolesOptions[$role->getId()] = $lang->text('base', 'authorization_role_'.$role->getName());
        }
        $field = new Selectbox('role');
        $field->setLabel($lang->text('frmquestionroles','select_role'));
        $field->addOptions($systemRolesOptions);
        $field->setRequired(true);
        $form->addElement($field);

        $allSelectableQuestionElements = BOL_QuestionService::getInstance()->allSelectableQuestionElements();

        # ---------------  RANGE
        $allJoinQuestionsKey = array();
        foreach ($allSelectableQuestionElements as $question){
            $qName = $question->getAttribute('name');
            $allJoinQuestionsKey[] = $qName;
            $qOptions = array();
            $qOptions[$qName . '__equal'] = OW::getLanguage()->text('frmquestionroles', 'select_role_question_equal');
            foreach ($question->getOptions() as $question_option_number=>$question_option){
                $qOptions[$question_option->questionName . '__' . $question_option->value] = OW::getLanguage()->text('base', 'questions_question_' . $question_option->questionName . '_value_' . $question_option->value);
            }
            $field = new Selectbox($qName);
            $question_label = OW::getLanguage()->text('base', 'questions_question_' . $question->getAttribute('name') . '_label');
            $field->setLabel($question_label);
            $field->addOptions($qOptions);
            $form->addElement($field);
        }

        $element = new Submit('save');
        $form->addElement($element);

        $this->addForm($form);
        $this->assign('allJoinQuestionsKey', $allJoinQuestionsKey);


        if (OW::getRequest()->isPost() && $form->isValid($_POST)) {
            $data = $form->getValues();
            $roleData = array();
            if (empty($data['role'])) {
                OW::getFeedback()->warning(OW::getLanguage()->text('frmquestionroles', 'role_empty'));
            } else {
                $allDataIsEmpty = true;
                foreach ($allSelectableQuestionElements as $question) {
                    $qName = $question->getAttribute('name');
                    if (!empty($data[$qName])) {
                        $allDataIsEmpty = false;
                    }
                }
                if ($allDataIsEmpty) {
                    OW::getFeedback()->warning(OW::getLanguage()->text('frmquestionroles', 'all_fields_empty'));
                } else {
                    foreach ($allSelectableQuestionElements as $question) {
                        $qName = $question->getAttribute('name');
                        if (!empty($data[$qName])) {
                            $qValue = substr($data[$qName], strlen($qName) + 2);
                            $roleData[$qName] = $qValue;
                        }
                    }
                    $service->saveRoleWithData($data['role'], json_encode($roleData));
                }
            }
        }

        $allDefinedRoles = $service->findAllRoles();
        $rolesDefinedInfo = array();
        foreach ($allDefinedRoles as $role){
            $roleName = $systemRolesInfo['systemRolesData'][$role->roleId]['dto']->name;
            $roleName = $lang->text('base', 'authorization_role_'.$roleName);
            $deleteUrl = OW::getRouter()->urlForRoute('frmquestionroles.delete', array('id' => $role->getId()));
            $rolesDefinedInfoTemp = array(
                'id' => $role->getId(),
                'roleId' => $role->roleId,
                'roleName' => $roleName,
                'deleteUrl' => $deleteUrl,
            );
            $data = (array) json_decode($role->data);

            $rolesDefinedInfoTemp['questions'] = array();
            foreach ($data as $key => $value){
                $label = OW::getLanguage()->text('base', 'questions_question_' . $key . '_value_' . $value);
                if ($value == 'equal'){
                    $label = OW::getLanguage()->text('frmquestionroles', 'select_role_question_equal');
                }
                $rolesDefinedInfoTemp['questions'][] = array(
                    'title' => OW::getLanguage()->text('base', 'questions_question_' . $key . '_label'),
                    'value' => $label,
                );
            }

            $rolesDefinedInfo[] = $rolesDefinedInfoTemp;
        }

        $this->assign('rolesDefinedInfo', $rolesDefinedInfo);
        $plugin = OW::getPluginManager()->getPlugin('frmquestionroles');
        OW::getDocument()->addStyleSheet($plugin->getStaticCssUrl() . 'frmquestionroles.css');
        OW::getDocument()->addScript($plugin->getStaticJsUrl() . 'frmquestionroles.js');
        OW::getLanguage()->addKeyForJs('frmquestionroles', 'wait');
        OW::getLanguage()->addKeyForJs('frmquestionroles', 'are_you_sure_delete');
        $this->assign('deleteIconUrl', $plugin->getStaticUrl().'images/trash.svg');
    }


    /**
     * @param null $params
     * @throws Redirect404Exception
     */
    public function delete($params = NULL ){
        if (!isset($params['id'])) {
            throw new Redirect404Exception();
        }

        if (!OW::getRequest()->isAjax()) {
            throw new Redirect404Exception();
        }

        $service = FRMQUESTIONROLES_BOL_Service::getInstance();
        if (!$service->hasAccessToRolesManagement()) {
            throw new Redirect404Exception();
        }
        $service->deleteQuestionRole($params['id']);
        exit();
    }
}
