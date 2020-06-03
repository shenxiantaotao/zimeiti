<?php
/**
 * Created by PhpStorm.
 * User: shen
 * Date: 2020/1/7
 * Time: 21:22
 */

namespace app\poetry\model;


class Poetry
{
    public function getList($search = [], $page = 1, $page_size = 10, $order_by = '')
    {
        $where = [];
        if (isset($search['author'])&&!empty($search['author'])) {
            $where['author'] = $search['author'];
        }
        if (isset($search['title'])&&!empty($search['title'])) {
            $where['title'] = array('like', '%' . $search['title'] . '%');
        }
        if (isset($search['kind'])&&!empty($search['kind'])) {
            $where['kind'] = $search['kind'];
        }
        if (isset($search['status'])&&!empty($search['status'])) {
            $where['status'] = $search['status'];
        }
        if (isset($search['user_id'])&&!empty($search['user_id'])) {
            $where['user_id'] = $search['user_id'];
        }
        if ($page == 'all') {
            return db('poetry')->where($where)->order($order_by)->select();
        } else {
            $count = db('poetry')->where($where)->count();
            $list = db('poetry')->where($where)->order($order_by)->page($page, $page_size)->select();
            return array(
                'count' => $count,
                'list' => $list
            );
        }
    }

    public function getById($id)
    {
        if ($id) {
            return db('poetry')->where('id', $id)->find();
        } else {
            return false;
        }
    }

    public function add($data, $all = false)
    {
        if ($all == false) {
            return db('poetry')->insertGetId($data);
        } else {
            return db('poetry')->insertAll($data);
        }
    }

    public function update($data, $where)
    {
        return db('poetry')->where($where)->update($data);
    }

    public function delete($id)
    {
        if ($id) {
            return db('poetry')->delete($id);
        } else {
            return false;
        }
    }

    public function getAuthor(){
        return db('poetry')->distinct('author')->field('author')->select();
    }

}