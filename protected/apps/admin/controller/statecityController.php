<?php
class statecityController extends commonController
{
	private $Statecity = NULL;
    
    function __construct(  )
    {
		parent::__construct(  );
		$this->Statecity    = new statecityModel();
    }

	function city(  )
    {
    	$sc_pid = in( $_GET['city_id'] );
    	$Statecity_list = $this->Statecity->getOplevelArray($sc_pid);
		echo '[["0","请选择城市"],'.$Statecity_list;
    }
	
	 function area(  )
    {
    	$sc_pid = in( $_GET['area_id'] );
    	$Statecity_list = $this->Statecity->getOplevelArray($sc_pid);
		echo '[["0","请选择区县"],'.$Statecity_list;
	}

}