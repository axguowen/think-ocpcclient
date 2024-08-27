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

return [
    // 默认平台
    'default' => 'baidu',
    // 平台配置
    'platforms' => [
        // 百度平台
        'baidu' => [
            // 驱动类型
            'type'          => 'Baidu',
            // 令牌
            'token'         => '',
            // 页面url
            'logidUrl'      => '',
            // 转化类型
            'newType'       => '',
            // 深度转化类型
            'deepType'      => '',
            // 转化时间
            'convertTime'   => '',
        ],
        // 其它
        'other' => [
            // 驱动类型
            'type'          => 'OceanEngine',
        ],
    ]
];
