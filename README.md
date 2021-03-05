# 项目说明

系统直接发送短信、站内消息和邮件（包括延迟消息）。

# 系统环境

需要PHP7.4+、MySQL和 Redis。

# 接口鉴权

## 接口地址

`url`：http://www.api.com

## 头部信息

> signature不参与签名

| 参数名     | 类型     | 是否必填 | 默认值 | 说明      |
|---------|--------|------|-----|---------|
| app_key | string | 是    | -   | APP KEY |
| nonce_str | string | 是    | -   | 随机字符串 |
| timestamp | string | 是    | -   | 当前时间：2021-03-04 11:42:36 |
| signature | string | 是    | -   | 签名信息 |
| version | string | 是    | 1.0   | 版本号（固定值） |

## 签名算法

提交内容和头部信息按A-Z进行排序，组成签名字符串+分配的密码,然后使用MD5加密获取签名字符串。