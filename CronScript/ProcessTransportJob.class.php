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

class ProcessTransportJob extends Cron
{
    /**
     * 执行定时任务Demo
     * @param string $cronId
     */
    public function run($cronId)
    {
        TransportService::task_exec_limit();
    }
}