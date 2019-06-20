<?php
/**
 * Created by PhpStorm.
 * User: Денис
 * Date: 20.06.2019
 * Time: 2:26
 */

namespace app\commands;

use app\models\Log;
use yii\console\Controller;
use yii\console\ExitCode;


class DelController extends Controller
{
    public function actionIndex()
    {
        echo 'Удалено записей: '.Log::deleteAll();
        return ExitCode::OK;
    }
}
