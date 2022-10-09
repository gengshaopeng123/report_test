<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_bucket".
 *
 * @property int $id
 * @property string $user_name 用户名
 * @property string $bucket_name 桶名称
 * @property string $created_at
 */
class UserBucket extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_bucket';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at'], 'safe'],
            [['user_name', 'bucket_name','ak','sk'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_name' => 'User Name',
            'bucket_name' => 'Bucket Name',
            'created_at' => 'Created At',
        ];
    }
}
