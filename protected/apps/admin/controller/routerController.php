<?php
class routerController extends commonController
{
    private $m = NULL;
    private $c = NULL;
    private $extras = NULL;
    public  $table = 'orders'; /* 表名 */
    public  $numPerPage;  // 每页显示的条数
	public  $currentPage; // 当前页码
    public  $pageNum;     // 下一个页码
	public  $orderField;  // 排序字段
    public  $orderDirection; // 排序方法:asc or desc  
    private $router = NULL;
    private $partner = NULL;
    
    function __construct(  )
    {
    	parent::__construct();
        $this->m = model( 'router' );
        $this->router=new routerModel();
        $this->extras = new extrasModel();
        $this->partner = new partnerModel();
        $this->numPerPage = $_POST['numPerPage'] ? in($_POST['numPerPage']) :20;
		$this->pageNum = $_POST['pageNum'] ? in($_POST['pageNum']) : 1;
        $this->orderDirection = $_POST['orderDirection'] ? in($_POST['orderDirection']) : 'desc';
		$this->orderField = $_POST['orderField'] ? in($_POST['orderField']) : 'id';
		$this->status = array(
				'TOKEN_VALIDATED' =>'认证通过',
				'LOG_OUT'         =>'连接超时',
		);      /* 状态 */
		$this->assign( 'arrStatus', $this->status );
        $this->assign('numPerPage', $this->numPerPage);
		$this->assign('orderField', $this->orderField);
		$this->assign('pageNum', $this->pageNum);
        $this->assign('orderDirection', $this->orderDirection);
		$this->assign('pageNumShown', 10);
    }
    
    public function index() 
    {
    	//业务员权限模块
    	$userid = U::$userdata['user_id'];
    	$pos_id = U::$userdata['pos_id'];
    	if($pos_id==4){
    		$condition  = ' 1  and userid='.$userid;
    	}else{
    		$condition  = ' 1 ';
    	}
       
        if($_REQUEST['customer_id']!=''){
            $customer_id = intval($_REQUEST['customer_id']);
            $condition .= ' and customer='.$customer_id;
        }
        if ( $this->isPost(  ) )
        {

        	
        	if($_POST['sOrderNo']!="")
        	{
        		$sOrderNo = in($_POST['sOrderNo']);
				$condition .= " and orderno like '%".$sOrderNo."%'";
				$this->assign('sOrderNo', $_POST['sOrderNo']);
			}
			//根据网关id查询
			if($_POST['sItem']!="")
			{
				$sItem = in($_POST['sItem']);
				$condition .= " and item like '%".$sItem."%'";
				$this->assign('sItem', $_POST['sItem']);
			}
			if($_POST['order_status']!=""){
				$sOrderStatus = in($_POST['order_status']);
				$condition .= " and audit_status='".$sOrderStatus."'";
				$this->assign('s_order_status', $_POST['order_status']);
			}else{
				$this->assign('s_order_status', '3');
			}
			//根据路由添加时间查询
			if( !empty( $_POST['c_startdate'] ))
			{
				$c_sdate = strtotime($_POST['c_startdate']);
				$this->assign( 'c_startdate', $_POST['c_startdate'] );
				$condition .= " AND create_time >= $c_sdate ";
			}
			
			if( !empty( $_POST['c_enddate'] ))
			{
				$c_edate = strtotime($_POST['c_enddate']." 23:59:59");
				$this->assign( 'c_enddate', $_POST['c_enddate'] );
				$condition .= " AND create_time<= $c_edate ";
			}			
            $routers = $this->router->getList($condition,($this->pageNum-1)*$this->numPerPage,$this->numPerPage,$this->orderField,$this->orderDirection);
        	$total_count = $this->m->count($condition); 
        }
        else
        {
        	$routers = $this->router->getList($condition,($this->pageNum-1)*$this->numPerPage,$this->numPerPage,$this->orderField,$this->orderDirection);
        	$total_count = $this->m->count($condition);         
        }
        $routers = empty($routers)?array():$routers;
        foreach($routers as $k=>$v){
        	$res = model('caigou')->find('order_id='.$v['id'],'sum(price)');
        	$routers[$k]['facroty_price'] = $res['sum(price)'];
        	$routers[$k]['create_time']=$v['create_time']==0?'--':date('Y-m-d H:i',$v['create_time']);
        	$routers[$k]['pre_time']=$v['pre_time']==0?'--':date('Y-m-d H:i',$v['pre_time']);
        	$routers[$k]['en_price']=$v['qa']*$v['price'] - $v['other_price'] - $res['sum(price)'];
        }
		$this->assign('numPerPage', $this->numPerPage);
		$this->assign('orderField', $this->orderField);
		$this->assign($this->orderField.'_orderDirection', $this->orderDirection);
		$this->assign('pageNum', $this->pageNum);
		$this->assign('pageNumShown', 10);
		$this->assign('totalCount', $total_count);
        $this->assign('routers', $routers);
        $this->assign( "pos_id", $pos_id );
        $this->display();
    }

    public function add() 
    {
        if ( $this->isPost() )
        {
                /* 名称 */
                $item = in($_REQUEST['item']);
                /*  */
                $price = in($_REQUEST['price']); 
                //$facroty_price = in($_REQUEST['facroty_price']);
                /*  */
                $customer = in($_REQUEST['choose_customer_id']);
                $created_at = strtotime(in($_REQUEST['create_time']));
                $pre_at = strtotime(in($_REQUEST['pre_time']));
                $orderno = in($_REQUEST['orderno']);
                $qa =in($_REQUEST['qa']);
                $description = isset( $_REQUEST['description'] ) ? in( $_REQUEST['description'] ) : '';
                $userid = U::$userdata['user_id'];
                if (model('router')->insert(array('orderno'=>$orderno,'item'=>$item,'price'=>$price, 'customer'=>$customer, 'userid'=>$userid,'create_time'=>$created_at,'pre_time'=>$pre_at, 'description'=>$description, 'qa'=>$qa)) !== FALSE)
                {
                    $this->response( 1, '添加订单成功！', 'routerList' );
                }
                else
                {
                    $this->response( 4, '添加订单失败！' );        
                }            
        }
        else
        {
            $this->display();
        }
    }

    public function edit() 
    {
        if ( $this->isPost() )
        {
            if ( isset( $_REQUEST['id'],  $_REQUEST['item'] ) )
            {
                $id = in($_REQUEST['id']);
                $item = in($_REQUEST['item']);
                $price = in($_REQUEST['price']);
               // $facroty_price = in($_REQUEST['facroty_price']);
                $customer = in($_REQUEST['choose_customer_id']);
                $created_at = strtotime(in($_REQUEST['create_time']));
                $pre_at = strtotime(in($_REQUEST['pre_time']));
                $qa =in($_REQUEST['qa']);
                $description = isset( $_REQUEST['description'] ) ? in( $_REQUEST['description'] ) : '';

                 if ($this->m->update("id=$id", array('item'=>$item,'price'=>$price, 'customer'=>$customer,'create_time'=>$created_at,'pre_time'=>$pre_at, 'description'=>$description, 'qa'=>$qa)) !== FALSE)
                {
                    $this->response( 1, '修改订单信息成功！', 'routerList' );
                }
                else
                {
                    $this->response( 0, '修改订单信息失败！' );
                }
            }
        }
        else
        {
            if ( isset($_GET['id']) )
            {
                $id = intval(in( $_GET['id'] ));
                if( $id > 0 )
                {
                    if ($data = $this->m->find( "id=$id", '*' ))
                    {
                    	$data['create_time'] = date('Y-m-d',$data['create_time']);
                    	$data['pre_time'] = date('Y-m-d',$data['pre_time']);
                    	$res = model('partner')->find('id='.$data['customer'],'customer_name');
                    	$data['customer_name'] = $res['customer_name'];
                        $this->assign( 'data', $data );
                    }
                }                    
            }
            $this->display();
        }
    }
	//删除
    public function del(  ) 
    {
        if (isset( $_REQUEST['id']) && !empty( $_REQUEST['id'] ))
        {
            $id = in( $_REQUEST['id'] );
            if (model( 'router' )->delete( array( 'id'=> $id ) ))
            {
                $this->response( 3, '删除成功！', 'routerList' );
            }
            else
            {
                $this->response( 0, '删除失败！', 'routerList' );
            }
        }
    }
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
    			$condition .= " AND customer_name like '%".$keyword."%' ";
    		}
    	}
    	$area_id = isset( $_POST['area_id'] ) ? in( $_POST['area_id'] ) : "";
    	$tag_id = isset( $_POST['tag_id'] ) ? in( $_POST['tag_id'] ) : "";
    	//分页信息
    	$this->assign('numPerPage', $this->numPerPage);
    	$this->assign('orderField', $this->orderField);
    	$this->assign('pageNum', $this->pageNum);
    	$this->assign('pageNumShown', 10);
    	$company_list = $this->partner->getList($condition,($this->pageNum-1)*$this->numPerPage,$this->numPerPage,$this->orderField,$this->orderDirection);
    	$total_count = $this->partner->count($condition);    
    	foreach ($company_list as $k=>$v){
    		$v['customer_name'] = trim($v['customer_name']);
    		$company_list[$k]['customer_name'] = str_replace(" ",'&nbsp;',$v['customer_name']);
    	}
    	if(is_array($company_list))
    		$this->assign( "company_list", $company_list );
    	else
    		$this->assign( "company_list", array());
    	$this->assign( "totalCount", $total_count );
    	$this->assign( "keyword", $keyword );
    	$this->display();
    }
    public function otherpricelist(){
    	$condition = ' 1 ';
        if ( $this->isPost()  )
        {
        	$p = isset($_POST['pageNum'])?$_POST['pageNum']:1;
        	$order_id=in($_POST['id']);
        	$condition .= " and order_id='$order_id'";
        	$routers = $this->extras->getList( $condition,($p-1)*$this->numPerPage,$this->numPerPage,$this->orderField,$this->orderDirection );
        	$total = $this->extras->count($condition);
        }
        else
        {
        	$p = isset($_POST['pageNum'])?in($_POST['pageNum']):1; 
        	$order_id=in($_GET['id']);
        	$condition .= " and order_id='$order_id'";
            $routers = $this->extras->getList( $condition,($p-1)*$this->numPerPage,$this->numPerPage,$this->orderField,$this->orderDirection );
            $total = $this->extras->count($condition); 
        }
        $extra_arr=array('1'=>'订单支出费用');
        $routers=empty($routers)?array():$routers;
        foreach($routers as $k=>$v){
        	$routers[$k]['extra_type']=$extra_arr[$v['extra_type']];
        	$routers[$k]['orderno']=$this->router->getOrderNo($v['order_id']);
        }
        $this->assign( 'id', $order_id );
        $this->assign('totalCount', $total);
        $this->assign('routers', $routers);
        $this->display();
    }
    /*采购单 列表*/
    public function caigoulist(){
    	$condition = ' 1 ';
    	$caigou = new caigouModel();
    	if ( $this->isPost()  )
    	{
    		$p = isset($_POST['pageNum'])?$_POST['pageNum']:1;
    		$order_id=in($_POST['id']);
    		$condition .= " and order_id='$order_id'";
    		
    		$routers = $caigou->getList( $condition,($p-1)*$this->numPerPage,$this->numPerPage,$this->orderField,$this->orderDirection );
    		$total = $caigou->count($condition);
    	}
    	else
    	{
    		$p = isset($_POST['pageNum'])?in($_POST['pageNum']):1;
    		$order_id=in($_GET['id']);
    		$condition .= " and order_id='$order_id'";
    		$routers = $caigou->getList( $condition,($p-1)*$this->numPerPage,$this->numPerPage,$this->orderField,$this->orderDirection );
    		$total = $caigou->count($condition);
    	}
    	$routers=empty($routers)?array():$routers;
    	foreach($routers as $k=>$v){
    		$routers[$k]['create_time']=$v['create_time']==0?'--':date('Y-m-d H:i',$v['create_time']);
    		$routers[$k]['orderno']=$this->router->getOrderNo($v['order_id']);
    	}
    	$this->assign( 'id', $order_id );
    	$this->assign('totalCount', $total);
    	$this->assign('routers', $routers);
    	$this->display();
    }
    /* 唯一性检查 */
    public function gw_id_check()
    {        
        if ( !empty($_GET['gw_id']  ) )
        {
            $keyword = in( $_GET['gw_id'] );
            if ($this->m->find( "gw_id=$keyword", 'gw_id' ))
                echo 'false';
            else
                echo 'true';
        }        
    }

    /* 数据导出*/
    public function export()
    {
    	//导出
    	if ( isset( $_POST['out_excel'] ) && $_POST['out_excel'] ) {
    		set_time_limit(300);
			require(dirname(__FILE__).'/../PHPExcel/PHPExcel.php');
			require(dirname(__FILE__).'/../PHPExcel/PHPExcel/Style.php');
    		$objExcel    = new PHPExcel();
    		$sharedStyle = new PHPExcel_Style();
    		
    		$objWorksheet = $objExcel->getActiveSheet();
    		$objExcel->setActiveSheetIndex(0); //设置当前活动的sheet
    		$objWorksheet->setTitle('订单列表'); //设置sheet名字
    		$objWorksheet->setCellValue('A1', "订单编号");
    		$objWorksheet->getColumnDimension('A')->setWidth(8);
    		$objWorksheet->setCellValue('B1', "产品品名/信息");
    		$objWorksheet->getColumnDimension('B')->setWidth(15);
    		$objWorksheet->setCellValue('C1', "订单美金金额");
    		$objWorksheet->getColumnDimension('C')->setWidth(15);
    		$objWorksheet->setCellValue('D1', "下单客户");
    		$objWorksheet->getColumnDimension('D')->setWidth(18);
    		$objWorksheet->setCellValue('E1', "业务员");
    		$objWorksheet->getColumnDimension('E')->setWidth(15);
    		$objWorksheet->setCellValue('F1', "下单时间");
    		$objWorksheet->getColumnDimension('F')->setWidth(24);
    		$objWorksheet->setCellValue('G1', "预计交货时间");
    		$objWorksheet->getColumnDimension('G')->setWidth(11);
    		$objWorksheet->setCellValue('H1', "采购成本");
    		$objWorksheet->getColumnDimension('H')->setWidth(11);
    		$objWorksheet->setCellValue('I1', "利润(RMB)");
    		$objWorksheet->getColumnDimension('I')->setWidth(11);
    		$objWorksheet->setCellValue('J1', "其他支出");
    		$objWorksheet->getColumnDimension('J')->setWidth(18);
    		$objWorksheet->setCellValue('K1', "订单状态");
    		$objWorksheet->getColumnDimension('K')->setWidth(18);
    		$objWorksheet->setCellValue('L1', "备注");
    		$objWorksheet->getColumnDimension('L')->setWidth(18);
    		
    	    	//业务员权限模块
    	$userid = U::$userdata['user_id'];
    	$pos_id = U::$userdata['pos_id'];
    	if($pos_id==4){
    		$condition  = ' 1  and userid='.$userid;
    	}else{
    		$condition  = ' 1 ';
    	}
       
        if($_REQUEST['customer_id']!=''){
            $customer_id = intval($_REQUEST['customer_id']);
            $condition .= ' and customer='.$customer_id;
        }
        if($_POST['sOrderNo']!="")
        {
        	$sOrderNo = in($_POST['sOrderNo']);
        	$condition .= " and orderno like '%".$sOrderNo."%'";
        }
        //根据网关Item查询
        if($_POST['sItem']!="")
        {
        	$sItem = in($_POST['sItem']);
        	$condition .= " and item like '%".$sItem."%'";
        }
        if($_POST['order_status']!=""){
        	$sOrderStatus = in($_POST['order_status']);
        	$condition .= " and audit_status='".$sOrderStatus."'";
        }
        //根据添加时间查询
        if( !empty( $_POST['c_startdate'] ))
        {
        	$c_sdate = strtotime($_POST['c_startdate']);
        	$condition .= " AND create_time >= $c_sdate ";
        }
        	
        if( !empty( $_POST['c_enddate'] ))
        {
        	$c_edate = strtotime($_POST['c_enddate']." 23:59:59");
        	$condition .= " AND create_time<= $c_edate ";
        }
            $query = "SELECT *,(select sum(price) from caigou where order_id=a.id) as facroty_price,";
            $query.= " (select user_realname from admin_users where user_id=a.userid) as user_name,";
            $query.= " (select sum(price) from extras where order_id=a.id ) as other_price,";
            $query.= " (select customer_name from customer where id=a.customer) as customer";
            $query.=" FROM orders as a  where $condition ORDER BY {$this->orderField} {$this->orderDirection}";
    		$routers = $this->m->query($query);
    		$routers = empty($routers)?array():$routers;
    		$y=2;
    		$no = 0;
    		$oS = array('1'=>'新订单','2'=>'已完成');
    		foreach($routers as $v){
    			$order_status = $oS[$v['order_status']];
    			$v['create_time']=$v['create_time']==0?'--':date('Y-m-d H:i',$v['create_time']);
    			$v['pre_time']=$v['pre_time']==0?'--':date('Y-m-d H:i',$v['pre_time']);
    			$v['en_price']=$v['qa']*$v['price'] - $v['other_price'] - $v['facroty_price'];
    			//数据
    			$objWorksheet->getCell('A'.$y)->setValueExplicit($v['orderno'], PHPExcel_Cell_DataType::TYPE_STRING);//
    			$objWorksheet->getCell('B'.$y)->setValueExplicit($v['item'], PHPExcel_Cell_DataType::TYPE_STRING);//
    			$objWorksheet->getCell('C'.$y)->setValueExplicit($v['price'], PHPExcel_Cell_DataType::TYPE_STRING);//
    			$objWorksheet->getCell('D'.$y)->setValueExplicit($v['customer'], PHPExcel_Cell_DataType::TYPE_STRING);//
    			$objWorksheet->getCell('E'.$y)->setValueExplicit($v['user_name'], PHPExcel_Cell_DataType::TYPE_STRING);//
    			$objWorksheet->getCell('F'.$y)->setValueExplicit($v['create_time'], PHPExcel_Cell_DataType::TYPE_STRING);//
    			$objWorksheet->getCell('G'.$y)->setValueExplicit($v['pre_time'], PHPExcel_Cell_DataType::TYPE_STRING);//
    			$objWorksheet->getCell('H'.$y)->setValueExplicit($v['facroty_price'], PHPExcel_Cell_DataType::TYPE_STRING);//
    			$objWorksheet->getCell('I'.$y)->setValueExplicit($v['en_price'], PHPExcel_Cell_DataType::TYPE_STRING);//
    			$objWorksheet->getCell('J'.$y)->setValueExplicit($v['other_price'], PHPExcel_Cell_DataType::TYPE_STRING);//
    			$objWorksheet->getCell('K'.$y)->setValueExplicit($order_status, PHPExcel_Cell_DataType::TYPE_STRING);//
    			$objWorksheet->getCell('L'.$y)->setValueExplicit($v['description'], PHPExcel_Cell_DataType::TYPE_STRING);//
    			//$objWorksheet->getStyle('I'.$y)->getAlignment()->setWrapText(true);
    			$y++;
    			$no++;
    		}
    		//导出excel
    		header('Content-Type: application/vnd.ms-excel');
    		header('Content-Disposition: attachment;filename="'.date("Ymd-His").'.xls"');
    		header('Cache-Control: max-age=0');
    	
    		$objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
    		$objWriter->save('php://output');
    		exit;
    	}

    }
}
