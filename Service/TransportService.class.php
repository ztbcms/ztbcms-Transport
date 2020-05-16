<?php
/**
 * Created by PhpStorm.
 * User: FHYI
 * Date: 2020/5/6
 * Time: 19:12
 */

namespace Transport\Service;

use System\Service\BaseService;
use Transport\Model\TransportTaskLogModel;
use Transport\Model\TransportTaskModel;
use Transport\Core\Export;
use Transport\Core\ExportField;
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
    static function getSpeed()
    {
        $TransportTaskLogModel = new TransportTaskLogModel();
        $task_log_id = I('get.task_log_id');
        $data = $TransportTaskLogModel
            ->where(['id' => $task_log_id])
            ->find();
        $speed = number_format($data['success_amount'] / $data['total_amount'], 3);

        if(!empty($data['result_file'])){
            $speed = 1;
        }
        $returnData = [
            'speed' => sprintf("%.1f", $speed * 100),
            'result_file' => $data['result_file']
        ];
        return self::createReturn(true, $returnData);
    }

    /**
     * 执行任务 限制数量分批
     */
    static function task_exec_limit()
    {
        // 查询未执行的任务列表
        $where['process_status'] = ['in', [0, 1]];

        $TransportTaskLogModel = new TransportTaskLogModel();
        $list = $TransportTaskLogModel->where($where)->select();

        if(!empty($list)){
            foreach ($list as $item) {
                $TransportTaskModel = new TransportTaskModel();
                $task_info = $TransportTaskModel->where(['id'=>$item['task_id']])->find();
                //如果type = 1 则执行导入
                if ($task_info['type'] == TransportTaskModel::TYPE_IMPORT) {
                    self::import($item);
                }
                //如果type = 2 则执行导出
                if ($task_info['type'] == TransportTaskModel::TYPE_EXPORT) {
                    self::export($item);
                }
            }
        }
    }

    /**
     * 导入方法
     * @param $task_log
     */
    static function import($task_log)
    {
        $TransportTaskLogModel = new TransportTaskLogModel();
        $TransportTaskModel = new TransportTaskModel();

        // 模型
        $task_model = $TransportTaskModel->where(['id' => $task_log['task_id']])->find()['model'];

        $limit = 10; //配置项
        $tbody_file_url = str_replace(cache('Config.sitefileurl'), './d/file/', $task_log['filename']);
        $tbody_res = MyExportService::getExcelData($tbody_file_url);
        $tbody_res = array_merge($tbody_res, []);
        $total_items = count($tbody_res);
        // 更新任务中的总数
        $TransportTaskLogModel->where(['id' => $task_log['id']])
            ->save(['total_amount' => $total_items, 'start_transport_time' => time(), 'process_status' => 1]);

        $total_pages = ceil($total_items / $limit);

        //获取模型字段
        $modelid = M('Model')->where(['tablename' => $task_model])->getField('modelid');
        $model_field = M('ModelField')->where(['modelid' => $modelid])->order('`listorder` ASC')->getField('field', true);

        // 定时执行
        $page = S('import_' . $task_log['id'] . '_page');
        if (!$page) {
            $page = 1;
            S('import_' . $task_log['id'] . '_page', $page, 3600);//存储缓存
        } else {
            $page = S('import_' . $task_log['id'] . '_page') + 1;
            S('import_' . $task_log['id'] . '_page', $page, 3600);//存储缓存
        }

        if ($page <= $total_pages) {
            $first_row = ($page - 1) * $limit;
            $last_row = $page * $limit;
            $last_row = min($total_items, $last_row);
            //数据处理
            for ($i = $first_row; $i < $last_row; $i++) {
                $val = $tbody_res[$i];
                $_data = [];
                foreach ($model_field as $k => $v) {
                    $_data[$v] = $val[$k];
                }
                // 插入数据
                $sql = M($task_model)->fetchSql(true)->add($_data);
                $res = M($task_model)->execute($sql);
                if ($res) {
                    // 更新任务累加成功数
                    $TransportTaskLogModel->where(['id' => $task_log['id']])->setInc('success_amount');
                    $TaskLogInfo = $TransportTaskLogModel->where(['id' => $task_log['id']])->find();
                    $use_time = time() - $TaskLogInfo['start_transport_time'];
                    // 更新使用时间与结果
                    $result = 1;// 成功
                    if ($TaskLogInfo['success_amount'] < $TaskLogInfo['total_amount']) {
                        $result = 2; //失败
                    }
                    $TransportTaskLogModel->where(['id' => $task_log['id']])
                        ->save(['end_transport_time' => time(), 'use_time' => $use_time, 'result' => $result]);
                }
            }
        } else {
            // 更新进行状态 变为处理完成
            $TransportTaskLogModel->where(['id' => $task_log['id']])->save(['process_status' => 2,'update_time'=>time()]);
            S('import_' . $task_log['id'] . '_page',NULL);
        }
    }

    /**
     *
     * 导出方法
     *
     */
    static function export($task_log)
    {
        //获取任务信息
        $TransportTaskModel = new TransportTaskModel();
        $TransportTaskLogModel = new TransportTaskLogModel();
        $task = $TransportTaskModel->where(['id' => $task_log['task_id']])->find();

        //导出任务处理
        $export = new Export($task_log['id']);
        $filename = empty($task_log['filename']) ? $task['title'] . date('YmdHis', time()) : $task_log['filename'];
        $export->setFilename($filename); //导出文件名
        $export->setModel($task['model']); //导出模型

        //筛选条件
        $task_conditions = M('TransportCondition')->where(['task_id' => $task['id']])->select();
        $where = [];
        foreach ($task_conditions as $index => $condition) {

            if (!empty($condition)) {
                $filter = trim($condition['filter']);
                $operator = trim($condition['operator']);
                $value = trim($condition['value']);

                if (empty($where[$filter])) {
                    $where[$filter] = [];
                }

                if (strtolower($operator) == 'like') {
                    $new_condition = array($operator, '%' . $value . '%');
                } else {
                    $new_condition = array($operator, $value);
                }
                $where[$filter][] = $new_condition;
            }
        }
        $export->setCondition($where);

        //字段映射
        $fields = [];
        $task_fields = M('TransportField')->where(['task_id' => $task['id']])->select();
        foreach ($task_fields as $index => $field) {
            $fields[] = new ExportField($field['field_name'], $field['export_name'], $field['filter']);
        }
        $export->setFields($fields);

        $url = $export->exportXlsSrc($task_log['filename']);
        // 保存文件
        if(!empty($url)){
            $TransportTaskLogModel->where(['id' => $task_log['id']])->save(['result' => 2,'update_time'=>time(),'result_file'=>$url]);
        }
        \Think\Log::record($url);
//        return $url;
    }
}