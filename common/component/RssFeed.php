<?php

namespace common\component;


use tugmaks\RssFeed\RssReader;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;

class RssFeed extends RssReader
{

    public function run()
    {
        $xml = @simplexml_load_file($this->channel);
        if ($xml === false) {
            die('Error parse Rss: ' . $rss);
        }
        $xml = $xml->children('http://www.w3.org/2005/Atom');
        $items = [];
        foreach ($xml->entry as $item) {
            $element = new AtomFeedElement();
            $element->load($item);
            $items[] = $element;
        }
        ArrayHelper::multisort($items, function ($item) {
            return strtotime($item->updated);
        }, SORT_DESC);
        $provider = new ArrayDataProvider([
            'allModels' => $items,
            'pagination' => [
                'pageSize' => $this->pageSize,
            ],
        ]);


        return $this->render('@vendor/tugmaks/yii2-rss-reader/views/wrap', [
            'provider' => $provider,
        ]);
    }
}