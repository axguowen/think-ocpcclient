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
 * 票圈广告
 * @link https://www.piaoquantv.com/
 */
class Piaoquantv extends Platform
{
    /**
     * 基础URL
     * @const
     */
    const BASE_URL = 'https://open.piaoquantv.com';

	/**
     * 平台配置参数
     * @var array
     */
    protected $options = [
        // 鉴权参数
        'access_token' => '',
        // 应用ID
        'app_id' => '',
        // 应用密钥
        'app_secret' => '',
        // 广告追踪参数
        'pqt_id' => '',
        // 转化事件类型
        'event_type' => '',
        // 转化事件时间戳, 单位秒
        'event_time' => 0,
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
        if(empty($this->options['app_id'])){
            return [null, new \Exception('未指定参数app_id', 400)];
        }
        if(empty($this->options['app_secret'])){
            return [null, new \Exception('未指定参数app_secret', 400)];
        }
        if(empty($this->options['pqt_id'])){
            return [null, new \Exception('未指定参数pqt_id', 400)];
        }

        // 请求头
        $headers = [
            'Content-Type' => 'application/json',
        ];
        // 请求体
        $body = [
            'event' => [
                [
                    'eventTime' => $this->options['event_time'] * 1000,
                    'eventType' => $this->options['event_type'] * 1,
                    'pqtId' => $this->options['pqt_id'],
                    'timeStamp' => time() * 1000,
                ]
            ]
        ];
        // 构造签名串
        $signStr = $this->options['app_secret'];
        // 遍历body
        foreach($body as $key => $value){
            $signStr .= $key . json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        // 生成签名
        $sign = sha1($signStr);
        // 请求参数
        $query = [
            'appId' => $this->options['app_id'],
            'sign' => $sign,
            'accessToken' => $this->options['access_token'],
        ];
        // 请求地址
        $requestUrl = static::BASE_URL . '/ad/open/api/conv/v1?' . http_build_query($query);
        // 获取请求结果
        $sendRequestResult = $this->sendRequest($requestUrl, $body, $headers);
        // 返回结果
        return $sendRequestResult;
	}

    /**
     * 发送请求
     * @access protected
     * @param string $url 请求地址
     * @param array $data 请求体
     * @param array $headers 请求头
     * @return array
     */
	protected function sendRequest($url, array $data, $headers = [])
	{
        try{
            // 发送请求
            $response = HttpClient::post($url, json_encode($data, JSON_UNESCAPED_UNICODE), $headers);
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