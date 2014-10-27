<?php
class supplierController extends commonController
{
	public  $numPerPage;  // 每页显示的条数
	public  $pageNum;     // 当前页码
	public  $orderField;  // 排序字段
	private $supplier = NULL;
	private $router = NULL;

	public function __construct(  )
	{
		parent::__construct();
		$this->supplier =new supplierModel();
		$this->router=new routerModel();
		$this->numPerPage   = isset($_POST['numPerPage']) ? in($_POST['numPerPage']) :20;
		$this->pageNum      = isset($_POST['pageNum']) ? in($_POST['pageNum']) : 1;
		$this->orderField   = 'id';
		$this->assign('numPerPage', $this->numPerPage);
		$this->assign('orderField', $this->orderField);
		$this->assign('pageNum', $this->pageNum);
		$this->assign('orderDirection', $this->orderDirection);
		$this->assign('pageNumShown', 10);
	}

	/*------------------------------------------------------ */
	//-- 排序、分页、查询
	/*------------------------------------------------------ */
	function index(  )
	{
	    //业务员权限模块
    	$userid = U::$userdata['user_id'];
    	$pos_id = U::$userdata['pos_id'];
    	if($pos_id==4){
    		$condition  = ' 1  and create_by='.$userid;
    	}else{
    		$condition  = ' 1 ';
    	}
		//初始化参数
		if( isset($_GET['cat_id']) && !empty($_GET['cat_id']))
		{
			$cat_id  =  intval( $_GET['cat_id'] );
			$keyword = '';
		}
		else
		{
			$cat_id  = isset( $_POST['cat_id']  ) ? $_POST['cat_id'] : 0;
			$keyword = isset( $_POST['keyword'] ) ? in( $_POST['keyword'] ) : '';
			if(empty($keyword)){}else{
				$condition .=" AND supplier_name like '%".$keyword."%' ";
			}
		}
		$tag_id   = isset( $_POST['tag_id']  ) ? in( $_POST['tag_id'] )  : "";
		//分页信息
		$this->assign('numPerPage', $this->numPerPage);
		$this->assign('orderField', $this->orderField);
		$this->assign('pageNum',    $this->pageNum);
		$this->assign('pageNumShown', 10);
		$start = ($this->pageNum - 1) * $this->numPerPage;
		$company_list = $this->supplier->getList($condition,($this->pageNum-1)*$this->numPerPage,$this->numPerPage,$this->orderField,$this->orderDirection);
		$total_count = $this->supplier->count($condition);
		
		if( !empty( $company_list ))
		{
		    foreach ( $company_list as $k => $v )
		    {
			    $company_list[$k]['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
		    }
		}
		if(is_array($company_list))
		$this->assign( "company_list", $company_list );
		else
		$this->assign( "company_list", array());
		$this->assign( "totalCount", $total_count );
		$this->assign( "keyword", $keyword );
		$this->display();
	}

	function add(  )
	{
		$this->display();
	}


	/* 插入数据到数据库 */
	function insert(  )
	{
		$supplier_name      = in( $_POST['supplier_name'] );   //公司名称
		$supplier_addr      = in( $_POST['supplier_addr'] );    //公司详细地址
		$supplier_mobile    = in( $_POST['supplier_mobile'] ); //手机号码
		$supplier_phone     = in( $_POST['supplier_phone'] );   //电话号码
		$supplier_fax       = in( $_POST['supplier_fax'] );       //传真
		$supplier_email     = in( $_POST['supplier_email'] );  //电子邮箱
		$supplier_desc      =  $_POST['supplier_desc'] ;     //公司描述
		$user_id   = U::$userdata['user_id']; //负责人姓名

		if ( empty($supplier_name) || strlen($supplier_name) < 3)
		{
			$status = 0;
			$msg = '请输入正确的客户名称!';
		}
		elseif ( $this->supplier->is_supplier_exist($comp_name) > 0)
		{
			$status = 0;
			$msg = '该供应商已存在!';
		}
		else
		{			
			$data_ary = array(
							'supplier_name' 		=> $supplier_name,
							'supplier_addr' 	    => $supplier_addr,
							'supplier_mobile' 	    => $supplier_mobile,
							'supplier_phone' 	    => $supplier_phone,
							'supplier_fax' 	        => $supplier_fax,
							'supplier_email' 	    => $supplier_email,
							'create_time' 	    => time(),
					        'create_by' 	    => $user_id,
							'supplier_desc' 	    => $supplier_desc
			);
			if ($this->supplier->insert($data_ary) !== FALSE)
			{
				$status = 1;
				$msg = '添加供应商成功！';
			}
			else
			{
				$status = 0;
				$msg = '添加供应商失败！';
			}
		}
		$this->response( $status, $msg, 'supplierList' );
	}

	function del(  )
	{
	    if (isset( $_REQUEST['supplier_id']) && $_REQUEST['supplier_id']!=='')
        {
            $id = intval( $_REQUEST['supplier_id'] );
            if ($this->supplier->delete( array( 'id'=> $id ) ))
            {
                $this->response( 3, '删除成功！', 'supplierList' );
            }
            else
            {
                $this->response( 0, '删除失败！', 'supplierList' );
            }
            exit;
        }
        $this->response( 0, '删除失败！', 'supplierList' );
        exit;
	}


	function edit()
	{
		if ( isset( $_GET['supplier_id'] ) )
		{
			$customer_id = intval( $_GET['supplier_id'] );
			$customer_info = $this->supplier->getinfo( $customer_id );
			
			
			$customer_info['supplier_desc'] = isset($customer_info['supplier_desc'])?$customer_info['supplier_desc']:'';

			$this->assign( "comp_info", $customer_info );
			$this->display();
		}
	}


	/* 更新数据到数据库 */
	function modify(  )
	{
		$supplier_id      = in( $_POST['supplier_id'] );   //公司ID
		$supplier_name      = in( $_POST['supplier_name'] );   //公司名称
		$supplier_addr      = in( $_POST['supplier_addr'] );    //公司详细地址
		$supplier_mobile    = in( $_POST['supplier_mobile'] ); //手机号码
		$supplier_phone     = in( $_POST['supplier_phone'] );   //电话号码
		$supplier_fax       = in( $_POST['supplier_fax'] );       //传真
		$supplier_email     = in( $_POST['supplier_email'] );  //电子邮箱
		$supplier_desc      =  $_POST['supplier_desc'] ;     //公司描述

		if ( empty($supplier_name) || strlen($supplier_name) < 3)
		{
			$status = 0;
			$msg = '请输入正确的供应商名称!';
		}
		elseif ( $this->supplier->is_supplier_exist($supplier_name,$supplier_id) > 0)
		{
			$status = 0;
			$msg = '该供应商已存在!';
		}
		else
		{			
			$data_ary = array(
							'supplier_name' 		=> $supplier_name,
							'supplier_addr' 	    => $supplier_addr,
							'supplier_mobile' 	    => $supplier_mobile,
							'supplier_phone' 	    => $supplier_phone,
							'supplier_fax' 	        => $supplier_fax,
							'supplier_email' 	    => $supplier_email,
							'create_time' 	    => time(),
							'supplier_desc' 	    => $supplier_desc
			);
			if ( FALSE !== $this->supplier->update("id=$supplier_id", $data_ary) )
			{
				$status = 1;
				$msg = '修改成功！';
			}
			else
			{
				$status = 0;
				$msg = '修改失败！';
			}
		}
		$this->response( $status, $msg, 'supplierList' );
	}
	/*
	 * 添加采购单时 选择供应商
	*
	*
	* */
	public function chooseup(){
		$condition  = ' 1 ';
		//初始化参数
		if(isset($_GET['cat_id'] ) && !empty($_GET['cat_id']))
		{
			$cat_id =  intval( $_GET['cat_id'] );
			$keyword = '';
		}
		else
		{
			$cat_id =  isset($_POST['cat_id'])?in($_POST['cat_id']):0;
			$keyword = isset($_POST['keyword'])?in( $_POST['keyword'] ):'';
			if(empty($keyword)){
				
			}else{
			    $condition .= " AND supplier_name like '%".$keyword."%' ";
			}
		}
		$area_id = isset( $_POST['area_id'] ) ? in( $_POST['area_id'] ) : "";
		$tag_id = isset( $_POST['tag_id'] ) ? in( $_POST['tag_id'] ) : "";
		//分页信息
		$this->assign('numPerPage', $this->numPerPage);
		$this->assign('orderField', $this->orderField);
		$this->assign('pageNum', $this->pageNum);
		$this->assign('pageNumShown', 10);
		$company_list = $this->supplier->getList($condition,($this->pageNum-1)*$this->numPerPage,$this->numPerPage,$this->orderField,$this->orderDirection);
		$total_count = $this->supplier->count($condition);
		if(is_array($company_list))
			$this->assign( "lists", $company_list );
		else
			$this->assign( "lists", array());
		$this->assign( "totalCount", $total_count );
		$this->assign( "keyword", $keyword );
		$this->display();
	}
}

?>