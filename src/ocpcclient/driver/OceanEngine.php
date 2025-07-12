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
 * 巨量引擎
 * @document https://event-manager.oceanengine.com/docs/8650/app_api_docs/
 * @document https://bytedance.feishu.cn/docx/ZrzEd4n2SoNJRwxFu0VcGUxxnle
 */
class OceanEngine extends Platform
{
    /**
     * 基础URL
     * @const
     */
    const BASE_URL = 'https://analytics.oceanengine.com/api/v2';

	/**
     * 平台配置参数
     * @var array
     */
    protected $options = [
        // 事件类型
        'event_type' => '',
        // 回调参数
        'callback' => '',
        // 转化时间戳, 单位秒
        'timestamp' => '',
        // 授权access_token
        'app_access_token' => '',
        // 上下文扩展属性
        'context_extends' => [],
        // 其它属性
        'properties' => [],
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
            return [null, new \Exception('版权资质未指定巨量引擎转化事件类型', 400)];
        }

        // 转化回调参数错误
        if(empty($this->options['callback'])){
            return [null, new \Exception('未指定参数callback', 400)];
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
            'context' => [
                'ad' => [
                    'callback' => explode('#', $this->options['callback'])[0]
                ]
            ],
            'timestamp' => $this->options['timestamp'] * 1000,
        ];

        // 遍历上下文扩展属性
        foreach($this->options['context_extends'] as $key => $value){
            // 追加属性
            $requestData['context'][$key] = $value;
        }

        // 如果是APP内下单
        if($this->options['event_type'] == 'in_app_order'){
            // 追加属性
            $requestData['properties'] = $this->options['properties'];
        }

        // 发送请求并返回结果
        return $this->sendRequest($requestData, '/conversion');
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
        // 请求头
        $requestHeader = [
            'Content-Type' => 'application/json;charset=utf-8',
            'Content-Length' => strlen($requestJson)
        ];
        // 如果有授权token
        if(!empty($this->options['app_access_token'])){
            $requestHeader['App-Access-Token'] = $this->options['app_access_token'];
        }

        try{
            // 发送请求
            $response = HttpClient::post(self::BASE_URL . $path, $requestJson, $requestHeader);
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