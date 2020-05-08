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
                <if condition="$type EQ 1">
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
                </if>
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
    var percent = "0.0";
    setInterval(function () {
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
                    //背景成绿色
                    $(".progress").css("background", "#15AD66");
                    //归零 隐藏
                    $("#mt-progress-length").css({"width": "0%", "opacity": "0"});
                    
                    $("#success_text").css({"opacity": "1"});
                }
                percentStr = percentStr.substring(0, percentStr.indexOf("."));
                $("#mt-progress-length").css("width", percentStr + "%");
            }
        })
    }, 2000);


    $(":file").change(function () {
        var file = this.files[0];
        var name = file.name;
        var size = file.size;
        var type = file.type;

        url = window.URL.createObjectURL(file);

        totalSize += size;

        $(".show_info").html("文件名：" + name + "<br>文件类型：" + type + "<br>文件大小：" + size + "<br>url: " + url);
        console.log("ok");

        //恢复进度条的状态
        //背景成绿色
        $(".progress").css("background", "#262626");
        //归零 隐藏
        $("#mt-progress-length").css({"width": "0%", "opacity": "1"});
        $("#mt-progress-value").html(0);

    })


    function upload() {
        //背景恢复
        $(".progress").css("background", "#262626");
        //归零 隐藏
        $("#mt-progress-length").css({"width": "0%", "opacity": "1"});
        $("#mt-progress-value").html(0);


        //创建formData对象  初始化为form表单中的数据
        //需要添加其他数据  就可以使用 formData.append("property", "value");
        var formData = new FormData();
        var fileInput = document.getElementById("myFile");
        var file = fileInput.files[0];
        formData.append("file", file);

        // ajax异步上传
        $.ajax({
            url: "{:U('Transport/Upload/upload')}",
            type: "POST",
            data: formData,
            contentType: false, //必须false才会自动加上正确的Content-Type
            processData: false,  //必须false才会避开jQuery对 formdata 的默认处理
            enctype: 'multipart/form-data',
            xhr: function () {
                //获取ajax中的ajaxSettings的xhr对象  为他的upload属性绑定progress事件的处理函数
                var myXhr = $.ajaxSettings.xhr();
                if (myXhr.upload) {
                    //检查其属性upload是否存在
                    myXhr.upload.addEventListener("progress", resultProgress, false);
                }
                return myXhr;
            },
            success: function (result) {
                if(result.status){
                    $('input[name=filename]').val(result.data.url);
                }
                console.log(result);
            },
            error: function (data) {
                alert(data.msg)
                console.log(data);
            }
        })
    }

    //上传进度回调函数
    function resultProgress(e) {
        if (e.lengthComputable) {
            var percent = e.loaded / e.total * 100;
            $(".show_result").html(percent + "%");
            var percentStr = String(percent);
            if (percentStr == "100") {
                percentStr = "100.0";
            }
            percentStr = percentStr.substring(0, percentStr.indexOf("."));
            console.log(percentStr)
            $("#mt-progress-value").html(percentStr);
            $("#mt-progress-length").css("width", percentStr + "%");

            if (percentStr == "100") {
                setTimeout(function () {
                    //背景成绿色
                    $(".progress").css("background", "#15AD66");
                    //归零 隐藏
                    $("#mt-progress-length").css({"width": "0%", "opacity": "0"});
                }, 500);
            }
        }
    }
</script>

</body>
</html>
