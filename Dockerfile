FROM longjianghu/hyperf:2.0

MAINTAINER Longjianghu <215241062@qq.com>

RUN set -xe \
    && git clone https://github.com/longjianghu/good-job.git /data \
    && cd /data && composer install \
    && cp .env.example .env
