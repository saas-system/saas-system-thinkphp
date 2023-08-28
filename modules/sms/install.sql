CREATE TABLE IF NOT EXISTS `__PREFIX__sms_template`
(
    `id`         int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `title`      varchar(100) NOT NULL DEFAULT '' COMMENT '模板标题',
    `code`       varchar(100) NOT NULL DEFAULT '' COMMENT '唯一标识',
    `template`   varchar(100) NOT NULL DEFAULT '' COMMENT '服务商模板ID',
    `content`    text COMMENT '短信内容',
    `variables`  varchar(200) NOT NULL DEFAULT '' COMMENT '模板变量',
    `status`     tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态:0=禁用,1=启用',
    `updatetime` int(10) unsigned DEFAULT NULL COMMENT '更新时间',
    `createtime` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='短信模板表';

BEGIN;
INSERT INTO `__PREFIX__sms_template` VALUES ('1', '用户注册', 'user_register', '', '', '', '1', '1664349210', '1664260457');
INSERT INTO `__PREFIX__sms_template` VALUES ('2', '用户身份验证', 'user_mobile_verify', '', '', '', '1', '1664348583', '1664296092');
INSERT INTO `__PREFIX__sms_template` VALUES ('3', '用户验证新手机号', 'user_change_mobile', '', '', '', '1', '1664296339', '1664296212');
INSERT INTO `__PREFIX__sms_template` VALUES ('4', '用户找回密码', 'user_retrieve_pwd', '', '', '', '1', '1664296333', '1664296300');
COMMIT;

CREATE TABLE `__PREFIX__sms_variable` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `title` varchar(100) NOT NULL DEFAULT '' COMMENT '标题',
    `name` varchar(50) NOT NULL DEFAULT '' COMMENT '变量名称',
    `value_source` enum('literal','func','sql') NOT NULL DEFAULT 'literal' COMMENT '变量值来源:literal=字面量,func=方法返回值,sql=sql查询结果',
    `value` varchar(200) NOT NULL DEFAULT '' COMMENT '变量值',
    `sql` varchar(500) NOT NULL DEFAULT '' COMMENT 'SQL语句',
    `namespace` varchar(200) NOT NULL DEFAULT '' COMMENT '命名空间',
    `class` varchar(100) NOT NULL DEFAULT '' COMMENT '类名',
    `func` varchar(100) NOT NULL DEFAULT '' COMMENT '方法名',
    `param` varchar(100) NOT NULL DEFAULT '' COMMENT '传递的参数',
    `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态:0=禁用,1=启用',
    `updatetime` int(10) unsigned DEFAULT NULL COMMENT '更新时间',
    `createtime` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='模板变量表';

BEGIN;
INSERT INTO `__PREFIX__sms_variable` VALUES ('1', '随机数字-6位', 'code', 'func', '', '', 'modules\\sms\\library', 'Helper', 'numeric', '6', '1', '1664349253', '1664255892');
INSERT INTO `__PREFIX__sms_variable` VALUES ('2', '随机字符-4位', 'alnum', 'func', '', '', 'modules\\sms\\library', 'Helper', 'alnum', '4', '1', '1664348499', '1664256828');
INSERT INTO `__PREFIX__sms_variable` VALUES ('3', '系统会员数量', 'user_count', 'sql', '', 'SELECT count(id) FROM __PREFIX__user', '', '', '', '', '1', '1664257064', '1664257064');
COMMIT;