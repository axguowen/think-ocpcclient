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
 * 爱奇艺奇炬平台
 * @document https://iq.feishu.cn/wiki/YbDzwp0K3iy5IOkLHjGcBKcxnBc
 */
class Iqiyi extends Platform
{
    /**
     * 基础URL
     * @const
     */
    const BASE_URL = 'https://tc.cupid.iqiyi.com/dsp_lpapi';

	/**
     * 平台配置参数
     * @var array
     */
    protected $options = [
        // 事件类型
        'event_type' => 0,
        // 深度转化类型
        'deep_type' => 0,
        // 广告ID
        'impress_id' => '',
    ];

	/**
     * 转化回传
     * @access public
     * @return array
     */
	public function convertGenerally()
	{
        // 事件类型错误
        if(empty($this->options['event_type'])){
            return [null, new \Exception('事件类型参数错误', 400)];
        }
        // 广告ID参数错误
        if(empty($this->options['impress_id'])){
            return [null, new \Exception('未指定参数impress_id', 400)];
        }

        // 转化数据
        $requestData = [
            // 转化类型
            'event_type' => $this->options['event_type'],
            // 广告ID
            'impress_id' => $this->options['impress_id'],
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
        // 事件类型错误
        if(empty($this->options['event_type'])){
            return [null, new \Exception('事件类型参数错误', 400)];
        }
        // 广告ID参数错误
        if(empty($this->options['impress_id'])){
            return [null, new \Exception('未指定参数impress_id', 400)];
        }

        // 转化数据
        $requestData = [
            // 转化类型
            'event_type' => $this->options['deep_type'],
            // 广告ID
            'impress_id' => $this->options['impress_id'],
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
            // 请求成功
            if ($response->ok()) {
                // 返回成功
                return ['操作成功', null];
            }
            // 获取请求结果
            $result = is_null($response->body) ? [] : $response->json();
            // 如果没有错误信息
            if(empty($result) || !isset($result['message'])){
                return [null, new \Exception($response->error, 400)];
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