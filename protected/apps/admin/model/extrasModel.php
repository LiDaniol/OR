<?php
/*
 * 其他费用
 * */
class extrasModel extends baseModel
{
	protected $table = 'extras';

	public function getList($condition=1,$start=0,$length=20,$orderField,$orderDirection)
    {
        $query = "SELECT *,(select orderno from orders where id=a.order_id) as order_no,";
        $query.= " (select user_realname from admin_users where user_id=a.create_by) as user_name ";
        $query.= " FROM ".$this->table." as a WHERE $condition ";
        $query.= " ORDER BY $orderField $orderDirection ";
        $query.= " limit $start,$length";
		return $this->model->query($query); 
    }
    /**
     * 查询费用信息
     * @param string $customer_id		客户ID
     */
     
    public function getinfo( $id )
    {
    	$data = array(
    			'id' => $id
    	);
    	$info = $this->model->table( $this->table )->where( $data )->find();
    	return $info;
    }
}
?>