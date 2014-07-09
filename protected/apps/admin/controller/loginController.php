<?php
class loginController extends commonController
{
    private $m = NULL;
    public  $numPerPage;  // 每页显示的条数
	public  $pageNum;     // 当前页码
	public  $orderField;  // 排序字段
	
    function __construct(  )
    {
        parent::__construct();
        $this->m = model( 'adminuser' );
    }
    
    public function index(  )
    {
        if (U::isLogin())
		{
			$this->redirect( url( 'index/index' ) );
		}
        $this->display();
    }

    /* 超时弹出框 */
    public function dialog(  )
    {
        $this->display();
    }

    public function login(  )
	{		
		//if ($this->_checkLogin())
		if (U::isLogin())
		{
			$this->redirect( url( 'index/index' ) );
		}
		$username = in( $_POST['user'] );
		$password = $_POST['password'];
		$checkcode = $_POST['checkcode'];

		if ( empty( $username ) )
		{
			echo json_encode( array( 'msg'=>'用户名不能为空', 'code'=>'0' ) );
			return ;
		}
		if ( empty( $password ) )
		{
			echo json_encode( array( 'msg'=>'密码不能为空', 'code'=>'0' ) );
			return;
		}
		if (empty($_POST['checkcode']))
		{
			echo json_encode(array("msg" => '请输入验证码', "result" => '0'));
			return;
		}
		if ($checkcode != $_SESSION['verify'])
		{
			echo json_encode(array("msg" => '验证码错误', "result" => '0'));
			return;
		}
		if ( $this->_login( $username, $password ) )
		{
			echo json_encode( array( 'msg'=>'登录成功', 'code'=>'1' ) );
		}
        else
        {
			echo json_encode( array( 'msg'=>'用户名或者密码错误', 'code'=>'0' ) );
		}
	}

	private function _login( $username, $password )
	{
		$password = md5( $password );

        /* 简单的验证,不需要数据库 */
        /*
        if ( $username == 'admin' && ($password == md5( 'wifiadmin' )) )
        {
            $_SESSION['username'] = $username;
            return TRUE;
        }
        else
        {
            return FALSE;
        }
        */
        /* 简单验证结束 */
            
        

		//$userInfo = $this->model->table( TBL_ADMIN_USERS )->where( "`user_status`=1 AND `user_name`='$username' AND `user_password`='$password' " )->find();
		$userInfo = $this->m->find( "`user_name`='$username' AND `user_password`='$password'", '*' );
		if(!empty($userInfo['user_id'])){//登录成功
			$user_id = $userInfo['user_id'];
			$SessionAdmin = new SessionAdmin();
			//echo $SessionAdmin;
			$SessionAdmin->session_begin($user_id);
			//更新用户登录相关信息与登录日志
			$userUpdateData = array(  );
			$userLoginData = array(  );
			$now = time();
			$clientIp = get_client_ip(  );

			/*
			switch (App::$config['IP_STATUS'])
			{
				case 0:
					$address = '';
					break;
				case 1:
					require 'ext/query_ip.php';
					$address = getIPLoc( $clientIp );
					break;
			}
			*/
			//用户登录相关数据
			$userUpdateData['last_login_time'] = $now;
			$userUpdateData['login_num'] = $userInfo['login_num'] + 1;
			$userUpdateData['last_login_ip'] = $clientIp;
			//$this->model->table( TBL_ADMIN_USERS )->where(array('user_id'=>$user_id))->data( $userUpdateData )->update(  );
			$this->m->update("user_id=$user_id", $userUpdateData);
			/*/登录日志
			$userLoginData['uid'] = $user_id;
			$userLoginData['username'] = $userInfo['user_name'];
			$userLoginData['logintime'] = $now;
			$userLoginData['ip'] = $clientIp;
			$userLoginData['ipaddress'] = $address;
			//replace first record when records more then 50
			$condition = "`uid`='$user_id'";
			$recordNum = $this->model->table( self::$login_log_table )->where( $condition )->count(  );
			if ( $recordNum > 50 )
            {
				$loginLogInfo = $this->model->table( self::$login_log_table )->field( 'id' )->where( $condition )->order( 'logintime desc' )->find(  );
				$this->model->table( self::$login_log_table )->data( $userLoginData )->where( array( 'id'=>$loginLogInfo['id'] ) )->replace(  );
			}
            else
            {
				$this->model->table( self::$login_log_table )->data( $userLoginData )->insert(  );
			}
			*/		
			return true;
		}
		return false;
	}

	public function logout(  )
	{		
        //unset( $_SESSION['username'] );
        $SessionAdmin = new SessionAdmin();
        $SessionAdmin->session_end(U::$userdata['user_id']);
		$this->redirect( url('login/index') );
	}

    public function verify(  )
	{
		Image::buildImageVerify();
	}
}