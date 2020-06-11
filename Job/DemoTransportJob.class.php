<?php
/**
 * User: jayinton
 * Date: 2020/6/11
 * Time: 17:36
 */

namespace Transport\Job;


use Queue\Libs\Job;

class DemoTransportJob extends Job
{

    function handle()
    {
        // 这里实现你的导入，导出逻辑
    }
}