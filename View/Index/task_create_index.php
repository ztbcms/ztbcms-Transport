<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <h3>添加任务</h3>
            <div class="filter-container">
                <el-form :model="form">
                    <el-form-item label="任务标题" label-width="120px" required>
                        <el-input v-model="form.title" style="width: 400px" placeholder=""></el-input>
                    </el-form-item>
                    <el-form-item label="任务描述" label-width="120px" required>
                        <el-input v-model="form.description" style="width: 400px" placeholder=""></el-input>
                    </el-form-item>
                    <el-form-item label="任务类型" label-width="120px">
                        <template>
                            <el-select v-model="form.type" clearable placeholder="请选择">
                                <el-option
                                        v-for="item in typeList"
                                        :key="item.value"
                                        :label="item.label"
                                        :value="item.value">
                                </el-option>
                            </el-select>
                        </template>
                    </el-form-item>
                    <el-form-item label="模型" label-width="120px">
                        <template>
                            <el-select v-model="form.model"  placeholder="请选择" clearable>
                                <el-option
                                    v-for="item in modelList"
                                    :key="item.value"
                                    :label="item.label"
                                    :value="item.value">
                                </el-option>
                            </el-select>
                        </template>
                    </el-form-item>
                    <el-form-item label-width="120px" required>
                        <el-button type="primary" @click="doEdit">提交</el-button>
                    </el-form-item>
                </el-form>
            </div>
        </el-card>
    </div>

    <style>
        .filter-container {
            padding-bottom: 10px;
        }

    </style>
    <script>
        $(document).ready(function () {
            new Vue({
                el: '#app',
                data: {
                    form: {
                        title:'',
                        description:'',
                        type: 1,
                        model: '',
                    },
                    typeList: [
                        {
                            value: 1,
                            label: '导入任务'
                        },
                        {
                            value: 2,
                            label: '导出任务'
                        }
                    ],
                    modelList:[], // 模型列表
                    tableKey: 0,
                },
                watch: {},
                filters: {},
                methods: {
                    doEdit: function () {
                        var that = this;
                        $.ajax({
                            url:"{:U('task_create')}",
                            dataType:"json",
                            data:that.form,
                            type:"post",
                            success(res){
                                if(res.status){
                                    layer.msg(res.msg, {time: 1000}, function () {
                                        window.location.href = '/Transport/Index/task_edit_index?id=' + res.data.id
                                    });
                                }else{
                                    layer.msg(res.msg, {time: 1000}, function () {

                                    });
                                }
                            }
                        })
                    },
                    getModelList(){
                        var that = this;
                        $.ajax({
                            url:"/Transport/Index/getEditTaskParam",
                            dataType:"json",
                            type:"get",
                            success(res){
                                that.modelList = res.data;
                            }
                        })
                    }
                },
                mounted: function () {
                    this.getModelList();
                },
            })
        })
    </script>
</block>

