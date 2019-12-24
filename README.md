# 阿里云移动推送服务端扩展
PhalApi 2.x 扩展类库：基于阿里云移动推送的服务端扩展。

## 安装和配置
修改项目下的composer.json文件，并添加：  
```
    "vivlong/phalapi-aliyun-push":"dev-master"
```
然后执行```composer update```。  

安装成功后，添加以下配置到/path/to/phalapi/config/app.php文件：  
```php
    /**
     * 阿里云推送相关配置
     */
    'AliyunPush' =>  array(
        'accessKeyId'       => '<yourAccessKeyId>',
        'accessKeySecret'   => '<yourAccessKeySecret>',
        'regionId'          => 'cn-hangzhou',
    ),
```
并根据自己的情况修改填充。  

## 注册
在/path/to/phalapi/config/di.php文件中，注册：  
```php
$di->aliyunPush = function() {
    return new \PhalApi\AliyunPush\Lite();
};
```

## 使用
使用方式：
```php
  \PhalApi\DI()->aliyunPush->sendPush();
```  

