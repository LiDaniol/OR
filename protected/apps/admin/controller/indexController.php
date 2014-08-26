<?php
class indexController extends commonController
{
    public function index(  ) 
    {
        $this->layout = 'layout';
        $this->assign('power', U::$userdata['user_id']);
        $this->assign('caiwu',U::$userdata['pos_id']);
        $this->assign('user_name', U::$userdata['user_name']);
        $this->assign('user_realname', U::$userdata['user_realname']);
        $this->display();				
    }	
}
