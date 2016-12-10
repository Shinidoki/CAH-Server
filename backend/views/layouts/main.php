<?php

/* @var $this \yii\web\View */
/* @var $content string */

use backend\assets\AppAsset;
use common\widgets\Alert;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use yii\helpers\Url;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => 'Carson against Humanity',
        'brandUrl' => Yii::$app->homeUrl.'dashboard',
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    if (Yii::$app->user->isGuest) {
        $menuItems[] = ['label' => 'Login', 'url' => ['/login']];
    } else {
        $menuItems[] = '<form class="navbar-form navbar-left navbar-search">
                          <div class="form-group">
                              <div class="inner-addon left-addon">
                                <i class="glyphicon glyphicon-search"></i>                                    
                                    <input type="text" class="form-control" placeholder="Search">
                              </div>
                          </div>
                        </form>';
        $menuItems[] = '<li class="dropdown">
                          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                          '.Yii::$app->user->identity->username.' 
                            <span class="caret"></span>
                          </a>
                          <ul class="dropdown-menu">
                            <li>'. 
                                Html::beginForm(['/logout'], 'post')
                                . Html::submitButton(
                                    'Logout (' . Yii::$app->user->identity->username . ')',
                                    ['class' => 'btn btn-link logout']
                                )
                                . Html::endForm().
                            '</li>
                          </ul>
                        </li>';
    }
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => $menuItems,
    ]);
    NavBar::end();
    ?>

    <div class="container-fluid content-wrapper">
        <div class="row">
            <div class="col-md-2">
                <?php if(!Yii::$app->user->isGuest): ?>
                    <div class="list-group nav-sidebar">
                        <a class="list-group-item <?= Yii::$app->controller->id == 'dashboard' ? 'active':'' ?>"  href="<?= Url::to(['dashboard/index'])?>">
                            <i class="glyphicon glyphicon-home"></i>
                            Dashboard
                        </a>
                        <a class="list-group-item <?= Yii::$app->controller->id == 'category' ? 'active':'' ?>"  href="<?= Url::to(['category/index'])?>">
                            <i class="glyphicon glyphicon-th-list"></i>
                            Kategorien
                        </a>
                        <a class="list-group-item"><i class="glyphicon glyphicon-list-alt"></i>Karten</a>
                        <a class="list-group-item"><i class="glyphicon glyphicon-cog"></i>Settings</a>
                    </div>
                <?php endif;?>
            </div>
            <div class="col-md-10">
                <?= Alert::widget() ?>
                <?= $content ?>
            </div>
        </div>
    </div>
</div>

<footer class="footer navbar-fixed-bottom">
    <div class="container">
        <p class="pull-left">&copy; My Company <?= date('Y') ?></p>

        <p class="pull-right"><?= Yii::powered() ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
