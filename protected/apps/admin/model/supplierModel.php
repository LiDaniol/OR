<?php
class supplierModel extends baseModel
{
    private $partner_cat = 'partner_category';				/* 公司分类信息表 */
    private $customer     = 'supplier';				            /* 公司信息表 */
    public $model;                                                /* 数据库操作 */
    public $table ='supplier';
    public function getList($condition=1,$start=0,$length=20,$orderField="id",$orderDirection="desc")
    {
    	$query = "SELECT * FROM ".$this->table." WHERE $condition ";
    	$query.= "ORDER BY $orderField $orderDirection ";
    	$query.= "limit $start,$length";
    	return $this->model->query($query);
    }
	/**
	 * 添加公司信息
	 * @param string $admin_user  	管理员用户名
	 * @param string $comp_data		公司数据数组
	 */
    public function add($admin_user, $comp_data )
    {
        $cid = $this->model->table( $this->customer )->data( $comp_data )->insert(  );
        return $cid;
    }
     
    
    /**
	 * 更新公司信息
	 * @param string $comp_id     	公司ID
	 * @param string $comp_data		公司数据数组
	 */
    public function edit( $comp_id, $comp_data )
    {
    	return $this->model->table( $this->customer )->data( $comp_data )->where(array( 'comp_id'=>$comp_id ))->update(  );
    }
    
    
    
    
     /**
	 * 查询供应商在数据库中是否已存在
	 * @param string $supplier_name		供应商名称
	 */
     
    public function is_supplier_exist($supplier_name,$supplier_id='')
    {
    	$condition = "supplier_name='$supplier_name'";
    	 if($supplier_id!=''){
    	 	$condition.=' and id<>'.$supplier_id;
    	 }
        $count = $this->model->table( $this->customer )->where( $condition )->count(); 	
    	return $count;
    }
    
    
    
      /**
	 * 查询客户信息
	 * @param string $customer_id		客户ID
	 */
     
    public function getinfo( $customer_id )
    {
    	 $data = array(
            'id' => $customer_id
        );
        $customer_info = $this->model->table( $this->customer )->where( $data )->find(); 	
    	return $customer_info;
    }
    
     /*查询公司信息*/
  public function getSearchCompany($keyword, $cat_id, $start, $length, $order='comp_id')
    {
    	if(empty($keyword))
    	$where = '';
    	else 
    	{
    		$where = ' and ';
    		$where .=  "( comp_name like '%" .$keyword. "%' or comp_addr like '%" .$keyword. "%' or comp_mobile like '%" .$keyword. "%' or comp_phone like '%" .$keyword. "%' or comp_manager like '%" .$keyword. "%' )";
    		
    	}
    	$sql_list = "SELECT * FROM " . $this->prefix . $this->customer . " as c WHERE " . $this->Compcat->get_companychildren($cat_id) .$where.
    	" order by " . $order . " LIMIT " . $start . "," . $length;
    	return $this->model->query($sql_list);
    }
    
    /*查询公司总数*/
   public function getSearchCompanynum($keyword, $cat_pid)
    {
    	$sql = "SELECT count(*) as count FROM " . $this->prefix . $this->customer . " as c WHERE " . $this->Compcat->get_companychildren($cat_id) .
    	" and ( comp_name like '%" .$keyword. "%' or comp_addr like '%" .$keyword. "%' or comp_mobile like '%" .$keyword. "%' or comp_phone like '%" .$keyword. "%' or comp_manager like '%" .$keyword. "%' )";   	   	
    	 $count_info = $this->model->query($sql);
    	return $count_info[0]['count'];
    }
    
    
    
    
	function random($length) {
	    $hash = '';
	    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';   // 指定要返回的字符串
	    $max = strlen($chars) - 1;
	    mt_srand((double)microtime() * 1000000);
	    for($i = 0; $i < $length; $i++) {
	        $hash .= $chars[mt_rand(0, $max)];
	    }
	    return $hash;
	}
	

}
?>