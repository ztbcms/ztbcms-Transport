<?php
/**
 * Created by PhpStorm.
 * User: FHYI
 * Date: 2020/5/6
 * Time: 19:12
 */

namespace Transport\Service;

use System\Service\BaseService;
use Transport\Core\Import;
use Transport\Model\TransportTaskLogModel;
use Transport\Model\TransportTaskModel;
use Transport\Core\Export;
use Transport\Core\ExportField;

/**
 * 导入服务
 * Class TransportService
 *
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

        $url = "";
        if (!empty($data['result_file'])) {
            $url = urlDomain(get_url());
            $speed = 1;
        }
        $returnData = [
            'speed'       => sprintf("%.1f", $speed * 100),
            'result_file' => $url.$data['result_file']
        ];
        return self::createReturn(true, $returnData);
    }

    /**
     * 导入
     * @param $task_log
     */
    static function import($task_log)
    {
        //获取任务信息
        $TransportTaskModel = new TransportTaskModel();
        $task = $TransportTaskModel->where(['id' => $task_log['task_id']])->find();

        $import = new Import($task_log['id']);
        $import->setModel($task['model']);

        $fields = [];
        $task_fields = M('TransportField')->where(['task_id' => $task['id']])->select();
        foreach ($task_fields as $index => $field) {
            $fields[] = new ExportField($field['field_name'], $field['export_name'], $field['filter']);
        }
        $import->setFields($fields);

        //法二： 采用Excel文件导入
        $filename = rtrim(SITE_PATH, '/').$task_log['filename'];
        $import->setFilename($filename);

        //导入
        $import->import();
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
        $filename = empty($task_log['filename']) ? $task['title'].date('YmdHis', time()) : $task_log['filename'];
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
                    $new_condition = array($operator, '%'.$value.'%');
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

        $url = $export->exportXlsSrc($task_log['filename'], $task['title']);

        // 保存文件
        if (!empty($url)) {
            $TransportTaskLogModel->where(['id' => $task_log['id']])->save(['result' => 2, 'update_time' => time(), 'result_file' => $url]);
        }
        return self::createReturn(true, ['result_file' => $url]);
    }
}