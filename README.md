# SAAS系统介绍
## 一：安装介绍
#### 第一步： 复制env-example到.env文件中，并修改正确的信息
```shell
git clone https://github.com/sxqibo/saas-system-thinkphp.git  
git submodule update --init --force --remote
```

#### 第二步： 后端安装和运行
```shell
composer install
php think migrate:run
php think seed:run
php think run 
```

#### 第三步：前端安装
```shell
cd web
pnpm i
pnpm dev
```

#### 第四步：打开浏览器运行


## 二：平台端说明
```
网址： http://localhost:1818/#/platform
用户名：admin
密码： 123123
```
## 三：租户端说明
平台端创建租户并且创建管理员和密码进行登录
