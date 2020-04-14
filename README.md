# 抽奖系统
> 使用Laravel+Vue构建的抽奖系统，包含后台管理，前台WEB和H5用户界面

技术栈
----

#### 后端技术栈：

* Laravel       5.8
* Nginx
* PostgreSql
* Laravel-mix

#### 前端技术栈
* Vue      
* Vuex
* Vue-router
* Element UI
* Vant

> 前台模板采用VUE CLI3构建，采用Element UI作为开发前端开发框架
> H5移动端采用Vant框架

目录结构
---------
* backend       ：管理后台
* frontend      ：用户前台
* common        ：公共模块目录
* doc           ：相关文档和配置目录

部署方法
-------
#### 1.安装Nginx、Redis、Mysql或PostgreSql、Laravel-echo-server、composer、nodejs服务，安装PHP以及所需扩展，拷贝doc目录下配置文件到nginx配置目录
#### 2.拉取代码，安装项目扩展库
```bash
$ git clone git@github.com:tuo0/laravel-admin.git
$ cd laravel-admin/backend/
$ composer install
$ npm install
$ cd ../frontend-api
$ composer install
$ cd ../frontend-web
$ npm install
```

#### 3.启动对接监听
```bash
$ php artisan queue:work redis
```

#### 4.安装部署laravel-echo
```bash
# 安装
$ npm install -g laravel-echo-server
# 配置
$ laravel-echo-server init
# 启动
$ laravel-echo-server start
```

#### 5.启动redis订阅队列
```bash
$ php artisan queue:listen --tries=1
```

#### 6.复制 .env.example 文件为 .env,修改数据库、Redis，创建key
```bash
$ php artisan  key:generate
```

#### 7.编译前后台前端文件

##### 后台backend编译
```bash
$ npm run prod
```

##### 前台frontend-web编译
```bash
$ vue run build
```

Ajax 接口返回json数据说明
-----------------------
```php
[
    'code'  => 1,
    'msg'   => '',
    'data'  => [],
]
```
* code:错误码  1：成功  0:失败 >1:失败错误码
* msg:错误消息
* data:返回数据 

## 开发规范
> * 如果控制器名称中含有大写，则权限表(`admin_role_permissions`)权限必须使用[_]分割，以及前台请求接口以及前台组件权限验证规则(`v-permission`标签)必须与权限表相同  
> 例如：控制器名称`UserGroupController`方法`putEdit`，则请求接口以及权限验证标签为：`user_group/edit`  
> * 网站所有小图标采用SVG格式可以在 [iconfont](https://www.iconfont.cn/) 中查找  
