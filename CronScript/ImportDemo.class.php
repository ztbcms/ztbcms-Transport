<?php
/**
 * Created by PhpStorm.
 * User: FHYI
 * Date: 2020/5/6
 * Time: 16:31
 */

namespace Cron\CronScript;


use Cron\Base\Cron;
use Think\Exception;
use Transport\Service\TransportService;

class ImportDemo extends Cron
{
    /**
     * 导入定时任务Demo
     * @param string $cronId
     */
    public function run($cronId)
    {
        \Think\Log::record("我执行了计划导入任务 ImportDemo.class.php！");

        // 查询未执行的任务列表
        $where['process_status'] = ['in',[0,1]];
        $list =  M('TransportTaskLog')->where($where)->select();
        // 执行导入方法
        foreach ($list as $item){
            TransportService::task_exec_limit($item['id']);
        }

        //模拟长时间执行
//        sleep(5);
        //模拟异常Exception
//        throw new Exception('突然出错了');

    }
}