<?php
/**
 * User: jayinton
 * Date: 2020/6/10
 * Time: 17:58
 */

namespace Transport\Job;


use Queue\Libs\Job;
use Transport\Model\TransportTaskLogModel;
use Transport\Model\TransportTaskModel;
use Transport\Service\TransportService;

class TransportJob extends Job
{
    protected $task_log_id = null;

    public function __construct($task_log_id)
    {
        $this->task_log_id = $task_log_id;
    }


    function handle()
    {
        $TransportTaskLogModel = new TransportTaskLogModel();
        $where = [
            'id' => $this->task_log_id
        ];
        $taskLog = $TransportTaskLogModel->where($where)->find();
        $TransportTaskModel = new TransportTaskModel();
        $task_info = $TransportTaskModel->where(['id' => $taskLog['task_id']])->find();
        if (!$taskLog || !$task_info) {
            return false;
        }
        if ($taskLog['process_status'] == TransportTaskLogModel::PROCESS_STATUS_WAITTING) {
            if ($task_info['type'] == TransportTaskModel::TYPE_IMPORT) {
                $res = TransportService::import($taskLog);
            }
            if ($task_info['type'] == TransportTaskModel::TYPE_EXPORT) {
                $res = TransportService::export($taskLog);
            }
        }
    }
}