<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
use \think\Exception;
// 应用公共文件
/**
 * @todo 将二维数组变为一维键值对
 * @author lhw 2019-10-16
 * @param array $array 二维数据
 * @param string $column_key 键值
 * @param string $index_key 键名
 * @return array
 */
function arrayColumn($array, $column_key, $index_key = null)
{
    if (function_exists('array_column')) {
        $arr = array_column($array, $column_key, $index_key);
    } else {
        $arr = [];
        foreach ($array as $v) {
            if ($index_key) {
                $arr[$v[$index_key]] = $v[$column_key];
            } else {
                $arr[] = $v[$column_key];
            }
        }
    }
    return $arr;
}

/**
 * @todo 将二维数组key替换
 * @author lhw 2019-10-16
 * @param array $array 二维数据
 * @param string $index_key 键名
 * @return array
 */
function arrayKeyReplace($array, $index_key)
{
    $arr = [];
    foreach ($array as $v) {
        $arr[$v[$index_key]] = $v;
    }

    return $arr;
}

/**
 * @todo 输出json，并结束执行
 * @author lhw 2019-10-16
 * @param mixed $result 返回给客户端的数据
 * @param int $code 代码 0 成功 1 失败 2 未登录，注：更多自行定义
 * @param string $message 错误时的提示
 * @return void
 */
function showJson($result = null, $code = 0, $message = '')
{
    header('Content-Type:application/json; charset=utf-8');
    $r = ['code' => $code, 'message' => $message, 'response' => $result];

    exit(json_encode($r, 256));
}

/**
 * @todo 获取limit数据
 * @author lhw 2019-10-16
 * @param array $args 搜索参数
 * @return string
 */
function getLimit(array $args)
{
    $offset = isset($args['page']) && $args['page'] > 0 ? (int)$args['page'] : 1;
    $length = isset($args['limit']) && $args['limit'] > 0 ? (int)$args['limit'] : 10;

    if ($length > 200) {
        $length = 10;
    }

    $offset = ($offset - 1) * $length;

    return $offset.','.$length;
}

/**
 * @todo 获取随机字符串
 * @author lhw 2019-10-16
 * @param number $length
 * @param int $type 随机字符串类型 0大小字母+数字 1数字 2小写字母 3大写字母 4数字+小写字母 5数字+大写字母 6大小写字母
 * @return string|mixed
 */
function getRandomStr($length = 5, $type = 0)
{
    switch ($type) {
        case 1:
            $arr = range(0, 9);
            break;
        case 2:
            $arr = range('a', 'z');
            break;
        case 3:
            $arr = range('A', 'Z');
            break;
        case 4:
            $arr = array_merge(range(0, 9), range('a', 'z'));
            break;
        case 5:
            $arr = array_merge(range(0, 9), range('A', 'Z'));
            break;
        case 6:
            $arr = array_merge(range('a', 'z'), range('A', 'Z'));
            break;
        default:
            $arr = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
            break;
    }
    $len = count($arr) - 1;
    $str = '';
    for ($i = 0; $i < $length; $i ++) {
        $str .= $arr[mt_rand(0, $len)];
    }

    return $str;
}

/**
 * @todo 递归过滤自定义函数，默认删除空格
 * @author lhw
 * @param string|array $data 过滤的字符串或数组
 * @param string $filter_list 过滤函数名，多个以“｜”符号分隔，格式：trim或trim|addslashes
 * @return mixed
 */
function filter($data, $filter_list = 'trim')
{
    $filter_arr = explode('|', $filter_list);

    if (is_string($data)) {
        foreach ($filter_arr as $filter) {
            $result = call_user_func($filter, $data);
            $data   = $result;
        }
    } else if (is_array($data)) {
        $result = array();
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $result[$k] = filter($v, $filter_list);
            } else {
                foreach ($filter_arr as $filter) {
                    $result[$k] = call_user_func($filter, $v);
                    $v = $result[$k];
                }
            }
        }
        $data   = $result;
    }

    return $data;
}

/**
 * @todo 读取Excel文件，支持xls（03版excel格式）与xlsx（07版excel格式），自动根据上传文件后缀判断，目前只支持 A-Z
 * @param string $field_name    上传文件字段名称
 * @param array $fields         读取字段名称数组，如果指定有数字key值，则以key值读取列，默认从0开始，如：['area_id','area_name'] 或[3=>'area_id',5=>'area_name']
 * @param number $start_row     起始行
 * @param number $end_row       结束行，如果大于能读取的行业，以能读取的为准，否只读取指定行数
 * @return array;
 */
function readExcel($field_name, $fields, $start_row = 2, $end_row = 1000)
{
    if (!isset($_FILES[$field_name]['error']) || $_FILES[$field_name]['error'] !== 0) {
        throw new Exception('上传文件不存在');
    }
    $ext = substr($_FILES[$field_name]['name'], strrpos($_FILES[$field_name]['name'], '.'));
    if (!in_array($ext, array('.xls','.xlsx'))) {
        throw new Exception('上传文件格式不正确，只能为：.xls或.xlsx');
    }

    vendor('phpexcel.PHPExcel');
    if ($ext == '.xls') {
        $objReader = \PHPExcel_IOFactory::createReader('Excel5');
    } else {
        $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
    }
    $objPHPExcel = $objReader->load($_FILES[$field_name]['tmp_name']);
    $sheet = $objPHPExcel->getSheet(0);
    $rows = $sheet->getHighestRow();        // 取得总行数
    if ($rows > $end_row) {
        $rows = $end_row;                   // 赋值给定行数
    }
    //$cols = $sheet->getHighestColumn();     // 取得总列数
    $arr = array();
    $cols_range = range('A', 'Z');
    for ($i = $start_row; $i <= $rows; $i ++) {
        foreach ($fields as $k => $v) {
            $arr[$i][$v] = trim($sheet->getCell($cols_range[$k].$i)->getValue());
        }
    }

    return $arr;
}

/**
 * 保存base64提交的图片文件
 * @author lhw
 * @param string $base64_image    图片的编码字符串
 * @param bool $domain          是返回带域名url地址
 * @param array $config         默认配制，dir：保存的目录名称，相对路径或绝对路径；url：相对于域名访问的路径地址；ext：图片扩展名称；size：图片文件大小
 * @throws Exception
 * @return void|array           返回保存后带目录的文件名称和访问url地址
 */
function saveBase64Image($base64_image, $domain = false, $config = [])
{
    //默认配制
    $conf = ['dir'=>'./upload/image/','url'=>'/upload/image/','ext'=>['png','jpg','jpeg','gif'],'size'=>2000000];
    if (!empty($config)) {
        //合并配制
        $conf = array_merge($conf, $config);
    }
    //$base64_image为图片的编码字符串
    if (empty($base64_image)) {
        return ;
    }

    preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image, $result);
    try{
        if (count($result) != 3) {
            throw new \think\Exception('图片数据不正确。');
        }
        $file_ext = strtolower($result[2]);
        if (!in_array($file_ext, $conf['ext'])) {
            throw new \think\Exception('上传图片文件扩展不支持。');
        }
        if (strlen($base64_image) > $conf['size']) {
            throw new \think\Exception('上传图片文件太大了。');
        }

        $date           = date('Ymd');
        $pathname       = $conf['dir'].'/'.$date;

        if (!is_dir($pathname) && !mkdir($pathname, 0777, true)) {
            throw new \think\Exception('保存的图片目录没有权限。');
        }
        $name       = md5($base64_image).'.'.$file_ext;      //图片文件名加上图片扩展
        $savepath   = $pathname.'/'.$name;                  //图片保存目录
        //对图片进行解析并保存
        if (!file_put_contents($savepath, base64_decode(str_replace($result[1], '', $base64_image)))) {
            throw new \think\Exception('保存图片文件失败。');
        }

        $server_name = '';
        if ($domain) {
            $server_name = '//'.$_SERVER['SERVER_NAME'];
            if (is_ssl() || (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'https://') === 0)) {
                $server_name = 'https:'.$server_name;
            }
        }
        $saveurl = $server_name.$conf['url'].'/'.$date.'/'.$name;
        return ['dir'=>$savepath, 'url'=>$saveurl];
    }catch (Exception $e){
        showJson($e->getMessage());
    }
}

/**
 * 上传文件
 * @author sxt
 * @param string $field_name 上传的参数名
 * @param string $save_dir 保存目录
 * @param array $config 配置 大小 和 扩展名
 * @return array|string
 */
function upload($field_name='image',$save_dir='/upload/image',$config=['size'=>1048576,'ext'=>'jpg,png,gif']){
    // 获取表单上传文件 例如上传了001.jpg
    $file = request()->file($field_name);
    if(empty($file)){
        return '未选择上传文件！';
    }
    // 移动到框架应用根目录/public/uploads/ 目录下
    $info = $file->validate($config)->move(ROOT_PATH . 'public' .$save_dir);
    if($info){
        // 成功上传后 获取上传信息
        return ['save_path'=>$save_dir.'/'.str_replace('\\','/',$info->getSaveName()),'ext'=>$info->getExtension(),'filename'=>$info->getFilename()];
    }else{
        // 上传失败获取错误信息
        return $file->getError();
    }
}

/**
 *导出Excel
 * @param $list 需导出数据源
 * @param $colums 标题
 * 格式 ['index'=>'序号','name'=>'原料名称'] 第一种
 * 格式 ['index'=>['name'=>'序号','width'=>10],'name'=>['name'=>'原料名称','width'=>25]] width 不传 为自己适应 第二种
 * @param $name 文件名称
 * @param $title 大标题
 * @param $sheet_size sheet条数
 * @param $colums
 * zxj 2019-10-31
 * **/
function exportExcel($list,$colums=[],$name="",$title="",$sheet_size=5000){
    vendor('PHPExcel');
    $xls_title=$title;
    $file_name=$name.'_'.date('YmdHis');
    $excel = new \PHPExcel();
    //横向单元格标识
    $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q',
        'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI',
        'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');
    //设置大标题
    $colums_cout = count($colums);
    $count = count($list);
    $sheets = ceil($count/$sheet_size);
    for($sheet = 0;$sheet<$sheets;$sheet++){
        if($sheet>0){
            $excel->createSheet();
        }
        $excel->setActiveSheetIndex($sheet)->getStyle ( 'A:'.$cellName[$colums_cout-1] )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );  // 设置单元格水平对齐格式
        $excel->setActiveSheetIndex($sheet)->getStyle ( 'A:'.$cellName[$colums_cout-1] )->getAlignment ()->setVertical ( \PHPExcel_Style_Alignment::VERTICAL_CENTER );        // 设置单元格垂直对齐格式
        if($title != ''){
            $excel->getActiveSheet($sheet)->setCellValue('A1', $xls_title)->mergeCells('A1:'.$cellName[$colums_cout-1].'1')->getStyle()->getFont()->setSize(16);
            $excel->getActiveSheet($sheet)->getRowDimension('1')->setRowHeight(30);
        }

        $index = 0;
        foreach ($colums as $k => &$v){
            if(is_array($v)){
                if(isset($v['width']))
                    $excel->getActiveSheet($sheet)->getColumnDimension($cellName[$index])->setWidth($v['width']);
                else
                    $excel->getActiveSheet($sheet)->getColumnDimension($cellName[$index])->setAutoSize(true);
                //设置表头
                $excel->getActiveSheet($sheet)->setCellValue($cellName[$index].'2', $v['name']);
            }else{
                $excel->getActiveSheet($sheet)->setCellValue($cellName[$index].'2', $v);
            }
            $index++;
        }
        //表头加粗
        $excel->getActiveSheet($sheet)->getStyle('A1:'.$cellName[$colums_cout-1].'2')->getFont()->setBold(true);
        //加边框样式
        $styleThinBlackBorderOutline = ['borders' => ['allborders' =>['style' => \PHPExcel_Style_Border::BORDER_THIN ]]];
        unset($i,$index);
        $index = 1;
        for($t=$sheet*$sheet_size;$t<count($list);$t++){
            $i = $index + 2;
            $y = 0;
            if($t == ($sheet+1)*$sheet_size){
                break;
            }
            foreach ($colums as $ck => &$cv) {
                $excel->getActiveSheet($sheet)->setCellValue( $cellName[$y]. $i, $list[$t][$ck]);
//                $s = strlen($list[$t][$ck]);
//                if(intval($cv['width']) < intval($s))
//                    $excel->getActiveSheet($sheet)->getColumnDimension($cellName[$y])->setWidth($s);
                $y++;
            }
            $index++;
        }
        $excel->getActiveSheet($sheet)->getStyle( 'A2:'.$cellName[$colums_cout-1].($index+1))->applyFromArray($styleThinBlackBorderOutline);
        $excel->getActiveSheet($sheet)->getStyle('A2:'.$cellName[$colums_cout-1].($index+1))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    }
    header('Content-type:application/vnd.ms-excel;charset=utf-8');
    header("Content-Disposition:attachment;filename=$file_name.xls");//attachment新窗口打印inline本窗口打印
    header('Cache-Control: max-age=0');
    $obj_writer=\PHPExcel_IOFactory::createWriter($excel,'Excel5');
    $obj_writer->save('php://output');
    exit;
}

/**
 * @todo 生成或直接输出二维码图片，图片大小不固定的
 * @author lhw 2019-11-13
 * @param string $text  二维码内容
 * @param false|string $outfile  是否保存图片，默认false，直接输出；保存图片需要传入绝对文件路径，目录必须存在，格式：Windows D:\qrcode\qrcode.png 或 linux /web/public/qrcode.png
 * @param number $level 容错级别，默认 0 范围值：0，1，2，3
 * @param number $size  二维码尺寸，默认6
 * @param number $margin 二维码补白，默认0无
 * @param boolean $saveandprint 是否保存图片并输出，默认false
 * @param array $merge_image    合并图片使用
 * @return null
 */
function qrcode($text, $outfile = false, $level = 0, $size = 6, $margin = 0, $saveandprint = false, $merge_image = [])
{
    vendor('qrcode.qrcode');

    \QRcode::png($text, $outfile, $level, $size, $margin, $saveandprint, $merge_image);
}

/**
 * @param $filePath //下载文件的路径
 * @param int $readBuffer //分段下载 每次下载的字节数 默认1024bytes
 * @param array $allowExt //允许下载的文件类型
 * @return void
 */
function downloadFile($filePath, $readBuffer = 1024, $allowExt = ['jpeg', 'jpg', 'peg', 'gif', 'zip', 'rar', 'txt'])
{
    //检测下载文件是否存在 并且可读
    if (!is_file($filePath) && !is_readable($filePath)) {
        return false;
    }
    //检测文件类型是否允许下载
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowExt)) {
        return false;
    }
    //设置头信息
    //声明浏览器输出的是字节流
    header('Content-Type: application/octet-stream');
    //声明浏览器返回大小是按字节进行计算
    header('Accept-Ranges:bytes');
    //告诉浏览器文件的总大小
    $fileSize = filesize($filePath);//坑 filesize 如果超过2G 低版本php会返回负数
    header('Content-Length:' . $fileSize); //注意是'Content-Length:' 非Accept-Length
    //声明下载文件的名称
    header('Content-Disposition:attachment;filename=' . basename($filePath));//声明作为附件处理和下载后文件的名称
    //获取文件内容
    $handle = fopen($filePath, 'rb');//二进制文件用‘rb’模式读取
    while (!feof($handle) ) { //循环到文件末尾 规定每次读取（向浏览器输出为$readBuffer设置的字节数）
        echo fread($handle, $readBuffer);
    }
    fclose($handle);//关闭文件句柄
    exit;
}

/**
 * @param  string $card 导出压缩包的文件名
 * @param  array $cand_photo 图片数组：[['cand_face'=>'./static/htmladmin/image/201911/E22297291.png'],['cand_face'=>'./static/htmladmin/image/201911/E25730693.png']]
 * @return bool
 */
function zipDown($card,$cand_photo)
{
    $file_template = './static/downzip/cand_picture.zip';//在此之前你的项目目录中必须新建一个空的zip包（必须存在）
    if(!file_exists($file_template)){
        return false;
    }
    $downname = $card . '.zip';//你即将打包的zip文件名称
    $file_name =  './static/downzip/' . $card . '.zip';//把你打包后zip所存放的目录
    $result = copy($file_template, $file_name);//把原来项目目录存在的zip复制一份新的到另外一个目录并重命名（可以在原来的目录）
    $zip = new ZipArchive();//新建一个对象
    if ($zip->open($file_name, ZipArchive::CREATE) === TRUE) { //打开你复制过后空的zip包
        $zip->addEmptyDir($card);//在zip压缩包中建一个空文件夹，成功时返回 TRUE， 或者在失败时返回 FALSE
    //下面是我的场景业务处理，可根据自己的场景需要去处理（我的是将所有的图片打包）
    $i = 1;
    foreach ($cand_photo as $key3 => $value3) {
        $file_ext = explode('.', $value3['cand_face']);//获取到图片的后缀名
        $zip->addFromString($card . '/' . $card . '_' . $i . '.' . $file_ext[2], file_get_contents($value3['cand_face']));//（图片的重命名，获取到图片的二进制流）
        $i++;
    }
    $zip->close();
    $fp = fopen($file_name, "r");
    $file_size = filesize($file_name);//获取文件的字节
    //下载文件需要用到的头
    Header("Content-type: application/octet-stream");
    Header("Accept-Ranges: bytes");
    Header("Accept-Length:" . $file_size);
    Header("Content-Disposition: attachment; filename=$downname");
    $buffer = 1024; //设置一次读取的字节数，每读取一次，就输出数据（即返回给浏览器）
    $file_count = 0; //读取的总字节数
    //向浏览器返回数据 如果下载完成就停止输出，如果未下载完成就一直在输出。根据文件的字节大小判断是否下载完成
    while (!feof($fp) && $file_count < $file_size) {
        $file_con = fread($fp, $buffer);
        $file_count += $buffer;
        echo $file_con;
    }
    fclose($fp);
    //下载完成后删除压缩包，临时文件夹
    if ($file_count >= $file_size) {
        unlink($file_name);
        }
    }
    return true;
}