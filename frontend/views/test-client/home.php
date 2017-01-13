<?php
/* @var $this yii\web\View */
/** @var $user \backend\models\User */

$this->title = 'CAH TestClient - Home';
?>
<div class="body-content">
    <div class="row">
        <div class="col-lg-4">
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
                </tbody>
            </table>
        </div>
        <div class="col-lg-8">
            <?= \kartik\grid\GridView::widget([
                'dataProvider' => $dataProvider,
//                'columns' => ['game_id'],
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