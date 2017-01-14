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
class Import {

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

    public function __construct() {
        include(APP_PATH . '/Transport/Libs/PHPExcel.php');

        $this->phpexcel = new \PHPExcel();
    }

    /**
     * 导入表格
     */
    function importTable() {
        $this->importHeaders();
        $this->importRows();
    }

    /**
     * 导入数据
     */
    public function importData() {
        $db = M($this->getModel());
        if (!empty($this->data)) {
            foreach ($this->data as $index => $data) {
                //TODO 可以配置导入策略：1. 若有相同，覆盖导入 2. 若有相同忽略导入 （目前默认1,以主键为唯一表示）
                //TODO 检测哪一些导入成功，哪一些失败了

                $pk = $db->getPk();
                if (isset($data[$pk])) {
                    unset($data[$pk]);
                }
                $db->add($data);
            }
        }
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
    }

    /**
     * 输出表格
     */
    private function previewTable() {
        $content = '<table>';

        foreach ($this->data as $index => $row) {
            $content .= '<tr>';

            foreach ($row as $i => $cell) {
                $content .= '<td>' . $cell . '</td>';
            }
            $content .= '</tr>';
        }
        $content .= '</table>';
        echo $content;
        exit();
    }

    /**
     * 导入XLS数据，但不插入到数据库，做阅览
     */
    public function exportTable() {
        $this->loadExcelData();
        $this->importTable();

        $this->previewTable();
    }


    /**
     * 开始导入
     */
    public function import() {

        $this->loadExcelData();
        $this->importTable();

        $this->importData();
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

}