<?php

// +----------------------------------------------------------------------
// | Author: Jayin Ton <tonjayin@gmail.com>
// +----------------------------------------------------------------------

namespace Transport\Core;

/**
 * 导出操作核心类
 *
 * @package Transport\Core
 */
class Export extends Transport {

    //导出模型名称(一般为不含前缀的表名)
    protected $model = '';

    //导出字段
    protected $fields = array();

    //条件筛选
    protected $condition = array();
    protected $filterString = ''; //sql

    //导出文件名
    protected $filename = 'export';

    //导出表格内容
    protected $_content = '';

    //导出的表头
    protected $excel_header_data = [];

    /**
     * 源数据
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
     * 获取数据筛选条件
     *
     * @return array
     */
    public function getConditions() {
        return $this->getCondition();
    }

    /**
     * 获取数据
     *
     * @return array|mixed
     */
    public function getExportData() {
        $filter = $this->getConditions();
        $filterString = $this->getFilterString();
        $db = M($this->getModel())->where($filter);

        if (!empty($filterString)) {
            $db = $db->where($filter);
        }
        $data = $db->select();

        if (empty($data)) {
            return [];
        }

        return $data;
    }


    /**
     * 表格头单列渲染
     *
     * @param $field ExportField
     * @return string
     */
    private function exportHeader($field) {
        return $field->getExportName();
    }

    /**
     * 表格头
     *
     * @param $fields array
     * @return string
     */
    private function exportHeaders($fields = []) {
        $content_header = '<tr>';
        foreach ($fields as $index => $field) {
            $content_header .= '<th>' . $this->exportHeader($field) . '</th>';
        }

        $content_header .= '</tr>';

        return $content_header;
    }

    /**
     * 渲染一格
     *
     * @param ExportField $field
     * @param array       $row_data
     * @return string
     */
    private function exportCell(ExportField $field, $row_data) {
        return $field->filterValue($field->getFieldName(), $row_data[$field->getFieldName()], $row_data);
    }

    /**
     * 渲染一行
     *
     * @param array $row_data
     * @return string
     */
    private function exportRow($row_data = []) {
        $row = '<tr>';

        foreach ($row_data as $index => $cell_data) {
            $row .= '<td>' . $cell_data . '</td>';
        }

        $row .= '</tr>';

        return $row;
    }

    /**
     * 渲染行
     *
     * @return string
     */
    private function exportRows() {
        $content_rows = '';
        $data = $this->getExcelData();

        foreach ($data as $index => $row_data) {
            $content_rows .= $this->exportRow($row_data);
        }

        return $content_rows;
    }

    /**
     * 渲染整个表格
     *
     * @return string
     */
    public function exportTable() {
        //先提取数据
        $data = $this->getData();
        if (empty($data)) {
            $data = $this->getExportData();
            $this->setData($data);
        }

        $this->loadExcelData();

        $this->_content .= '<table>';
        $this->_content .= $this->exportHeaders($this->fields);
        $this->_content .= $this->exportRows();
        $this->_content .= '</table>';

        return $this->_content;
    }

    private function loadExcelData(){
        $this->loadHeaders();
        $this->loadRows();
    }

    /**
     * 生成 XLS 文件
     */
    public function exportXls() {
        $this->onStartTransport();

        //先提取数据
        $this->onStartLoadData();
        $data = $this->getData();
        if (empty($data)) {
            $data = $this->getExportData();
            $this->setData($data);
        }

        //整理数据到excel表格
        $this->loadExcelData();

        $this->onFinishLoadData();

        //开始处理数据
        $this->onStartHandleData();

        //设置表格
        $this->phpexcel->getProperties()->setCreator($this->filterString)->setLastModifiedBy('ZTBCMS')->setTitle("Office 2007 XLSX Document")->setSubject("Office 2007 XLSX Document")->setDescription("Document for Office 2007 XLSX, generated using PHP classes.")->setKeywords("office 2007 openxml php")->setCategory("ZTBCMS");

        $header_data = $this->excel_header_data;

        $excel_data = $this->getExcelData();

        //填充数据
        $export_data = [$header_data];
        foreach ($excel_data as $index => $row){
            $export_data []= $row;
        }

        foreach ($export_data as $key => $row) {

            $this->onStartHandleRowData();

            $num = $key + 1;
            $i = 0;
            foreach ($row as $key2 => $value2) {
                $value2 = ' ' . $value2; //处理XLS自动把该行纯数字并且比较长，自动转为客服计数，会自动补全0
                $this->phpexcel->setActiveSheetIndex(0)->setCellValue(\PHPExcel_Cell::stringFromColumnIndex($i) . ($num),
                    $value2);
                $i++;
            }

            $this->onFinishHandlRowData();
        }

        //设置表格并输出
        $this->phpexcel->getActiveSheet()->setTitle($this->filename);
        ob_end_clean();//清除缓冲区,避免乱码
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename={$this->filename}.xls");
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public'); // HTTP/1.0
        $objWriter = \PHPExcel_IOFactory::createWriter($this->phpexcel, 'Excel5');
        $objWriter->save('php://output');

        $this->onFinishHandleData();

        $this->onFinishTransport();
        exit;
    }


    /**
     * 生成 XLS 文件 并保存到本地
     */
    public function exportXlsSrc() {
        $this->onStartTransport();
        //先提取数据
        $this->onStartLoadData();
        $data = $this->getData();
        if (empty($data)) {
            $data = $this->getExportData();
            $this->setData($data);
        }

        //整理数据到excel表格
        $this->loadExcelData();

        $this->onFinishLoadData();
        //开始处理数据
        $this->onStartHandleData();

        //设置表格
        $this->phpexcel->getProperties()->setCreator($this->filterString)->setLastModifiedBy('ZTBCMS')->setTitle("Office 2007 XLSX Document")->setSubject("Office 2007 XLSX Document")->setDescription("Document for Office 2007 XLSX, generated using PHP classes.")->setKeywords("office 2007 openxml php")->setCategory("ZTBCMS");

        $header_data = $this->excel_header_data;

        $excel_data = $this->getExcelData();

        //填充数据
        $export_data = [$header_data];
        foreach ($excel_data as $index => $row){
            $export_data []= $row;
        }

        foreach ($export_data as $key => $row) {

            $this->onStartHandleRowData();

            $num = $key + 1;
            $i = 0;
            foreach ($row as $key2 => $value2) {
                $value2 = ' ' . $value2; //处理XLS自动把该行纯数字并且比较长，自动转为客服计数，会自动补全0
                $this->phpexcel->setActiveSheetIndex(0)->setCellValue(\PHPExcel_Cell::stringFromColumnIndex($i) . ($num),
                    $value2);
                $i++;
            }

            $this->onFinishHandlRowData();
        }

        //设置表格并输出
        $this->phpexcel->getActiveSheet()->setTitle($this->filename);
        ob_end_clean();//清除缓冲区,避免乱码
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename={$this->filename}.xls");
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public'); // HTTP/1.0
        $objWriter = \PHPExcel_IOFactory::createWriter($this->phpexcel, 'Excel5');
//        $objWriter->save('php://output');

        $this->onFinishHandleData();
        $this->onFinishTransport();

        // 返回保存地址
        $savePath = "./d/file/module_transport/".date("Ymd",time())."/";
        // 检查上传目录
        if (!is_dir($savePath)) {
            // 检查目录是否编码后的
            if (is_dir(base64_decode($savePath))) {
                $savePath = base64_decode($savePath);
            } else {
                // 尝试创建目录
                if (!mkdir($savePath)) {
                    $this->error = '上传目录' . $savePath . '不存在';
                    return false;
                }
            }
        } else {
            if (!is_writeable($savePath)) {
                $this->error = '上传目录' . $savePath . '不可写';
                return false;
            }
        }

        $fileName = time();
        $_fileName = iconv("utf-8", "gb2312", $fileName);   //转码
        $_savePath = $savePath.$_fileName.'.xls';
        $objWriter->save($_savePath);
        return $savePath.$fileName.'.xls';
        exit;
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
    public function setFields($fields) {
        $this->fields = $fields;
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
     * @return array
     */
    public function getData() {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData($data) {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getFilterString() {
        return $this->filterString;
    }

    /**
     * @param string $filterString
     */
    public function setFilterString($filterString) {
        $this->filterString = $filterString;
    }

    /**
     * @return array
     */
    public function getCondition() {
        return $this->condition;
    }

    /**
     * @param array $condition
     */
    public function setCondition($condition) {
        $this->condition = $condition;
    }

    private function loadHeaders(){
        $header_data = [];
        $fields = $this->getFields();
        foreach ($fields as $index => $field) {
            $header_data []= $this->exportHeader($field);
        }

        $this->excel_header_data = $header_data;

        return $header_data;
    }

    private function loadRows() {
        $rows = [];
        $data = $this->getData();

        foreach ($data as $index => $row_data) {
            $rows []= $this->loadRow($row_data);
        }

        $this->setExcelData($rows);

        return $rows;
    }

    private function loadRow($row_data){
        $row = [];
        $fields = $this->getFields();

        foreach ($fields as $index => $field) {
            $row []= $this->exportCell($field, $row_data);
        }

        return $row;
    }

}