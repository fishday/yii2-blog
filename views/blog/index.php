<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel common\models\BlogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Blogs';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="blog-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Blog', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'class' => 'yii\grid\ActionColumn', 
                'template' => '{view} {update} {delete} {check}',

                'buttons' => [
                    'check' => function($url, $model, $key){
                        return Html::a('<i class="fa fa-check" aria-hidden="true"></i>', $url);
                        // return Html::a('sdda', $url);

                    }
                ],
                // 'visibleButons' => [
                // ]
            ],
            'id',
            'title',
            'text:ntext',
            [
                'attribute' => 'url', 
                'format' => 'url'
            ], 
            [
                'attribute' => 'status_id', 
                'filter' => \fishday\blog\models\Blog::STATUS_LIST,
                'value' => 'statusName' 
            ],
            'sort',
            [
                'attribute' => 'tags', 
                'value' => 'tagsAsString',
            ],
            'date_create:datetime',
            'date_update:datetime',

            'smallImage:image',
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
