<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "log".
 *
 * @property string $ip
 * @property string $date
 * @property string $url
 * @property string $useragent
 * @property string $os
 * @property string $archi
 * @property string $browser
 */
class Log extends \yii\db\ActiveRecord
{
    public $date2;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ip', 'date', 'url', 'useragent'], 'required'],
            [['date'], 'safe'],
            [['ip', 'url', 'useragent', 'os', 'archi', 'browser'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ip' => 'Ip',
            'date' => 'Date',
            'url' => 'Url',
            'useragent' => 'Useragent',
            'os' => 'Os',
            'archi' => 'Archi',
            'browser' => 'Browser',
        ];
    }
}
