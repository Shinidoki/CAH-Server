<?php
/**
 * Created by PhpStorm.
 * User: dennis.schnitzmeier
 * Date: 13.01.2017
 * Time: 12:17
 */

namespace common\component;


class AtomFeedElement
{
    public $id;
    public $link;
    public $title;
    public $updated;
    public $mediaHeight;
    public $mediaWidth;
    public $mediaUrl;
    public $author;
    public $authorUrl;
    public $content;

    /**
     * @param \SimpleXMLElement $data
     */
    public function load($data)
    {
        $this->id = $data->id;
        $this->link = $data->link->attributes()->href;
        $this->title = $data->title;
        $this->updated = $data->updated;
        $this->mediaHeight = $data->children('media', true)->thumbnail->attributes()->height;
        $this->mediaWidth = $data->children('media', true)->thumbnail->attributes()->width;
        $this->mediaUrl = $data->children('media', true)->thumbnail->attributes()->url;
        $this->author = $data->author->name;
        $this->authorUrl = $data->author->uri;
        $this->content = $data->content;
    }
}