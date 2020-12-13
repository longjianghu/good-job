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

 Date: 13/12/2020 17:10:20
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for abort
-- ----------------------------
DROP TABLE IF EXISTS `abort`;
CREATE TABLE `abort` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `is_deleted` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除',
  `task_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '任务ID',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '拦截状态 0:未知 1:拦截成功',
  `created_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='拦截记录';

-- ----------------------------
-- Records of abort
-- ----------------------------
BEGIN;
COMMIT;

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
  `link_url` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '接口地址',
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注信息',
  `created_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `app_key` (`app_key`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='工作任务';

-- ----------------------------
-- Records of application
-- ----------------------------
BEGIN;
INSERT INTO `application` VALUES (1, 0, '测试任务', '8682683675436539', 'G5NDKs51VG02Zzg5Hqe8452nb0E2u9db', 10, 3, 'http://www.api.com/', '这是一个测试任务', 1601260786, 0);
COMMIT;

-- ----------------------------
-- Table structure for logs
-- ----------------------------
DROP TABLE IF EXISTS `logs`;
CREATE TABLE `logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `is_deleted` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除',
  `task_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '任务ID',
  `retry` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '重试次数',
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注信息',
  `created_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统日志';

-- ----------------------------
-- Records of logs
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for task
-- ----------------------------
DROP TABLE IF EXISTS `task`;
CREATE TABLE `task` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `is_deleted` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '任务状态 0:待处理 1:处理中 2:已处理 3:已取消',
  `app_key` char(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'APP KEY',
  `task_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '任务编号',
  `step` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '重试间隔(秒)',
  `runtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '执行时间',
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '任务内容',
  `created_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `task_no` (`app_key`,`task_no`),
  KEY `is_deleted` (`is_deleted`,`status`,`runtime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='任务列表';

-- ----------------------------
-- Records of task
-- ----------------------------
BEGIN;
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
