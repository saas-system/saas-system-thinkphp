# SAAS系统介绍
## 安装介绍
- 复制env-example到.env文件中，并修改正确的信息

- 后端安装和运行
```shell
composer install
php think migrate:run
php think seed:run
php think run 
```

- 前端安装
```shell
cd web
pnpm i
pnpm dev
```

- 运行
- 

## 平台端说明
```
网址： http://localhost:1818/
用户名：admin
密码： 123123
```
## 租户端说明
平台端创建租户并且创建管理员和密码进行登录
