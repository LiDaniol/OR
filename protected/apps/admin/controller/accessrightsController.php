<?php
class accessrightsController extends commonController
{
    private $a = NULL;
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
        $this->a = model( 'adminuser' );
        $this->statecity=new statecityModel();
        $this->adminuser=new adminuserModel();
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
        if ( $this->isPost(  ) )
        {			
			
            $users = $this->adminuser->getList($condition,($this->pageNum-1)*$this->numPerPage , $this->numPerPage);
        	$total_count = $this->a->count($condition); 
        }
        else
        {
        	$users = $this->adminuser->getList($condition,($this->pageNum-1)*$this->numPerPage , $this->numPerPage);
        	$total_count = $this->a->count();         
        }
        $users = !empty($users)?$users:array();
        foreach ( $users as $k => $v ){      	
        	$users[$k]['user_regdate']=date("Y-m-d H:i:s",$v['user_regdate']);
        }
		$this->assign('numPerPage', $this->numPerPage);
		$this->assign('orderField', $this->orderField);
		$this->assign('pageNum', $this->pageNum);
		$this->assign('pageNumShown', 10);
		$this->assign('totalCount', $total_count);
        $this->assign('users', $users);
        $this->display();
    }

    public function add() 
    {
        $this->display();
    }
    /* 插入数据到数据库 */
    function insert(  )
    {
    	$user_name = in( $_POST['user_name'] );
    	$user_password = in( $_POST['user_password'] );
    	$user_realname = in( $_POST['user_realname'] );
    	$user_emp_no = in( $_POST['user_emp_no'] );
    	$user_gender = in( $_POST['user_gender'] );
    	$user_mobile = in( $_POST['user_mobile'] );
    	$user_phone = in( $_POST['user_phone'] );
    	$user_email = in( $_POST['user_email'] );
    	$user_address = in( $_POST['user_address'] );
    	$user_desc = in( $_POST['user_desc'] );
    
    	$data = array(
    			'user_name'     => $user_name,
    			'user_password' => md5( $user_password ),
    			'user_realname' => $user_realname,
    			'user_emp_no'   => $user_emp_no,
    			'user_gender'   => $user_gender,
    			'user_mobile'   => $user_mobile,
    			'user_phone'    => $user_phone,
    			'user_email'    => $user_email,
    			'user_address'  => $user_address,
    			'user_desc'     => $user_desc,
    			'user_regdate'  => time(),
    	);
    
    	if ( FALSE !== $this->adminuser->insert( $data ) )
    	{
    		$status = 1;
    		$msg = '添加用户信息成功！';
    	}
    	else
    	{
    		$status = 0;
    		$msg = '添加用户信息失败！';
    	}
    	$this->response( $status, $msg, 'accessrightsList' );
    }
    public function edit() 
    {
    	if ( isset( $_GET['id'] ) )
    	{
    		$user_id = in( $_GET['id'] );
    		$admin_info = $this->adminuser->getInfo( $user_id );
    		$admin_info['sc_name'] = $admin_info['sc_id']?$this->statecity->find( "sc_id=".$admin_info['sc_id'], 'sc_name' ):'';
    		$admin_info['user_regdate']=date("Y-m-d H:i:s",$admin_info['user_regdate']);
    		
    		$this->assign( 'admin_info', $admin_info );
    	}
        $this->display();
    }
    /* 更新数据到数据库 */
    function modify(  )
    {
    	$user_id = in( $_POST['user_id'] );
    	$user_password = in( $_POST['user_password'] );
    	$user_realname = in( $_POST['user_realname'] );
    	$user_emp_no = in( $_POST['user_emp_no'] );
    	$user_gender = in( $_POST['user_gender'] );
    	$user_mobile = in( $_POST['user_mobile'] );
    	$user_phone = in( $_POST['user_phone'] );
    	$user_email = in( $_POST['user_email'] );
    	$user_address = in( $_POST['user_address'] );
    	$user_desc = in( $_POST['user_desc'] );
    
    	$data = array(
    			'user_realname'       => $user_realname,
    			'user_emp_no'         => $user_emp_no,
    			'user_gender'         => $user_gender,
    			'user_mobile'         => $user_mobile,
    			'user_phone'          => $user_phone,
    			'user_email'          => $user_email,
    			'user_address'        => $user_address,
    			'user_desc'           => $user_desc,
    	);
    
    	if ( $user_password )
    	{
    		$data['user_password'] = md5( $user_password );
    	}
    
    	if ( FALSE !== $this->a->update("user_id=$user_id", $data) )
    	{
    		$status = 1;
    		$msg = '更新用户信息成功！';
    	}
    	else
    	{
    		$status = 0;
    		$msg = '更新用户信息失败！';
    	}
    	$this->response( $status, $msg, 'accessrightsList' );
    }
    public function del(  ) 
    {
        if (isset( $_REQUEST['id']) && !empty( $_REQUEST['id'] ))
        {
            $id = in( $_REQUEST['id'] );
            if (model( 'adminuser' )->delete( array( 'user_id'=> $id ) ))
            {
                $this->response( 3, '删除成功！', 'accessrightsList' );
            }
            else
            {
                $this->response( 0, '删除失败！', 'accessrightsList' );
            }
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
    /* 修改密码 */
    function changepwd( )
    {
    	$user_id = U::$userdata['user_id'];
    	if ($data = $this->a->getInfo( $user_id ))
    	{
    		$this->assign( 'data', $data );
    		 
    	}
    	$this->display();
    
    }
    function doChangepwd( )
    {
    	$user_id = in($_POST['user_id']);
    	$oldPassword = in($_POST['oldPassword']);
    	$password = in($_POST['password']);
    	$arr=array();
    	if ($data = $this->a->getInfo( $user_id ))
    	{
    		$arr = $data;
    	}
    
    	if ( !empty($password) )
    	{
    		$data_ary['user_password'] = md5( $password );
    	}
    
    	if ( $arr['user_password']  != md5( $oldPassword ))
    	{
    		$status = 0;
    		$msg = '原始密码输入不正确！';
    		 
    	}else if(FALSE !== $this->a->update("user_id=$user_id", $data_ary) )
    	{
    		$status = 1;
    		$msg = '修改密码成功！';
    	}
    	else
    	{
    		$status = 0;
    		$msg = '修改密码失败！';
    	}
    	$this->response($status, $msg );
    
    }
}
