<?php
class adminsessionModel extends baseModel
{
	protected $table = 'admin_sessions';

	public function getList($condition=1,$start=0,$length=20)
    {
        $query = "SELECT * FROM ".$this->table." WHERE $condition ";
        $query.="limit $start,$length";
		return $this->model->query($query); 
    } 
    public function getInfo($session_id)
    {
    	$query = "SELECT * FROM ".$this->table." WHERE session_id=".$session_id;
    	$info = $this->model->query($query);
    	if( !empty( $info ))
    	{
    		return $info[0];
    	}
    }
}
?>