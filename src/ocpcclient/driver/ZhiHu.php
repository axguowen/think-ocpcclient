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
 * 知乎推广
 * @document https://zhstatic.zhihu.com/commercial/bidding/%E8%90%BD%E5%9C%B0%E9%A1%B5%E8%BD%AC%E5%8C%96%E8%BF%BD%E8%B8%AA%E6%96%87%E6%A1%A32.pdf
 */
class ZhiHu extends Platform
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
        'event_type' => '',
        // 转化时间戳, 单位秒
        'timestamp' => '',
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
        if(empty($this->options['event_type'])){
            return [null, new \Exception('版权资质未指定知乎化事件类型', 400)];
        }
        if(empty($this->options['timestamp'])){
            return [null, new \Exception('未指定参数timestamp', 400)];
        }
        if(empty($this->options['callback'])){
            return [null, new \Exception('未指定参数callback', 400)];
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
            $response = HttpClient::get(str_replace(['__EVENTTYPE__','__TIMESTAMP__'], [$this->options['event_type'], $this->options['timestamp']], $this->options['callback']));
            // 请求失败
            if (!$response->ok()) {
                return [null, new \Exception($response->error, 400)];
            }
            return ['操作成功', null];
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