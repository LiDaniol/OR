<?php
class customerController extends commonController
{
	public  $numPerPage;  // 每页显示的条数
	public  $pageNum;     // 当前页码
	public  $orderField;  // 排序字段
	private $partner = NULL;
	private $router = NULL;

	public function __construct(  )
	{
		parent::__construct();
		$this->partner =new partnerModel();
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
    		$condition  = ' 1  and createby='.$userid;
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
		$tag_id   = isset( $_POST['tag_id']  ) ? in( $_POST['tag_id'] )  : "";
		//分页信息
		$this->assign('numPerPage', $this->numPerPage);
		$this->assign('orderField', $this->orderField);
		$this->assign('pageNum',    $this->pageNum);
		$this->assign('pageNumShown', 10);
		$start = ($this->pageNum - 1) * $this->numPerPage;
		$company_list = $this->partner->getList($condition,($this->pageNum-1)*$this->numPerPage,$this->numPerPage,$this->orderField,$this->orderDirection);
		$total_count = $this->partner->count($condition);
		
		$customer_ids     = '';
		if( !empty( $company_list ))
		{
		    foreach ( $company_list as $v )
		    {
		 	    $customer_ids .= $customer_ids == '' ? $v['id'] : ',' . $v['id'];
		    }
		}
		else
		{
			$customer_ids = '-1';
		}
		$customer_infos = $this->router->getInfo(' customer in(' . $customer_ids . ')');
		if( !empty( $customer_infos ))
		{
		    foreach ( $customer_infos as $v )
		    {
			    $customer_infos['count'][$v['customer']]++;
		    }
		}
		if( !empty( $company_list ))
		{
		    foreach ( $company_list as $k => $v )
		    {
			    $company_list[$k]['order_num'] = isset( $customer_infos['count'][$v['id']] ) ? $customer_infos['count'][$v['id']] : 0;
			    $company_list[$k]['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
			    $userInfo = model('adminuser')->getInfo($v['create_by']);
			    $company_list[$k]['create_by'] = $userInfo['user_realname'];
		    }
		}
		if(is_array($company_list))
		$this->assign( "company_list", $company_list );
		else
		$this->assign( "company_list", array());
		$this->assign( "totalCount", $total_count );
		$this->assign( "keyword", $keyword );
		$this->assign( "pos_id", $pos_id );
		$this->display();
	}

	function add(  )
	{
		require_once(BASE_PATH . 'system/ext/KindEditor.class.php');
		$KindEditor_obj = new KindEditor();
		$customer_desc_editor = $KindEditor_obj->create_editor('customer_desc');
		$userId = U::$userdata['user_id'];
		//将变量值写入模板
		$this->assign("customer_desc_editor", $customer_desc_editor);
		$this->assign("create_by",$userId);
		$this->display();
	}


	/* 插入数据到数据库 */
	function insert(  )
	{
		$comp_name      = in( $_POST['customer_name'] );   //客户名称
		$comp_addr      = in( $_POST['customer_addr'] );    //客户详细地址
		$comp_mobile    = in( $_POST['customer_mobile'] ); //手机号码
		$comp_phone     = in( $_POST['customer_phone'] );   //电话号码
		$comp_fax       = in( $_POST['customer_fax'] );       //传真
		$comp_email     = in( $_POST['customer_email'] );  //电子邮箱
		$comp_manager   = U::$userdata['user_id']; //负责人姓名
		$comp_desc      =  $_POST['customer_desc'] ;     //客户描述
		$customer_country      =  $_POST['customer_country'] ;     //公司
		$customer_company      =  $_POST['customer_company'] ;     //国家
		$customer_no      =  $_POST['customer_no'] ;     //编号

		if ( empty($comp_name) || strlen($comp_name) < 3)
		{
			$status = 0;
			$msg = '请输入正确的客户名称!';
		}
		elseif ( $this->partner->is_company_exist($comp_name) > 0)
		{
			$status = 0;
			$msg = '该客户名已存在!';
		}
		else
		{			
			$data_ary = array(
							'customer_name' 		=> $comp_name,
					'customer_country' 	    => $customer_country,
					'customer_company' 	    => $customer_company,
					'customer_no' 	    => $customer_no,
							'customer_addr' 	    => $comp_addr,
							'customer_mobile' 	    => $comp_mobile,
							'customer_phone' 	    => $comp_phone,
							'customer_fax' 	        => $comp_fax,
							'customer_email' 	    => $comp_email,
							'create_time' 	    => time(),
							'create_by' 	    => $comp_manager,
							'customer_desc' 	    => $comp_desc
			);
			if ($this->partner->insert($data_ary) !== FALSE)
			{
				$status = 1;
				$msg = '添加客户成功！';
			}
			else
			{
				$status = 0;
				$msg = '添加客户失败！';
			}
		}
		$this->response( $status, $msg, 'customerList' );
	}

	function del(  )
	{
	    if (isset( $_REQUEST['customer_id']) && !empty( $_REQUEST['customer_id'] ))
        {
            $id = in( $_REQUEST['customer_id'] );
            if ($this->partner->delete( array( 'id'=> $id ) ))
            {
                $this->response( 3, '删除成功！', 'customerList' );
            }
            else
            {
                $this->response( 0, '删除失败！', 'customerList' );
            }
        }
	}


	function edit()
	{
		if ( isset( $_GET['customer_id'] ) )
		{
			$customer_id = intval( $_GET['customer_id'] );
			require_once(BASE_PATH . 'system/ext/KindEditor.class.php');
			$KindEditor_obj = new KindEditor();
			$customer_info = $this->partner->getinfo( $customer_id );
			
			
			$customer_info['customer_desc'] = isset($customer_info['customer_desc'])?$customer_info['customer_desc']:'';

			$this->assign( "comp_info", $customer_info );
			$customer_desc_editor = $KindEditor_obj->create_editor('customer_desc');
			//将变量值写入模板
			$this->assign("customer_desc_editor", $customer_desc_editor);
			$this->display();
		}
	}

	function detail()
	{
		if ( isset( $_GET['comp_id'] ) )
		{
			$comp_info = $this->partnerDAO->get_PartnerInfo( $_GET['comp_id'] );
			
			$comp_info['comp_desc'] = isset($comp_info['comp_desc'])?$comp_info['comp_desc']:'';
			$this->assign( "comp_info", $comp_info );
			$this->assign( "cat_list", $cat_list );

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
		$this->response( $status, $msg, 'customerList' );
	}
	//商家下的路由器列表
	public function routerlist()
	{
		$condition = ' 1 ';
		if ( $this->isPost() && !empty( $_POST['id'] ) )
		{
			$p = isset($_POST['pageNum'])?$_POST['pageNum']:1;
			$comp_id=intval($_POST['id']);
			$condition .= " and comp_id=".$comp_id;
			//根据时间查询
			if(!empty( $_POST['startdate'])){
				$sdate = $_POST['startdate'];
				$this->assign( 'startdate', $_POST['startdate'] );
				$condition .= " AND to_days(last_heartbeat_at) >= to_days('$sdate') ";
			}
			 
			if(!empty( $_POST['enddate'])){
				$edate = $_POST['enddate']." 23:59:59";
				$this->assign( 'enddate', $_POST['enddate'] );
				$condition .= " AND to_days(last_heartbeat_at) <= to_days('$edate') ";
			}
			$result = $this->router->getList( $condition,($p-1)*$this->numPerPage,$this->numPerPage );
			$total = $this->m->count($condition);
		}
		else
		{
		    $p = isset($_POST['pageNum'])?$_POST['pageNum']:1;
		    $comp_id=intval($_GET['comp_id']);
		    $condition .= " and comp_id=".$comp_id;
		    $result = $this->router->getList( $condition,($p-1)*$this->numPerPage,$this->numPerPage );
		    $total = $this->m->count($condition);
		}
		$this->assign( 'id', $comp_id );
	    $this->assign('totalCount', $total);
		$this->assign('result', $result);
		$this->display();
	}	

	/*
	 * 没有接口 先屏蔽
	* */
	function delCompPlace(  )
	{
		$cp_id = in( $_GET[0] );

		$cp_machine = $this->CompMachine->checkCPMachine( $cp_id );
		if( !empty( $cp_machine ) )
		{
			$this->response( 0, "该场所存在机器!", 'Partner' ); exit;
		}

		$cp_menu = $this->MachineMenu->checkCompPlace( $cp_id );
		if( !empty( $cp_menu ) )
		{
			$this->response( 0, "该场所已经配置菜单", 'Partner' ); exit;
		}

		if ( FALSE !== $this->partner->delCompPlace( $cp_id ) )
		{
			$status = 4;
			$msg = '删除公司场所成功！';
		}
		else
		{
			$status = 0;
			$msg = '删除公司场所失败！';
		}

		$this->response( $status, $msg, 'place_add', '');
	}






	function taglist(  ){
		$start = ($this->pageNum - 1) * $this->numPerPage;
    	$tag_title = in ( $_POST['tag_title'] );

    	$tag_info = $this->partnerDAO->get_AllPartnerTagList($start, $this->numPerPage,$tag_title);
    	$TagList_list = $tag_info['tag_list'];
    	$total = $tag_info['tag_count'];
    	
    	$this->assign( "TagList_list", $TagList_list );
    	$this->assign('numPerPage', $this->numPerPage);
    	$this->assign('orderField', $this->orderField);
    	$this->assign('pageNum', $this->pageNum);
    	$this->assign('tag_title', $tag_title);
    	$this->assign('pageNumShown', 10);
    	$this->assign('totalCount', $total);   
		$this->display(  );
	}
}

?>