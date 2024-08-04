<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.8.3
 */
class BASE_CMP_BlockedUserList extends BASE_CMP_Users
{
    /**
     * @param $userId
     * @param array $additionalInfo
     * @return BASE_CMP_ContextAction|null
     */
    public function getContextMenu($userId, $additionalInfo = array())
    {
        $contextActionMenu = new BASE_CMP_ContextAction();

        $contextParentAction = new BASE_ContextAction();
        $contextParentAction->setKey('block_user_' . $userId);
        $contextActionMenu->addAction($contextParentAction);

        $contextAction = new BASE_ContextAction();
        $contextAction->setParentKey($contextParentAction->getKey());
        $contextAction->setKey('unblock_user');
        $contextAction->setLabel(OW::getLanguage()->text('base', 'user_unblock_btn_lbl'));

        $url = OW::getRouter()->urlForRoute('users-blocked');
        $contextAction->setUrl('javascript://');
        $contextAction->addAttribute('onclick', "OW.postRequest('{$url}', {userId: {$userId}})");

        $contextActionMenu->addAction($contextAction);

        return $contextActionMenu;
    }

    public function getFields( $userIdList )
    {
        $fields = array();

        $qs = array();

        $qBdate = BOL_QuestionService::getInstance()->findQuestionByName('birthdate');

        if ( $qBdate !== null && $qBdate->onView )
            $qs[] = 'birthdate';

        $qSex = BOL_QuestionService::getInstance()->findQuestionByName('sex');

        if ( $qSex !== null && $qSex->onView )
            $qs[] = 'sex';

        $questionList = BOL_QuestionService::getInstance()->getQuestionData($userIdList, $qs);

        foreach ( $questionList as $uid => $question )
        {

            $fields[$uid] = array();

            $age = '';

            if ( !empty($question['birthdate']) )
            {
                $date = UTIL_DateTime::parseDate($question['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);

                $age = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);
            }

            $sexValue = '';
            if ( !empty($question['sex']) )
            {
                $sex = $question['sex'];

                for ( $i = 0; $i < 64; $i++ )
                {
                    $val = $i+1;
                    if ( (int) $sex == $val )
                    {
                        $sexValue .= BOL_QuestionService::getInstance()->getQuestionValueLang('sex', $val) . ', ';
                    }
                }

                if ( !empty($sexValue) )
                {
                    $sexValue = substr($sexValue, 0, -2);
                }
            }

            if ( !empty($sexValue) && !empty($age) )
            {
                $fields[$uid][] = array(
                    'label' => '',
                    'value' => $sexValue . ' ' . $age
                );
            }
        }

        return $fields;
    }
}
