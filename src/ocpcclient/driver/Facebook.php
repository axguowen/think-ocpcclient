<?php
// +----------------------------------------------------------------------
// | ThinkPHP OcpcClient [Simple OCPC Client For ThinkPHP]
// +----------------------------------------------------------------------
// | ThinkPHP OcpcClient客户端
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: axguowen <axguowen@qq.com>
// +----------------------------------------------------------------------

namespace think\ocpcclient\driver;

use think\ocpcclient\Platform;
use FacebookAds\Object\AdsPixel;
use FacebookAds\Api;
use FacebookAds\Logger\CurlLogger;

/**
 * Facebook推广
 * @document https://developers.facebook.com/docs/marketing-api/conversions-api
 */
class Facebook extends Platform
{
	/**
     * 平台配置参数
     * @var array
     */
    protected $options = [
        // 访问令牌
        'access_token' => '',
        // 应用ID
        'app_id' => '',
        // 应用密钥
        'app_secret' => '',
        // 像素ID
        'pixel_id' => '',
        // 事件名称
        'event_name' => '',
        // 深度转化事件名称
        'event_deep' => '',
        // 转化时间
        'event_time' => 0,
        // 页面URL
        'event_source_url' => '',
        // 转化来源
        'action_source' => '',
        // 转化的价值
        'conversion_value' => 0,
        // 币种代码
        'currency_code' => 'USD',
        // IP地址
        'client_ip_address' => '',
    ];

	/**
     * 转化回传
     * @access public
     * @return array
     */
    public function convertGenerally()
    {
        if(empty($this->options['access_token'])){
            return [null, new \Exception('未填写Facebook访问令牌')];
        }
        if(empty($this->options['pixel_id'])){
            return [null, new \Exception('未填写Facebook像素ID')];
        }
        if(empty($this->options['event_name'])){
            return [null, new \Exception('未填写Facebook转化事件名称')];
        }
        if(empty($this->options['client_ip_address'])){
            return [null, new \Exception('未填写访客IP地址')];
        }
        $accessToken = $this->options['access_token'];
        $appId = $this->options['app_id'];
        // 如果为空
        if(empty($appId)){
            $appId = null;
        }
        $appSecret = $this->options['app_secret'];
        // 如果为空
        if(empty($appSecret)){
            $appSecret = null;
        }
        $pixelId = trim($this->options['pixel_id']);
        $clientIpAddress = $this->options['client_ip_address'];
        $eventName = $this->options['event_name'];
        $eventTime = $this->options['event_time'];
        // 如果为空
        if(empty($eventTime)){
            $eventTime = time();
        }
        $currencyCode = $this->options['currency_code'];
        // 如果为空
        if(empty($currencyCode)){
            $currencyCode = 'USD';
        }
        $actionSource = $this->options['action_source'];
        // 如果为空
        if(empty($actionSource)){
            $actionSource = 'website';
        }
        // 构造回传数据
        $postData = [
            'event_name' => $eventName,
            'event_time' => $eventTime,
            'event_source_url' => $this->options['event_source_url'],
            'action_source' => $this->options['action_source'],
            'user_data' => [
                'client_ip_address' => $clientIpAddress,
            ],
        ];
        // 如果设置了价值
        if(!empty($this->options['conversion_value'])){
            // 设置价值
            $postData['custom_data'] = [
                'currency' => $currencyCode,
                'value' => $this->options['conversion_value'],
            ];
        }

        try {
            // 初始化
            $api = Api::init($appId, $appSecret, $accessToken);
            // 初始字段
            $fields = [];
            // 构造参数
            $params = [
                'data' => [$postData],
            ];
            // 实例化像素
            $adsPixel = new AdsPixel($pixelId);
            // 获取响应
            $response = $adsPixel->createEvent($fields, $params)->exportAllData();
            // 如果成功
            if(isset($response['events_received']) && $response['events_received'] == 1){
                // 返回成功
                return [$response, null];
            }
            // 返回失败
            return [null, new \Exception('转化回传失败')];
        } catch (\Exception $e) {
            // 返回
            return [null, $e];
        }
    }

    /**
     * 深度回传
     * @access public
     * @return array
     */
    public function convertDeeply()
    {
        if(empty($this->options['access_token'])){
            return [null, new \Exception('未填写Facebook访问令牌')];
        }
        if(empty($this->options['pixel_id'])){
            return [null, new \Exception('未填写Facebook像素ID')];
        }
        if(empty($this->options['event_deep'])){
            return [null, new \Exception('未填写Facebook深度转化事件名称')];
        }
        if(empty($this->options['client_ip_address'])){
            return [null, new \Exception('未填写访客IP地址')];
        }
        $accessToken = $this->options['access_token'];
        $appId = $this->options['app_id'];
        // 如果为空
        if(empty($appId)){
            $appId = null;
        }
        $appSecret = $this->options['app_secret'];
        // 如果为空
        if(empty($appSecret)){
            $appSecret = null;
        }
        $pixelId = trim($this->options['pixel_id']);
        $clientIpAddress = $this->options['client_ip_address'];
        $eventName = $this->options['event_deep'];
        $eventTime = $this->options['event_time'];
        // 如果为空
        if(empty($eventTime)){
            $eventTime = time();
        }
        $currencyCode = $this->options['currency_code'];
        // 如果为空
        if(empty($currencyCode)){
            $currencyCode = 'USD';
        }
        $actionSource = $this->options['action_source'];
        // 如果为空
        if(empty($actionSource)){
            $actionSource = 'website';
        }
        // 构造回传数据
        $postData = [
            'event_name' => $eventName,
            'event_time' => $eventTime,
            'event_source_url' => $this->options['event_source_url'],
            'action_source' => $this->options['action_source'],
            'user_data' => [
                'client_ip_address' => $clientIpAddress,
            ],
        ];
        // 如果设置了价值
        if(!empty($this->options['conversion_value'])){
            // 设置价值
            $postData['custom_data'] = [
                'currency' => $currencyCode,
                'value' => $this->options['conversion_value'],
            ];
        }

        try {
            // 初始化
            $api = Api::init($appId, $appSecret, $accessToken);
            // 初始字段
            $fields = [];
            // 构造参数
            $params = [
                'data' => [$postData],
            ];
            // 实例化像素
            $adsPixel = new AdsPixel($pixelId);
            // 获取响应
            $response = $adsPixel->createEvent($fields, $params)->exportAllData();
            // 如果成功
            if(isset($response['events_received']) && $response['events_received'] == 1){
                // 返回成功
                return [$response, null];
            }
            // 返回失败
            return [null, new \Exception('转化回传失败')];
        } catch (\Exception $e) {
            // 返回
            return [null, $e];
        }
    }
}