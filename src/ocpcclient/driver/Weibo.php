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
 * 微博广告
 * @document https://developers.biz.weibo.com/docs/#/track/no_auth_activate
 */
class Weibo extends Platform
{
    /**
     * 基础URL
     * @const
     */
    const BASE_URL = 'https://api.biz.weibo.com';

	/**
     * 平台配置参数
     * @var array
     */
    protected $options = [
        // 转化追踪参数
        'mark_id' => '',
        // 转化时间戳, 单位毫秒
        'convert_time' => '',
        // 转化类型
        'convert_type' => '',
        // 回传来源
        'host' => null,
        // 小程序同步监测相关参数，请求唯一id，微信小程序必须有此字段，需要urlencode
        'imp' => null,
        // 小程序同步监测相关参数，behavior=3003时可选，单位：元，保留小数点后两位，精确到分。
        'paid_amount' => null,
        // 表单提交相关参数，behavior=1001时可选
        // 意向度从高到低，分别回传5/4/3/2，四个等级。
        // 如少于四个意向度，则自己定义，例如3/2，如多于四个意向度，则最多定义四个。
        // 回传1是负向（无意向），例如选择了“不参与活动”，或直接点击关闭意向表单
        'score' => null,
        // 技术服务商名，默认为空，使用三方技术服务时必填，未填写将影响实际结算
        'technical_provider' => null,
        // 付款订单商品数相关参数，behavior编码2001必选，参数格式：item_order_pay=urlencode(json)。
        'item_order_pay' => null,
    ];

	/**
     * 转化回传
     * @access public
     * @return array
     */
	public function convertGenerally()
	{
        if(empty($this->options['mark_id'])){
            return [null, new \Exception('未指定转化追踪参数', 400)];
        }
        if(empty($this->options['convert_time'])){
            return [null, new \Exception('未指定转化时间', 400)];
        }
        if(empty($this->options['convert_type'])){
            return [null, new \Exception('版权资质未指定微博转化事件类型', 400)];
        }

        // 转化数据
        $requestData = [
			'mark_id' => urlencode(urldecode($this->options['mark_id'])),
            'time' => $this->options['convert_time'] * 1000,
			'behavior' => $this->options['convert_type'],
        ];

        // 如果存在回传来源
        if(!is_null($this->options['host'])){
            $requestData['host'] = $this->options['host'];
        }
        // 如果存在小程序请求唯一ID
        if(!is_null($this->options['imp'])){
            $requestData['imp'] = $this->options['imp'];
        }
        // 如果存在小程序支付金额
        if(!is_null($this->options['paid_amount'])){
            $requestData['paid_amount'] = $this->options['paid_amount'];
        }
        // 如果存在表单意向
        if(!is_null($this->options['score'])){
            $requestData['score'] = $this->options['score'];
        }
        // 如果存在技术服务商名
        if(!is_null($this->options['technical_provider'])){
            $requestData['technical_provider'] = $this->options['technical_provider'];
        }
        // 如果存在付款订单商品数相关参数
        if(!is_null($this->options['item_order_pay'])){
            $requestData['item_order_pay'] = $this->options['item_order_pay'];
        }

        // 发送请求并返回结果
        return $this->sendRequest($requestData, '/v4/track/activate');
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