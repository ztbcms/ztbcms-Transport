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
    static function getSpeed($task_log_id)
    {
        $data = M('TransportTaskLog')
            ->where(['id' => $task_log_id])
            ->find();
        $speed = number_format($data['success_amount'] / $data['total_amount'], 3);
        $returnData = [
            'speed' => sprintf("%.1f", $speed * 100)
        ];
        return self::createReturn(true, $returnData);
    }

    /**
     * 执行任务 限制数量分批
     */
    static function task_exec_limit($task_log_id)
    {
        $task_log = M('TransportTaskLog')->where(['id' => $task_log_id])->find();
        $task = M('TransportTask')->where(['id' => $task_log['task_id']])->find();

        $limit = 20;  // 可配置每次执行的数量
        if ($task['type'] == 1) {
            $tbody_file_url = str_replace(cache('Config.sitefileurl'), './d/file/', $task_log['filename']);
            $tbody_res = MyExportService::getExcelData($tbody_file_url);
            $tbody_res = array_merge($tbody_res, []);
            $total_items = count($tbody_res);
            // 进行更新数据库总条数
            M('TransportTaskLog')->where(['id' => $task_log_id])
                ->save(['total_amount' => $total_items, 'start_transport_time' => time(), 'process_status' => 1]);

            $total_pages = ceil($total_items / $limit);

            //获取模型字段
            $modelid = M('Model')->where(['tablename' => $task['model']])->getField('modelid');
            $model_field = M('ModelField')->where(['modelid' => $modelid])->order('`listorder` ASC')->getField('field', true);

            // 定时执行
            $page = S('import_'.$task_log_id.'_page');
            if(!$page){
                $page = 1;
                S('import_'.$task_log_id.'_page',$page,3600);//存储缓存
            }else{
                $page = S('import_'.$task_log_id.'_page') + 1;
                S('import_'.$task_log_id.'_page',$page,3600);//存储缓存
            }

            if( $page <= $total_pages){
                $first_row = ($page - 1) * $limit;
                $last_row = $page * $limit;
                $last_row = min($total_items, $last_row);
                //数据处理
                $data = [];
                for ($i = $first_row; $i < $last_row; $i++) {
                    $val = $tbody_res[$i];
                    $_data = [];
                    foreach ($model_field as $k => $v) {
                        $_data[$v] = $val[$k];
                    }
                    $sql = M($task['model'])->fetchSql(true)->add($_data);
                    $res = M($task['model'])->execute($sql);
                    if ($res) {
                        // 进行更新数据库累加成功数
                        M('TransportTaskLog')->where(['id' => $task_log_id])->setInc('success_amount');
                        $TaskLogInfo = M('TransportTaskLog')->where(['id' => $task_log_id])->find();
                        $use_time = time() - $TaskLogInfo['start_transport_time'];
                        // 更新使用时间 更新结果
                        $result = 1;// 成功
                        if ($TaskLogInfo['success_amount'] < $TaskLogInfo['total_amount']) {
                            $result = 2; //失败
                        }
                        M('TransportTaskLog')->where(['id' => $task_log_id])->save(['end_transport_time' => time(), 'use_time' => $use_time, 'result' => $result]);
                    }
                }
            }else{
                // 更新进行状态 变为处理完成
                M('TransportTaskLog')->where(['id' => $task_log_id])->save(['process_status' => 2]);
            }

        }
    }
}