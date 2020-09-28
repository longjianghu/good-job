<p align="center">
    <a href="https://github.com/longjianghu/good-job.git" target="_blank">
        <img src="https://raw.githubusercontent.com/longjianghu/good-job/master/logo.png" alt="GoodJob">
    </a>
</p>

# GoodJob

> ** 一款简易的任务管理系统 **

系统通过调用接口方式执行任务(自带重试机制)，被调用方需要自已实现对应的业务逻辑。

## 运行环境

系统其于 <a href="http://www.swoft.org" target="_blank" title="Swoft官网">Swoft2.0</a> 开发,数据库采用 MySQL, 消息队列使用 Redis。 

## 安装 Docker

```
curl -fsSL https://get.docker.com | bash -s docker --mirror Aliyun

usermod -aG docker  root

systemctl start docker
```

## 初始化数据库

> 直接导入目录下的SQL文件创建相关数据表。

## 使用镜像

> 请根据你的实际路径进行调整

```
docker run --name good.job -p 8083:18306 -v /data/var/etc/good-job.cnf:/data/.env --restart=always -d longjianghu/good-job:1.0.0
```

> 请使用 .env.example 生成本地的配置文件。

## 自行部署

首先克隆项目到本地

```
git pull https://github.com/longjianghu/good-job.git
```

step1:

> /data/var/www/good-job 请根据你的实际路径进行调整。

```
docker run --rm -it -v /data/var/www/good-job:/data longjianghu/swoft:4.5.2 sh
```

setp2:

```
composer install
```

> TIPS:可以在容器里使用 php ./bin/swoft migrate:up -y 初始化数据库

step3:

```
cp .env.example .env 

vi .env // 请根据实际情况修改配置参数
```

step4:

退出窗口并执行

```
docker run --name good.job -p 8083:18306 -v /data/var/www/good-job:/data --restart=always -d longjianghu/swoft:4.5.2
```

## 应用接入

系统部署完成后输入系统访问地址即可查看所有的开放接口

## License

GoodJob is an open-source software licensed under the [LICENSE](LICENSE)
