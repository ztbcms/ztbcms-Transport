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
                    <el-form-item label="进度条" label-width="120px">
                        <div class="progress">
                            <div class="progress-bar progress-bar-info progress-bar-striped active" id="mt-progress-length"
                                 style="width: 0%;">
                                <div class="progress-value" id="mt-progress-value">0%</div>
                            </div>
                            <span style="font-size: 14px;
                                position: absolute;
                                opacity: 0;
                                left: 42%;
                                top: 4px;
                                color: #fff;" id="success_text">完成</span>
                        </div>
                        <small v-show="download">文件路径：<span id="result_file"></span><a :href="result_file">下载</a></small>
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
        .progress {
            width: 50%;
            height: 15px;
            background: #262626;
            padding: 5px;
            overflow: visible;
            border-radius: 20px;
            border-top: 1px solid #000;
            border-bottom: 1px solid #7992a8;
            margin-top: 10px;
        }

        .progress .progress-bar {
            border-radius: 20px;
            position: relative;
            animation: animate-positive 2s;
        }

        .progress .progress-value {
            width: 37px;
            display: block;
            padding: 0px 7px;
            font-size: 14px;
            color: #fff;
            border-radius: 4px;
            background: #191919;
            border: 1px solid #000;
            position: absolute;
            top: -36px;
            right: -31px;
            height: 25px;
            line-height: 26px;
        }

        .progress .progress-value:after {
            content: "";
            border-top: 10px solid #191919;
            border-left: 10px solid transparent;
            border-right: 10px solid transparent;
            position: absolute;
            bottom: -6px;
            left: 26%;
        }

        .progress-bar.active {
            animation: reverse progress-bar-stripes 0.40s linear infinite, animate-positive 2s;
        }

        @-webkit-keyframes animate-positive {
            0% {
                width: 0;
            }
        }

        @keyframes animate-positive {
            0% {
                width: 0;
            }
        }

        #mt-progress-length{
            background: rgb(21, 173, 102);
            height: 100%;
        }

    </style>
    <script>
        $(document).ready(function () {
            new Vue({
                el: '#app',
                data: {
                    task_log_id:"{:I('get.id')}",
                    form: {
                        title:'',
                        description:'',
                        type: 1,
                        model: '',
                        filename:""
                    },
                    tableKey: 0,
                    download:false,
                    result_file:''
                },
                watch: {},
                filters: {
                    typeName: function (type_id) {
                        if(type_id == 1) return '导入任务';
                        if(type_id == 2) return '导出任务';
                    }
                },
                methods: {
                    // 获取详情
                    getInfo(task_log_id){
                        var that = this;
                        var url = "/Transport/Index/task_exec_info";
                        if(task_log_id){
                            url = url + '?id=' + task_log_id
                        }
                        $.ajax({
                            url:url,
                            dataType:"json",
                            type:"get",
                            success(res){
                                console.log(res)
                                that.form = res.data.task;
                            }
                        })
                    },

                    //进行轮询查询进度
                    toSearch(task_log_id){
                        var that = this
                        var time = setInterval(function () {
                            var url = "{:U('Transport/index/getSpeed')}";
                            url = url + '&task_log_id=' + task_log_id;
                            $.ajax({
                                url:url,
                                dataType:"json",
                                type:"get",
                                success(res){
                                    $("#mt-progress-value").html(res.data.speed + "%");

                                    var percentStr = String(res.data.speed);

                                    if (percentStr == "100") {
                                        percentStr = "100.0";
                                    }
                                    if (percentStr == "100.0"){
                                        clearTimeout(time);
                                        //背景成绿色
                                        $(".progress").css("background", "#15AD66");
                                        //进度条归零并隐藏
                                        $("#mt-progress-length").css({"width": "0%", "opacity": "0"});
                                        //显示完成字样
                                        $("#success_text").css({"opacity": "1"});
                                    }
                                    // 导出文件路径
                                    if(res.data.result_file != ""){
                                        clearTimeout(time)
                                        $("#result_file").html(res.data.result_file)
                                        that.download = true
                                        that.result_file = res.data.result_file
                                    }
                                    percentStr = percentStr.substring(0, percentStr.indexOf("."));
                                    $("#mt-progress-length").css("width", percentStr + "%");
                                    $("#mt-progress-length").css("width", percentStr + "%");
                                }
                            })
                        }, 2000);
                    },
                },
                mounted: function () {
                    if(this.task_log_id){
                        this.getInfo(this.task_log_id)
                        this.toSearch(this.task_log_id)
                    }
                },
            })
        })
    </script>
</block>

