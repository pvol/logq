# pvol/logq

## 简介

log多服务器管理解决方案

## 方案说明

*  logq作为插件在各服务器上分别将日志写入redis
*  cron_logq会在指定的服务器把redis中的日志写入文件
