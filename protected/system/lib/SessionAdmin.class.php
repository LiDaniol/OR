<?php
class SessionAdmin{
	//private $prefix;
	public $model;
	private $cookiename;
	private	$cookiepath;
	private	$cookiedomain;
	private	$cookiesecure;
	private	$conkieslength;

	function __construct(){
		$this->model = model('adminsession');
        //$this->prefix =  $this->model->pre;
		$this->cookiename="boboadmin_sid";
		$this->cookiepath="/";
		$this->cookiedomain="";
		$this->cookiesecure="";
		$this->conkieslength=1800;
	}
	function __destruct(){
		unset($this->model);
		//unset($this->prefix);
		unset($this->cookiename);
		unset($this->cookiepath);
		unset($this->cookiedomain);
		unset($this->cookiesecure);
		unset($this->conkieslength);
	}
	function __get($propertyName){
		if (isset($this->$propertyName)){
			return ($this->$propertyName);
		}else{
			return (null);
		}
	}
	function __set($propertyName,$value){
		$this->$propertyName = $value;
	}

	public function  get_client_ip(){
		if  (isset($_SERVER["HTTP_X_FORWARDED_FOR"])){
			$realip  =  $_SERVER["HTTP_X_FORWARDED_FOR"];
		}elseif(isset($_SERVER["HTTP_CLIENT_IP"])){
			$realip  =  $_SERVER["HTTP_CLIENT_IP"];
		}else{
			$realip  =  $_SERVER["REMOTE_ADDR"];
		}
		preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
		$realip = !empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
		return  $realip;
	}

	public function encode_ip($dotquad_ip){
		$ip_sep = explode('.', $dotquad_ip);
		return sprintf('%02x%02x%02x%02x', $ip_sep[0], $ip_sep[1], $ip_sep[2], $ip_sep[3]);
	}

	public function session_begin($user_id){
		$current_time = time();
		$client_ip = $this->get_client_ip();
		$user_ip = $this->encode_ip($client_ip);		

		if (isset($_COOKIE[$this->cookiename . '_sid'])){
			$session_id = isset($_COOKIE[$this->cookiename . '_sid']) ? $_COOKIE[$this->cookiename . '_sid'] : '-1';
		}else{
			$session_id = ( isset($_GET['sid']) ) ? $_GET['sid'] : '-1';
		}
		$expiry_time = $current_time - $this->conkieslength;
		$login = 0;//默认未登录
		if($user_id > 0){
			//$user_info = $this->model->table( TBL_ADMIN_USERS )->field('user_id,user_name')->where( array( 'user_id'=>$user_id ) )->find(  );
			$user_info = model("adminuser")->find( "user_id=$user_id", 'user_id,user_name' );
			
			if(!empty($user_info['user_id'])){
				$login = 1;//检测有效时设置为登录状态
			}
		}
		if($login == 0){
			$user_id = 0;
		}

		$session_info = $this->model->find( "session_id='$session_id'", 'session_id' );
		//table( TBL_ADMIN_SESSIONS )->field('session_id')->where( array( 'session_id'=>$session_id ) )->find(  );
		if($session_info['session_id']){
			//如果session_id已经存在,更新
			$data =array(
			"session_user_id" => $user_id,
			"session_last_visit" => $current_time,
			"session_time" => $current_time,
			"session_ip" => $user_ip,
			"session_logged_in" => $login,
			);
			$this->model->update( "session_id='$session_id'", $data );
			//$this->model->table( TBL_ADMIN_SESSIONS )->data( $data )->where(array( 'session_id'=>$session_id ))->update(  );
		}else{
			//如果session_id不存在,插入
			$session_id = md5(uniqid($user_ip));
			$data =array(
			"session_id" => $session_id,
			"session_user_id" => $user_id,
			"session_last_visit" => $current_time,
			"session_start" => $current_time,
			"session_time" => $current_time,
			"session_ip" => $user_ip,
			"session_logged_in" => $login,
			);
			$this->model->insert($data);//updte( "session_id='$session_id'", $data );
			//$this->model->table( TBL_ADMIN_SESSIONS )->data( $data )->insert(  );
			//设置cookie值
			@setcookie($this->cookiename . '_sid', $session_id, 0, $this->cookiepath, $this->cookiedomain, $this->cookiesecure);
		}
		//清除session中的用户信息
		unset($_SESSION[config('SPOT') . 'userdata']);
		return true;
	}


	public function session_passport(){
		$current_time = time();
		$userdata = null;
		if ( isset($_COOKIE[$this->cookiename . '_sid']) ){
			$session_id = isset( $_COOKIE[$this->cookiename . '_sid'] ) ? $_COOKIE[$this->cookiename . '_sid'] : '';
		}else{
			$session_id = ( isset($_GET['sid']) ) ? $_GET['sid'] : '';
		}
		if (!empty($session_id)){
			$expiry_time = $current_time -  $this->conkieslength;
			//判断session表中是否存在有效的session信息
			$session_info = $this->model->find( "session_id='$session_id' AND `session_time`>$expiry_time", 'session_id,session_user_id,session_logged_in,session_time' );
			//$session_info = $this->model->table( TBL_ADMIN_SESSIONS )->field('session_id,session_user_id,session_logged_in,session_time')->where( "`session_id`='$session_id' AND `session_time`>$expiry_time" )->find(  );
			if($session_info['session_id']){
				//判断是否已登录
				$user_id = $session_info['session_user_id'];
				$session_logged_in = $session_info['session_logged_in'];
				if($user_id > 0  && $session_logged_in == 1){//已登录
					/*判断session中是否已有用户信息*/
					$session_userdata = isset($_SESSION[config('SPOT') . 'userdata'])?$_SESSION[config('SPOT') . 'userdata']:'';
					if(!empty($session_userdata['user_id']) && $session_userdata['user_id']==$user_id){//如果session中已存在，直接读session数据						
						$userdata = $_SESSION[config('SPOT') . 'userdata'];
					}else {//如果session中不存在，则从用户表中读取		
						$userdata = model("adminuser")->find( "user_id=$user_id", 'user_id,user_name,user_realname,sc_id,login_num,last_login_ip,last_login_time' );
						//$userdata = $this->model->table( TBL_ADMIN_USERS )->field('user_id,user_name,login_num,last_login_ip,last_login_time')->where( array( 'user_id'=>$user_id ) )->find(  );
						$_SESSION[config('SPOT') . 'userdata'] = $userdata;
					}
				}
				//更新session时间与删除无效session
				if ($current_time - $session_info['session_time'] > 60){
					//更新时间
					$this->model->update( "session_id='$session_id'", array("session_time"=>$current_time) );
					//$this->model->table( TBL_ADMIN_SESSIONS )->data( array("session_time"=>$current_time) )->where(array( 'session_id'=>$session_id ))->update(  );
					//删除无效SESSION数据
					$this->model->delete('`session_time`<'.$expiry_time);
					//$this->model->table( TBL_ADMIN_SESSIONS )->where( '`session_time`<'.$expiry_time )->delete(  );
				}
			}else {
				$this->session_begin(-1);//为游客插入一条数据到session表
			}
		}else {
			$this->session_begin(-1);//为游客插入一条数据到session表
		}
		return $userdata;
	}

	public function session_end($user_id){
		$current_time = time();
		if ( isset($_COOKIE[$this->cookiename . '_sid']) ){
			$session_id = isset( $_COOKIE[$this->cookiename . '_sid'] ) ? $_COOKIE[$this->cookiename . '_sid'] : '-1';
		}else{
			$session_id = ( isset($_GET['sid']) ) ? $_GET['sid'] : '-1';
		}
		//删除数据表中session值
		$this->model->delete("`session_id`='$session_id' AND `session_user_id`='$user_id' ");
		//$this->model->table( TBL_ADMIN_SESSIONS )->where( "`session_id`='$session_id' AND `session_user_id`='$user_id' " )->delete(  );
		//unset($_SESSION[App::$config['SPOT'] . 'userdata']);
		@session_destroy();
		//清除cookie
		@setcookie($this->cookiename . '_sid', '', $current_time - 31536000, $this->cookiepath, $this->cookiedomain, $this->cookiesecure);
		return true;
	}

}
