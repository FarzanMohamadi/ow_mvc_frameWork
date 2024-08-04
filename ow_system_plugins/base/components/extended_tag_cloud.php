<?php
/**
 * Extended tag cloud component.  
 * 
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_ExtendedTagCloud extends OW_Component
{
    /**
     * @var integer
     */
    protected $entityId;
    /**
     * @var string
     */
    protected $entityType;
    /**
     * @var string
     */
    protected $url;
    /**
     * @var string
     */
    protected $routeName;
    /**
     * @var integer
     */
    protected $tagsCount;
    /**
     * @var BOL_TagService
     */
    protected $service;

    /**
     * Constructor.
     * 
     * @param string $entityType
     * @param string $url
     * @param integer $tagsCount
     */
    public function __construct( $entityType, $url, $tagsCount = null )
    {
        parent::__construct();

        $this->service = BOL_TagService::getInstance();
        $this->entityType = trim($entityType);
        $this->url = trim($url);
        $this->tagsCount = $tagsCount;
    }

    /**
     * @see OW_Rendarable::onBeforeRender
     */
    public function onBeforeRender()
    {
        // find tags to show
        if ( $this->entityId !== null )
        {
            $tags = $this->service->findEntityTagsWithPopularity($this->entityId, $this->entityType);
        }
        else
        {
            if ( $this->tagsCount === null )
            {
                $this->tagsCount = $this->service->getConfig(BOL_TagService::CONFIG_DEFAULT_TAGS_COUNT);
            }

            $tags = $this->service->findMostPopularTags($this->entityType, $this->tagsCount);
        }

        // get font sizes from configs
        $minFontSize = $this->service->getConfig(BOL_TagService::CONFIG_MIN_FONT_SIZE);
        $maxFontSize = $this->service->getConfig(BOL_TagService::CONFIG_MAX_FONT_SIZE);

        // get min and max tag's items count
        $minCount = null;
        $maxCount = null;

        foreach ( (!empty($tags) ? $tags : array() ) as $tag )
        {
            if ( $minCount === null )
            {
                $minCount = (int) $tag['count'];
                $maxCount = (int) $tag['count'];
            }

            if ( (int) $tag['count'] < $minCount )
            {
                $minCount = (int) $tag['count'];
            }

            if ( (int) $tag['count'] > $maxCount )
            {
                $maxCount = (int) $tag['count'];
            }
        }

        // prepare array to assign
        foreach ( (!empty($tags) ? $tags : array() ) as $key => $value )
        {
            $tags[$key]['url'] = ($this->routeName === null) ? OW::getRequest()->buildUrlQueryString($this->url, array('tag' => $value['tagLabel'])) : OW::getRouter()->urlForRoute($this->routeName, array('tag' => $value['tagLabel']));

            $fontSize = ($maxCount === $minCount ? ($maxFontSize / 2) : floor(((int) $value['itemsCount'] - $minCount) / ($maxCount - $minCount) * ($maxFontSize - $minFontSize) + $minFontSize));

            $tags[$key]['size'] = $fontSize;
            $tags[$key]['lineHeight'] = $fontSize + 4;
        }

        $this->assign('tags', $tags);
    }

    /**
     * Sets route name for tag items.
     * Route should be added to router and contain var - `tag`.
     * 
     * @param string $routeName
     * @return BASE_CMP_TagCloud
     */
    public function setRouteName( $routeName )
    {
        $this->routeName = trim($routeName);
        return $this;
    }

    /**
     * @return string
     */
    public function getRouteName()
    {
        return $this->routeName;
    }
}