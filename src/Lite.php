<?php

namespace PhalApi\AliyunPush;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

class Lite
{
    protected $config;

    public function __construct($config = null)
    {
        $this->config = $config;
        if (is_null($this->config)) {
            $this->config = \PhalApi\DI()->config->get('app.AliyunPush');
        }
        AlibabaCloud::accessKeyClient($this->config['accessKeyId'], $this->config['accessKeySecret'])
            ->regionId($this->config['regionId'])    // 设置客户端区域，使用该客户端且没有单独设置的请求都使用此设置
            ->timeout(6)                            // 超时10秒，使用该客户端且没有单独设置的请求都使用此设置
            ->connectTimeout(10)                    // 连接超时10秒，当单位小于1，则自动转换为毫秒，使用该客户端且没有单独设置的请求都使用此设置
            //->debug(true) 						// 开启调试，CLI下会输出详细信息，使用该客户端且没有单独设置的请求都使用此设置
            ->asDefaultClient();
    }

    public function pushMessage($device_id, $device_type, $content, $title, $extras)
    {
        $params = [
            'AppKey' => $this->config['appKey'],
            'PushType' => 'MESSAGE',
            'DeviceType' => $device_type,
            'Target' => 'DEVICE',
            'TargetValue' => $device_id,
            'Body' => $content,
            'Title' => $title,
            'StoreOffline' => 'false',
        ];
        if ($device_type == 'ANDROID') {
            $params = array_merge($params, [
                'AndroidNotifyType' => 'BOTH',            //通知的提醒方式 "VIBRATE" : 震动 "SOUND" : 声音 "BOTH" : 声音和震动 NONE : 静音
                //'AndroidNotificationBarType' => "1",	//通知栏自定义样式0-100
                //'AndroidOpenType' => 'APPLICATION',		//点击通知后动作 "APPLICATION" : 打开应用 "ACTIVITY" : 打开AndroidActivity "URL" : 打开URL "NONE" : 无跳转
                //'AndroidOpenUrl' => '', 				//Android收到推送后打开对应的url,仅当AndroidOpenType="URL"有效
                //'AndroidActivity' => '',
                //'AndroidPopupActivity' => "com.b2b2c.app.MainActivity", 	//设定通知打开的activity，仅当AndroidOpenType="Activity"有效
                //'AndroidPopupTitle' => $title,
                //'AndroidPopupBody' => $content,
                //'AndroidNotificationChannel' => '',
                //'AndroidNotificationBarPriority' => '',
                //'AndroidMusic' => "default",			//Android通知音乐
                'AndroidExtParameters' => $extras, // 设定android类型设备通知的扩展属性
            ]);
        }

        return $this->rpcRequest('Push', $params);
    }

    private function rpcRequest($action, $params)
    {
        $di = \PhalApi\DI();
        $rs = array(
            'code' => 0,
            'msg' => '',
            'data' => null,
        );
        try {
            $result = AlibabaCloud::rpc()
                ->product('Push')
                ->version('2016-08-01')
                ->action($action)
                ->method('POST')
                ->host('cloudpush.aliyuncs.com')
                ->options([
                    'query' => array_merge([
                        'RegionId' => $this->config['regionId'],
                    ], $params),
                ])
                ->request();
            if ($result->isSuccess()) {
                $rs['code'] = 1;
                $rs['msg'] = 'success';
                $rs['data'] = $result->toArray();
                if ($di->debug) {
                    $di->logger->log('AliyunPush', 'push', array('rs' => $response['body']));
                }
            } else {
                $rs['code'] = -1;
                $rs['msg'] = $result;
                if ($di->debug) {
                    $di->logger->log('AliyunPush', 'push', array('failed' => $result));
                }
            }
        } catch (ClientException $e) {
            \PhalApi\DI()->logger->error('AliyunPush # rpcRequest', $e->getErrorMessage());
            $rs['code'] = -1;
            $rs['msg'] = $result;
            if ($di->debug) {
                $di->logger->log('AliyunPush', 'push', array('failed' => $result));
            }
        } catch (ServerException $e) {
            \PhalApi\DI()->logger->error('AliyunPush # rpcRequest', $e->getErrorMessage());
            $rs['code'] = -1;
            $rs['msg'] = $result;
            if ($di->debug) {
                $di->logger->log('AliyunPush', 'push', array('failed' => $result));
            }
        }

        return $rs;
    }
}
