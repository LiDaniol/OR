<?php
class usersModel extends baseModel
{
	protected $table = 'user';

	public function getList($condition=1,$start=0,$length=20)
    {
        $query = "SELECT * FROM ".$this->table." WHERE $condition ";
        $query.="limit $start,$length";
		return $this->model->query($query); 
    } 
    public function getInfo($user_id)
    {
    	$query = "SELECT * FROM ".$this->table." WHERE id=".$user_id;
    	$info = $this->model->query($query);
    	if( !empty( $info ))
    	{
    		return $info[0];
    	}
    }
}
?>