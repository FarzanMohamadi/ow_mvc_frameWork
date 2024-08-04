<?php
/**
 * FRM Graph
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgraph
 * @since 1.0
 */

class FRMGRAPH_CMP_AdminOneUser extends OW_Component
{

    /**
     * FRMGRAPH_CMP_AdminOneUser constructor.
     * @param BASE_CLASS_WidgetParameter $params
     */
    public function __construct($params, $isAdminPage = true)
    {
        parent::__construct();
        $this->oneUserCmp($params, $isAdminPage);
    }

    private function oneUserCmp($params, $isAdminPage = true)
    {
        $service = FRMGRAPH_BOL_Service::getInstance();
        if($isAdminPage) {
            $this->assign('sections', $service->getAdminSections(2));
            $this->assign('subsections', $service->getAdminSubSections(2, 1));
        }else{
            $this->assign('sections', $service->getGraphSections(2));
            $this->assign('subsections', $service->getGraphSubSections(2, 1));
        }

        $selectedGroupId = $service->getSelectedGroupId();
        if (isset($selectedGroupId))
            $this->assign("lastCalculationDate", UTIL_DateTime::formatSimpleDate(
                $service->getLastGraphCalculatedMetricsByGroupId($selectedGroupId)->time));

        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('frmgraph')->getStaticCssUrl().'graph.css');

        $form = new Form('mainForm');
        if($isAdminPage) {
            $form->setAction(OW::getRouter()->urlForRoute('frmgraph.admin.user.one_user'));
        }else{
            $form->setAction(OW::getRouter()->urlForRoute('frmgraph.user.one_user'));
        }
        $usernameField = new TextField('username');
        $usernameField->setLabel(OW::getLanguage()->text('frmgraph','label_username'));
        $usernameField->setRequired();
        $form->addElement($usernameField);

        $submitField = new Submit('submit');
        $form->addElement($submitField);

        $this->addForm($form);

        if (OW::getRequest()->isPost() && $form->isValid($_POST)) {
            $username = $form->getValues()['username'];
            $user = BOL_UserService::getInstance()->findByUsername($username);
            if( $user != null ){
                $this->addComponent('userComponent', new FRMGRAPH_CMP_UserInfo($user->username));

                $selectedGroupId = $service->getSelectedGroupId();
                $userDataObject = $service->getUserDataByGroupId($user->id, $selectedGroupId);
                if(isset($userDataObject)) {
                    $userDataObject->time = UTIL_DateTime::formatDate($userDataObject->time, false);
                    unset($userDataObject->id);
                    $tmpArray = (array) $userDataObject;
                    $fields = array('userId', 'degree_cent','closeness_cent','betweenness_cent','eccentricity_cent','hub','authority','cluster_coe','page_rank',
                        'contents_count','pictures_count','videos_count','news_count','all_contents_count', 'user_all_likes_count', 'user_all_comments_count',
                        'all_activities_count', 'all_done_likes_count', 'all_done_comments_count', 'all_done_activities_count');
                    $userData = array();
                    foreach ($fields as $fieldName) {
                        $userData[$fieldName] = array('label' => OW::getLanguage()->text('frmgraph','label_'.$fieldName),
                            'value'=>$tmpArray[$fieldName]
                        );
                    }
                    $this->assign('userData', $userData);
                    $this->assign('noUsername', false);

                    //tooltip
                    $tooltipKeyList = array('degree_cent','closeness_cent','betweenness_cent','eccentricity_cent','hub','authority','cluster_coe','page_rank',
                        'all_contents_count','all_activities_count','all_done_activities_count');
                    $tooltipList = array();
                    foreach($tooltipKeyList as $key){
                        $tooltipList[$key] = OW::getLanguage()->text('frmgraph','tooltip_'.$key);
                    }
                    $this->assign('tooltipKeyList', $tooltipKeyList);
                    $this->assign('tooltipList', $tooltipList);
                    return;
                }
                else {
                    $usernameField->addError(OW::getLanguage()->text('frmgraph','no_data_to_display'));
                }
            }else {
                $usernameField->addError(OW::getLanguage()->text('frmgraph','no_data_to_display'));
            }
        }
        $this->assign('noUsername',true);
    }
}