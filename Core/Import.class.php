<?php

// +----------------------------------------------------------------------
// | Author: Jayin Ton <tonjayin@gmail.com>
// +----------------------------------------------------------------------

namespace Transport\Core;

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
        $excel_data = $this->getExcelData();
        array_shift($excel_data);
        $this->setExcelData($excel_data);
        return $excel_data;
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
        $excel_data = $this->getExcelData();
        foreach ($excel_data as $index => $row_data) {
            $this->data[] = $this->importRow($row_data);
        }

        return $this->data;
    }

    /**
     * 加载Excel数据
     */
    public function loadExcelData() {
        $this->onStartLoadData();
        if (empty($this->getExcelData())) {
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

            $this->setExcelData($excelData);
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

}