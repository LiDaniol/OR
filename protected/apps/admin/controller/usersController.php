<?php
class usersController extends commonController
{
    private $u = NULL;
    public  $numPerPage;  // 每页显示的条数
	public  $currentPage; // 当前页码
    public  $pageNum;     // 下一个页码
	public  $orderField;  // 排序字段
    public  $orderDirection; // 排序方法:asc or desc  
    private $adminuser = NULL;
    private $statecity = NULL;
    
    function __construct(  )
    {
    	parent::__construct();
    	$this->partnerDAO   = new partnerDAO();
        $this->u = model( 'users' );
        $this->statecity=new statecityModel();
        $this->users=new usersModel();
        $this->router=new routerModel();
        $this->numPerPage = $_POST['numPerPage'] ? in($_POST['numPerPage']) :20;
		$this->pageNum = $_POST['pageNum'] ? in($_POST['pageNum']) : 1;
        $this->orderDirection = $_POST['orderDirection'] ? in($_POST['orderDirection']) : 'desc';
		$this->orderField = $_POST['orderField'] ? in($_POST['orderField']) : 'id';
        $this->assign('numPerPage', $this->numPerPage);
		$this->assign('orderField', $this->orderField);
		$this->assign('pageNum', $this->pageNum);
        $this->assign('orderDirection', $this->orderDirection);
		$this->assign('pageNumShown', 10);
    }
    
    public function index() 
    {
        $condition  = ' 1 ';
        if(U::$userdata['sc_id'] != 0){
        	$sc_id = U::$userdata['sc_id'];
        	$sc_type = strlen($sc_id)/2;
        	$res = $this->partnerDAO->getPartnerIdsList("",$sc_id,$sc_type,"","");
        	$partner_ids_list = $res['partner_ids'];
        	//$company_infos = $this->partnerDAO->getPartnerIdsList($sc_id,$sc_type);
        	$company_ids = '';
        	if(!empty($partner_ids_list))
        	{
        		foreach ($partner_ids_list as $v){
        			$company_ids .= $company_ids ==''?$v:','.$v;
        		}
        	}else
        	{
        		$company_ids = -1;
        	}
        	$sql .= " comp_id in($company_ids)";
        	$gw_ids = $this->router->getAllGwId($sql);
        	$gw_ids = ltrim(implode(',',$gw_ids),',');
        	$gw_ids = empty($gw_ids)? -1 : $gw_ids;
        	$condition .=" AND gw_id in($gw_ids)";
        }
        if ( $this->isPost(  ) )
        {			
			
            $results = $this->users->getList($condition,($this->pageNum-1)*$this->numPerPage , $this->numPerPage);
        	$total_count = $this->u->count($condition); 
        }
        else
        {
        	$results = $this->users->getList($condition,($this->pageNum-1)*$this->numPerPage , $this->numPerPage);
        	$total_count = $this->u->count();         
        }
		$this->assign('numPerPage', $this->numPerPage);
		$this->assign('orderField', $this->orderField);
		$this->assign('pageNum', $this->pageNum);
		$this->assign('pageNumShown', 10);
		$this->assign('totalCount', $total_count);
        $this->assign('user_infos', $results);
        $this->display();
    }



    public function show()
    {
    	if ( isset( $_GET['id'], $_GET['status'] ) )
    	{
    		$id = in($_GET['id']);
    		$status=$_GET['status']==0?1:0;
    		if ($this->u->update( "id='$id'", array('status'=>$status)))
    		{
    			$this->response( 3, '更改成功!', 'usersList' );
    		}
    		$this->response( 4, '更改失败!', 'usersList' );
    	}
    }
    /* 验证用户名*/
    public function checkUser()
    {
    	$username = in($_GET['user_name']);
    	if ( empty($username) )
    	{
    		echo 0;
    		exit;
    	}
    	$admin_info = $this->a->find( "user_name='$username'", '*' );
    	if ( !empty( $admin_info['user_id'] ) ) {
    		echo 'false';
    	} else {
    		echo 'true';
    	}
    	exit;
    }

}
