<?php

/**
 * author: Jayin <tonjayin@gmail.com>
 */

namespace Transport\Core;

abstract  class Transport {

    protected $success_data = [];
    protected $fail_data = [];

    /**
     * 传输处理开始
     * @return mixed
     */
    protected abstract function onStartTransport();
    /**
     * 开始加载数据
     */
    protected abstract function onStartLoadData();

    /**
     * 加载完数据项的回调
     */
    protected abstract function onFinishLoadData();
    /**
     * 开始处理数据操作(导出，导入)
     * @return mixed
     */
    protected abstract function onStartHandleData();
    /**
     * 开始处理单行数据
     */
    protected abstract function onStartHandleRowData();
    /**
     * 处理完当前行数据后的回调
     */
    protected abstract function onFinishHandlRowData();
    /**
     * 数据处理操作(导出，导入)完成
     * @return mixed
     */
    protected abstract function onFinishHandleData();
    /**
     * 数据传输结束
     */
    protected abstract function onFinishTransport();

}