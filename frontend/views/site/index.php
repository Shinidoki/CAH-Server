<?php

/* @var $this yii\web\View */
\rmrevin\yii\fontawesome\AssetBundle::register($this);
$this->title = 'Carson Against Humanity';
?>
<div class="site-index">

    <div class="jumbotron">
        <h1>Carson Against Humanity!</h1>

        <p class="lead">This is a <a href="https://www.cardsagainsthumanity.com/">Cards Against Humanity</a> project.
        </p>

        <p>
            We are developing an online interface as a school project. <br>
            Our goal is to make a simple application which lets you play Cards Against Humanity with other people online
            via this interface.
        </p>
    </div>

    <div class="body-content">

        <div class="row">
            <div class="col-lg-4">
                <h2>Documentation</h2>

                <p>Interface Documentation (commentaries are just in german for now)</p>

                <p><a class="btn btn-default" target="_blank"
                      href="https://drive.google.com/file/d/0B7TxIzdpex_vRXJNeTFCVVVfNU0/view?usp=drive_web"><?= \rmrevin\yii\fontawesome\FA::icon('file-pdf-o') ?>
                        Documentation as PDF</a></p>
            </div>
            <div class="col-lg-4">
                <h2>Sourcecode</h2>

                <p><a class="btn btn-default" target="_blank"
                      href="https://github.com/Shinidoki/CAH-Server"><?= \rmrevin\yii\fontawesome\FA::icon('github') ?>
                        Server/Interface</a></p>
                <p><a class="btn btn-default" target="_blank"
                      href="https://github.com/MartinSchmieschek/CAH-Client"><?= \rmrevin\yii\fontawesome\FA::icon('github') ?>
                        Unity Client</a></p>
            </div>
            <div class="col-lg-4">
                <h2>Github - Server/Interface</h2>
                <?php
                \yii\widgets\Pjax::begin(['timeout' => 10000]);
                echo \common\component\RssFeed::widget([
                    'channel' => 'https://github.com/Shinidoki/CAH-Server/commits/master.atom',
                    'pageSize' => 5,
                    'itemView' => '@frontend/views/site/_rss_item', //To set own viewFile set 'itemView'=>'@frontend/views/site/_rss_item'. Use $model var to access item properties
                    'wrapTag' => 'div',
                    'wrapClass' => 'rss-wrap',
                ]);
                \yii\widgets\Pjax::end();
                ?>

            </div>
        </div>

    </div>
</div>
