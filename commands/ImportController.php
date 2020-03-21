<?php


namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use app\models\Import;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ImportController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionStart($message = 'hello world')
    {
        $import = new Import();
        $import->run();

        return ExitCode::OK;
    }


}