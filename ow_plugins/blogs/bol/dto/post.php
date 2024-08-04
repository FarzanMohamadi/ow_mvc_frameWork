<?php
/**
 * @package ow_plugins.blogs.bol.dto
 * @since 1.0
 */
class Post extends OW_Entity
{
    public
    $authorId,
    $title,
    $post,
    $timestamp,
    $isDraft,
    $bundleId,
    $privacy = 'everybody';

    /**
     * @return mixed
     */
    public function getBundleId()
    {
        return $this->bundleId;
    }

    /**
     * @param mixed $bundleId
     */
    public function setBundleId($bundleId)
    {
        $this->bundleId = $bundleId;
    }

    /**
     * @return int
     */
    public function getAuthorId()
    {
        return $this->authorId;
    }

    /**
     * @return string
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * @return int
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    public function isDraft()
    {
        return $this->isDraft > 0;
    }

    public function getStatus()
    {
        return $this->isDraft;
    }

    /**
     * @param int $authorId
     * 
     * @return $this
     */
    public function setAuthorId( $authorId )
    {
        $this->authorId = $authorId;

        return $this;
    }

    /**
     * @param string $post
     * 
     * @return $this
     */
    public function setPost( $post )
    {
        $this->post = $post;

        return $this;
    }

    /**
     * @param int $timestamp
     * 
     * @return $this
     */
    public function setTimestamp( $timestamp )
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * @param string $title
     */
    public function setTitle( $title )
    {
        $this->title = $title;

        return $this;
    }

    public function setIsDraft( $isDraft )
    {
        $this->isDraft = $isDraft;

        return $this;
    }

    public function setPrivacy( $privacy )
    {
        $this->privacy = $privacy;

        return $this;
    }

    public function getPrivacy()
    {
        return $this->privacy;
    }

}