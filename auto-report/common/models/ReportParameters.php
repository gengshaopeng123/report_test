<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "report_parameters".
 *
 * @property int $id
 * @property int $report_type_id
 * @property int $report_template_id
 * @property string $unique
 * @property string $ctime
 */
class ReportParameters extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'report_parameters';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['report_type_id', 'report_template_id', 'unique'], 'required'],
            [['report_type_id', 'report_template_id'], 'integer'],
            [['ctime'], 'safe'],
            [['unique'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'report_type_id' => 'Report Type ID',
            'report_template_id' => 'Report Template ID',
            'unique' => 'Unique',
            'ctime' => 'Ctime',
        ];
    }
}
