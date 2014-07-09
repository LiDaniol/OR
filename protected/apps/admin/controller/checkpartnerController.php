<?php
class checkpartnerController extends commonController
{
    private $m = NULL;
    public  $numPerPage;  // 每页显示的条数
	public  $pageNum;     // 当前页码
	public  $orderField;  // 排序字段
    public  $config;
    private $nodes = NULL;
 
    function __construct(  )
    {
    	$this->nodes    = new nodesModel();
        $this->m = model( 'advert' );
        $this->numPerPage = $_POST['numPerPage'] ? in($_POST['numPerPage']) :20;
		$this->pageNum = $_POST['pageNum'] ? in($_POST['pageNum']) : 1;
		$this->orderField = 'id';
        $this->config = config( 'UPLOAD' );
        $this->adver_img_path= "upload/";
        if ( isset( $_POST['router_gw_id'] ) )
        {
            $this->source_folder = ROOT_PATH . $this->config['FOLDER'] . '/';
            $this->dist_folder   = ROOT_PATH . $this->config['FOLDER'] . '/' . in($_POST['router_gw_id']) . '/store/';
            !is_dir( $this->dist_folder ) && mkdir ( $this->dist_folder, 0755, TRUE );
        }
    }

    public function index(  )
    {        
    	if ( $this->isPost() ){
    		$node_name=$_POST['node_name'];
    	}else{
    		$node_name="";
    	}
		$nodes_list = $this->nodes->getList($node_name,($this->pageNum-1)*$this->numPerPage,$this->numPerPage);
		$total_count=count($this->nodes->getListCount($node_name));  
		$this->assign("nodes_list", $nodes_list);     
		$this->assign("s_node_name", $node_name);
		$this->assign('numPerPage', $this->numPerPage);
		$this->assign('orderField', $this->orderField);
		$this->assign('pageNum', $this->pageNum);
		$this->assign('pageNumShown', 10);
		$this->assign('totalCount', $total_count);    
    	$this->display();
    }
}