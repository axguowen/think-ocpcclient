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

/**
 * Vivo推广
 * @document https://open-ad.vivo.com.cn/doc/index?id=217
 */
class Vivo extends Platform
{
    /**
     * 基础URL
     * @const
     */
    const BASE_URL = 'https://marketing-api.vivo.com.cn/openapi/v1';

	/**
     * 平台配置参数
     * @var array
     */
    protected $options = [
        // 令牌
        'access_token' => '',
        // 数据源ID
        'srcid' => '',
        // 落地页链接
        'page_url' => '',
        // 转化类型
        'convert_type' => '',
        // 转化时间戳, 单位秒
        'convert_time' => '',
        // 请求ID
        'request_id' => '',
        // 转化追踪参数
        'creative_id' => '',
    ];

	/**
     * 转化回传
     * @access public
     * @return array
     */
	public function convertGenerally()
	{
        if(empty($this->options['access_token'])){
            return [null, new \Exception('未指定参数access_token', 400)];
        }
        if(empty($this->options['srcid'])){
            return [null, new \Exception('未指定参数srcid', 400)];
        }
        if(empty($this->options['page_url'])){
            return [null, new \Exception('未指定参数page_url', 400)];
        }
        if(empty($this->options['convert_type'])){
            return [null, new \Exception('版权资质未指定VIVO转化事件类型', 400)];
        }
        if(empty($this->options['convert_time'])){
            return [null, new \Exception('未指定参数convert_time', 400)];
        }
        if(empty($this->options['request_id'])){
            return [null, new \Exception('未指定参数request_id', 400)];
        }
        if(empty($this->options['creative_id'])){
            return [null, new \Exception('未指定参数creative_id', 400)];
        }

        // 转化数据
        $requestData = [
            'srcType' => 'Web',
			'pageUrl' => $this->options['page_url'],
			'srcId' => $this->options['srcid'],
			'dataList' => [
				'cvType' => $this->options['convert_type'],
				'cvTime' => $this->options['convert_time'] * 1000,
				'requestId' => $this->options['request_id'],
				'creativeId' => $this->options['creative_id'],
			],
        ];

        // 发送请求并返回结果
        return $this->sendRequest($requestData, '/advertiser/behavior/upload');
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
        $requestQuery = http_build_query([
            'access_token' => $this->options['access_token'],
			'timestamp' => time() * 1000,
			'nonce' => hash('md5', time() . '_' . mt_rand(100000,999999)),
        ]);

        try{
            // 发送请求
            $response = HttpClient::post(self::BASE_URL . $path . '?' . $requestQuery, $requestJson, [
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
                return ['操作成功', null];
            }
            // 返回失败
            return [null, new \Exception('操作失败, 错误信息: ' . $result['message'], 400)];
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

	/**
     * 获取访问令牌
     * @access public
     * @param string $clientId 客户端ID
     * @param string $secret 客户端密钥
     * @param string $code 授权码
     * @return array
     */
	public function getAccessToken($clientId, $secret, $code)
	{
        // 构造请求参数
        $requestQuery = http_build_query([
            'client_id' => $clientId,
            'client_secret' => $secret,
            'grant_type' => 'code',
            'code' => $code,
        ]);
        try{
            // 发送请求
            $response = HttpClient::get(self::BASE_URL . '/oauth2/token?' . $requestQuery);
            // 请求失败
            if (!$response->ok()) {
                return [null, new \Exception($response->error, 400)];
            }
            // 获取请求结果
            $result = is_null($response->body) ? [] : $response->json();
            // 如果回传成功
            if($result['code'] == 0){

                return [$result['data'], null];
            }
            // 返回失败
            return [null, new \Exception('操作失败, 错误信息: ' . $result['message'], 400)];
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