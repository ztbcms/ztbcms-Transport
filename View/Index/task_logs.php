<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <h3>执行记录</h3>
            <el-table
                :key="tableKey"
                :data="list"
                border
                fit
                highlight-current-row
                style="width: 100%;"
            >
                <el-table-column label="ID" align="center">
                    <template slot-scope="scope">
                        <span>{{ scope.row.id }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="计划标题" align="center">
                    <template slot-scope="scope">
                        <span>{{ scope.row.title }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="备注" align="center">
                    <template slot-scope="scope">
                        <span>{{ scope.row.remark }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="文件名" align="center">
                    <template slot-scope="scope">
                        <span>{{ scope.row.filename }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="创建时间" align="center">
                    <template slot-scope="scope">
                        <span>{{ scope.row.inputtime | parseTime('{y}-{m}-{d} {h}:{i}:{s}')  }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="操作" align="center" width="230" class-name="small-padding fixed-width">
                    <template slot-scope="scope">
                        <el-button type="primary" size="mini" @click="toExec(scope.row.id)">
                            立即执行
                        </el-button>
                        <el-button size="mini" type="primary"
                                   @click="toView(scope.row.id)">
                            预览
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
                        limit: 20,
                        total: 0
                    },
                },
                watch: {},
                filters: {
                    parseTime: function (time, format) {
                        return Ztbcms.formatTime(time, format)
                    },
                },
                methods: {
                    //立即执行
                    toExec(id){
                        var url = '/Transport/Index/task_exec';
                        if(id){
                            url = url + '?task_log_id=' + id
                        }
                        window.open(url)
                    },
                    //预览
                    toView(id){
                        var url = '/Transport/Index/task_exec';
                        if(id){
                            url = url + '?task_log_id=' + id + '&preview=1'
                        }
                        window.open(url)
                    },
                    //获取执行任务记录
                    getList: function () {
                        var that = this;
                        $.ajax({
                            url:"{:U('task_logs_get')}",
                            dataType:"json",
                            data: that.listQuery,
                            type:"get",
                            success(res){
                                that.list = res.data.items;
                                that.listQuery.total = res.data.total_items;
                                that.listQuery.limit = res.data.limit;
                                that.listQuery.page = res.data.page;
                            }
                        })
                    },
                },
                mounted: function () {
                    this.getList();
                },

            })
        })
    </script>
</block>

