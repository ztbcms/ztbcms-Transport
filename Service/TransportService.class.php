<?php
/**
 * Created by PhpStorm.
 * User: FHYI
 * Date: 2020/5/6
 * Time: 19:12
 */

namespace Transport\Service;

use System\Service\BaseService;

/**
 * 导入服务
 * Class TransportService
 * @package Transport\Service
 */
class TransportService extends BaseService
{
    /**
     * 获取进度
     */
    static function getSpeed($task_log_id){
        $data = M('TransportTaskLog')
            ->where(['id'=>$task_log_id])
            ->find();
        $speed = number_format($data['progress'] / $data['total_amount'],1) * 100;
        $returnData = [
            'speed'=> $speed
        ];
        return self::createReturn(true,$returnData);
    }
}