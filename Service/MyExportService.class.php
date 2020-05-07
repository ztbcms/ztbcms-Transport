<?php
/**
 * Created by PhpStorm.
 * User: yezhilie
 * Date: 2019/11/1
 * Time: 18:28
 */

namespace Transport\Service;

use System\Service\BaseService;

class MyExportService extends BaseService{

    static function getExcelHead($file_url){
        include_once(APP_PATH . '/Transport/Libs/PHPExcel/IOFactory.php');
        include_once(APP_PATH . '/Transport/Libs/PHPExcel/Cell.php');
        $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
        $objPHPExcel = $objReader->load($file_url);
        $objWorksheet = $objPHPExcel->getActiveSheet();
        $highestRow = 1;
        $highestColumn = $objWorksheet->getHighestColumn();
        $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
        $excelData = [];
        for ($row = 1; $row <= $highestRow; $row++) {
            for ($col = 0; $col < $highestColumnIndex; $col++) {
                $excelData[$row][] = trim((string)$objWorksheet->getCellByColumnAndRow($col, $row)->getValue());
            }
        }
        return $excelData[1];
    }

    static function getExcelData($file_url){
        include_once(APP_PATH . '/Transport/Libs/PHPExcel/IOFactory.php');
        include_once(APP_PATH . '/Transport/Libs/PHPExcel/Cell.php');
        $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
        $objPHPExcel = $objReader->load($file_url);
        $objWorksheet = $objPHPExcel->getActiveSheet();
        $highestRow = $objWorksheet->getHighestRow();
        $highestColumn = $objWorksheet->getHighestColumn();
        $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
        $excelData = [];
        for ($row = 1; $row <= $highestRow; $row++) {
            for ($col = 0; $col < $highestColumnIndex; $col++) {
                $excelData[$row][] = trim((string)$objWorksheet->getCellByColumnAndRow($col, $row)->getValue());
            }
        }
        array_shift($excelData);
        return $excelData;
    }

    static function exportArray($head, $data, $filename = ''){
        include(APP_PATH . '/Transport/Libs/PHPExcel.php');

        $excel = new \PHPExcel();
        $excel->getProperties()->setTitle("Office 2007 XLSX Document")->setSubject("Office 2007 XLSX Document")->setDescription("Document for Office 2007 XLSX, generated using PHP classes.")->setKeywords("office 2007 openxml php")->setCategory("ZTBCMS");

        $i = 0;
        $j = 0;

        $j++;
        foreach($head as $key => $value){
            $excel->setActiveSheetIndex(0)->setCellValueExplicit(\PHPExcel_Cell::stringFromColumnIndex($i) . ($j), $value);
            $i++;
        }

        foreach ($data as $key => $row){
            $i = 0;
            $j++;
            foreach ($row as $key2 => $value2) {
                $excel->setActiveSheetIndex(0)->setCellValueExplicit(\PHPExcel_Cell::stringFromColumnIndex($i) . ($j), $value2, \PHPExcel_Cell_DataType::TYPE_STRING);
                $i++;
            }
        }

        //设置表格并输出
        if(!$filename){
            $filename = '数据导出'.date('Ymdhis');
        }
        $excel->getActiveSheet()->setTitle($filename);
        $objWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        ob_end_clean();//清除缓冲区,避免乱码
        header("Pragma: public"); // HTTP/1.0
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Disposition: attachment;filename={$filename}.xls");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-excel");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
        exit;
    }
}