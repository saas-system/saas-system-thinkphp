# SAAS系统介绍
## 一：安装介绍
#### 第一步： 复制env-example到.env文件中，并修改正确的信息
```shell
git clone https://github.com/sxqibo/saas-system-thinkphp.git  
git clone https://github.com/sxqibo/saas-system-vue.git  
mv saas-system-vue saas-system-thinkphp/web
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


## 四：批量测试接口
采用 https://www.jetbrains.com/help/phpstorm/http-client-cli.html 进行测试

我本地测试， 把 ijhttp 放到系统环境变量中
```shell
export PATH="/Users/mac/Documents/wwwroot/code-tools/ide/ijhttp:$PATH"
source ~/.zshrc
```

操作
```shell
cd http 目录
ijhttp --env-file http-client.env.json --env dev api/index.http
```
