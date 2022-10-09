<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "auth_role".
 *
 * @property int $id
 * @property int $role_id 角色id
 * @property int $auth_id 权限id
 * @property string $created_at
 */
class AuthRole extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'auth_role';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'role_id', 'auth_id'], 'required'],
            [['id', 'role_id', 'auth_id'], 'integer'],
            [['created_at'], 'safe'],
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
            'role_id' => 'Role ID',
            'auth_id' => 'Auth ID',
            'created_at' => 'Created At',
        ];
    }
}
