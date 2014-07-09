<?php
class uploadController extends commonController
{    
	private $up_file_name="";
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        header("content-type:text/html; charset=utf-8");
        $config = config( 'UPLOAD' );
        if ( $this->isPost() )
        {            
            if ( !empty( $_POST['Filename'] ) )
            {
                $uploadpath = ROOT_PATH . $config['FOLDER'] . '/';
                $file_name = time(). uniqid();
                $upload = $this->upload($uploadpath, $config['SIZE'], $config['TYPE']);
                $upload->saveRule = $file_name;
                $upload->upload();
                $fileInfo = $upload->getUploadFileInfo();


                if ( empty( $fileInfo['upload']['error'] ) )
                {
                    $filename = $fileInfo['upload']['savename'];
                    $this->response( 1, '上传成功！', '', '', json_encode(array('src'=>$filename,)));
                                               
                }
                else
                {
                    $this->response( 0, '上传失败！' . $fileInfo['upload']['error'] );
                }               
            }            
            $this->response( 0, '上传失败！没有选择店家图片.' );
        }
        $this->response( 0, '上传失败! 提交参数错误' );
    }    
}