<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use vova07\imperavi\Widget;
use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $model common\models\Blog */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="blog-form">

    <?php $form = ActiveForm::begin([
            'options' => ['enctype' => 'multipart/form-data'],
    ]);
    ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'text')->widget(Widget::className(), [
        'settings' => [
            'lang' => 'ru',
            'minHeight' => 200,
            'formatting' =>[
                'p', 'blockquote', 'h2'
            ],
            'imageUpload' => \yii\helpers\Url::to(['/site/save-redactor-img', 'sub' => 'blog']),
            'plugins' => [
                'clips',
                'fullscreen',
            ],
        ],
    ]) ?>    

    <!-- <?= $form->field($model, 'text')->textarea(['rows' => 6]) ?> -->

    <?= $form->field($model, 'url')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'status_id')->dropDownList(\fishday\blog\models\Blog::STATUS_LIST) ?>

    <?= $form->field($model, 'sort')->textInput() ?>

    <?= $form->field($model, 'tags_array')->widget(\kartik\select2\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(\fishday\blog\models\Tag::find()->all(), 'id', 'name'),
        'value' => \yii\helpers\ArrayHelper::map($model->tags, 'id', 'name'),
        'language' => 'ru',
        'options' => ['placeholder' => 'Выбрать tag ... ', 'multiple' => true],
        'pluginOptions' => [
            'allowClear' => true,
            'tags' =>true,
            'maximumInputLength' => 10
        ],
    ]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?= $form->field($model, 'file')->widget(\kartik\file\FileInput::className(), [
        'options' => ['accept' => 'image/*'],
        'pluginOptions' => [
            'deleteUrl' => Url::toRoute(['/blog/delete-image']),
            'initialPreview' => $model->imagesLinks,
            'initialPreviewAsDate' => true,
            'overwriteInitial' => false,
            'initialPreviewConfig' => $model->imagesLinksData,
            'uploadUrl' => Url::to('/site/save-img'),

            'showCaption' => false,
            'showRemove' => false,
            'showUpload' => false,
            'browseClass' => 'btn btn-primary btn-block',
            'browseIcon' => '<i class ="glyphicon glyphicon-camera"></i>',
            'browseLabel' => 'Выбрать фото',
        ]
    ]) ?>


    <?php ActiveForm::end(); ?>


    <?= \kartik\file\FileInput::widget([
        'name' =>'ImageManager[attachment]',
        'options' => [
            'multiple' => true,
//            'accept' => 'image/*'
        ],
        'pluginOptions' => [
            'deleteUrl' => Url::toRoute(['/blog/delete-image']),
            'initialPreview' => $model->imagesLinks,
            'initialPreviewAsDate' => true,
            'overwriteInitial' => false,
            'initialPreviewConfig' => $model->imagesLinksData,
            'uploadUrl' => Url::to('/site/save-img'),
            'uploadExtraData' =>
                [
                    'ImageManager[class]' => $model->formName(),
                    'ImageManager[item_id]' => $model->id,
                ],
            'maxFileCount' => 10,
        ],
        'pluginEvents' => [
            'filesorted' => new \yii\web\JsExpression('function(event, params){
                $.post("'.Url::toRoute(["/blog/sort-image", "id" => $model->id]).'", {sort:params});
            }')
        ],
    ]) ?>
</div>
'