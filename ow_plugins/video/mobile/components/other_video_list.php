<?php
/**
 * Video list component
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.video.components
 * @since 1.0
 */
class VIDEO_MCMP_OtherVideoList extends OW_MobileComponent
{
    /**
     * @var VIDEO_BOL_ClipService 
     */
    private $clipService;

    /**
     * Class constructor
     *
     * @param array $params
     */
    public function __construct( array $params )
    {
        parent::__construct();

        $exclude = $params['exclude'];
        $itemsNum = $params['itemsNum'];

        $this->clipService = VIDEO_BOL_ClipService::getInstance();
        $userId = $this->clipService->findClipOwner($exclude);

        if ( !$userId )
        {
            $this->setVisible(false);
        }
        else
        {
            $clips = $this->clipService->findUserClipsList($userId, 1, $itemsNum, $exclude);

            if ( !$clips )
            {
                $this->setVisible(false);
            }

            $this->assign('clips', $clips);
        }
    }
}