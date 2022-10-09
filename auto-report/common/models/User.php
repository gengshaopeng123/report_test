<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property string $id
 * @property string $password
 * @property string $description
 * @property int $role_id 角色id
 */
class User extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['role_id'], 'integer'],
            [['id', 'password', 'description'], 'string', 'max' => 255],
            [['id'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'password' => 'Password',
            'description' => 'Description',
            'role_id' => 'Role ID',
        ];
    }
}
