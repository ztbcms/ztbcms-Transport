<Admintemplate file="Common/Head"/>

<!--  simditor 上传组件 -->
<script type="text/javascript" src="{$config_siteurl}statics/admin/simditor/scripts/module.min.js"></script>
<script type="text/javascript" src="{$config_siteurl}statics/admin/simditor/scripts/uploader.min.js"></script>

<style>
    .uploader-container{
        position: relative;
        width: 300px;
        height: 60px;
        background: grey;
    }
    .upload-draft{
        text-align: center;
        color: white;
        position: absolute;
        top: 40%;
        left: 39%;
    }
    .uploader-container input[type=file]{
        position: absolute;
        top: 0;
        right: 0;
        left: 0;
        bottom: 0;
        opacity: 0;
    }
    .progress {
        height: 25px;
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
        width: 62px;
        display: block;
        padding: 3px 7px;
        font-size: 13px;
        color: #fff;
        border-radius: 4px;
        background: #191919;
        border: 1px solid #000;
        position: absolute;
        top: -40px;
        right: -38px
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
</style>
<body class="J_scroll_fixed">
<div class="wrap">

    <Admintemplate file="Common/Nav"/>
    <div class="h_a">执行任务</div>
    <form class=""  action="{:U('Transport/Index/task_log_create')}" method="post">
        <div class="table_full">
            <table width="100%">
                <col class="th" />
                <col width="300" />
                <col />
                <tr>
                    <th>任务标题</th>
                    <td>{$title}
                        <input type="hidden" name="title" value="{$title}">
                    </td>
                    <td><div class="fun_tips"></div></td>
                </tr>

                <tr>
                    <th>任务类型</th>
                    <td>
                        <if condition="$type EQ 1">导入任务</if>
                        <if condition="$type EQ 2">导出任务</if>

                        <input type="hidden" name="type" value="{$type}">
                    </td>
                    <td><div class="fun_tips"></div></td>
                </tr>

                <tr>
                    <th>模型</th>
                    <td>
                        <?php $_model = M('Model')->where(['tablename' => $model])->find();?>
                        {$_model['name']}
                    </td>
                    <td><div class="fun_tips"></div></td>
                </tr>

                <tr style="display: none;">
                    <th>备注</th>
                    <td>
                        <input  class="input length_5 mr5" type="text" name="remark" value="">
                    </td>
                    <td><div class="fun_tips"></div></td>
                </tr>

                <tr style="display: none;">
                    <th>关联任务ID</th>
                    <td>
                        <input type="text" name="task_id" value="{$id}">
                    </td>
                    <td><div class="fun_tips"></div></td>
                </tr>
                <tr>
                    <th>进度条</th>
                    <td>
                        <div class="progress">
                            <div class="progress-bar progress-bar-info progress-bar-striped active" id="mt-progress-length"
                                 style="width: 0%;">
                                <div class="progress-value" id="mt-progress-value">0%</div>
                            </div>
                            <span style="font-size: 14px;
                                position: absolute;
                                opacity: 0;
                                left: 42%;" id="success_text">完成</span>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        <div class="" style="display:none;">
            <div class="btn_wrap_pd">
                <button class="btn btn_submit " type="submit">创建执行日志</button>
                <button class="btn btn_submit " type="button" onclick="">立即执行</button>
            </div>
        </div>
    </form>
    <!--结束-->
</div>
<script>
    //进行轮询查询进度
    var time = setInterval(function () {
        var url = "{:U('Transport/index/getSpeed')}";
        url = url + '&task_log_id=' + <?= $task_log_id ?>;
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
                    console.log('完成')
                    clearTimeout(time)
                    //背景成绿色
                    $(".progress").css("background", "#15AD66");
                    //归零 隐藏
                    $("#mt-progress-length").css({"width": "0%", "opacity": "0"});

                    $("#success_text").css({"opacity": "1"});
                }
                // 导出文件路径
                if(res.data.result_file != ""){
                    clearTimeout(time)
                    alert('文件路径:'+res.data.result_file)
                }
                percentStr = percentStr.substring(0, percentStr.indexOf("."));
                $("#mt-progress-length").css("width", percentStr + "%");
            }
        })
    }, 2000);
</script>

</body>
</html>