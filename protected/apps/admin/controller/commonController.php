<?php
//公共类
class commonController extends backgroundController
{
	public $layout = '';
	public function __construct()
	{
		parent::__construct();              		
		@session_start();       // 开启session
		U::init();
        $this->_checkLogin();
	}

    public function _checkLogin()
    {
        //if (!isset($_SESSION['username']) || empty($_SESSION['username']))
        if (!U::isLogin())
		{
			if ( CONTROLLER_NAME == 'login' && in_array(ACTION_NAME, array( 'index', 'login','dialog', 'verify','logout') ) )
			{
				return FALSE;
			}
			if ( CONTROLLER_NAME == 'upload' )
			{
				return FALSE;
			}
			if ( CONTROLLER_NAME == 'index')
			{
				$this->redirect(url('login/index'));
				exit;
			}
			else
			{
				//$this->response( -1, '登录超时！', '', 'closeCurrent', '登录超时', url('login/dialog') );
				exit;
			}
		}
		return TRUE;
    }

    //文件上传
	protected  function  upload($savePath='',$maxSize='',$allowExts='',$allowTypes='',$saveRule='')
	{
		$upload = new UploadFile($savePath,$maxSize,$allowExts,$allowTypes,$saveRule);
		return $upload;
	}

    /* $navTabId: 就是父页的rel */
	function response( $status, $msg, $navTabId='', $callbackType='', $data='', $forwardUrl='', $rel='' )
	{
		switch ( $status )
		{
			case 1 :
				// 操作成功，关闭当前窗口
				$statusCode = '200';
				$callbackType = 'closeCurrent';  
				break;
			case 2 :
				// 操作成功，跳转
				$statusCode = '200';
				$callbackType = 'forward';
				break;
			case 3:
                // 成功，刷新当前页面
                $status = 1;
				$statusCode = '200';
				$callbackType = 'refreshCurrent';
				break;
            case 4:
                // 成功，刷新当前页面
                $status = 1;
				$statusCode = '200';
				$callbackType = $callbackType;
				break;    
			case -1 :
				// 会话超时，需要重新登录
                $status = '301';
				$statusCode = '301';
				break;
			default:
				// 操作失败
                $status = 0;
				$statusCode = '300';
		}

		$message = array(
            'status'       => $status,
            'statusCode'   => $status,
            'navTabId'     => $navTabId,
            'rel'          => $rel,
            'callbackType' => $callbackType,
            'forwardUrl'   => $forwardUrl,  // 跳转的网址，json_encode格式
            'confirmMsg'   => '',
            'message'      => $msg,
            'data'         => $data, /* json */
            //'error'      => '',
            );
		echo json_encode( $message );
	}

    /* 上传相关 */
    public function assign_upload(  )
    {        
		/* 上传有关 */
		//$this->assign( "upload_folder", $config['ACCESSORY_FOLDER']);
        $config = config( 'UPLOAD' );
		$fileExts = explode( ',', $config['TYPE'] );
		$fileExt = '';
		foreach( $fileExts as $ext )
		{
			$fileExt .= "*.$ext;";
		}
		$fileExt =  rtrim( $fileExt, ';' );
		if ( ! $fileExt ) $fileExt = "*"; /* 文件类型 */
		$fileDesc = '请选择文件后缀'; /* 文件类型提示符 */
		$this->assign( "upload_fileExt", $fileExt );
		$this->assign( "upload_fileDesc", $fileDesc );
		$this->assign( "upload_script", url('admin/upload/index') );
		$this->assign( "upload_data", "{PHPSESSID:'xxx', ajax:1,}" ); /* 上传到服务器额外的信息 */
		$this->assign( "upload_fileQueue", "fileQueue" );
        $this->assign( "upload_folder", 'upload' );
		$this->assign( "upload_buttonText", '浏  览' );
		$this->assign( "upload_sizeLimit", $config['SIZE'] );
		$this->assign( "upload_multi", $config['MULTI'] );
		$this->assign( "upload_num", $config['NUM'] );
		$this->assign( "upload_auto", $config['AUTO']? '' : '0' );
		$this->assign( "upload_buttonWidth", '120' );
		$this->assign( "upload_buttonHeight", '30' );
		$this->assign( "upload_hideButton", 'false' );
		$this->assign( "upload_onComplete", 'uploadifyComplete' );
		$this->assign( "upload_onAllComplete", 'uploadifyAllComplete' );
		$this->assign( "copyright", config('COPYRIGHT') );
    }
}
?>