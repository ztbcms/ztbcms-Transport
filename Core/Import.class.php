<?php

// +----------------------------------------------------------------------
// | Author: Jayin Ton <tonjayin@gmail.com>
// +----------------------------------------------------------------------

namespace Transport\Core;

use Transport\Model\TransportTaskLogModel;

/**
 * 导入Excel表格核心实现类
 *
 * @package Transport\Core
 */
class Import extends Transport {

    //模型名称(一般为不含前缀的表名)
    protected $model = '';

    //字段
    protected $fields = array();

    //导入文件名
    protected $filename = '';
    /**
     * 数据
     *
     * @var array
     */
    protected $data = [];

    /**
     * Excel处理器
     *
     * @var null|\PHPExcel
     */
    private $phpexcel = null;
    /**
     * Excel数据
     *
     * @var array
     */
    private $excel_data = [];

    /**
     * 任务日志ID
     *
     * @var string
     */
    private $task_log_id;

    public function __construct($task_log_id = '') {
        include(APP_PATH . '/Transport/Libs/PHPExcel.php');

        $this->phpexcel = new \PHPExcel();

        $this->task_log_id = $task_log_id;
    }

    /**
     * 导入表格
     */
    function importTable() {
        $this->onStartHandleData();
        $this->importHeaders();
        $this->importRows();
        $this->onFinishHandleData();
    }

    /**
     * 导入数据
     */
    public function importData() {
        $this->onStartHandleData();

        $db = M($this->getModel());
        if (!empty($this->data)) {
            foreach ($this->data as $index => $data) {
                //TODO 可以配置导入策略：1. 若有相同，覆盖导入 2. 若有相同忽略导入 （目前默认1,以主键为唯一表示）
                //TODO 检测哪一些导入成功，哪一些失败了

                $this->onStartHandleRowData();
                $pk = $db->getPk();
                if (isset($data[$pk])) {
                    unset($data[$pk]);
                }
                $res = $db->add($data);
                if ($res) {
                    $this->success_data[] = $data;
                } else {
                    $this->fail_data[] = $data;
                }
                $this->onFinishHandlRowData();
            }
        }

        $this->onFinishHandleData();
    }

    /**
     * 处理导入表头
     *
     * @return mixed
     */
    private function importHeaders() {
        return array_shift($this->excel_data);
    }

    /**
     * 处理导入一个单元格
     *
     * @param ExportField $field
     * @param string      $cell_data
     * @param array       $row_data
     * @return mixed
     */
    private function importCell(ExportField $field, $cell_data, $row_data) {
        return $field->filterValue($field->getFieldName(), $cell_data, $row_data);
    }

    /**
     * 处理导入一行
     *
     * @param array $row_data
     * @return array
     */
    private function importRow(array $row_data) {
        $result = [];
        foreach ($this->fields as $index => $field) {

            if (isset($row_data[$index])) {
                $cell_data = $row_data[$index];
            } else {
                $cell_data = '';
            }
            $result[$field->getFieldName()] = $this->importCell($field, $cell_data, $row_data);
        }

        return $result;
    }

    /**
     * 处理导入多行
     *
     * @return array
     */
    private function importRows() {
        foreach ($this->excel_data as $index => $row_data) {
            $this->data[] = $this->importRow($row_data);
        }

        return $this->data;
    }

    /**
     * 加载Excel数据
     */
    public function loadExcelData() {
        $this->onStartLoadData();
        if (empty($this->excel_data)) {
            $objReader = \PHPExcel_IOFactory::createReader('Excel5');
            $objPHPExcel = $objReader->load($this->filename);
            $objWorksheet = $objPHPExcel->getActiveSheet();
            $highestRow = $objWorksheet->getHighestRow();
            $highestColumn = $objWorksheet->getHighestColumn();
            $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
            $excelData = array();
            for ($row = 1; $row <= $highestRow; $row++) {
                for ($col = 0; $col < $highestColumnIndex; $col++) {
                    $excelData[$row][] = trim((string)$objWorksheet->getCellByColumnAndRow($col, $row)->getValue());
                }
            }

            $this->setImportData($excelData);
        }

        $this->onFinishLoadData();
    }

    /**
     * 输出表格
     */
    private function previewTable() {
        $content = '<table>';

        foreach ($this->data as $index => $row) {

            $this->onStartHandleRowData();

            $content .= '<tr>';
            foreach ($row as $i => $cell) {
                $content .= '<td>' . $cell . '</td>';
            }
            $content .= '</tr>';

            $this->success_data[] = $row;

            $this->onFinishHandlRowData();
        }
        $content .= '</table>';
        echo $content;
    }

    /**
     * 导入XLS数据，但不插入到数据库，做阅览
     */
    public function exportTable() {
        $this->onStartTransport();

        $this->loadExcelData();
        $this->importTable();

        $this->previewTable();
        $this->onFinishTransport();
        exit();
    }


    /**
     * 开始导入
     */
    public function import() {
        $this->onStartTransport();
        $this->loadExcelData();
        $this->importTable();

        $this->importData();
        $this->onFinishTransport();
    }

    /**
     * @return string
     */
    public function getModel() {
        return $this->model;
    }

    /**
     * @param string $model
     */
    public function setModel($model) {
        $this->model = $model;
    }

    /**
     * @return array
     */
    public function getFields() {
        return $this->fields;
    }

    /**
     * @param array $fields
     */
    public function setFields(array $fields) {
        $this->fields = $fields;
    }

    /**
     * 获取数据
     *
     * @return array|mixed
     */
    public function getImportData() {
        return $this->excel_data;
    }

    /**
     * 设置导入数据
     *
     * @param array $excel_data
     */
    public function setImportData(array $excel_data) {
        $this->excel_data = $excel_data;
    }

    /**
     * @return string
     */
    public function getFilename() {
        return $this->filename;
    }

    /**
     * @param string $filename
     */
    public function setFilename($filename) {
        $this->filename = $filename;
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
            $total_amount = count($this->getImportData()) - 1; //第一行为表头
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
        M('TransportTaskLog')->where(['id' => $this->task_log_id])->save(['progress' => 0, 'update_time' => time()]);
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