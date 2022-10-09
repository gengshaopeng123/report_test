<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "report".
 *
 * @property int $id 报告列表id
 * @property string $sample_name 样本编号
 * @property int $type_id 类别id
 * @property int $template_id 模板id
 * @property string $user_name
 * @property string $unique 唯一标识
 * @property string $download_path 下载地址
 * @property string $preview_path 预览pdf地址
 * @property string $sex 性别 
 * @property int $age 年龄
 * @property int $shen_uid 审核者
 * @property int $uid 生成报告用户
 * @property int $status 0待审核1审核成功2审核失败3生成中
 * @property string $ctime 评价时间
 */
class Report extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'report';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sample_name','template_id'], 'required'],
            [['type_id', 'template_id','shen_uid', 'uid', 'status'], 'integer'],
            [['ctime'], 'safe'],
            [['sample_name', 'user_name', 'download_path', 'preview_path','comments','age'], 'string', 'max' => 255],
            [['unique'], 'string', 'max' => 20],
            [['sex'], 'string', 'max' => 10],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sample_name' => 'Sample Name',
            'type_id' => 'Type ID',
            'template_id' => 'Template ID',
            'user_name' => 'User Name',
            'unique' => 'Unique',
            'download_path' => 'Download Path',
            'preview_path' => 'Preview Path',
            'sex' => 'Sex',
            'age' => 'Age',
            'shen_uid' => 'Shen Uid',
            'uid' => 'Uid',
            'status' => 'Status',
            'ctime' => 'Ctime',
        ];
    }

    public function getAdmin()
    {
        // hasOne要求返回两个参数 第一个参数是关联表的类名 第二个参数是两张表的关联关系
        // 这里uid是auth表关联id, 关联user表的uid id是当前模型的主键id
        return $this->hasOne(Admin::className(), ['id' => 'uid']);
    }

    public function getReportClassify()
    {
        return $this->hasOne(ReportClassify::className(), ['id' => 'template_id']);
    }
}
