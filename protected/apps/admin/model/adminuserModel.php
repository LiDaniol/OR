<?php
class adminuserModel extends baseModel
{
	protected $table = 'admin_users';

	public function getList($condition=1,$start=0,$length=20)
    {
        $query = " SELECT * FROM ".$this->table." WHERE $condition and user_id>1 ";
        $query.= " limit $start,$length";
		return $this->model->query($query); 
    } 
    public function getInfo($user_id)
    {
    	$query = "SELECT * FROM ".$this->table." WHERE user_id=".$user_id;
    	$info = $this->model->query($query);
    	if( !empty( $info ))
    	{
    		return $info[0];
    	}
    }
}
?>