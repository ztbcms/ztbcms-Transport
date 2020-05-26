<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <h3>任务列表
                <el-button type="primary" size="mini" style="margin-left:10px;" @click="openCreateItem">
                    创建任务
                </el-button>
            </h3>
            <el-table
                :key="tableKey"
                :data="list"
                border
                fit
                highlight-current-row
                style="width: 100%;"
            >
                <el-table-column label="标题" align="center">
                    <template slot-scope="scope">
                        <span>{{ scope.row.title }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="描述" align="center">
                    <template slot-scope="scope">
                        <span>{{ scope.row.description }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="类型" align="center">
                    <template slot-scope="scope">
                        <span>{{ scope.row.type | typeName }}</span>
                    </template>
                </el-table-column>

                <el-table-column label="创建时间" align="center">
                    <template slot-scope="scope">
                        <span>{{ scope.row.inputtime | parseTime('{y}-{m}-{d} {h}:{i}:{s}')  }}</span>
                    </template>
                </el-table-column>

                <el-table-column label="操作" align="center" class-name="small-padding fixed-width">
                    <template slot-scope="scope">
                        <el-button type="primary" size="mini"  @click="toEdit(scope.row.id)">
                            编辑
                        </el-button>
                        <el-button size="mini" type="danger"
                                   @click="handleDelete(scope.row.id)">
                            删除
                        </el-button>

                        <el-button size="mini" type="danger"
                                   @click="toCreateItem(scope.row.id)">
                            创建执行任务
                        </el-button>
                    </template>
                </el-table-column>

            </el-table>

            <div class="pagination-container">
                <el-pagination
                    background
                    layout="prev, pager, next, jumper"
                    :total="listQuery.total"
                    v-show="listQuery.total>0"
                    :current-page.sync="listQuery.page"
                    :page-size.sync="listQuery.limit"
                    @current-change="getList"
                >
                </el-pagination>
            </div>

        </el-card>
    </div>

    <style>
        .filter-container {
            padding-bottom: 10px;
        }

        .pagination-container {
            padding: 32px 16px;
        }
    </style>

    <script>
        $(document).ready(function () {
            new Vue({
                el: '#app',
                data: {
                    tableKey: 0,
                    list: [],
                    listQuery: {
                        page: 1,
                        limit: 15,
                        total:0
                    },
                },
                watch: {},
                filters: {
                    parseTime: function (time, format) {
                        return Ztbcms.formatTime(time, format)
                    },
                    typeName: function (type_id) {
                        if(type_id == 1) return '导入任务';
                        if(type_id == 2) return '导出任务';
                    }
                },
                methods: {
                    //创建任务
                    openCreateItem(){
                        Ztbcms.openNewIframeByUrl('创建任务', '/index.php?g=Transport&m=Index&a=task_create_index')
                    },
                    //打开编辑框
                    toEdit: function (id = 0) {
                        var url = "/Transport/Index/task_edit_index";
                        if (id) {
                            url += '?id=' + id
                        }
                        var that = this;
                        layer.open({
                            type: 2,
                            title: '编辑',
                            content: url,
                            area: ['80%', '80%'],
                            end: function () {
                                that.getList()
                            }
                        })
                    },
                    //创建执行任务
                    toCreateItem: function (id = 0) {
                        var url = "/Transport/Index/task_exec_index";
                        if (id) {
                            url += '?id=' + id
                        }
                        Ztbcms.openNewIframeByUrl('创建执行任务', url )
                    },
                    // 删除任务
                    toDelete(id){
                        var that = this;
                        var url = "/Transport/Index/task_delete";
                        if (id) {
                            url += '?id=' + id
                            $.ajax({
                                url: url,
                                dataType:"json",
                                type:"get",
                                success(res){
                                    that.$message.success(res.info);
                                    that.getList()
                                }
                            })
                        }
                    },
                    handleDelete: function (id) {
                        var that = this;
                        layer.confirm('是否确定删除该内容吗？', {
                            btn: ['确认', '取消'] //按钮
                        }, function () {
                            that.toDelete(id);
                            layer.closeAll();
                        }, function () {
                            layer.closeAll();
                        });
                    },
                    //获取任务列表数据
                    getList(){
                        var that = this;
                        $.ajax({
                            url:"{:U('index')}",
                            dataType:"json",
                            data: that.listQuery,
                            type:"get",
                            success(res){
                                if(res.status){
                                    that.list = res.data.items;
                                    that.listQuery.total = res.data.total_items;
                                    that.listQuery.limit = res.data.limit;
                                    that.listQuery.page = res.data.page;
                                }
                            }
                        })

                    }
                },
                mounted: function () {
                    this.getList();
                },

            })
        })
    </script>
</block>