<?php
/* @var $this yii\web\View */
/** @var $model \yii\base\DynamicModel */

$this->title = 'CAH TestClient';

//echo Yii::$app->session->getFlash('error');

$form = \yii\widgets\ActiveForm::begin();

echo $form->field($model, 'Name');

echo \yii\helpers\Html::submitButton('Senden', ['class' => 'btn btn-success']);

$form->end();
?>

