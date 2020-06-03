<?php


namespace app\web\controller;


use app\web\model\Wechat;

class Test
{
    public function index(){
        $option=array(
            'token'=>'shen',
            'encodingaeskey'=>'USaT1Q3nax7qxzmNyMcQHfjYcffhG6KcId21ytxiCoU',
            'appid'=>'wxa965eeefe9047eaa',
            'appsecret'=>'a091d8ced6779bf65a195a7c0a583912',
            'debug'=>false,
            'logcallback'=>false
        );
        $w=new Wechat($option);
        return $w->valid();
    }

}