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
 * TikTok
 * @document https://business-api.tiktok.com/portal/docs?id=1771100865818625
 */
class TikTok extends Platform
{
    /**
     * 基础URL
     * @const
     */
    const BASE_URL = 'https://business-api.tiktok.com/open_api/v1.3/event/track';

	/**
     * 平台配置参数
     * @var array
     */
    protected $options = [
        // 令牌
        'access_token' => '',
        // 像素值
        'pixel' => '',
        // 事件类型
        'event_type' => '',
        // 事件ID
        'event_id' => '',
        // 点击ID
        'ttclid' => '',
        // 页面链接
        'page_url' => '',
        // 转化时间戳, 单位秒
        'timestamp' => '',
    ];

	/**
     * 转化回传
     * @access public
     * @return array
     */
	public function convertGenerally()
	{
        // 令牌为空
        if(empty($this->options['access_token'])){
            return [null, new \Exception('版权资质未指定TikTok回传Token', 400)];
        }
        // pixel为空
        if(empty($this->options['pixel'])){
            return [null, new \Exception('版权资质未指定TikTok回传Pixel', 400)];
        }
        // 如果事件类型为空
        if(empty($this->options['event_type'])){
            return [null, new \Exception('版权资质未指定TikTok转化事件类型', 400)];
        }

        // 转化数据
        $data = [
            'event' => $this->options['event_type'],
            'event_time' => $this->options['timestamp'],
            'event_id' => $this->options['event_id'],
            'user' => [
                'ttclid' => $this->options['ttclid'],
            ],
            'page' => [
                'url' => $this->options['page_url'],
            ],
        ];
        // 请求数据
        $requestData = [
            // 事件来源
            'event_source' => 'web',
            // 事件源ID
            'event_source_id' => $this->options['pixel'],
            // 转化数据
            'data' => $data,
        ];

        // 发送请求并返回结果
        return $this->sendRequest($requestData, '/');
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
                'Access-Token' => $this->options['access_token'],
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

}