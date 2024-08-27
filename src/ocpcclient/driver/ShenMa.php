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
 * 卧龙移动搜索/神马
 */
class ShenMa extends Platform
{
    /**
     * 基础URL
     * @const
     */
    const BASE_URL = 'https://e.sm.cn/api';

	/**
     * 平台配置参数
     * @var array
     */
    protected $options = [
        // 用户名
        'username' => '',
        // 密码
        'password' => '',
        // 转化追踪标识
        'click_id' => '',
        // 转化类型
        'conv_type' => '',
        'conv_name' => '在线咨询',
		'conv_value' => '1',
        // 转化时间戳, 单位秒
        'date' => '',
    ];

	/**
     * 转化回传
     * @access public
     * @return array
     */
	public function convertGenerally()
	{
        if(empty($this->options['username'])){
            return [null, new \Exception('未指定参数username', 400)];
        }
        if(empty($this->options['password'])){
            return [null, new \Exception('未指定参数password', 400)];
        }
        if(empty($this->options['click_id'])){
            return [null, new \Exception('未指定参数click_id', 400)];
        }
        if(empty($this->options['conv_type'])){
            return [null, new \Exception('版权资质未指定神马转化事件类型', 400)];
        }
        if(empty($this->options['date'])){
            return [null, new \Exception('未指定参数date', 400)];
        }

        // 转化数据
        $requestData = [
            'header' => [
                'username'=> $this->options['username'],
			    'password'=> $this->options['password'],
            ],
            'body' => [
                'source' => 1,
                'data' => [
                    [
                        'date' => date('Y-m-d', $this->options['date']),
                        'click_id' => $this->options['click_id'],
                        'conv_type' => $this->options['conv_type'],
                        'conv_name' => '在线咨询',
                        'conv_value' => '1'
                    ],
                ]
            ]
        ];

        // 发送请求并返回结果
        return $this->sendRequest($requestData, '/uploadConversions');
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
            ]);
            // 请求失败
            if (!$response->ok()) {
                return [null, new \Exception($response->error, 400)];
            }
            // 获取请求结果
            $result = is_null($response->body) ? [] : $response->json();
            // 如果回传成功
            if(isset($result['header'])){
                if ($result['header']['status'] == 0){
                    return ['操作成功', null];
                }
                // 返回失败
                return [null, new \Exception('操作失败, 错误信息: ' . $result['header']['desc'], 400)];
            }
            // 返回未知
            return [null, new \Exception('操作失败, 错误信息: 未知', 400)];
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