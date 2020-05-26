<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <h3>编辑任务</h3>
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
                                        :value="item.label">
                                </el-option>
                            </el-select>
                        </template>
                    </el-form-item>
                    <el-form-item label-width="120px" required>
                        <el-button type="primary" @click="doEdit">提交</el-button>
                    </el-form-item>
                </el-form>
            </div>

            <h3 style="margin: 10px 0px;">设置字段映射</h3>
            <div class="filter-container">
                <el-form :model="form">
                    <el-form-item label="新增字段映射" label-width="120px" required>
                        内部字段名：<el-input style="width: 150px" placeholder="" v-model="new_field.new_field_name"></el-input>
                        外部名称：<el-input style="width: 150px" placeholder="" v-model="new_field.new_export_name"></el-input>
                        过滤处理器：<el-input style="width: 150px" placeholder="" v-model="new_field.new_filter_name"></el-input>
                        <el-button type="primary" @click="addField">提交</el-button>
                    </el-form-item>

                    <el-form-item label="当前字段映射" label-width="120px" required>
                        <div v-for="item,index in FieldList" style="margin-bottom: 5px;">
                            内部字段名：<el-input style="width: 150px" placeholder="" v-model="item.field_name"></el-input>
                            外部名称：<el-input style="width: 150px" placeholder="" v-model="item.export_name"></el-input>
                            过滤处理器：<el-input style="width: 150px" placeholder="" v-model="item.filter"></el-input>
                            <el-button type="danger" @click.prevent="removeField(item)">删除</el-button>
                        </div>
                    </el-form-item>
                    <el-form-item label-width="120px" required>
                        <el-button type="primary" @click="doUpdateField">提交</el-button>
                    </el-form-item>

                </el-form>
            </div>

            <!-- 导出才需要设置筛选条件-->
            <h3 style="margin: 10px 0px;" v-if="form.type == 2">设置筛选条件</h3>
            <div class="filter-container" v-if="form.type == 2">
                <el-form :model="form">
                    <el-form-item label="设置筛选条件" label-width="120px">
                        字段：<el-input style="width: 150px" v-model="new_filter.new_filter_name"></el-input>
                        条件：<el-select v-model="new_filter.new_operator" clearable placeholder="请选择" style="width: 100px;">
                            <el-option
                                    v-for="item in operator"
                                    :key="item.value"
                                    :label="item.label"
                                    :value="item.value">
                            </el-option>
                        </el-select>
                        值：<el-input style="width: 150px" v-model="new_filter.new_value"></el-input>
                        <el-button type="primary" @click="addFitter">提交</el-button>
                    </el-form-item>

                    <el-form-item label="当前筛选条件" label-width="120px" required>
                        <div v-for="item in fitterList" style="margin-bottom: 5px;">
                            字段：<el-input style="width: 150px" placeholder="" v-model="item.filter"></el-input>
                            条件：<el-input style="width: 100px" placeholder="" v-model="item.operator" disabled></el-input>
                            值：<el-input style="width: 150px" placeholder="" v-model="item.value"></el-input>
                            <el-button type="danger" @click.prevent="removeFilter(item)">删除</el-button>
                        </div>
                    </el-form-item>

                    <el-form-item label-width="120px" required>
                        <el-button type="primary" @click="doUpdateCondition">提交</el-button>
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
                    task_id: "{:I('get.id')}",
                    form: {
                        title:'',
                        description:'',
                        type: '1',
                        model: '',
                    },
                    new_field:{
                        new_field_name:'',
                        new_export_name:'',
                        new_filter_name:'',
                    },
                    new_filter:{
                        new_filter_name:'',
                        new_operator:'EQ',
                        new_value:'',
                    },
                    typeList: [
                        {
                            value: '1',
                            label: '导入任务'
                        },
                        {
                            value: '2',
                            label: '导出任务'
                        }
                    ],
                    //筛选条件符号
                    operator:[
                        {
                            value: 'EQ',
                            label: '='
                        },
                        {
                            value: 'NEQ',
                            label: '!='
                        },
                        {
                            value: 'GT',
                            label: '>'
                        },
                        {
                            value: 'EGT',
                            label: '>='
                        },
                        {
                            value: 'LT',
                            label: '<'
                        },
                        {
                            value: 'ELT',
                            label: '<='
                        },
                        {
                            value: 'LIKE',
                            label: 'LIKE'
                        },
                    ],
                    modelList:[],  //模型列表
                    tableKey: 0,
                    FieldList:[],  //映射字段
                    fitterList:[], //筛选条件
                },
                watch: {},
                filters: {},
                methods: {
                    getInfo(id){
                      var that = this;
                      var url = "{:U('Transport/index/task_edit_index')}";
                      url = url + "&id=" + id;
                      $.ajax({
                          url: url,
                          dataType:"json",
                          type:"get",
                          success(res){
                              if(res.status){
                                  that.form = res.data.task
                                  that.form.task_id = that.task_id
                                  that.FieldList = res.data.task_fields
                                  that.fitterList = res.data.task_conditions
                              }
                          }
                      })

                    },
                    doEdit: function () {
                        var that = this;
                        $.ajax({
                            url:"{:U('task_edit')}",
                            dataType:"json",
                            data:that.form,
                            type:"post",
                            success(res){
                                if(res.status){
                                    layer.msg("修改成功", {time: 1000}, function () {
                                        if (window !== window.parent) {
                                            setTimeout(function () {
                                                window.parent.layer.closeAll();
                                            }, 1000);
                                        }
                                    });
                                }else{
                                    layer.msg("修改失败", {time: 1000}, function () {
                                        if (window !== window.parent) {
                                            setTimeout(function () {
                                                window.parent.layer.closeAll();
                                            }, 1000);
                                        }
                                    });
                                }
                            }
                        })
                    },
                    // 添加映射字段
                    addField(){
                        this.FieldList.push({
                            field_name: this.new_field.new_field_name,
                            export_name: this.new_field.new_export_name,
                            filter: this.new_field.new_filter_name
                        });
                    },
                    // 添加筛选字段
                    addFitter(){
                        this.fitterList.push({
                            filter: this.new_filter.new_filter_name,
                            operator: this.new_filter.new_operator,
                            value: this.new_filter.new_value,
                        });
                    },
                    // 更新映射字段
                    doUpdateField(){
                        var that = this;
                        $.ajax({
                            url:"{:U('task_update_field')}",
                            dataType:"json",
                            data: {
                                task_id: that.task_id,
                                list:that.FieldList
                            },
                            type:"post",
                            success(res){
                                layer.msg(res.msg, {time: 1000});
                            }
                        })
                    },

                    //更新筛选条件信息
                    doUpdateCondition(){
                        var that = this;
                        $.ajax({
                            url:"{:U('task_update_condition')}",
                            dataType:"json",
                            data: {
                                task_id: that.task_id,
                                list:that.fitterList
                            },
                            type:"post",
                            success(res){
                                layer.msg(res.msg, {time: 1000});
                            }
                        })
                    },
                    // 移除映射字段
                    removeField(item) {
                        var index = this.FieldList.indexOf(item)
                        if (index !== -1) {
                            this.FieldList.splice(index, 1)
                        }
                    },
                    // 移除筛选条件
                    removeFilter(item){
                        var index = this.fitterList.indexOf(item)
                        if (index !== -1) {
                            this.fitterList.splice(index, 1)
                        }
                    },
                    // 获取模型列表数据
                    getModelList(){
                        var that = this;
                        $.ajax({
                            url:"{:U('task_create_index')}",
                            dataType:"json",
                            type:"get",
                            success(res){
                                that.modelList = res.data;
                            }
                        })
                    }
                },
                mounted: function () {
                    this.getInfo(this.task_id);
                    this.getModelList();
                },
            })
        })
    </script>
</block>

