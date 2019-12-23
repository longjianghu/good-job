/*
 Navicat Premium Data Transfer

 Source Server         : percona
 Source Server Type    : MySQL
 Source Server Version : 50551
 Source Host           : 127.0.0.1:3306
 Source Schema         : good_job

 Target Server Type    : MySQL
 Target Server Version : 50551
 File Encoding         : 65001

 Date: 21/12/2019 16:00:55
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for abort
-- ----------------------------
DROP TABLE IF EXISTS `abort`;
CREATE TABLE `abort` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `is_deleted` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除',
  `task_id` char(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '任务ID',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '拦截状态 0:未知 1:拦截成功',
  `created_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='拦截记录';

-- ----------------------------
-- Table structure for application
-- ----------------------------
DROP TABLE IF EXISTS `application`;
CREATE TABLE `application` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `is_deleted` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除',
  `app_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '应用名称',
  `app_key` char(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'APP KEY',
  `secret_key` char(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'SECRET KEY',
  `step` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '重试间隔(秒)',
  `retry_total` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '重试次数',
  `mobile` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '手机号码',
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Email',
  `link_url` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '接口地址',
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注信息',
  `created_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `app_key` (`app_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='工作任务';

-- ----------------------------
-- Table structure for logs
-- ----------------------------
DROP TABLE IF EXISTS `logs`;
CREATE TABLE `logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `is_deleted` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除',
  `task_id` char(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '任务ID',
  `retry` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '重试次数',
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注信息',
  `created_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统日志';

-- ----------------------------
-- Table structure for notify
-- ----------------------------
DROP TABLE IF EXISTS `notify`;
CREATE TABLE `notify` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `is_deleted` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除',
  `task_id` char(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '任务ID',
  `receiver` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '收件人',
  `retry` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '重试次数',
  `task_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '任务编号',
  `created_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='预警记录';

-- ----------------------------
-- Table structure for task
-- ----------------------------
DROP TABLE IF EXISTS `task`;
CREATE TABLE `task` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `is_deleted` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除',
  `task_id` char(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '任务ID',
  `app_key` char(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'APP KEY',
  `task_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '任务编号',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '任务状态 0:待处理 1:处理中 2:已处理 3:已取消',
  `step` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '重试间隔(秒)',
  `runtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '执行时间',
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '任务内容',
  `created_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `task_id` (`task_id`),
  KEY `task_no` (`app_key`,`task_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='任务列表';

SET FOREIGN_KEY_CHECKS = 1;
