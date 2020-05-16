<?php
/**
 * Created by PhpStorm.
 * User: FHYI
 * Date: 2020/5/6
 * Time: 16:31
 */

namespace Transport\CronScript;


use Cron\Base\Cron;
use Transport\Service\TransportService;

class ImportDemo extends Cron
{
    /**
     * 执行定时任务Demo
     * @param string $cronId
     */
    public function run($cronId)
    {
        TransportService::task_exec_limit();
        //模拟长时间执行
//        sleep(5);
        //模拟异常Exception
//        throw new Exception('突然出错了');
    }
}