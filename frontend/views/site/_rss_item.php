<?php /** @var \common\component\AtomFeedElement $model */ ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?= \yii\helpers\Html::a('<span class="badge">' . date('Y-m-d H:i:s', strtotime($model->updated)) . '</span>', $model->link) ?>
        <?= \yii\helpers\Html::a(\yii\helpers\Html::img($model->mediaUrl, ['width' => 30, 'height' => 30]), $model->authorUrl, ['class' => 'pull-right', 'title' => $model->author]) ?>
    </div>
    <div class="panel-body">
        <?= $model->title ?>
    </div>
</div>