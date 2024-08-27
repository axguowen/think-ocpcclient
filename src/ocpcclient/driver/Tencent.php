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
 * 腾讯广点通
 * @document https://imgcache.qq.com/qzone/biz/gdt/tracking/conversion_web/h5_api_doc.pdf
 */
class Tencent extends Platform
{
    /**
     * 基础URL
     * @const
     */
    const BASE_URL = 'https://tracking.e.qq.com';

	/**
     * 平台配置参数
     * @var array
     */
    protected $options = [
        // 推广域名
        'domain_name' => '',
        // 转化类型
        'action_type' => '',
        // 转化追踪参数
        'click_id' => '',
        // 转化时间戳, 单位秒
        'action_time' => '',
    ];

	/**
     * 转化回传
     * @access public
     * @return array
     */
	public function convertGenerally()
	{
        if(empty($this->options['domain_name'])){
            return [null, new \Exception('未指定参数domain_name', 400)];
        }
        if(empty($this->options['action_type'])){
            return [null, new \Exception('版权资质未指定广点通转化事件类型', 400)];
        }
        if(empty($this->options['click_id'])){
            return [null, new \Exception('未指定参数click_id', 400)];
        }
        if(empty($this->options['action_time'])){
            return [null, new \Exception('未指定参数action_time', 400)];
        }

        // 转化数据
        $requestData = [
            'actions' => [
                [
                    'outer_action_id' => $this->options['click_id'],
                    'url' => $this->options['domain_name'],
                    'action_time' => $this->options['action_time'],
                    'action_type' => $this->options['action_type'],
                    'trace' => [
						'click_id' => $this->options['click_id'],
					]
                ]
            ]
        ];

        // 发送请求并返回结果
        return $this->sendRequest($requestData, '/conv');
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
                'Content-Type' => 'application/json;charset=utf-8',
                'cache-control' => 'no-cache',
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
            // 版权资质未指定转化类型
            if(strpos($result['message'], 'unknown action') !== false){
                // 返回失败
                return [null, new \Exception('操作失败, 错误信息: 版权资质广点通转化类型设置错误或账户域名不正确', 400)];
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