<?php


namespace app\web\controller;


use app\web\model\Ticket;
use think\Controller;

class Home extends Controller
{

    /**
     * 获取单个奖票
     * @param int id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getTicket(){
        $input=input('get.id');
        if(empty($input)){
            showJson('',1,'未选择卡券');
        }
        $ticket=new Ticket();
        $r=$ticket->getticket($input);
        $r['create_time']=date('Y-m-d H:i:s',$r['create_time']);
        showJson($r);
    }

    /**
     * 抽奖
     * @param int id
     * @param string code
     */
    public function draw(){
        $input=input('post.');
        $ticket=new Ticket();
        if(isset($input['code'])&&!empty($input['code'])){
            $code=$input['code'];
            $data['is_use']=1;
            $data['use_time']=time();
            $data['number']=$input['number'];
            $r=$ticket->mod($data,['code'=>$code]);
            if($r==false){
                showJson('',1,'没有中奖,请重新扫码抽奖！');
            }
            showJson($r);
        }else{
            showJson('',1,'已抽完');
        }
    }

    /**
     * 跳转抽奖首页
     */
    public function getIndex(){
        $code=input('code')?:1;
        $url='/static/web/index.html?code='.$code;
        showJson($url);
    }

    /**
     * 跳转抽奖界面
     */
    public function getDraw(){
        $code=input('post.code');
        $url='/static/web/draw.html?code='.$code;
        showJson($url);
    }

    /**
     * 跳转规则界面
     */
    public function getRule(){
        $code=input('post.code');
        $url='/static/web/rule.html?code='.$code;
        showJson($url);
    }

    /**
     * 查询是否抽过奖
     */
    public function checkDraw(){
        $code=input('post.code');
        if(empty($code)){
            showJson('',1,'已抽完');
        }
        $t=new Ticket();
        $r=$t->getByWhere(['code'=>$code]);
        if(isset($r[0]['is_use'])&&$r[0]['is_use']==0){
            showJson();
        }else{
            showJson($r[0]['number'],1,'已抽完');
        }
    }


}