<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "report_classify".
 *
 * @property int $id 分类ID
 * @property string $name 类型名称
 * @property string $desc 分类描述
 * @property int $pid
 * @property string $ctime 创建时间
 */
class ReportClassify extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'report_classify';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pid'], 'integer'],
            [['ctime'], 'safe'],
            [['name'], 'string', 'max' => 16],
            [['desc'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'desc' => 'Desc',
            'pid' => 'Pid',
            'ctime' => 'Ctime',
        ];
    }

    public function getReport()
    {
        return $this->hasOne(Report::className(), ['type_id' => 'id']);
    }
}
