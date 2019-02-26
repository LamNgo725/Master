<?php
		//                                                     !!!!!!!!!!!!!!!!!!!DO NOT PUSH THIS FILE TO GITHUB!!!!!!!!!!!!!!!!!!!!
	$connctn = new PDO("mysql:host=localhost"; $DB, $USER, $PASS, array('charset'=>'utf8'));

	$connctn->query("SET CHARACTER SET utf8");
	//sets the incoming stat_id to this var
	$stat_val = $_REQUEST['stat_id'];
	$dbw_val = $_REQUEST['dbw'];
	$public_val = $_REQUEST['public'];
	
	if($dbw_val == 'yes')
	{
		$dbw_val = " and A.dbw_own = 1";
	}
	elseif($dbw_val == 'no')
	{
		$dbw_val = " and A.dbw_own = 0";
	}
	else
	{
		$dbw_val = "";
	}
	
	if($public_val == 'yes')
	{
		$public_val = " and A.public = 1";
	}
	elseif($public_val == 'no')
	{
		$public_val = " and A.public = 0";
	}
	else
	{
		$public_val = "";
	}

	//so, if the selection vlaue = 0 I want the information to remain uncahnged.
	if($stat_val [0] == '0')
	{
		$select_item = $connctn->prepare("SELECT item_Backid, item_size, item_modeltype, inv_name, cat_name, item_Frontid, public, D.stat_name
					FROM Item A, Inventory B, Category C, Status D
					WHERE A.inv_id = B.inv_id and B.cat_id = C.cat_id and A.stat_id = D.stat_id" . $dbw_val . $public_val . "
					ORDER BY inv_name, item_modeltype");

		$select_item->execute();
		$display_array = $select_item->fetchAll();
		$connctn = null; //Also remember to close the Database Connection
		print json_encode($display_array); //Returns the data back to the AJAX call in JSON format
	}
	//else lets change that information according to the value of the status. 1-5
	else
	{
		$int_value_stat = (int)$stat_val[0];

		$select_item = $connctn->prepare("select item_Backid, item_size, item_modeltype, inv_name, cat_name, item_Frontid, public, D.stat_name
				from Item A, Inventory B, Category C, Status D
				where A.inv_id = B.inv_id and B.cat_id = C.cat_id and A.stat_id = D.stat_id and D.stat_id = :a" . $dbw_val . $public_val . "
				ORDER BY inv_name, item_modeltype");

		$select_item->bindValue(':a', $int_value_stat, PDO::PARAM_INT);
		$select_item->execute();
		$display_array = $select_item->fetchAll();
		$connctn = null;
		print json_encode($display_array);
	}
 ?>