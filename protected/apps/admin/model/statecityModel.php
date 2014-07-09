<?php
class statecityModel extends baseModel
{
	protected $table = 'statecity';
	/**
	 * 获取地区直接上一级ID
	 * @param string $sc_id		地区ID
	 */
	public function getScArr(){
		$query = "SELECT * FROM ".$this->table;		
		$result = $this->model->query($query);
		$returnArr = array();
		foreach ($result as $v){
			$returnArr[$v['sc_id']] = $v['sc_name'];
		}
		return $returnArr;
	}
    public function getScpid( $sc_id)
    {
    	$query = "SELECT sc_pid FROM ".$this->table." WHERE sc_id=$sc_id";
        //$info = $this->model->table( $this->anl_statecity )->field('sc_pid')->where($data)->find();
        $info = $this->model->query($query);
        $info['sc_pid']=isset($info[0]['sc_pid'])?$info[0]['sc_pid']:0;
        return $info['sc_pid'];
    
    }
	/**
	 * 获取地区下的直接一级地区
	 * @param string $sc_pid		父类ID
	 * @param string $id		    当前选择的地区
	 */
    public function getOplevel( $sc_pid = 0,$id = '')
    {
      
		$query = "SELECT * FROM ".$this->table." WHERE sc_pid=$sc_pid order by vieworder ASC";
		$res   = $this->model->query($query); 
		$res = empty( $res ) ? array() : $res;
		$cates_array = '';
		foreach($res as $k=>$v)
	    {
		  $sc_id = $v['sc_id'];
		  $sc_name = $v['sc_name'];
		  if($sc_id == $id)
		  $cates_array .= "<option value=$sc_id selected='ture'>$sc_name</option>";
		  else
		  $cates_array .= "<option value=$sc_id>$sc_name</option>";
	    }
		
        return $cates_array;
    }
 	/**
	 * 获取地区下的直接一级地区 返回数组
	 * @param string $sc_pid		父类ID
	 */
    public function getOplevelArray( $sc_pid = 0)
    {
       
		$query = "SELECT * FROM ".$this->table." WHERE sc_pid=$sc_pid order by vieworder ASC";
		$res   = $this->model->query($query);
		
		$cates_array = '';
		$num = count($res);
		$i = 0;
		foreach($res as $k=>$v)
	    { $i++;
		  $sc_id = $v['sc_id'];
		  $sc_name = $v['sc_name'];
		  if($i == $num)
		  $cates_array .= '["'.$sc_id.'","'.$sc_name.'"]';
		  else
		  $cates_array .= '["'.$sc_id.'","'.$sc_name.'"],';
	    }
		$cates_array .= ']';
        return $cates_array;
    } 
    public function getAreaId($city)
    {
    	$new_area = array();
    	//$info = $this->model->table( $this->anl_statecity )->field('sc_id')->where("sc_pid in (".$city.") ")->select();
    	$query = "SELECT sc_id FROM ".$this->table." WHERE sc_pid in (".$city.")";
    	$info   = $this->model->query($query);
    	if ($info)
    	{
    		foreach ($info as $k=>$v)
    		{
    			$new_area[] = $v['sc_id'];
    		}
    	}
    	return $new_area;
    } 
}
?>