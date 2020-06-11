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
        return true;
    }
}