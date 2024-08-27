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
 * 哔哩哔哩广告
 * @document https://docs.qq.com/doc/DRmVwVnd1RllYS0Fo
 */
class Bilibili extends Platform
{
    /**
     * 基础URL
     * @const
     */
    const BASE_URL = 'https://cm.bilibili.com/conv/api/conversion/ad/cb/v1';

	/**
     * 平台配置参数
     * @var array
     */
    protected $options = [
        // 转化类型
        'conv_type' => '',
        // 转化时间戳, 单位秒
        'conv_time' => '',
        // 追踪ID
        'track_id' => '',
    ];

	/**
     * 转化回传
     * @access public
     * @return array
     */
	public function convertGenerally()
	{
        // 如果事件类型为空
        if(empty($this->options['conv_type'])){
            return [null, new \Exception('版权资质未指定哔哩哔哩转化事件类型', 400)];
        }

        // 广告ID参数错误
        if(empty($this->options['track_id'])){
            return [null, new \Exception('未指定参数track_id', 400)];
        }

        // 未设置转化时间
        if(empty($this->options['conv_time'])){
            $this->options['conv_time'] = time();
        }

        // 转化数据
        $requestData = [
            // 转化类型
            'conv_type' => $this->options['conv_type'],
            // 转化时间
            'conv_time' => $this->options['conv_time'] * 1000,
            // 广告ID
            'track_id' => $this->options['track_id'],
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