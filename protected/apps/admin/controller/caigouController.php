<?php
class caigouController extends commonController
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
    private $caigou=NULL;
    
    function __construct(  )
    {
    	parent::__construct();
        $this->m = model( 'caigou' );
        $this->caigou=new caigouModel();
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
    	//if($userid>1){
    	//	$condition  = ' 1  and userid='.$userid;
    	//}else{
    		$condition  = ' 1 ';
    	//}
       
        if($_REQUEST['customer_id']!=''){
            $customer_id = intval($_REQUEST['customer_id']);
            $condition .= ' and customer='.$customer_id;
        }
        if ( $this->isPost(  ) )
        {

        	
        	if($_POST['sOrderNo']!="")
        	{
        		$sOrderNo = in($_POST['sOrderNo']);
				$condition .= " and order_no like '%".$sOrderNo."%'";
				$this->assign('sOrderNo', $_POST['sOrderNo']);
			}
			//根据网关id查询
			if($_POST['sItem']!="")
			{
				$sItem = in($_POST['sItem']);
				$condition .= " and item like '%".$sItem."%'";
				$this->assign('sItem', $_POST['sItem']);
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
            $routers = $this->caigou->getList($condition,($this->pageNum-1)*$this->numPerPage,$this->numPerPage,$this->orderField,$this->orderDirection);
        	$total_count = $this->m->count($condition); 
        }
        else
        {
        	$routers = $this->caigou->getList($condition,($this->pageNum-1)*$this->numPerPage,$this->numPerPage,$this->orderField,$this->orderDirection);
        	$total_count = $this->m->count($condition);         
        }
        $routers = empty($routers)?array():$routers;
        foreach($routers as $k=>$v){
        	$routers[$k]['create_time']=$v['create_time']==0?'--':date('Y-m-d H:i',$v['create_time']);
        }
		$this->assign('numPerPage', $this->numPerPage);
		$this->assign('orderField', $this->orderField);
		$this->assign($this->orderField.'_orderDirection', $this->orderDirection);
		$this->assign('pageNum', $this->pageNum);
		$this->assign('pageNumShown', 10);
		$this->assign('totalCount', $total_count);
        $this->assign('routers', $routers);
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
                /*  */
                $sup_id = in($_REQUEST['choose2_sup_id']);//供应商id
                $orderid = in($_REQUEST['choose_order_id']);//订单id
                $created_at = time();//添加时间
                $orderno = in($_REQUEST['choose_order_no']);
                $description = isset( $_REQUEST['description'] ) ? in( $_REQUEST['description'] ) : '';
                //$userid = U::$userdata['user_id']; //添加人 @todo
                if (model('caigou')->insert(array('order_id'=>$orderid,'order_no'=>$orderno,'item'=>$item, 'sup_id'=>$sup_id,'price'=>$price, 'create_time'=>$created_at, 'description'=>$description)) !== FALSE)
                {
                    $this->response( 1, '添加采购单成功！', 'caigouList' );
                }
                else
                {
                    $this->response( 4, '添加采购单失败！' );        
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
            if ( isset( $_REQUEST['id'],$_REQUEST['item'] ) )
            {
                $id = in($_REQUEST['id']);
                /* 商家名称 */
                $item = in($_REQUEST['item']);
                /* 商家id */
               $price = in($_REQUEST['price']);
                $description = isset( $_REQUEST['description'] ) ? in( $_REQUEST['description'] ) : '';


                if ($this->caigou->update("id=$id", array('item'=>$item, 'price'=>$price,'description'=>$description)))
                {
                    $this->response( 1, '修改采购单信息成功！', 'caigouList' );
                }
                else
                {
                    $this->response( 0, '修改采购单信息失败！' );
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
                    if ($data = $this->caigou->find( "id=$id", 'id,sup_id, order_no,item, price, description' ))
                    {
                    	$supplier = new supplierModel();
                    	$sup_name = $supplier->find("id=".$data['sup_id'],'supplier_name');
                    	$data['sup_name'] = $sup_name['supplier_name'];
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
            if (model( 'caigou' )->delete( array( 'id'=> $id ) ))
            {
                $this->response( 3, '删除成功！', 'caigouList' );
            }
            else
            {
                $this->response( 0, '删除失败！', 'caigouList' );
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
    		$objWorksheet->setCellValue('A1', "序号");
    		$objWorksheet->getColumnDimension('A')->setWidth(8);
    		$objWorksheet->setCellValue('B1', "名称");
    		$objWorksheet->getColumnDimension('B')->setWidth(15);
    		$objWorksheet->setCellValue('C1', "id");
    		$objWorksheet->getColumnDimension('C')->setWidth(15);
    		$objWorksheet->setCellValue('D1', "时间");
    		$objWorksheet->getColumnDimension('D')->setWidth(18);
    		$objWorksheet->setCellValue('E1', "");
    		$objWorksheet->getColumnDimension('E')->setWidth(15);
    		$objWorksheet->setCellValue('F1', "");
    		$objWorksheet->getColumnDimension('F')->setWidth(24);
    		$objWorksheet->setCellValue('G1', "");
    		$objWorksheet->getColumnDimension('G')->setWidth(11);
    		$objWorksheet->setCellValue('H1', "");
    		$objWorksheet->getColumnDimension('H')->setWidth(11);
    		$objWorksheet->setCellValue('I1', "");
    		$objWorksheet->getColumnDimension('I')->setWidth(11);
    		$objWorksheet->setCellValue('J1', "");
    		$objWorksheet->getColumnDimension('J')->setWidth(18);
    		$condition = ' WHERE 1 ';
    		
    		if(U::$userdata['sc_id'] != 0){
    			$sc_id = U::$userdata['sc_id'];
    			$sc_type = strlen($sc_id)/2;
    			$res = $this->partnerDAO->getPartnerIdsList("",$sc_id,$sc_type,"","");
    			$company_infos = $res['partner_ids'];
    			//$company_infos = $this->partnerDAO->getPartnerIdsList($sc_id,$sc_type);
    			$company_ids = '';
    			if(!empty($company_infos))
    			{
    				foreach ($company_infos as $v){
    					$company_ids .= $company_ids ==''?$v:','.$v;
    				}
    			}else
    			{
    				$company_ids = -1;
    			}
    			$condition .= " and comp_id in($company_ids)";
    		}
    		
    		if( $_POST['gwId'] != "" ){
    			$condition .= " and gw_id like '%".$_POST['gwId']."%'";
    		}
    		if($_POST['router_name']!=""){
    			$condition .= " and name like '%".$_POST['router_name']."%'";
    		}
    		if($_POST['udd_name']!="")
    		{
    			$condition .= " and name =''";
    		}
    		if( !empty( $_POST['startdate'] ))
    		{
    			$sdate = $_POST['startdate'];
    			$condition .= " AND to_days(last_heartbeat_at) >= to_days('$sdate') ";
    		}
    		
    		if( !empty( $_POST['enddate'] ))
    		{
    			$edate = $_POST['enddate']." 23:59:59";
    			$condition .= " AND to_days(last_heartbeat_at) <= to_days('$edate') ";
    		}
    		//根据路由添加时间查询
    		if( !empty( $_POST['c_startdate'] ))
    		{
    			$c_sdate = $_POST['c_startdate'];
    			$condition .= " AND to_days(created_at) >= to_days('$c_sdate') ";
    		}
    			
    		if( !empty( $_POST['c_enddate'] ))
    		{
    			$c_edate = $_POST['c_enddate']." 23:59:59";
    			$condition .= " AND to_days(created_at) <= to_days('$c_edate') ";
    		}
    		
    		$query = "SELECT * FROM ".config( 'DB_PREFIX' ).$this->table." $condition ORDER BY {$this->orderField} {$this->orderDirection}";
    		$routers = $this->m->query($query);
    		$routers = empty($routers)?array():$routers;
    		//$routers = $this->router->getList($condition,0,1000);
    		$y=2;
    		$no = 0;

    		foreach($routers as $excel){
    	
    			$name = !empty( $excel['name'] ) ? $excel['name'] : '未知';
    			$sys_time = $excel['last_heartbeat_sys_uptime'];
    			$day = intval($sys_time/3600/24);
    			$hour = intval(($sys_time - $day*3600*24)/3600);
    			$minute = intval(($sys_time - $day*3600*24 - $hour*3600)/60);
    			$second = ($sys_time - $day*3600*24 - $hour*3600-minute*60)%60;
    			if ( !empty( $day ) || !empty( $hour ) || !empty( $minute ) || !empty( $second ) )
    				$sys_uptime = $day.' 天'.$hour.' 小时'.$minute.' 分钟'.$second.' 秒';
    			else
    				$sys_uptime = '';
    			$memfree = !empty($excel['last_heartbeat_sys_memfree'])?intval($excel['last_heartbeat_sys_memfree']/1024).'M':'';
    			//数据
    			$objWorksheet->getCell('A'.$y)->setValueExplicit($no, PHPExcel_Cell_DataType::TYPE_NUMERIC);//
    			$objWorksheet->getCell('B'.$y)->setValueExplicit($name, PHPExcel_Cell_DataType::TYPE_STRING);//
    			$objWorksheet->getCell('C'.$y)->setValueExplicit($excel['gw_id'], PHPExcel_Cell_DataType::TYPE_STRING);//
    			$objWorksheet->getCell('D'.$y)->setValueExplicit($excel['last_heartbeat_at'], PHPExcel_Cell_DataType::TYPE_STRING);//
    			$objWorksheet->getCell('E'.$y)->setValueExplicit($excel['last_heartbeat_ip'], PHPExcel_Cell_DataType::TYPE_STRING);//
    			$objWorksheet->getCell('F'.$y)->setValueExplicit($sys_uptime, PHPExcel_Cell_DataType::TYPE_STRING);//
    			$objWorksheet->getCell('G'.$y)->setValueExplicit($memfree, PHPExcel_Cell_DataType::TYPE_STRING);//
    			$objWorksheet->getCell('H'.$y)->setValueExplicit($excel['last_heartbeat_sys_load'], PHPExcel_Cell_DataType::TYPE_STRING);//
    			$objWorksheet->getCell('I'.$y)->setValueExplicit($excel['last_heartbeat_vertion'], PHPExcel_Cell_DataType::TYPE_STRING);//
    			$objWorksheet->getCell('J'.$y)->setValueExplicit($excel['created_at'], PHPExcel_Cell_DataType::TYPE_STRING);//
    			$objWorksheet->getStyle('I'.$y)->getAlignment()->setWrapText(true);
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
