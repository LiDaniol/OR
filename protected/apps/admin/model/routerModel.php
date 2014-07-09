<?php
class routerModel extends baseModel
{
	protected $table = 'orders';

	public function getList($condition=1,$start=0,$length=20,$orderField="id",$orderDirection="desc")
    {
        $query = "SELECT *,(select sum(price) from extras where order_id=a.id ) as other_price, ";
        $query.= " (select user_realname from admin_users where user_id=a.userid) as user_name,";
        $query.= " (select customer_name from customer where id=a.customer) as customer";
        $query.= " FROM ".$this->table." as a WHERE $condition ";
        $query.= " ORDER BY $orderField $orderDirection ";
        $query.= " limit $start,$length";
		return $this->model->query($query); 
    } 
    public function getInfo($condition)
    {
    	$query = "SELECT id,customer FROM ".$this->table." WHERE $condition ";
    	return $this->model->query($query);
    }
    public function getOrderNo($order_id)
    {
    	$query = "SELECT orderno FROM ".$this->table." WHERE id=".$order_id;
    	$info = $this->model->query($query);
    	if( !empty( $info ))
    	{
    		return $info[0]['orderno'];
    	}
    }
    /**
     * @desc 获取安装路由的所有商家id
     * @author daniol
     * @param string
     * @return array
     * */
    public function getAllNodeId($condition)
    {
    	$query = "SELECT id FROM ".$this->table." where $condition";
    	$res=$this->model->query($query);
    	if(!empty($res)){
    		$arr_id="";
    		foreach ($res as $value){
    			$arr_id[]=$value['id'];
    		}
    		return $arr_id;
    	}else{
    		return array();
    	}
    
    }
    /**
     * @desc 获取网关id
     * @author daniol
     * @param string
     * @return array
     * */
    public function getAllGwId($condition)
    {
    	$query = "SELECT gw_id FROM ".$this->table." where $condition";
    	$res=$this->model->query($query);
    	if(!empty($res)){
    		$arr_id="";
    		foreach ($res as $value){
    			$arr_id[]=$value['gw_id'];
    		}
    		return $arr_id;
    	}else{
    		return array();
    	}
    
    }
    /**
     * @desc 查询标签下商家的数量
     * */
    public function checkRouterTag($tag_id)
    {
    	$query = "SELECT count(*) FROM ".$this->table." WHERE tag_id=$tag_id ";
    	$result = $this->model->query($query);
    	return $result[0]['count(*)'];
    }
    /**
     * @desc 查询标签下商家的数量
     * */
    public function getRouterTagNums()
    {
    	$query = "SELECT count(tag_id) num,tag_id FROM ".$this->table." GROUP BY tag_id ";
    	$result = $this->model->query($query);
    	$return_arr = '';
    	foreach ($result as $v){
    		$return_arr[$v['tag_id']] = $v['num'] ;
    	}
    	return $return_arr;
    }  
    
}
?>