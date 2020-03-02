<p align="center">
    <a href="https://github.com/longjianghu/good-job.git" target="_blank">
        <img src="https://raw.githubusercontent.com/longjianghu/good-job/master/logo.png" alt="GoodJob">
    </a>
</p>

> ** 一款简易的任务管理系统 **

系统通过调用接口方式执行任务(解耦)和任务重试，调用方需要通过接口方式实现自已的业务逻辑。系统预警目前只能通过邮件和手机短信(需要自行实现)的方式进行通知。

## 运行环境

系统其于 <a href="http://www.swoft.org" target="_blank" title="Swoft官网">Swoft2.0.6</a> 开发,数据库采用 MySQL, 消息队列使用 Redis。建议使用<a href="https://www.docker.com/" target="_blank" title="Docker官网">Docker</a> 进行项目部署。

## 如何使用

> 如果你使用Docker进行项目部署，/data/var/www/good-job需要替换成你的项目部署目录。

> 当APP_DEBUG=1时,系统不会校验提交的签名(生产环境建议关闭)。

### 拉取代码

```bash
git pull https://github.com/longjianghu/good-job.git
```

### 更改配置

```bash
cp .env.example .env
vi .env # 请根据实际情况进行调整
```

### Docker安装(可选)

为了便于项目的部署,我们制作好了一个基础运行镜像，只需要简单的几步即可完成项目的部署。

```bash
docker pull longjianghu/swoft:1.2.2

docker run --rm -it -v /data/var/www/good-job:/data longjianghu/swoft:1.2.2 sh
```

### Composer安装

```bash
composer install
```

### 初始化数据库

```bash
php ./bin/swoft migrate:up -y
```

除了使用上面的命令之外你也可以直接导入目录下的SQL文件创建相关数据表。


### 运行容器(可选)

```bash
docker run --name good-job -p 8081:18306 -v /data/var/www/good-job:/data -d longjianghu/swoft:1.2.2 php /data/bin/swoft http:start
```

### 应用接入

系统部署完成后输入系统访问地址即可查看所有的开放接口

## License

GoodJob is an open-source software licensed under the [LICENSE](LICENSE)
