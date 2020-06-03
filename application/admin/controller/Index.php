<?php


namespace app\admin\controller;


use app\web\model\Ticket;

class Index
{
    public function test(){
        echo LOG_AUTH;
    }
    public function _empty(){
        echo '地址弄错了';
    }
    /**
     * 下载奖券
     */
    public function downTicket(){
        $t=new Ticket();
        $ts=$t->getByWhere(['is_use'=>0]);
        $photo_arr=[];
        foreach ($ts as $k=>&$v){
            $photo_arr[$k]['cand_face']='.'.$v['image_url'];
        }
        zipDown('奖券',$photo_arr);
        showJson();
    }
    /**
     * 批量生成奖券
     */
    public function postMuchTicket(){
        $number=input('post.number');
        $req_url=
        $data=[];
        if(!is_int((int)$number)){
            showJson('',1,'张数要输入整数字');
        }else if($number>20){
            showJson('',1,'每次输入张数不要超过20');
        }
        $s=0;
        for ($i=0;$i<(int)$number;$i++){
            $data[$i]['code']='E'.getRandomStr(8,1);
            $data[$i]['create_time']=time();
            $code=getRandomStr(5);
            $r=$this->qrMade($data[$i]['code']);
            if($r!=false){
                $data[$i]['image_url']=$r['dir'];
                $data[$i]['req_url']=$r['req_url'];
            }else{
                unset($data[$i]);
                continue;
            }
            $s++;
        }
        $ticket=new Ticket();
        $r=$ticket->add($data,true);
        if($r==false){
            showJson('',1,$ticket->getError());
        }
        showJson(['fum'=>$r,'sum'=>$s]);
    }
    /**
     * 查看奖券列表
     */
    public function getList(){
        $input=input('get.');
        $ticket=new Ticket();
        $r=$ticket->getList($input);
        $count=$ticket->getCount($input);
        foreach($r as $k=>&$v){
            $v['create_time']=date('Y-m-d H:i:s',$v['create_time']);
        }
        showJson(['list'=>$r,'count'=>$count]);
    }
    /**
     * 根据code生成二维码
     */
    private function qrMade($code){
        if(empty($code)){
            return false;
        }
        $content=$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME'].'/static/web/index.html?code='.$code;
        $merge_image = [
            'bg_image' => ['file'=>'./static/back.png','bg_x'=>48,'bg_y'=>143],
            //写一行文字
            'bg_text'  => [
                ['font'=>'./static/Font/fz.ttf','text'=>$code,'size'=>'14','color_rgb'=>[0,0,0],'pos'=>[48,261]],
            ]
        ];
        $dir=ROOT_PATH.DS.'public'.DS.'static'.DS.'htmladmin'.DS.'image'.DS.date('Ym',time()).DS;
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        $outfile=$dir.$code.'.png';
        qrcode($content,$outfile,0,3,0,false,$merge_image);
        $image_url='/static/htmladmin/image/'.date('Ym',time()).'/'.$code.'.png';
        if(!file_exists($outfile)){
            return false;
        }
        return ['dir'=>$image_url,'req_url'=>$content,'image_name'=>$code.'.png'];
    }
}