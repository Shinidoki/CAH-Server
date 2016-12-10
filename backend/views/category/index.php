<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */
use backend\assets\CategoryAsset;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

CategoryAsset::register($this);

$this->title = 'Kategorien';
?>
<?php $this->beginPage() ?>
<?php $this->beginBody() ?>
<div class="container-fluid sub-content sub-action">
    <div class="row">
        <div class="col-md-2 col-npr col-npl">
            <button type="button" class="btn-lg btn-default btn-tool" data-toggle="modal" data-target="#createCategory">Kategorie anlegen</button>
        </div>
    </div>
</div>
<div class="container-fluid sub-content">
    <div class="row">
        <div class="col-md-12 transparancy">
        </div>
  </div>
</div>
<!-- Modal Create Category-->
<div class="modal fade" id="createCategory" tabindex="-1" role="dialog" aria-labelledby="createCategoryLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="createCategoryLabel">Neue Kategorie anlegen</h4>
      </div>
      <div class="modal-body">
        <div class="alert alert-danger" id="createCategoryAlert" role="alert"></div>
        <div class="input-group">
          <span class="input-group-addon" id="category">Kategorie</span>
          <input type="text" class="form-control" id="createCategoryName" name="categoryName" placeholder="Titel" aria-describedby="category">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" id="saveCategory" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>
<?php $this->endBody() ?>
<?php $this->endPage() ?>