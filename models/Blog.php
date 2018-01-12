<?php

namespace fishday\blog\models;
use common\models\ImageManager;
use common\models\User;
use common\components\behaviors\StatusBehavior;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;
use yii\helpers\Url;

/**
 * This is the model class for table "blog".
 *
 * @property int $id
 * @property string $title
 * @property string $text
 * @property string $url
 * @property string $image
 * @property string $date_create
 * @property string $date_update
 * @property int $status_id
 * @property int $sort
 */
class Blog extends \yii\db\ActiveRecord
{
    public $tags_array;
    const STATUS_LIST = ['off', 'on'];
    public $file;
    // public $newtags;
    /**
     * @inheritdoc
     */

    public static function tableName()
    {
        return 'blog';
    }

    public function behaviors()
    {
        return [
            'timestampBehavior' => [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'date_create',
                'updatedAtAttribute' => 'date_update',
                'value' => new Expression('NOW()'),
            ],
            'statusBehavior' => [
                'class' => StatusBehavior::className(),
                'statusList' => self::STATUS_LIST,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'url'], 'required'],
            [['text'], 'string'],
            [['url'], 'unique'],
            [['status_id', 'sort'], 'integer'],
            [['sort'], 'integer', 'max' => 99, 'min' => 1],
            [['title', 'url'], 'string', 'max' => 150],
            [['tags_array', 'date_create', 'date_update'], 'safe'],
            [['image'], 'string', 'max' => 100],
            [['file'], 'image'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Заголовок',
            'text' => 'Текст',
            'url' => 'ЧПУ',
            'status_id' => 'Статус',
            'sort' => 'Сортировка',
            'tags_array' => 'Тэги',
            'tagsAsString' => 'Тэги',
            'author.username' => 'Имя автора',
            'author.email' => 'Почта автора',
            'date_update' => 'Обновлено',
            'date_create' => 'Создано',
            'date_create' => 'Создано',
            'image' => 'Картинка',
            'file' => 'Картинка'
        ];
    }

    public function getStatusName()
    {
        $list = self::getStatusList();
        return $list[$this->status_id];
    }

    public static function getStatusList()
    {
        return ['off', 'on'];
    }

    public function getAuthor(){
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getBlogTag(){
        return $this->hasMany(BlogTag::className(), ['blog_id' => 'id']);
    }

    public function getImages(){
        return $this->hasMany(ImageManager::className(), ['item_id' => 'id'])->andWhere(['class'=>self::tableName()])->orderBy('sort');
    }

    public function getTags()
    {
        return $this->hasMany(Tag::className(), ['id' => 'tag_id'])->via('blogTag');
    }

    public function beforeDelete()
    {
        if (parent::beforeDelete())
        {
            BlogTag::deleteAll(['blog_id' => $this->id]);
            return true;
        }
        else
        {
            return false;
        }
    }

    public function getSmallImage()
    {
        if ($this->image)
        {
            $path = str_replace('admin.','', Url::home(true)).'uploads/images/blog/50x50/'.$this->image;
        }
        else
        {
            $path = str_replace('admin.','', Url::home(true)).'uploads/images/nophoto.png';
        }

        return $path;
    }


    public function afterFind()
    {
        // $this->newtags = \yii\helpers\ArrayHelper::map($this->tags, 'tag', 'tag');
        parent::afterFind();
        $this->tags_array = $this->tags;
    }

    public function beforeSave($insert)
    {
        if ($file = UploadedFile::getInstance($this, 'file'))
        {
            $dir = Yii::getAlias('@images').'/blog/';
            if (!is_dir($dir . $this->image)) {
                if (file_exists($dir . $this->image)) {
                    unlink($dir . $this->image);
                }
                if (file_exists($dir . '50x50/' . $this->image)) {
                    unlink($dir . '50x50/' . $this->image);
                }
                if (file_exists($dir . '800x/' . $this->image)) {
                    unlink($dir . '800x/' . $this->image);
                }
            }
            $this->image = strtotime('now') . '_' . Yii::$app->getSecurity()->generateRandomString(6) . '.' . $file->extension;
            $file->saveAs($dir . $this->image);
            $imag = Yii::$app->image->load($dir . $this->image);
            $imag->background('#fff', 0);
            $imag->resize('50', '50', Yii\image\drivers\Image::INVERSE);
            $imag->crop('50', '50');
            if(!file_exists($dir.'50x50/')){
                FileHelper::createDirectory($dir.'50x50/');
            }
            $imag->save($dir . '50x50/' . $this->image, 90);

            $imag = Yii::$app->image->load($dir . $this->image);
            $imag->background('#fff', 0);
            $imag->resize('800', null, Yii\image\drivers\Image::INVERSE);
            if(!file_exists($dir.'800x/')){
                FileHelper::createDirectory($dir.'800x/');
            }
            $imag->save($dir . '800x/' . $this->image, 90);
        }
        return parent::beforeSave($insert); // TODO: Change the autogenerated stub
    }

    public function afterSave($insert, $changeAttributes)
    {
        parent::afterSave($insert, $changeAttributes);

        $arr = \yii\helpers\ArrayHelper::map($this->tags, 'id', 'id'); 
        foreach ($this->tags_array as $one) {
            if (!in_array($one, $arr))
            {
                $model = new BlogTag();
                $model->blog_id = $this->id;
                $model->tag_id = $one;
                $model->save();
            }
            if (isset($arr[$one]))
            {
                unset ($arr[$one]);
            }
        }
        BlogTag::deleteAll(['tag_id'=>$arr,'blog_id' => $this->id]);
    }

    public function getTagsAsString()
    {
        $arr = \yii\helpers\ArrayHelper::map($this->tags, 'id', 'name'); 
        return implode(', ', $arr);
    }

    public function getImagesLinks()
    {
        return ArrayHelper::getColumn($this->images, 'imageUrl');
    }

    public function getImagesLinksData()
    {
        return ArrayHelper::toArray($this->images, [
            ImageManager::className() => [
                'caption' => 'name',
                'key' => 'id',
            ]
        ]);
    }
}
