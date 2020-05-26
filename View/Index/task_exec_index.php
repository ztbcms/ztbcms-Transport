<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <h3>执行任务</h3>
            <div class="filter-container">
                <el-form>
                    <el-form-item label="任务标题" label-width="120px" >
                        <span>{{form.title}}</span>
                    </el-form-item>
                    <el-form-item label="任务类型" label-width="120px" >
                        <span>{{form.type | typeName}}</span>
                    </el-form-item>
                    <el-form-item label="模型" label-width="120px">
                        <span>{{form.model}}</span>
                    </el-form-item>
                    <el-form-item label="备注" label-width="120px">
                        <el-input v-model="form.remark" style="width: 400px" placeholder=""></el-input>
                    </el-form-item>

                    <el-form-item label="导入文件" label-width="120px" required v-if="form.type == 1" >
                        <span>
                            <span slot="tip" class="el-upload__tip">仅支持xls格式</span>
                            <el-upload
                                    class="upload-demo"
                                    action="{:U('Transport/Upload/upload')}"
                                    :on-preview="handlePreview"
                                    :on-remove="handleRemove"
                                    :on-success="onUploadSuccess"
                                    :before-remove="beforeRemove"
                                    :limit="1"
                                    :on-exceed="handleExceed"
                                    :before-upload="checkType"
                                    :file-list="form.fileList"
                                    accept=".xls"
                            >
                                <el-button size="small" type="primary">点击上传</el-button>
                            </el-upload>
                        </span>
                    </el-form-item>

                    <el-form-item label="导出文件名" label-width="120px" required v-if="form.type == 2">
                        <el-input v-model="form.filename" style="width: 400px" placeholder="默认:标题+创建时间"></el-input>
                    </el-form-item>
                    <el-form-item label-width="120px" required>
                        <el-button type="primary" size="mini" @click="toLog">创建执行日志</el-button>
                        <el-button type="primary" size="mini" @click="toCreateView">创建并查看进度</el-button>
                        <el-button type="primary" size="mini" @click="download" v-if="form.type == 1">下载导入示例</el-button>
                    </el-form-item>
                </el-form>
            </div>
        </el-card>
    </div>

    <style>
        .filter-container {
            padding-bottom: 10px;
        }
        .el-form-item{
            margin-bottom: 10px;
        }
    </style>
    <script>
        $(document).ready(function () {
            new Vue({
                el: '#app',
                data: {
                    task_id:"{:I('get.id')}",
                    form: {
                        title:'',
                        description:'',
                        type: 1,
                        model: '',
                        filename:""
                    },
                    tableKey: 0,
                },
                watch: {},
                filters: {
                    typeName: function (type_id) {
                        if(type_id == 1) return '导入任务';
                        if(type_id == 2) return '导出任务';
                    }
                },
                methods: {
                    getInfo(task_id){
                        var that = this;
                        var url = "/Transport/Index/task_exec_index";
                        if(task_id){
                            url = url + '?id=' + task_id
                        }
                        $.ajax({
                            url:url,
                            dataType:"json",
                            type:"get",
                            success(res){
                                that.form = res.data;
                            }
                        })
                    },

                    // 创建执行日志
                    toLog(){
                        var that = this;
                        that.form.task_id = that.task_id
                        that.form.id = ""
                        $.ajax({
                            url:"{:U('Transport/Index/task_log_create')}",
                            type:"POST",
                            data:that.form,
                            dataType:"json",
                            success(res){
                                if(res.status){
                                    layer.msg(res.msg, {time: 1000}, function(){
                                        window.location.reload()
                                    });
                                }else{
                                    layer.msg(res.msg);
                                }
                                console.log(res)
                            }
                        })
                    },
                    // 创建执行日志并查看进度
                    toCreateView(){
                        var that = this;
                        that.form.task_id = that.task_id
                        that.form.id = ""
                        $.ajax({
                            url:"{:U('Transport/Index/task_log_create')}",
                            dataType:"json",
                            type:"post",
                            data:that.form,
                            success(res){
                                if(res.status){
                                    layer.msg(res.msg, {time: 1000}, function(){
                                        // 跳转到详情中
                                        Ztbcms.openNewIframeByUrl('执行任务', '/Transport/Index/task_exec_info?id='+res.data)
                                    });
                                }else{
                                    layer.msg(res.msg);
                                }
                            }
                        })

                    },
                    // 下载示例
                    download(){
                        window.location.href = '/Transport/index/down';
                    },
                    // 上传成功
                    onUploadSuccess:function(response, file, fileList){
                        if(response.status){
                            this.form.filename = response.data.url
                        }else{
                            layer.msg(response.msg)
                            return false;
                        }
                    },
                    // 检查上传文件的类型
                    checkType(file){
                        const Xls = file.name.split('.');
                        const isLt2M = file.size / 1024 / 1024 < 10;
                        if(Xls[1] === 'xls'){}else{
                            this.$message.error('上传文件只能是 xls/xlsx 格式')
                            return false;
                        }
                    },
                    handleRemove(file, fileList) {
                        console.log(file, fileList);
                    },
                    handlePreview(file) {
                        console.log(file);
                    },
                    handleExceed(files, fileList) {
                        console.log(fileList)
                        console.log(files)
                        this.$message.warning(`当前限制选择 1 个文件`);
                    },
                    // 移除文件
                    beforeRemove(file, fileList) {
                        this.form.filename = ""
                    }
                },
                mounted: function () {
                    if(this.task_id){
                        this.getInfo(this.task_id)
                    }
                },
            })
        })
    </script>
</block>

