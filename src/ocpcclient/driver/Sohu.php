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
 * 搜狐汇算
 * @document https://fe-res.go.sohu.com/doc/SOHU%E7%BD%91%E9%A1%B5%E5%B9%BF%E5%91%8A%E8%BD%AC%E5%8C%96%E6%95%B0%E6%8D%AEAPI%E5%AF%B9%E6%8E%A5%E6%96%87%E6%A1%A3.pdf
 */
class Sohu extends Platform
{
    /**
     * 基础URL
     * @const
     */
    const BASE_URL = 'http://t.ads.sohu.com/count/ac';

	/**
     * 平台配置参数
     * @var array
     */
    protected $options = [
        // 事件类型
        'event_type' => '',
        // 转化追踪标识
        'shcallback' => '',
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
        // 如果事件类型为空
        if(empty($this->options['event_type'])){
            return [null, new \Exception('版权资质未指定搜狐转化事件类型', 400)];
        }

        // 转化追踪标识错误
        if(empty($this->options['shcallback'])){
            return [null, new \Exception('未指定参数shcallback', 400)];
        }

        // 未设置转化时间
        if(empty($this->options['timestamp'])){
            return [null, new \Exception('未指定转化时间', 400)];
        }

        // 转化数据
        $requestData = [
            // 事件类型
            'event_type' => $this->options['event_type'],
            // 转化上下文数据
            'shcallback' => $this->options['shcallback'],
            // 转化时间
            'timestamp' => $this->options['timestamp'] * 1000,
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
            // 如果返回空
            if(is_null($response->body)){
                // 返回失败
                return [null, new \Exception('操作失败, 未返回结果', 400)];
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