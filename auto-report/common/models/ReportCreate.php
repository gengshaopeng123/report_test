<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "report_create".
 *
 * @property int $id
 * @property string $relation 亲属关系
 * @property string $direction 验证方向
 * @property string $site 验证位点
 * @property string $relation_note 关系备注
 * @property string $variation_source 编译来源
 * @property string $heterozygosity 杂合性
 * @property string $company_name 公司名称
 * @property string $header_footer 页眉页脚
 * @property string $url 文件地址
 * @property string $unique 唯一标识
 * @property int $type 文件上传类型  
 * @property string $ctime 创建时间
 */
class ReportCreate extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'report_create';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['unique', 'type'], 'required'],
            [['type'], 'integer'],
            [['ctime'], 'safe'],
            [['relation', 'direction', 'site', 'relation_note', 'variation_source', 'heterozygosity', 'company_name', 'header_footer', 'url'], 'string', 'max' => 255],
			[['pipe_type', 'flag'], 'string', 'max' => 50],
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
            'relation' => 'Relation',
            'direction' => 'Direction',
            'site' => 'Site',
            'relation_note' => 'Relation Note',
            'variation_source' => 'Variation Source',
            'heterozygosity' => 'Heterozygosity',
            'company_name' => 'Company Name',
            'header_footer' => 'Header Footer',
            'url' => 'Url',
            'unique' => 'Unique',
            'type' => 'Type',
            'ctime' => 'Ctime',
        ];
    }
}
