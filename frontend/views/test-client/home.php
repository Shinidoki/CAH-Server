<?php
/* @var $this yii\web\View */
/** @var $user \backend\models\User */

$this->registerAssetBundle(\rmrevin\yii\fontawesome\AssetBundle::className());

$columns = [
    [
        'attribute' => 'game_id'
    ],
    [
        'attribute' => 'game_name'
    ],

    [
        'attribute' => 'hostUser.user_name'
    ],
    [
        'attribute' => 'state',
        'value' => function ($model) {
            /** @var \backend\models\Game $model */
            return $model->translateState($model->state);
        }
    ],
    [
        'attribute' => 'create_date',
        'format' => ['date', 'php:d.m.Y H:i:s']
    ],
    [
        'attribute' => 'last_activity',
        'format' => ['date', 'php:d.m.Y H:i:s']
    ],
    [
        'attribute' => 'gameusers',
        'value' => function ($model) {
            /** @var \backend\models\Game $model */
            return count($model->gameusers);
        },
        'label' => 'Current Players'
    ],
    [
        'class' => \kartik\grid\ActionColumn::className(),
        'template' => '{join}',
        'buttons' => [
            'join' => function ($url, $model, $key) {
                /** @var \backend\models\Game $model */
                return \yii\bootstrap\Html::a(\rmrevin\yii\fontawesome\FA::icon('sign-in'), \yii\helpers\Url::toRoute('test-client/join') . '?id=' . $model->game_id, ['title' => 'Join game']);
            }
        ]
    ]
];

$this->title = 'CAH TestClient - Home';
?>
<div class="body-content">
    <div class="row">
        <div class="col-lg-12">
            <h2>Your Data</h2>
            <table class="table table-hover">
                <tbody>
                <tr>
                    <th>ID</th>
                    <td><?= $user->user_id ?></td>
                </tr>
                <tr>
                    <th>Name</th>
                    <td><?= $user->user_name ?></td>
                </tr>
                <tr>
                    <th>Token</th>
                    <td><?= $user->generated_id ?></td>
                </tr>
                <tr>
                    <th>Current Game</th>
                    <td>
                        <?php
                        if (!empty($user->games)) {
                            echo \yii\bootstrap\Html::a($user->games[0]->game_name, \yii\helpers\Url::toRoute('test-client/lobby'));
                        } else {
                            echo "Currently in no game";
                        }
                        ?>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <?= \kartik\grid\GridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => $columns,
                'pjax' => true,
                'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-games']],
                'panel' => [
                    'type' => \kartik\grid\GridView::TYPE_DEFAULT,
                    'heading' => '<span class="glyphicon glyphicon-book"></span>  ' . \yii\helpers\Html::encode($this->title),
                ],
                'export' => false,
            ]); ?>
        </div>
    </div>
</div>
