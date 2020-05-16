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
</style>
<body class="J_scroll_fixed">
<div class="wrap">

    <Admintemplate file="Common/Nav"/>
    <div class="h_a">执行任务</div>
    <form class=""  action="{:U('Transport/Index/task_log_create')}" method="post" id="form1">
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

                        <input type="hidden" name="type" value="{$type}" id="type_id">
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

                <tr>
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
                        <th>导入文件</th>
                        <td>
                            <div class="uploader-container" >
                                <p class="upload-draft"  >点击上传</p>
                                <input type="file" name="upload_file"  accept="application/vnd.ms-excel,application/x-xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                            </div>
                            <input type="text" class="input length_5 mr5" name="filename" value="" readonly accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"></td>
                        <td><div class="fun_tips"></div></td>
                    </tr>
                </if>

                <if condition="$type EQ 2">
                    <tr>
                        <th>导出文件名</th>
                        <td><input type="text" class="input length_5 mr5" name="filename" value=""></td>
                        <td><div class="fun_tips">默认:标题+创建时间</div></td>
                    </tr>
                </if>

            </table>
        </div>
        <div class="">
            <div class="btn_wrap_pd">
                <button class="btn btn_submit " type="submit">创建执行日志</button>
                <button class="btn btn_submit " type="button" id="doPlay">创建并查看进度</button>
                <button class="btn btn_submit " type="button" id="download">下载示例</button>
            </div>
        </div>
        <small>Tip:导入导出前，请编辑任务，设置好字段映射</small>
    </form>
    <!--结束-->
</div>

<script>
    (function($){
        //=== 文件上传
        //TODO ： 添加上传进度条
        var uploader = simple.uploader({
            url: "{:U('Transport/Upload/upload')}"
        });
        ////上传前
        uploader.on('beforeupload', function (e, file) {
            // do something before upload
            console.log('beforeupload')
            console.log(file)
        });
        ////上传中
        uploader.on('uploadprogress', function (e, file, loaded, total) {
            // do something before upload
            console.log('uploadprogress')
        });
        //上传成功响应
        uploader.on('uploadsuccess', function (e, file, result) {
            // do something before upload

//            console.log('uploadsuccess');
//            console.log(e);
//            console.log(file);
//            console.log(result)
//             result = JSON.parse(result);
            // console.log(result)
            if(result.status){
                $('input[name=filename]').val(result.data.url);
            }else{
                alert(result.msg)
            }
        });
        //上传网络错误时
        uploader.on('uploaderror', function (e, file, xhr, status) {
            // do something before upload
            console.log('uploaderror')
            console.log(xhl);
            console.log(status)
        });

        $('input[name=upload_file]').on('change', function (e) {
            uploader.upload(this.files);
        });

        //=== 文件上传 END

        // 创建定时任务并查看详情
        $("#doPlay").on('click',function () {
            var type_id = $("#type_id").val()
            if(type_id == 1 && $("input[name=filename]").val() == ""){
                // 导入
                alert('请导入文件')
                return false;
            }
            if(type_id == 2 && $("input[name=filename]").val() == ""){
                // 导出
                alert('请输入导出文件名')
                return false;
            }
            document.getElementById('form1').action = "{:U('Transport/Index/task_exec_info')}";
            document.getElementById("form1").submit();
        })

        // 下载示例文件
        $("#download").on('click',function () {
            window.location.href = '/Transport/index/down';
        })
    })(jQuery);
</script>
</body>
</html>
