## 使用说明

*   先安装依赖，Queue 模块
*   上传导入文件格式仅支持xls
*   后台管理的导入导出任务创建后，不会自动执行，需开启队列，队列名为`Transport`
```shell
php index.php /queue/worker/run/queue/Transport
```
* 若需要自定义导入导出，请在`Job/`目录添加任务，参考该目录的实现


