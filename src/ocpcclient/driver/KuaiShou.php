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
 * 快手磁力智投
 * @document https://developers.e.kuaishou.com/docs?docType=DSP&documentId=1938
 */
class KuaiShou extends Platform
{
    /**
     * 基础URL
     * @const
     */
    const BASE_URL = 'http://ad.partner.gifshow.com/track/activate';

	/**
     * 平台配置参数
     * @var array
     */
    protected $options = [
        // 事件类型
        'event_type' => '',
        // 深度转化类型
        'deep_type' => '',
        // 广告ID
        'callback' => '',
        // 转化时间戳, 单位秒
        'event_time' => '',
    ];

	/**
     * 转化回传
     * @access public
     * @return array
     */
	public function convertGenerally()
	{
        // 如果事件类型为空
        if(empty($this->options['event_type'])){
            return [null, new \Exception('版权资质未指定快手转化事件类型', 400)];
        }

        // 广告ID参数错误
        if(empty($this->options['callback'])){
            return [null, new \Exception('未指定参数callback', 400)];
        }

        // 未设置转化时间
        if(empty($this->options['event_time'])){
            return [null, new \Exception('未指定转化时间', 400)];
        }

        // 转化数据
        $requestData = [
            // 转化类型
            'event_type' => $this->options['event_type'],
            // 广告ID
            'callback' => $this->options['callback'],
            // 转化时间
            'event_time' => $this->options['event_time'] * 1000,
        ];

        // 发送请求并返回结果
        return $this->sendRequest($requestData);
	}

    /**
     * 深度转化回传
     * @access public
     * @return array
     */
	public function convertDeeply()
	{
        // 如果事件类型为空
        if(empty($this->options['deep_type'])){
            return [null, new \Exception('未指定参数deep_type', 400)];
        }

        // 广告ID参数错误
        if(empty($this->options['callback'])){
            return [null, new \Exception('未指定参数callback', 400)];
        }

        // 未设置转化时间
        if(empty($this->options['event_time'])){
            return [null, new \Exception('未指定转化时间', 400)];
        }

        // 转化数据
        $requestData = [
            // 转化类型
            'event_type' => $this->options['deep_type'],
            // 广告ID
            'callback' => $this->options['callback'],
            // 转化时间
            'event_time' => $this->options['event_time'] * 1000,
        ];

        // 发送请求并返回结果
        return $this->sendRequest($requestData);
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
        // 构建query数据
        $requestQuery = http_build_query($data);

        try{
            // 发送请求
            $response = HttpClient::get(self::BASE_URL . $path . '?' . $requestQuery);
            // 请求失败
            if (!$response->ok()) {
                return [null, new \Exception($response->error, 400)];
            }
            // 获取请求结果
            $result = is_null($response->body) ? [] : $response->json();
            // 如果回传成功
            if($result['result'] == 1){
                return ['操作成功', null];
            }
            // 返回失败
            return [null, new \Exception('操作失败, 错误信息: ' . $result['error_msg'], 400)];
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