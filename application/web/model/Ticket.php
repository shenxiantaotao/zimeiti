<?php


namespace app\web\model;


use think\Db;
use think\Exception;
use think\Model;

class Ticket extends Model
{
    /**
     * 根据id获取单个信息
     * @author sxt 2019/10/21
     * @param int $id
     * @param string $field
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getticket(int $id, string $field = '*')
    {
        $r = Db::name('ticket')->field($field)->where(['id'=>$id])->find();
        return $r;
    }

    /**
     * 根据数组条件查询信息
     * @author sxt 2019/10/21
     * @param array $where
     * @param string $field
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getByWhere(array $where, string $field = '*')
    {
        $r = Db::name('ticket')->field($field)->where($where)->select();
        return $r;
    }

    /**
     * 获取列表信息
     * @author sxt 2019/10/21
     * @param array $args
     * @param string $field
     * @param string $order_by
     * @param bool $return_subitem
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList(array $args, string $field = '*', string $order_by='create_time DESC')
    {
        $where = $this->getListWhere($args);
        $p = getLimit($args);
        $r = Db::name('ticket')->field($field)->where($where)->limit($p)->order($order_by)->select();
        return $r;
    }

    /**
     * 获取总数
     * @author sxt 2019/10/21
     * @param array $args
     * @return int|string
     * @throws Exception
     */
    public function getCount(array $args)
    {
        $where = $this->getListWhere($args);
        $r = Db::name('ticket')->where($where)->count();

        return $r;
    }

    /**
     * @todo 组合搜索条件
     * @author sxt 2019-10-18
     * @param array $args 搜索条件参数
     * @return string
     */
    private function getListWhere(array $args)
    {
        $where = ' 1 ';
        if (isset($args['id']) && $args['id']!='') {
            $where .= "AND id = {$args['id']}";
        }
        if (isset($args['is_use']) && $args['is_use']!='') {
            $where .= "AND is_use = {$args['is_use']}";
        }
        if (isset($args['start_time']) && $args['start_time']!='' && isset($args['end_time']) && $args['end_time']!='') {
            $where .= "AND create_time >= '{$args['start_time']}' AND create_time <= '{$args['end_time']}'";
        }
        if (isset($args['code']) && $args['code']!='') {
            $where .= "AND code = '{$args['code']}'";
        }

        return $where;
    }

    /**
     * @todo 添加数据
     * @author sxt 2019-10-17
     * @param array $data
     * @return mixed
     */
    public function add(array $data,$all=false)
    {
        Db::startTrans();
        try {
            //数据判断代码...
            if($all==false){
                if ($data['code'] == '') {
                    throw new Exception('编码不能为空');
                }
                $id = Db::name('ticket')->insertGetId($data);
            }else{
                $id=Db::name('ticket')->insertAll($data);
            }
            //结果判断代码...
            if (!$id) {
                throw new Exception('保存数据失败');
            }
            //更多子项添加、逻辑等
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
        return $id;
    }

    /**
     * @todo 修改数据
     * @author sxt 2019-10-17
     * @param array $data
     * @return mixed
     */
    public function mod($data, $where = [])
    {
        try {
            Db::startTrans();
            //数据判断代码...
            if (empty($data)) {
                throw new Exception('没有更新数据');
            }

            $r = Db::name('ticket')->where($where)->update($data);

            //结果判断代码...
            if (!$r) {
                throw new Exception('保存数据失败');
            }
            //更多子项添加、逻辑等
            Db::commit();
            return $r;
        } catch (Exception $e) {
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * @todo 删除数据
     * @author sxt 2019/10/23
     * @param $id
     * @return bool|int
     * @throws Exception
     * @throws \think\exception\PDOException
     */
    public function del($id){
        if($id){
            $r = Db::name('ticket')->where('id',$id)->delete();
            return $r;
        }else{
            return false;
        }
    }

}