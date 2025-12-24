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
use Google\Ads\GoogleAds\Lib\V22\GoogleAdsClientBuilder;
use Google\Ads\GoogleAds\Lib\V22\GoogleAdsException;
use Google\Ads\GoogleAds\Lib\OAuth2TokenBuilder;
use Google\Ads\GoogleAds\V22\Common\Consent;
use Google\Ads\GoogleAds\V22\Services\ClickConversion;
use Google\Ads\GoogleAds\V22\Services\UploadClickConversionsRequest;
use Google\Ads\GoogleAds\V22\Services\UploadClickConversionsResponse;
use Google\Ads\GoogleAds\Util\V22\ResourceNames;
use Google\ApiCore\ApiException;

/**
 * 谷歌推广
 * @document https://developers.google.cn/google-ads/api/docs/get-started/make-first-call?hl=zh-cn
 */
class Google extends Platform
{
	/**
     * 平台配置参数
     * @var array
     */
    protected $options = [
        // 开发者token
        'developer_token' => '',
        // jsonKey路径
        'json_key_file_path' => '',
        // 客户ID
        'customer_id' => '',
        // 转化操作ID
        'conversion_action_id' => '',
        // 深度转化操作ID
        'deep_action_id' => '',
        // 广告追踪参数
        'gclid' => '',
        // 转化时间需要带时区
        'conversion_date_time' => '',
        // 转化的价值
        'conversion_value' => 20,
        // 币种代码
        'currency_code' => 'USD',
        // IP地址
        'user_ip_address' => '',
    ];

	/**
     * 转化回传
     * @access public
     * @return array
     */
    public function convertGenerally()
    {
        if(empty($this->options['customer_id'])){
            return [null, new \Exception('未填写谷歌账户ID')];
        }
        if(empty($this->options['conversion_action_id'])){
            return [null, new \Exception('未填写转化操作ID参数')];
        }
        // 获取开发者令牌
        $developerToken = $this->options['developer_token'];
        // 获取jsonKey路径
        $jsonKeyFilePath = \think\facade\App::getRootPath() . ltrim($this->options['json_key_file_path'], DIRECTORY_SEPARATOR);
        // 当前客户ID
        $customerId = str_replace('-', '', $this->options['customer_id']);
        // 转化操作ID
        $conversionActionId = $this->options['conversion_action_id'];
        // 转化价值
        $conversionValue = $this->options['conversion_value'];
        // 转化时间
        $conversionDateTime = $this->options['conversion_date_time'];
        // 币种
        $currencyCode = $this->options['currency_code'];
        // 构造授权实例
        $oAuth2Credential = (new OAuth2TokenBuilder())
                            ->withJsonKeyFilePath($jsonKeyFilePath)
                            ->withScopes('https://www.googleapis.com/auth/adwords')
                            ->build();

        // 构造客户端实例
        $googleAdsClient = (new GoogleAdsClientBuilder())
                            ->withDeveloperToken($developerToken)
                            ->withLoginCustomerId($customerId)
                            ->withOAuth2Credential($oAuth2Credential)
                            ->build();
        
        // 参数
        $conversionOptions = [
            'conversion_action' => ResourceNames::forConversionAction($customerId, $conversionActionId),
            'conversion_date_time' => $conversionDateTime,
            'currency_code' => $currencyCode,
        ];
        // 如果设置了转化价值
        if(!empty($conversionValue)){
            $conversionOptions['conversion_value'] = $conversionValue;
        }
        // 实例化转化
        $clickConversion = new ClickConversion($conversionOptions);
        // 设置广告追踪ID
        $clickConversion->setGclid($this->options['gclid']);
        // 设置consent
        $clickConversion->setConsent(new Consent(['ad_user_data' => 2]));
        
        try {
            // 获取上传转化请求客户端
            $conversionUploadServiceClient = $googleAdsClient->getConversionUploadServiceClient();
            // 发出请求以上传点击转化数据
            $response = $conversionUploadServiceClient->uploadClickConversions(
                // 上传点击转化数据 应始终将部分失败设置为true
                UploadClickConversionsRequest::build($customerId, [$clickConversion], true)
            );
            // 如果失败
            if ($response->hasPartialFailureError()) {
                // 返回
                return [null, new \Exception($response->getPartialFailureError()->getMessage())];
            }
            // 获取响应结果
            $uploadedClickConversion = $response->getResults()[0];
            // 返回
            return [$uploadedClickConversion, null];
        } catch (ApiException $apiException) {
            // 返回
            return [null, new \Exception($apiException->getBasicMessage())];
        }
    }

    /**
     * 深度回传
     * @access public
     * @return array
     */
    public function convertDeeply()
    {
        if(empty($this->options['customer_id'])){
            return [null, new \Exception('未填写谷歌账户ID')];
        }
        if(empty($this->options['deep_action_id'])){
            return [null, new \Exception('未填写深度转化操作ID参数')];
        }
        // 获取开发者令牌
        $developerToken = $this->options['developer_token'];
        // 获取jsonKey路径
        $jsonKeyFilePath = \think\facade\App::getRootPath() . ltrim($this->options['json_key_file_path'], DIRECTORY_SEPARATOR);
        // 当前客户ID
        $customerId = str_replace('-', '', $this->options['customer_id']);
        // 转化操作ID
        $conversionActionId = $this->options['deep_action_id'];
        // 转化价值
        $conversionValue = $this->options['conversion_value'];
        // 转化时间
        $conversionDateTime = $this->options['conversion_date_time'];
        // 币种
        $currencyCode = $this->options['currency_code'];
        // 构造授权实例
        $oAuth2Credential = (new OAuth2TokenBuilder())
                            ->withJsonKeyFilePath($jsonKeyFilePath)
                            ->withScopes('https://www.googleapis.com/auth/adwords')
                            ->build();

        // 构造客户端实例
        $googleAdsClient = (new GoogleAdsClientBuilder())
                            ->withDeveloperToken($developerToken)
                            ->withLoginCustomerId($customerId)
                            ->withOAuth2Credential($oAuth2Credential)
                            ->build();
        
        // 参数
        $conversionOptions = [
            'conversion_action' => ResourceNames::forConversionAction($customerId, $conversionActionId),
            'conversion_date_time' => $conversionDateTime,
            'currency_code' => $currencyCode,
        ];
        // 如果设置了转化价值
        if(!empty($conversionValue)){
            $conversionOptions['conversion_value'] = $conversionValue;
        }
        // 实例化转化
        $clickConversion = new ClickConversion($conversionOptions);
        // 设置广告追踪ID
        $clickConversion->setGclid($this->options['gclid']);
        // 设置consent
        $clickConversion->setConsent(new Consent(['ad_user_data' => 2]));
        
        try {
            // 获取上传转化请求客户端
            $conversionUploadServiceClient = $googleAdsClient->getConversionUploadServiceClient();
            // 发出请求以上传点击转化数据
            $response = $conversionUploadServiceClient->uploadClickConversions(
                // 上传点击转化数据 应始终将部分失败设置为true
                UploadClickConversionsRequest::build($customerId, [$clickConversion], true)
            );
            // 如果失败
            if ($response->hasPartialFailureError()) {
                // 返回
                return [null, new \Exception($response->getPartialFailureError()->getMessage())];
            }
            // 获取响应结果
            $uploadedClickConversion = $response->getResults()[0];
            // 返回
            return [$uploadedClickConversion, null];
        } catch (ApiException $apiException) {
            // 返回
            return [null, new \Exception($apiException->getBasicMessage())];
        }
    }
}