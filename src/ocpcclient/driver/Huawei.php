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
 * 华为鲸鸿
 * @document https://developer.huawei.com/consumer/cn/doc/promotion/ads-gongju-wyzc-jiance-0000001639699446
 */
class Huawei extends Platform
{
    /**
     * 基础URL
     * @const
     */
    const BASE_URL = 'https://ppscrowd-drcn.op.hicloud.com/action-lib-track/hiad/v2';

	/**
     * 平台配置参数
     * @var array
     */
    protected $options = [
        // 应用密钥
        'secret_key' => '',
        // 转化类型
        'conversion_type' => '',
        // 广告转化回传标识
        'callback' => '',
        // 转化时间戳, 单位秒
        'conversion_time' => '',
    ];

	/**
     * 转化回传
     * @access public
     * @return array
     */
	public function convertGenerally()
	{
        // 如果事件类型为空
        if(empty($this->options['conversion_type'])){
            return [null, new \Exception('版权资质未指定华为广告转化事件类型', 400)];
        }

        // 转化回调参数错误
        if(empty($this->options['callback'])){
            return [null, new \Exception('未指定参数callback', 400)];
        }

        // 未设置转化时间
        if(empty($this->options['conversion_time'])){
            return [null, new \Exception('未指定转化时间', 400)];
        }

        // 转化数据
        $requestData = [
            // 事件类型
            'conversion_type' => $this->options['conversion_type'],
            // 转化上下文数据
            'callback' => $this->options['callback'],
            // 转化时间
            'conversion_time' => $this->options['conversion_time'],
        ];

        // 发送请求并返回结果
        return $this->sendRequest($requestData, '/actionupload');
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
        // 追加请求参数
        $data['timestamp'] = time() * 1000;
        // json序列化后的数据
        $requestJson = json_encode($data, JSON_UNESCAPED_UNICODE);

        // 生成签名
        $signature = hash_hmac('sha256', $requestJson, $this->options['secret_key']);

        try{
            // 发送请求
            $response = HttpClient::post(self::BASE_URL . $path, $requestJson, [
                'Content-Type' => 'application/json;charset=utf-8',
                'Authorization' => 'Digest validTime="' . $data['timestamp'] . '", response="' . $signature . '"',
            ]);
            // 请求失败
            if (!$response->ok()) {
                return [null, new \Exception($response->error, 400)];
            }
            // 获取请求结果
            $result = is_null($response->body) ? [] : $response->json();
            // 如果回传成功
            if($result['resultCode'] == 0){
                return ['操作成功', null];
            }
            // 返回失败
            return [null, new \Exception('操作失败, 错误信息: ' . $result['resultMessage'], 400)];
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