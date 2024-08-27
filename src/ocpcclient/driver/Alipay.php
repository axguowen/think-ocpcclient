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
use think\ocpcclient\driver\alipay\AlipayConfig;
use think\ocpcclient\driver\alipay\AopClient;
use think\ocpcclient\driver\alipay\Request;

/**
 * 支付宝数字推广平台
 * @document https://admanage.alipay.com/lark/adrlark/dxw7fkkdnhm45spm
 */
class Alipay extends Platform
{
	/**
     * 平台配置参数
     * @var array
     */
    protected $options = [
        // 应用ID
        'appid' => '',
        // 开发者私钥
        'private_key' => '',
        // 支付宝公钥
        'alipay_public_key' => '',
        // 灯火平台token
        'biz_token' => '',
        // 商家标签
        'principal_tag' => '',
        // 转化流水号
        'biz_no' => '',
        // 转化事件类型
        'conversion_type' => '',
        // 转化时间
        'conversion_time' => '',
        // 转化回调扩展信息
        'callback_ext_info' => '',
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
            return [null, new \Exception('版权资质未指定支付宝灯火转化事件类型', 400)];
        }

        // 实例化配置
        $alipayConfig = new AlipayConfig();
        $alipayConfig->setServerUrl('https://openapi.alipay.com/gateway.do');
        $alipayConfig->setAppId($this->options['appid']);
        $alipayConfig->setPrivateKey($this->options['private_key']);
        $alipayConfig->setAlipayPublicKey($this->options['alipay_public_key']);
        $alipayConfig->setFormat('json');
        $alipayConfig->setCharset('UTF-8');
        $alipayConfig->setSignType('RSA2');

        // 初始化SDK
        $alipayClient = new AopClient($alipayConfig);
        // 构造请求参数以调用接口
        $request = new Request();
        // 设置业务参数
        $request->setBizContent(json_encode([
            'biz_token' => $this->options['biz_token'],
            'conversion_data_list' => [
                [
                    'source' => 'COMMON_TARGET',
                    'principal_tag' => $this->options['principal_tag'],
                    'biz_no' => $this->options['biz_no'],
                    'conversion_type' => $this->options['conversion_type'],
                    'conversion_time' => $this->options['conversion_time'],
                    'uuid_type' => 'PID',
                    'uuid' => '2088UID',
                    'callback_ext_info' => urldecode($this->options['callback_ext_info']),
                ],
            ],
        ], JSON_UNESCAPED_UNICODE));

        // 如果是第三方代调用模式, 请设置app_auth_token（应用授权令牌）
        $responseResult = $alipayClient->execute($request);
        // 构造响应字段
        $responseApiName = str_replace('.', '_', $request->getApiMethodName()) . '_response';
        // 获取响应结果
        $response = $responseResult->$responseApiName;
        // 成功
        if(!empty($response->code) && $response->code == 10000){
            return ['操作成功', null];
        }
        return [null, new \Exception($response->sub_msg, $response->code)];
	}
}