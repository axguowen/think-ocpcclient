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
 * Soul推广
 * @document https://docs.qq.com/doc/DUWJVbUpnU21TTElL
 * @document https://docs.qq.com/doc/DUVpQbVRQallJT3ZC
 */
class Soul extends Platform
{
    /**
     * 基础URL
     * @const
     */
    const BASE_URL = '';

	/**
     * 平台配置参数
     * @var array
     */
    protected $options = [
        // 转化回传URL
        'callback' => '',
        // 转化类型
        'event_type' => '',
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
        if(empty($this->options['callback'])){
            return [null, new \Exception('未指定参数callback', 400)];
        }
        if(empty($this->options['event_type'])){
            return [null, new \Exception('版权资质未指定Soul转化事件类型', 400)];
        }
        if(empty($this->options['event_time'])){
            return [null, new \Exception('未指定参数event_time', 400)];
        }

        // 转化数据
        $requestData = [];

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
            $response = HttpClient::get(str_replace(['__EVENTTYPE__', '__EVENTTS__'], [$this->options['event_type'], $this->options['event_time'] * 1000], $this->options['callback']));
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