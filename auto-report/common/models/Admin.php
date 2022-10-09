<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "admin".
 *
 * @property int $id 用户id
 * @property string $username 用户名
 * @property string $password 密码
 * @property int $sex 性别 1：男 ;2:女
 * @property string $ctime 创建时间
 */
class Admin extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'admin';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sex'], 'integer'],
            [['ctime'], 'safe'],
            [['username'], 'string', 'max' => 64],
            [['password'], 'string', 'max' => 32],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'password' => 'Password',
            'sex' => 'Sex',
            'ctime' => 'Ctime',
        ];
    }
}
