<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/1 0001
 * Time: 17:12
 */
$config = array (
    //支付宝公钥
    'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqOkIXHphgimE8S82vCLtF1xctwM7UuUOK4tiqaErjIvyQ95c3DHZihoOw+HMQ2gHKsRS0gavbzpusHMMgdRnY5BhilbddoEkVSMKy8/pYWTowtmoZT6YozD+FdN3aEQAkb4UTc35zYD85JqHqpSDX8RK4rZ0vMoVLB5Cignf46gEil9J5a0vFr3uXGd8y1UnVznC8C4QlNaATbwKDxg8EpPI88KpnFCxGyT828PtUgTIbpRIuJQ0Taaxznkdlip+OOmn3TpSAyTr+J+SconOiBc+IlcJTcCpKC9BOjXuHUowCKaU8K8Pai6rOU6KUnuUOhoMUfjPyl8l+Wwh2yTG7QIDAQAB",

    //商户私钥
    'merchant_private_key' => "MIIEpgIBAAKCAQEAvTOuVgIXfjeNRVnQpjqR5s538ioZyuO8etmK4Ax4iZYuFztwU9By9arJBkJRu9ESJYqyAzz8taWte35EoS+bpil6889k1ktklZo98nHqA61y7jB7/fDn9TUn0+HH0GISos2LfUgLNiissYurnI+KTj06kg/rmD2YU40Z497Hdptr5tyKYmhc9W572f+531SLdGDlHPfH3vjlxCZE+XGtaKE5B+/I2sGc0z6T5o3eAozQd1RXq+wDSRuSpV1wz0v76I7u/yQ4dEao9zXQyty/0lDpsglcpnt1TTMTq2mdqUScemF6jd0MJys/w60+ouejHwvo9KfddC3YEpYa2T8oQQIDAQABAoIBAQCMs39FZN7VtTgwx78br1jLPOYER/zQXhXPgjH9tih5oR57lm9NVSn4ud4u8mjX9H27P25sbBE+gIwH13nwKHhm1FgkMio6Fu0hOAgzYTV3MKjUq2e6DEpjlvkcX755oEVdnt/J9iaoSw1KJ6Uik7h1wKDq+D3rBHtgPwFu7UCGWXxEymOg1HLv0YSJjkeG/7tOjJou2Pnm8TuDn8zBP99F3zZyjIPcdFSJObwv/mdK4kp4MZl5OAVt7ixzQzfEXw3thn9vwISv+yyGAe/rqzQuaX/ykW/dBcb05LxLPnqKSCJU/zPVCYOVntdGwsja2cLXjG3/TBLSH1pWdgrGsOBVAoGBAO4pxPuXbtcQZ71J7F8dfu2tZIDkNjuDQJzvA1MBIniKAsPsJE40AORIGockNmfjkG2qLeB4bpEuumNV0f2x/9pIyE5ak4Gz2nRqnt6+7fgqKMAQxURzDqXG64901C2bl6SjqPE5+zGpTgrilwmU5itt5wMvVW6R/VxghSC+2dUrAoGBAMtfMQ1W386fMzYz6GHDRbZTrJrKGimPnZvmNMFLB9XoXuNHk35tn6ZoiOOAMQapsL7WGbgX/rCYLveX3YsvNGidyGs7dD9NktCpyBhgpGBewJf0+dnRTot3nZCewuqkTEy+D9uhdRExQGC0WBVJ1J8rZMXF2377ZA6OahO0whpDAoGBAL3Iiy/xfGHksMYmIjWpS3war/wF3zGNZe+ohv2d6dokIBAAHO40goFm87y9Hp2quWyqL+SwE2ud0OlXef6v2TIYzYWip+izaWFblT3q57bZ4Z8rvgbNqN5xpUSr1GiDlFOeFwIjMghg4c/KwJDiFoiFC5F+JpX1v+/c+jkd3HgBAoGBAK3WzMMuQ1kHVGs8q6MFnA1iMOSPZRWpoXulp+qiDCyY2KNGh5lGc3V3Xau55C5h1qcJtCpcuGSTcXJK6iETVKUoWizvQUrrMFgVCOltkpSd6dr6mKfL9mvY72KzksGnd2ESBdQji0IK65C+F5z64zi7iwRTQHCmyrx2l84IcPKpAoGBAJ8FzvaMfqEqTm9+iswbrjx73nfBg95Sh+w6VMBFvG0YGPYan4IEUDRN7s44cUAS1EPC6SvJ7w6HwtrQYINFcfo5HzcI+/lbZd55mba0LZqZzKsRrgm6M5XRNT1uPyjoIf/vZeHPcE2UpCW9Li/R9+imYQuC7Ki9h2G681R5NWNe",

    //编码格式
    'charset' => "UTF-8",

    //支付宝网关
    'gatewayUrl' => "https://openapi.alipay.com/gateway.do",

    //应用ID
    'app_id' => "2016080200146959",

    //异步通知地址,只有扫码支付预下单可用
    'notify_url' => "https://weapp.xiguawenhua.com/index/Index/index1",

    //最大查询重试次数
    'MaxQueryRetry' => "10",

    //查询间隔
    'QueryDuration' => "3"
);