<?php
/**
 * 数据导出导入模块安装
 */

namespace Transport\Install;

use Libs\System\InstallBase;
use Cron\Model\CronModel;

class Install extends InstallBase
{
    //模块地址
    private $path = NULL;

    public function __construct() {
        $this->path = APP_PATH . 'Transport/';
    }

    //安装前进行处理
    public function run() {
        return true;
    }

    //基本安装结束后的回调
    public function end() {
        // 添加定时任务
        $CronModel = new CronModel();
        $check = $CronModel->where(['cron_file' => 'Transport\CronScript\ImportDemo'])->find();
        if(!$check){
            $addData = [
                'subject'=> '导入与导出定时任务',
                'type'=> 1,
                'loop_type'=> 'now',
                'loop_daytime'=> '0-0-1',
                'cron_file'=> 'Transport\CronScript\ImportDemo',
                'isopen'=> '1',
                'created_time'=> time(),
            ];
            $CronModel->add($addData);
        }
        return true;
    }
}