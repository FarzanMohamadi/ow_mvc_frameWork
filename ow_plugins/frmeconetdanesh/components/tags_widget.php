<?php
class FRMECONETDANESH_CMP_TagsWidget extends BASE_CLASS_Widget
{

    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();

        $service = PostService::getInstance();

        if(FRMSecurityProvider::checkPluginActive('blogs', true)) {
            $enabledTagIdList = array();
            foreach ($params->customParamList as $tagKey => $tagParam) {
                if (strpos($tagKey, 'tag_') === 0) {
                    if ($tagParam == '1') {
                        $enabledTagIdList[] = (int)(substr($tagKey, 4));
                    }
                }
            }

            $maxCount = isset($params->customParamList['max_count']) ? $params->customParamList['max_count'] : 5;
            $maxCount = (int)($maxCount);

            $enabledTagList = BOL_TagDao::getInstance()->findByIdList($enabledTagIdList);
            $list = array();

            foreach ($enabledTagList as $tag) {
                $latestPosts = $service->findListByTag($tag->label, 0, $maxCount);
                $postItems = array();
                foreach ($latestPosts as $post) {
                    $postItems[] = array(
                        'title' => $post->title,
                        'link' => OW::getRouter()->urlForRoute('user-post', array('id' => $post->id))
                    );
                }
                $item = array(
                    'label' => $tag->label,
                    'link' => OW::getRouter()->urlForRoute(
                            'blogs.list',
                            array('list' => 'browse-by-tag')
                        )."?tag=".$tag->label,
                    'list' => $postItems
                );
                $list[] = $item;
            }
            $this->assign('list', $list);

            OW::getDocument()->addStyleSheet(
                OW::getPluginManager()->getPlugin('frmeconetdanesh')->getStaticCssUrl().'frmeconetdanesh.css'
            );
        }

    }

    public static function getSettingList()
    {
        $settingList = array();
        $settingList['max_count'] = array(
            'presentation' => self::PRESENTATION_NUMBER,
            'label' => OW::getLanguage()->text('frmeconetdanesh', 'cmp_widget_post_count'),
            'value' => 5
        );

        $topTags = BOL_TagService::getInstance()->findMostPopularTags('blog-post', 50);
        foreach($topTags as $tag) {
            if(empty($tag['label'])){
                continue;
            }
            $settingList['tag_' . $tag['id']] = array(
                'presentation' => self::PRESENTATION_CHECKBOX,
                'label' => $tag['label'].' ',
                'value' => false
            );
        }

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        $list = array(
            self::SETTING_TITLE => OW::getLanguage()->text('frmeconetdanesh', 'tag_widget_heading'),
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_ICON => 'ow_ic_write'
        );

        return $list;
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}

