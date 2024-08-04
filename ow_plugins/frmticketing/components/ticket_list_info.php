<?php
class FRMTICKETING_CMP_TicketListInfo extends OW_Component
{
    /**
     * FRMTICKETING_CMP_TicketListInfo constructor.
     * @param $ticketList
     * @param $ticketListCount
     * @param $page
     * @param $pageCount
     */
    public function __construct($ticketList, $ticketListCount, $page, $pageCount)
    {
        parent::__construct();
        $authorIdList = array();
        $ticketArray = array();
        $idList=array();
        foreach ( $ticketList as $ticket )
        {
            $ticket = array(
                'id' => $ticket['id'],
                'ticketTrackingNumber' => $ticket['ticketTrackingNumber'],
                'userId' => $ticket['userId'],
                'title' =>$ticket['title'],
                'description' => strip_tags($ticket['description']),
                'timeStamp' => UTIL_DateTime::formatDate($ticket['timeStamp']),
                'ticketUrl' => $this->getTicketUrl($ticket['id']),
                'categoryTitle' =>$ticket['categoryTitle'],
                'orderTitle' =>$ticket['orderTitle'],
                'locked' =>$ticket['locked']
            );
            $ticketArray[] = $ticket;
            $authorIdList[] = $ticket['userId'];
            $idList[] = $ticket['id'];
        }

        if ( !empty($idList) )
        {
            $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($authorIdList, true, false);
            foreach ($avatars as $avatar) {
                $userAvatarId = $avatar['userId'];
                $avatars[$userAvatarId]['url'] = BOL_UserService::getInstance()->getUserUrl($userAvatarId);
            }
            $this->assign('avatars', $avatars);

            $nlist = array();
            foreach ($avatars as $userId => $avatar) {
                $nlist[$userId] = $avatar['title'];
            }
            $urls = BOL_UserService::getInstance()->getUserUrlsForList($authorIdList);
            $this->assign('toolbars', $this->getToolbar($idList, $ticketArray, $urls, $nlist));
        }

        $this->addComponent('paging', new BASE_CMP_Paging($page, ceil($ticketListCount / $pageCount), 5));
        $this->assign('page', $page);
        $this->assign('tickets', $ticketArray);
    }

    private function getToolbar( $idList, $list, $ulist, $nlist )
    {
        if ( empty($idList) )
        {
            return array();
        }

        $toolbars = array();

        foreach ( $list as $item )
        {
            $id = $item['id'];

            $toolbars[$id] = array(
                array(
                    'class' => 'ow_ipc_date',
                    'label' => $item['timeStamp']
                ),
            );

            $toolbars[$id][] = array(
                'label' => ' <span class="ow_txt_value">' .$item['orderTitle']. '</span>',
            );

            if (isset($item['categoryTitle']))
            {
                $toolbars[$id][] = array(
                    'label' => ' <span class="ow_txt_value">' .$item['categoryTitle']. '</span>',
                );
            }

            else if (isset($item['networkUid']) )
            {
                if(!isset($item['networkUrl'])) {
                    $toolbars[$id][] = array(
                        'label' => ' <span class="ow_txt_value">' . $item['networkUid'] . '</span>',
                    );
                }else{
                    $value = "<span class='ow_wrap_normal'>";
                    $value .=' <a href="' . $item['networkUrl'] . '" target="_blank">'.$item["networkUid"].'</a>';
                    $value .= "</span>";
                    $toolbars[$id][] = array(
                        'label' => $value,
                    );
                }
            }
        }

        return $toolbars;
    }

    public function getTicketUrl($ticketId)
    {
        return OW::getRouter()->urlForRoute('frmticketing.view_ticket',array('ticketId'=>$ticketId));
    }
}
