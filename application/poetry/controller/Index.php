<?php
/**
 * Created by PhpStorm.
 * User: shen
 * Date: 2020/1/7
 * Time: 21:29
 */

namespace app\poetry\controller;


use app\poetry\model\Poetry;

class Index
{
    /**
     * 获取小程序首页诗
     */
    public function getPoetry(){
        $input=input('');
        $p=new Poetry();
        $list=$p->getList($input,'all');
        $max=count($list);
        $id=rand(1,$max);
        showJson($list[$id]);
    }

    /**
     * 获取作者列表
     */
    public function getAuthor(){
        $p=new Poetry();
        $list=$p->getAuthor();
        showJson($list);
    }

    /**
     * 保存用户创作
     */
    public function postPoetry(){
        $input=input('');
        if(empty($input['title'])||empty($input['content'])){
            showJson('',1,'请输入完标题和内容');
        }
        $input['create_time']=time();
        $p=new Poetry();
        $r=$p->add($input);
        showJson($r);
    }

    /**
     * 小程序授权登录
     */
    public function login(){
        $appid='wx6e1d2d004ac78b0d';
        $secret='fa191ec936903bb25e56c669878de434';
        $js_code=input('code');
        $url='https://api.weixin.qq.com/sns/jscode2session?appid='.$appid.'&secret='.$secret.'&js_code='.$js_code.'&grant_type=authorization_code';
        $res=file_get_contents($url);
        showJson($res);
    }

    /**
     * 小程序我的界面用户统计信息
     */



}