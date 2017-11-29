CREATE TABLE `cms_transport_condition` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL COMMENT '关联任务ID',
  `filter` varchar(128) NOT NULL DEFAULT '' COMMENT '字段',
  `operator` varchar(16) NOT NULL DEFAULT '' COMMENT '操作符',
  `value` varchar(128) DEFAULT '' COMMENT '值',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `cms_transport_field` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `field_name` varchar(128) NOT NULL DEFAULT '' COMMENT '字段名',
  `export_name` varchar(128) NOT NULL DEFAULT '' COMMENT '对外(表格)字段名',
  `filter` varchar(128) NOT NULL DEFAULT '' COMMENT '处理方法',
  `task_id` int(11) NOT NULL COMMENT '关联任务ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `cms_transport_task` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL DEFAULT '' COMMENT '标题',
  `description` varchar(256) DEFAULT '' COMMENT '描述',
  `model` varchar(128) NOT NULL DEFAULT '' COMMENT '对应模型表',
  `inputtime` int(11) NOT NULL COMMENT '创建时间',
  `type` int(11) NOT NULL DEFAULT '1' COMMENT '类型：1导入 2导出',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='任务模板';

CREATE TABLE `cms_transport_task_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL COMMENT '对应任务ID',
  `inputtime` int(11) NOT NULL COMMENT '创建时间',
  `filename` varchar(128) NOT NULL DEFAULT '' COMMENT '关联文件路径',
  `result` int(1) NOT NULL DEFAULT '1' COMMENT '任务结果:1成功 2失败',
  `remark` varchar(256) NOT NULL DEFAULT '' COMMENT '备注',
  `title` varchar(128) NOT NULL DEFAULT '' COMMENT '任务标题',
  `process_status` int(11) NOT NULL COMMENT '处理状态：0待处理1处理中2处理完成',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  `progress` int(11) NOT NULL COMMENT '当前进行数',
  `total_amount` int(11) NOT NULL COMMENT '总数',
  `success_amount` int(11) NOT NULL COMMENT '成功数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

