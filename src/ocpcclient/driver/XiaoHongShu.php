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
use axguowen\HttpClient;
use axguowen\RedisClient;

/**
 * 小红书推广
 * @document https://doc.weixin.qq.com/doc/w3_AUsADgakAI4cGnUb70VQKKrbPaE0N?scode=ANAAyQcbAAgwVVWj7wAAoABgbjAPY
 */
class XiaoHongShu extends Platform
{
    /**
     * 基础URL
     * @const
     */
    const BASE_URL = 'https://adapi.xiaohongshu.com/api';

    /**
     * 版本
     * @const
     */
    const VERSION = '1.0';

    /**
     * 缓存键规则
     * @var string
     */
    protected $cacheKey = 'cache:xhsocpc:access_token:advertiser_id:<advertiser_id>';

	/**
     * 平台配置参数
     * @var array
     */
    protected $options = [
        // 广告主ID
        'advertiser_id' => '',
        // 事件类型
        'event_type' => '122',
        // 转化时间戳, 单位毫秒
        'conv_time' => 0,
        // 广告点击ID
        'click_id' => '',
    ];

	/**
     * 转化回传
     * @access public
     * @return array
     */
	public function convertGenerally()
	{
        if(empty($this->options['advertiser_id'])){
            return [null, new \Exception('未填写广告主ID参数')];
        }
        if(empty($this->options['event_type'])){
            return [null, new \Exception('版权资质未选择小红书转化类型')];
        }
        if(empty($this->options['conv_time'])){
            return [null, new \Exception('未指定转化时间')];
        }
        if(empty($this->options['click_id'])){
            return [null, new \Exception('未指定访问追踪参数')];
        }

        $advertiser_id = $this->options['advertiser_id'];
        $method = 'aurora.leads';
        $timestamp = time() * 1000;
        // 构造签名内容字符串
        $signStr = [];
        $signStr[] = 'advertiser_id' . $advertiser_id;
        $signStr[] = 'method' . $method;
        $signStr[] = 'timestamp' . $timestamp;
        $signStr[] = 'version' . self::VERSION;
        // 生成签名
        $sign = strtoupper(hash('md5', implode('&', $signStr)));

        // 获取accessToken
        $getAccessTokenResult = $this->getAccessToken();
        // 如果失败
        if(is_null($getAccessTokenResult[0])){
            return $getAccessTokenResult;
        }
        $accessToken = $getAccessTokenResult[0];
        // 转化数据
        $requestData = [
            'advertiser_id' => $advertiser_id,
            'version' => self::VERSION,
            'sign' => $sign,
			'timestamp' => $timestamp,
            'method' => $method,
			'access_token' => $accessToken,
			'event_type' => $this->options['event_type'],
            'conv_time' => $this->options['conv_time'] * 1000,
			'click_id' => $this->options['click_id'],
        ];

        // 发送请求并返回结果
        return $this->sendRequest($requestData, '/open/common');
	}

    /**
     * 无效转化回传
     * @access public
     * @return array
     */
	public function invalidGenerally()
	{
        // 指定回传类型为负向样本
        $this->options['event_type'] = 200;
        // 返回回传结果
        return $this->convertGenerally();
    }

    /**
     * 获取AccessToken
     * @access protected
     * @return array
     */
	protected function getAccessToken()
	{
        // 构造缓存键名
        $cacheKey = 'xhsocpc_access_token_' . $this->options['advertiser_id'];
        // 实例化缓存器
        $redisClient = RedisClient::connect('cache');
        // 获取缓存键名构造器
        $cacheBuilder = $redisClient->key($this->cacheKey, [
            'advertiser_id' => $this->options['advertiser_id']
        ]);
        // 如果缓存存在
        if ($cacheBuilder->exist() && 5 === $cacheBuilder->type()) {
            // 获取缓存数据
            $cacheData = $cacheBuilder->hGetAll();
            // 如果没有过期
            if (isset($cacheData['expire_time']) && $cacheData['expire_time'] < time()) {
                // 返回token
                return [$cacheData['access_token'], null];
            }
        }

        // 通过接口获取token
        $getAccessTokenResult = $this->sendRequest([
            'advertiser_id' => $this->options['advertiser_id'],
            'version' => self::VERSION,
            'timestamp' => time() * 1000,
            'method' => 'Oauth.getAccessToken',
        ], '/open/common');

        // 如果失败
        if(is_null($getAccessTokenResult[0])){
            return $getAccessTokenResult;
        }
        // 获取token数据
        $accessTokenData = $getAccessTokenResult[0];
        $accessToken = $accessTokenData['access_token'];
        $expireIn = $accessTokenData['access_token_expires_in'];
        // 设置缓存
        $cacheBuilder->hMset([
            'access_token' => $accessToken,
            'expire_time' => time() + $expireIn,
        ]);
        $cacheBuilder->expire($expireIn - 200);
        // 返回
        return [$accessToken, null];
    }

    /**
     * 发送请求
     * @access protected
     * @param array $data 转化数据
     * @param string $path URL
     * @return array
     */
	protected function sendRequest(array $data, $path = '')
	{
        // json序列化后的数据
        $requestJson = json_encode($data);

        try{
            // 发送请求
            $response = HttpClient::post(self::BASE_URL . $path, $requestJson, [
                'Content-Type' => 'application/json;charset=utf-8',
            ]);
            // 请求失败
            if (!$response->ok()) {
                return [null, new \Exception($response->error, 400)];
            }
            // 获取请求结果
            $result = is_null($response->body) ? [] : $response->json();
            // 如果回传成功
            if($result['code'] == 0){
                // 如果是获取token
                if($data['method'] == 'Oauth.getAccessToken'){
                    return [$result['data'], null];
                }
                return ['操作成功', null];
            }
            // 返回失败
            return [null, new \Exception('操作失败, 错误信息: ' . $result['msg'], 400)];
        }
        // 异常捕获
        catch (\Exception $e) {
            // 如果开启调试模式
            if(\think\facade\App::isDebug()){
                // 手动抛出异常
                throw $e;
            }
            return [null, $e];
        }
    }

}