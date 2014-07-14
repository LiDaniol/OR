<?php
class extrasController extends commonController
{
	public  $numPerPage;  // 每页显示的条数
	public  $pageNum;     // 当前页码
	public  $orderField;  // 排序字段
	private $extras = NULL;
	private $router = NULL;

	public function __construct(  )
	{
		parent::__construct();
		$this->extras =new extrasModel();
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
		}
		//分页信息
		$this->assign('numPerPage', $this->numPerPage);
		$this->assign('orderField', $this->orderField);
		$this->assign('pageNum',    $this->pageNum);
		$this->assign('pageNumShown', 10);
		$start = ($this->pageNum - 1) * $this->numPerPage;
		$extras_list = $this->extras->getList($condition,($this->pageNum-1)*$this->numPerPage,$this->numPerPage,$this->orderField,$this->orderDirection);
		$total_count = $this->extras->count($condition);
		
		if(is_array($extras_list))
		$this->assign( "lists", $extras_list );
		else
		$this->assign( "lists", array());
		$this->assign( "totalCount", $total_count );
		$this->assign( "keyword", $keyword );
		$this->display();
	}

	function add(  )
	{
		$userId = U::$userdata['user_id'];
		//将变量值写入模板
		$this->assign("create_by",$userId);
		$this->display();
	}


	/* 插入数据到数据库 */
	function insert(  )
	{
		$extra_type      = intval( $_POST['extra_type'] );   //费用类型
		$order_id      = in( $_POST['choose_order_id'] );    //订单ID
		$price    = intval( $_POST['price'] ); //金额
		$description     = in( $_POST['description'] );   //备注信息
		$create_by = U::$userdata['user_id'];

		if ( empty($price))
		{
			$status = 0;
			$msg = '请输入金额!';
		}
		elseif ($extra_type==1 && $order_id == '')
		{
			$status = 0;
			$msg = '请选择订单';
		}
		else
		{			
			$data_ary = array(
							'extra_type' 		=> $extra_type,
							'order_id' 	    => $order_id,
							'price' 	    => $price,
							'description' 	    => $description,
							'create_by' 	    => $create_by,
			);
			if ($this->extras->insert($data_ary) !== FALSE)
			{
				$status = 1;
				$msg = '添加费用成功！';
			}
			else
			{
				$status = 0;
				$msg = '添加费用失败！';
			}
		}
		$this->response( $status, $msg, 'extrasList' );
	}

	function del(  )
	{
	    if (isset( $_REQUEST['id']) && !empty( $_REQUEST['id'] ))
        {
            $id = in( $_REQUEST['id'] );
            if ($this->extras->delete( array( 'id'=> $id ) ))
            {
                $this->response( 3, '删除成功！', 'extrasList' );
            }
            else
            {
                $this->response( 0, '删除失败！', 'extrasList' );
            }
        }
	}


	function edit()
	{
		if ( isset( $_GET['id'] ) )
		{
			$id = intval( $_GET['id'] );
			$info = $this->extras->getinfo( $id );
			
			
			$info['description'] = isset($info['description'])?$info['description']:'';

			$this->assign( "comp_info", $info );
			//将变量值写入模板
			$this->display();
		}
	}

	/* 更新数据到数据库 */
	function modify(  )
	{
		$comp_id      = in( $_POST['customer_id'] );   //公司ID
		$comp_name      = in( $_POST['customer_name'] );   //公司名称
		$comp_addr      = in( $_POST['customer_addr'] );    //公司详细地址
		$comp_mobile    = in( $_POST['customer_mobile'] ); //手机号码
		$comp_phone     = in( $_POST['customer_phone'] );   //电话号码
		$comp_fax       = in( $_POST['customer_fax'] );       //传真
		$comp_email     = in( $_POST['customer_email'] );  //电子邮箱
		$comp_manager   = in( $_POST['crate_by'] ); //负责人姓名
		$comp_desc      = $_POST['customer_desc'];     //公司描述

		if ( empty($comp_name) || strlen($comp_name) < 3)
		{
			$status = 0;
			$msg = '请输入正确的客户名称!';
		}
		elseif ( $this->partner->is_company_exist($comp_name,$comp_id) > 0)
		{
			$status = 0;
			$msg = '该客户名已存在!';
		}
		else
		{			
			$data_ary = array(
							'customer_name' 		=> $comp_name,
							'customer_addr' 	    => $comp_addr,
							'customer_mobile' 	    => $comp_mobile,
							'customer_phone' 	    => $comp_phone,
							'customer_fax' 	        => $comp_fax,
							'customer_email' 	    => $comp_email,
							'create_by' 	    => $comp_manager,
							'customer_desc' 	    => $comp_desc,
			);
			if ( FALSE !== $this->partner->update("id=$comp_id", $data_ary) )
			{
				$status = 1;
				$msg = '编辑客户成功！';
			}
			else
			{
				$status = 0;
				$msg = '编辑客户失败！';
			}
		}
		$this->response( $status, $msg, 'extrasList' );
	}	
	/*
	 * 添加费用时 选择订单
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
		}
		$area_id = isset( $_POST['area_id'] ) ? in( $_POST['area_id'] ) : "";
		$tag_id = isset( $_POST['tag_id'] ) ? in( $_POST['tag_id'] ) : "";
		//分页信息
		$this->assign('numPerPage', $this->numPerPage);
		$this->assign('orderField', $this->orderField);
		$this->assign('pageNum', $this->pageNum);
		$this->assign('pageNumShown', 10);
		$company_list = $this->router->getList($condition,($this->pageNum-1)*$this->numPerPage,$this->numPerPage,$this->orderField,$this->orderDirection);
		$total_count = $this->router->count($condition);
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