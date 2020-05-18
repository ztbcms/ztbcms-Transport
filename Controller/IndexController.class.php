<?php

// +----------------------------------------------------------------------
// | Author: Jayin Ton <tonjayin@gmail.com>
// +----------------------------------------------------------------------

namespace Transport\Controller;


use Common\Controller\AdminBase;
use Transport\Core\Export;
use Transport\Core\ExportField;
use Transport\Core\Import;
use Transport\Model\TransportTaskLogModel;
use Transport\Model\TransportTaskModel;
use Transport\Service\TransportService;

/**
 * Class IndexController
 *
 * @package Transport\Controller
 */
class IndexController extends AdminBase {

    private $db;

    protected function _initialize() {
        parent::_initialize();

        $this->db = D('Transport/TransportTask');
    }


    /**
     * 任务列表页
     */
    function index() {
        if(IS_AJAX){
            $TransportTaskModel = new TransportTaskModel;
            $_page = I('page',1);
            $_limit = I('limit',1);
            //获取总记录数
            $count = $TransportTaskModel->count();
            //总页数
            $total_page = ceil($count / $_limit);
            $page = $this->page($count, $_limit, $_page);

            //获取到的分页数据
            $data = $TransportTaskModel->limit($page->firstRow . ',' . $page->listRows)
                ->order('id desc')->select();
            $this->ajaxReturn(self::createReturnList(true, $data, $_page, $_limit, $count, $total_page));
        }
        $data = $this->db->select();
        $this->assign('data', $data);

        $this->display();
    }

    /**
     * 创建任务页
     */
    function task_create_index() {
        if(IS_AJAX){
            //返回模型列表
            $list = M('Model')->select();
            $newList = [];
            foreach ($list as $item){
                $obj['value'] = $item['modelid'];
                $obj['label'] = $item['name'];
                $newList[] = $obj;
            }
            $this->ajaxReturn(self::createReturn(true,$newList));
        }

        $this->display();
    }

    /**
     * 创建任务
     */
    function task_create() {
        $data = I('post.');

        if ($this->db->create($data)) {
            $this->db->add();
            $this->success('创建成功');
        } else {
            $this->error($this->db->getDbError());
        }

    }

    /**
     * 删除任务
     */
    function task_delete() {
        $this->db->where(['id' => I('get.id')])->delete();
        $this->success('操作成功');
    }

    /**
     * 编辑任务页
     */
    function task_edit_index() {
        if(IS_AJAX){
            $task_id = I('get.id');
            $task = $this->db->where(['id' => $task_id])->find();
            $task_conditions = M('TransportCondition')->where(['task_id' => $task_id])->select();
            $task_fields = M('TransportField')->where(['task_id' => $task_id])->select();
            $data['task'] = $task;
            $data['task_conditions'] = $task_conditions ?: [];
            $data['task_fields'] = $task_fields ?: [];
            $this->ajaxReturn(self::createReturn(true,$data));
        }

        $task_id = I('get.id');
        $task = $this->db->where(['id' => $task_id])->find();
        $this->assign($task);

        $task_conditions = M('TransportCondition')->where(['task_id' => $task_id])->select();
        $this->assign('task_conditions', $task_conditions);

        $task_fields = M('TransportField')->where(['task_id' => $task_id])->select();
        $this->assign('task_fields', $task_fields);

        $this->display();
    }

    /**
     * 编辑任务
     */
    function task_edit() {
        $task_id = I('task_id');
        $data = I('post.');
        $this->db->where(['id' => $task_id])->save($data);

        $this->success('操作成功');
    }

    /**
     * 更新筛选条件信息
     */
    function task_update_condition() {
        if(IS_AJAX){
            $task_id = I('post.task_id');
            $list = I('post.list');
            //先清空后加入
            M('TransportCondition')->where(['task_id' => $task_id])->delete();

            $batch_data = [];
            foreach ($list as $index => $f) {
                $batch_data[] = [
                    'task_id' => $task_id,
                    'filter' => $f['filter'],
                    'operator' => $f['operator'],
                    'value' => $f['value'],
                ];
            }
            $res = false;
            foreach ($batch_data as $index => $data) {
                $res = M('TransportCondition')->add($data);
            }
            $this->ajaxReturn(self::createReturn(true,$res,'操作成功'));
        }

        $task_id = I('post.task_id');
        $filter = I('post.condition_filter');
        $operator = I('post.condition_operator');
        $value = I('post.condition_value');

        //先清空后加入
        M('TransportCondition')->where(['task_id' => $task_id])->delete();

        $batch_data = [];
        foreach ($filter as $index => $f) {
            $batch_data[] = [
                'task_id' => $task_id,
                'filter' => $filter[$index],
                'operator' => $operator[$index],
                'value' => $value[$index],
            ];
        }

        foreach ($batch_data as $index => $data) {
            M('TransportCondition')->add($data);
        }

        $this->success('操作成功');
    }

    /**
     * 更新设置字段映射
     */
    function task_update_field() {
        if(IS_AJAX){
            $task_id = I('post.task_id');
            $list = I('post.list');
            //先清空后加入
            M('TransportField')->where(['task_id' => $task_id])->delete();

            $batch_data = [];
            foreach ($list as $index => $f) {
                $batch_data[] = [
                    'task_id' => $task_id,
                    'field_name' => $f['field_name'],
                    'export_name' => $f['export_name'],
                    'filter' => $f['filter'],
                ];
            }
            $res = false;
            foreach ($batch_data as $index => $data) {
                $res = M('TransportField')->add($data);
            }
            $this->ajaxReturn(self::createReturn(true,$res,'操作成功'));
        }

        $task_id = I('post.task_id');
        $field_name = I('post.field_field_name');
        $export_name = I('post.field_export_name');
        $filter = I('post.field_filter');

        //先清空后加入
        M('TransportField')->where(['task_id' => $task_id])->delete();

        $batch_data = [];
        foreach ($field_name as $index => $f) {
            $batch_data[] = [
                'task_id' => $task_id,
                'field_name' => $field_name[$index],
                'export_name' => $export_name[$index],
                'filter' => $filter[$index],
            ];
        }

        foreach ($batch_data as $index => $data) {
            M('TransportField')->add($data);
        }

        $this->success('操作成功');
    }

    /**
     * 执行任务预览页
     */
    function task_exec_index() {
        if(IS_AJAX){
            $task_id = I('get.id');
            $task = $this->db->where(['id' => $task_id])->find();
            $this->ajaxReturn(self::createReturn(true,$task));
        }
        $task_id = I('get.id');
        $task = $this->db->where(['id' => $task_id])->find();
        $this->assign($task);

        $task_conditions = M('TransportCondition')->where(['task_id' => $task_id])->select();
        $this->assign('task_conditions', $task_conditions);


        $task_fields = M('TransportField')->where(['task_id' => $task_id])->select();
        $this->assign('task_fields', $task_fields);

        $this->display();
    }

    /**
     * 执行任务
     */
    function task_exec() {
        $isPreview = I('get.preview');
        //设置脚本最大执行时间
        set_time_limit(0);

        $task_log_id = I('task_log_id');

        $task_log = M('TransportTaskLog')->where(['id' => $task_log_id])->find();
        $task = M('TransportTask')->where(['id' => $task_log['task_id']])->find();

        if ($task['type'] == TransportTaskModel::TYPE_EXPORT) {
            //导出任务处理

            $export = new Export($task_log_id);
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

            //取消下面两行注释,即可预览导出结果
            if ($isPreview) {
                $table = $export->exportTable();
                echo $table;
                exit();
            } else {
                $export->exportXls();
            }
        } else {
            //导入
            $import = new Import($task_log_id);

            $import->setModel($task['model']);

            //字段映射
            $fields = [];
            $task_fields = M('TransportField')->where(['task_id' => $task['id']])->select();
            foreach ($task_fields as $index => $field) {

                $fields[] = new ExportField($field['field_name'], $field['export_name'], $field['filter']);
            }
            $import->setFields($fields);

            $import->setFilename(getcwd() . $task_log['filename']);

            if ($isPreview) {
                $import->exportTable();
            } else {
                //开始导入
                $import->import();
//                $this->ajaxReturn(createReturn(true,'','导入成功'));
                $this->success('导入成功');
            }
        }
    }

    /**
     * 任务执行日志
     */
    function task_logs() {
        $default_limit = 20;

        $data = M('TransportTaskLog')->page(I('page', 1))->limit(I('limit',
            $default_limit))->order('inputtime DESC')->select();
        $this->assign('data', $data);

        $sum = M('TransportTaskLog')->count();
        $page = $this->page($sum, $default_limit);

        $this->assign('Page', $page->show());
        $this->display();
    }

    /**
     * 任务执行日志数据
     */
    function task_logs_get() {
        $TransportTaskLogModel = new TransportTaskLogModel;
        $_page = I('page',1);
        $_limit = I('limit',10);
        //获取总记录数
        $count = $TransportTaskLogModel->count();
        //总页数
        $total_page = ceil($count / $_limit);
        $page = $this->page($count, $_limit, $_page);

        //获取到的分页数据
        $data = $TransportTaskLogModel->limit($page->firstRow . ',' . $page->listRows)
            ->order('id desc')->select();
        $this->ajaxReturn(self::createReturnList(true, $data, $_page, $_limit, $count, $total_page));

    }

    /**
     * 创建任务执行日志
     */
    function task_log_create() {
        if(IS_AJAX){
            $data = I('post.');
            $data['inputtime'] = time();
            $TransportTaskLogModel = new TransportTaskLogModel();
            // 校验上传文件
            $type = $data['type'];
            if($type == TransportTaskModel::TYPE_IMPORT && empty($data['filename'])){
                $this->ajaxReturn(self::createReturn(false,'','请上传文件'));
            }
            $id = $TransportTaskLogModel->data($data)->add();
            if ($id) {
                $this->ajaxReturn(self::createReturn(true,$id,'创建任务执行日志成功'));
            }else{
                $this->ajaxReturn(self::createReturn(false,'','创建任务执行日志失败'));
            }
        }
        $data = I('post.');
        $data['inputtime'] = time();
        $TransportTaskLogModel = new TransportTaskLogModel();

        // 校验上传文件
        $type = $data['type'];
        $filename = $data['filename'];
        if($type == TransportTaskModel::TYPE_IMPORT && empty($filename)){
            $this->error('请上传文件');
        }
//        if($type == TransportTaskModel::TYPE_EXPORT && empty($filename)){
//            $this->error('请输入导出文件名');
//        }
        $id = $TransportTaskLogModel->data($data)->add();
        if ($id) {
            //跳转
            $this->redirect('task_logs');
            $this->success('创建任务执行日志成功', U('Transport/Index/task_logs'));
        } else {
            $this->error('创建任务执行日志失败');
        }

    }

    /**
     * 任务执行详情页
     */
    public function task_exec_info($id){
        $TransportTaskModel = new TransportTaskModel();
        $TransportTaskLogModel = new TransportTaskLogModel();
        if(IS_AJAX){
            $task_log = $TransportTaskLogModel->where(['id'=>$id])->find();
            $task = $TransportTaskModel->where(['id'=>$task_log['task_id']])->find();
            $this->ajaxReturn(self::createReturn(true,['task'=>$task,'task_log'=>$task_log]));
        }
//
//        $data = I('post.');
//        $data['inputtime'] = time();
//        // 校验上传文件
//        $type = $data['type'];
//        $filename = $data['filename'];
//        if($type == TransportTaskModel::TYPE_IMPORT && empty($filename)){
//            $this->error('请上传文件');
//        }
//        // 写入记录
//        $id = $TransportTaskLogModel->data($data)->add();
//        $task = $TransportTaskLogModel->where(['id'=>$id])->find();
//        $task_ = $TransportTaskModel->where(['id'=>$task['task_id']])->find();
//        $this->assign($task);
//        $this->assign($task_);
        $this->assign('task_log_id',$id);
        $this->display();
    }


    /**
     * 下载示例文件
     */
    public function down(){
        $filename = '示例文件';
        $strTable = '<table width="500" border="1">';
        $strTable .= '<tr>';
        $strTable .= '<td style="text-align:center;font-size:12px;" width="100">name</td>';
        $strTable .= '<td style="text-align:center;font-size:12px;" width="100">sex</td>';
        $strTable .= '</tr>';

        $strTable .= '<tr>';
        $strTable .= '<td style="text-align:center;font-size:12px;">&nbsp;小李</td>';
        $strTable .= '<td style="text-align:center;font-size:12px;">男</td>';
        $strTable .= '</tr>';

        header("Content-type: application/vnd.ms-excel");
        header("Content-Type: application/force-download");
        header("Content-Disposition: attachment; filename=" . $filename . ".xls");
        header('Expires:0');
        header('Pragma:public');
        echo '<html><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . $strTable . '</html>';
    }

    /**
     * 获取进度条数值
     * task_log_id 计划日志id
     */
    public function getSpeed(){
        $task_log_id = I('get.task_log_id');
        $res =  TransportService::getSpeed($task_log_id);
        $this->ajaxReturn($res);
    }


}