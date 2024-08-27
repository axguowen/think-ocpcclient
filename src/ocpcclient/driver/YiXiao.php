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
 * 易效推广
 * @document https://pg-ad-b1.ws.126.net/yixiao/convert/lp/helper-v2.0.pdf
 */
class YiXiao extends Platform
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
        // 转化类型
        'convert_type' => '',
        // 转化时间戳, 单位秒
        'convert_time' => '',
        // 转化追踪参数
        'callback' => '',
    ];

	/**
     * 转化回传
     * @access public
     * @return array
     */
	public function convertGenerally()
	{
        if(empty($this->options['convert_type'])){
            return [null, new \Exception('版权资质未指定易效转化事件类型', 400)];
        }
        if(empty($this->options['convert_time'])){
            return [null, new \Exception('未指定参数convert_time', 400)];
        }
        if(empty($this->options['callback'])){
            return [null, new \Exception('未指定参数callback', 400)];
        }

        // 转化数据
        $requestData = [
            't' => $this->options['convert_time'] * 1000,
            'conv_type' => $this->options['convert_type'],
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
            $response = HttpClient::get($this->options['callback'] . (strpos($this->options['callback'], '?') === false ? '?' : '&') . $requestQuery);
            // 请求失败
            if (!$response->ok()) {
                return [null, new \Exception($response->error, 400)];
            }
            // 获取请求结果
            $result = is_null($response->body) ? [] : $response->json();
            // 如果回传成功
            if($result['status'] == 'SUCCESSED'){
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