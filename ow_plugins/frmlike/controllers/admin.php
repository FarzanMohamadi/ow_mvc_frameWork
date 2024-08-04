<?php
/**
 * Admin page
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmlike.controllers
 * @since 1.0
 */
class FRMLIKE_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function index()
    {
        OW::getDocument()->setTitle(OW::getLanguage()->text('frmlike', 'admin_settings_heading'));
        $this->setPageTitle(OW::getLanguage()->text('frmlike', 'admin_title'));
        $this->setPageHeading(OW::getLanguage()->text('frmlike', 'admin_heading'));


        $dislikeActivateForm = new Form('dislikeActivateForm');
        $config = OW::getConfig();

        $disLikePostActivate = new CheckboxField('dislikePostActivate');
        $disLikePostActivate->setLabel(OW::getLanguage()->text('frmlike', 'dislike_post_activate_label'));
        $disLikePostActivate->setValue($config->getValue('frmlike', 'dislikePostActivate'));
        $dislikeActivateForm->addElement($disLikePostActivate);

        $disLikeActivate = new CheckboxField('dislikeActivate');
        $disLikeActivate->setLabel(OW::getLanguage()->text('frmlike', 'dislike_activate_label'));
        $disLikeActivate->setValue($config->getValue('frmlike', 'dislikeActivate'));


        $dislikeActivateFormSubmit = new Submit('dislikeActivateFormSubmit');
        $dislikeActivateFormSubmit->setValue(OW::getLanguage()->text('frmlike', 'dislike_activate_submit'));
        $dislikeActivateForm->addElement($dislikeActivateFormSubmit);

        $dislikeActivateForm->addElement($disLikeActivate);
        $this->addForm($dislikeActivateForm);

        $numberOfLikesToConvertByEachRequest = 1000;
        $this->addMergeLikeTabesFormToPage($numberOfLikesToConvertByEachRequest);


        if (OW::getRequest()->isPost()) {
            if ($dislikeActivateForm->isValid($_POST)) {
                if ($_POST['form_name'] == 'mergeLikeTablesForm') {
                    $this->processMergeLikeTablesForm($numberOfLikesToConvertByEachRequest);
                } else {
                $data = $dislikeActivateForm->getValues();

                if(!isset($data["dislikeActivate"])) {
                    $config->saveConfig('frmlike', 'dislikeActivate', 0);
                } else {
                    $config->saveConfig('frmlike', 'dislikeActivate', 1);
                }

                if(!isset($data["dislikePostActivate"])) {
                    $config->saveConfig('frmlike', 'dislikePostActivate', 0);
                } else {
                    $config->saveConfig('frmlike', 'dislikePostActivate', 1);
                }

                OW::getFeedback()->info(OW::getLanguage()->text('frmlike', 'submit_successful_message'));
                $this->redirect();
                }
            }
        }
    }


    /**
     * @param int $numberOfLikesToConvertByEachRequest
     */
    private function addMergeLikeTabesFormToPage($numberOfLikesToConvertByEachRequest) {
        $newsfeedLikeTableExists = OW::getDbo()->tableExist(FRMLIKE_BOL_Service::getInstance()->getNewsfeedLikeTableName());

        $newsfeedLikeCount = -1;
        if ($newsfeedLikeTableExists) {
            $newsfeedLikeCount = FRMLIKE_BOL_Service::getInstance()->countNewsfeedLikeTable();
        }
        $this->assign('newsfeedLikeCount', $newsfeedLikeCount);

        if ($newsfeedLikeCount > 0) {
            $mergeLikeTablesForm = new Form('mergeLikeTablesForm');
            $submit = new Submit('save');
            $submit->setValue(OW::getLanguage()->text('frmlike', 'mrege_like_tables_form_submit'));
            $mergeLikeTablesForm->addElement($submit);
            $this->addForm($mergeLikeTablesForm);

            $mergeLikeTablesMessage = OW::getLanguage()->text("frmlike", "mrege_like_tables_message", ['newsfeedLikeCount' => $newsfeedLikeCount, 'numberOfLikesToConvertByEachRequest' => $numberOfLikesToConvertByEachRequest]);
            $this->assign('mergeLikeTablesMessage', $mergeLikeTablesMessage);
        } else if ($newsfeedLikeCount == 0) {
            $mergeLikeTablesForm = new Form('mergeLikeTablesForm');
            $submit = new Submit('save');
            $submit->setValue(OW::getLanguage()->text('frmlike', 'delete_newsfeed_like_table_form_submit'));
            $mergeLikeTablesForm->addElement($submit);
            $this->addForm($mergeLikeTablesForm);

            $mergeLikeTablesMessage = OW::getLanguage()->text("frmlike", "mrege_like_tables_message", ['newsfeedLikeCount' => $newsfeedLikeCount, 'numberOfLikesToConvertByEachRequest' => $numberOfLikesToConvertByEachRequest]);
            $this->assign('mergeLikeTablesMessage', $mergeLikeTablesMessage);
        }
    }

    /**
     * @param int $numberOfLikesToConvertByEachRequest
     */
    private function processMergeLikeTablesForm($numberOfLikesToConvertByEachRequest) {
        $result = FRMLIKE_BOL_Service::getInstance()->getLikesFromNewsfeedLike($numberOfLikesToConvertByEachRequest, 0);

        $voteDtos = array();
        foreach ($result as $item) {
            $voteDto = new BOL_Vote();
            $voteDto->setUserId($item['userId']);
            $voteDto->setEntityType($item['entityType']);
            $voteDto->setEntityId($item['entityId']);
            $voteDto->setVote(1);
            $voteDto->setTimeStamp($item['timeStamp']);
            $voteDtos[] = $voteDto;
        }

        if (count($voteDtos) > 0) {
            BOL_VoteService::getInstance()->saveVotes($voteDtos);
            FRMLIKE_BOL_Service::getInstance()->deleteByIdListFromNewsfeedLike(array_column($result, 'id'));
            OW::getFeedback()->info(OW::getLanguage()->text('frmlike', 'merge_like_tables_successful_message'));
            $this->redirect();
        } else if (count($voteDtos) == 0) {
            FRMLIKE_BOL_Service::getInstance()->dropNewsfeedLikeTable();
            FRMSecurityProvider::dropBackupTable(FRMLIKE_BOL_Service::getInstance()->getNewsfeedLikeTableName());
            OW::getFeedback()->info(OW::getLanguage()->text('frmlike', 'merge_like_tables_successful_message'));
            $this->redirect();
        }
    }
}
