<?php

/**
 * author: Jayin <tonjayin@gmail.com>
 */

namespace Transport\Core;

use Transport\Model\TransportTaskLogModel;

/**
 * 数据传输基类
 */
class Transport {

    /**
     * Excel数据
     *
     * @var array
     */
    private $excel_data = [];

    /**
     * 成功导入的数据
     * @var array
     */
    protected $success_data = [];
    /**
     * 导入失败的数据项
     * @var array
     */
    protected $fail_data = [];
    /**
     * 任务日志ID
     *
     * @var string
     */
    protected $task_log_id;

    /**
     * @return array
     */
    public function getExcelData() {
        return $this->excel_data;
    }

    /**
     * @param array $excel_data
     */
    public function setExcelData($excel_data) {
        $this->excel_data = $excel_data;
    }

    /**
     * 传输处理开始
     *
     * @return mixed
     */
    protected function onStartTransport() {
        if (!empty($this->task_log_id)) {
            M('TransportTaskLog')->where(['id' => $this->task_log_id])->save([
                'process_status' => TransportTaskLogModel::PROCESS_STATUS_PRICESSING,
                'start_transport_time' => time(),
                'update_time' => time()
            ]);
        }
    }

    /**
     * 开始加载数据
     */
    protected function onStartLoadData() {
        if (!empty($this->task_log_id)) {

        }
    }

    /**
     * 加载完数据项的回调
     */
    protected function onFinishLoadData() {
        if (!empty($this->task_log_id)) {
            //导入项数
            $total_amount = count($this->getExcelData()) - 1; //第一行为表头
            M('TransportTaskLog')->where(['id' => $this->task_log_id])->save([
                'total_amount' => $total_amount,
                'update_time' => time()
            ]);
        }
    }
    /**
     * 开始处理数据操作(导出，导入)
     *
     * @return mixed
     */
    protected function onStartHandleData() {
        if (!empty($this->task_log_id)) {
            M('TransportTaskLog')->where(['id' => $this->task_log_id])->save(['progress' => 0, 'update_time' => time()]);
        }
    }

    /**
     * 开始处理单行数据
     */
    protected function onStartHandleRowData() {
        if (!empty($this->task_log_id)) {
            M('TransportTaskLog')->where(['id' => $this->task_log_id])->setInc('progress', 1);
        }
    }

    /**
     * 处理完当前行数据后的回调
     */
    protected function onFinishHandlRowData() {
        if (!empty($this->task_log_id)) {
            M('TransportTaskLog')->where(['id' => $this->task_log_id])->save([
                'success_amount' => count($this->success_data),
                'update_time' => time()
            ]);
        }
    }

    /**
     * 数据处理操作(导出，导入)完成
     *
     * @return mixed
     */
    protected function onFinishHandleData() {
        // TODO: Implement onFinishHandleData() method.
    }

    /**
     * 数据传输结束
     */
    protected function onFinishTransport() {
        if (!empty($this->task_log_id)) {
            $log = M('TransportTaskLog')->where(['id' => $this->task_log_id])->find();
            $now = time();
            $duration = $now - $log['start_transport_time'];
            M('TransportTaskLog')->where(['id' => $this->task_log_id])->save([
                'process_status' => TransportTaskLogModel::PROCESS_STATUS_FINISH,
                'end_transport_time' => $now,
                'use_time' => $duration,
                'update_time' => time()
            ]);
        }
    }

}