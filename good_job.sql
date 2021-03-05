/*
 Navicat Premium Data Transfer

 Source Server         : 127.0.0.1
 Source Server Type    : MySQL
 Source Server Version : 50551
 Source Host           : localhost:3306
 Source Schema         : good_job

 Target Server Type    : MySQL
 Target Server Version : 50551
 File Encoding         : 65001

 Date: 05/03/2021 08:30:28
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for application
-- ----------------------------
DROP TABLE IF EXISTS `application`;
CREATE TABLE `application` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `is_deleted` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否审核 0:否 1:是',
  `app_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '应用名称',
  `app_key` char(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'APP KEY',
  `secret_key` char(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'SECRET KEY',
  `step` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '重试间隔(秒)',
  `retry_total` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '重试次数',
  `link_url` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '接口地址',
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注信息',
  `created_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `unq_app_key` (`app_key`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='工作任务';

-- ----------------------------
-- Table structure for task
-- ----------------------------
DROP TABLE IF EXISTS `task`;
CREATE TABLE `task` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `is_deleted` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '任务状态 0:待处理 1:处理中 2:已处理 3:已取消',
  `app_key` char(32) NOT NULL DEFAULT '' COMMENT 'APP KEY',
  `task_no` varchar(50) NOT NULL DEFAULT '' COMMENT '任务编号',
  `step` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '重试间隔(秒)',
  `runtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '执行时间',
  `content` longtext NOT NULL COMMENT '任务内容',
  `created_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_task_no` (`app_key`,`task_no`),
  KEY `idx_is_deleted` (`is_deleted`,`status`,`runtime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='任务列表';

-- ----------------------------
-- Table structure for task_abort
-- ----------------------------
DROP TABLE IF EXISTS `task_abort`;
CREATE TABLE `task_abort` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `is_deleted` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除',
  `task_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '任务ID',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '拦截状态 0:未知 1:拦截成功',
  `created_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_task_id` (`task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='拦截记录';

-- ----------------------------
-- Table structure for task_log
-- ----------------------------
DROP TABLE IF EXISTS `task_log`;
CREATE TABLE `task_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `is_deleted` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除',
  `task_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '任务ID',
  `retry` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '重试次数',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注信息',
  `created_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_task_id` (`task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统日志';

SET FOREIGN_KEY_CHECKS = 1;
