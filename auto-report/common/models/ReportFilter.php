<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "report_filter".
 *
 * @property int $id
 * @property int $report_type_id 报告类型
 * @property int $report_template_id 报告模板
 * @property string $upload_url 上传文件地址
 * @property string $download_url 下载地址
 * @property string $unique 唯一标识
 * @property string $ctime
 */
class ReportFilter extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'report_filter';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['report_type_id', 'report_template_id'], 'integer'],
            [['ctime'], 'safe'],
            [['upload_url', 'download_url', 'unique'], 'string', 'max' => 255],
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
            'upload_url' => 'Upload Url',
            'download_url' => 'Download Url',
            'unique' => 'Unique',
            'ctime' => 'Ctime',
        ];
    }
}
