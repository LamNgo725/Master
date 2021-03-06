<?php
		
	//Connecting to the database through a PDO connection
	require('/home/centerac/public_html/connect_info.php');
	require('/home/centerac/public_html/DB.php');
    error_reporting(E_ERROR | E_PARSE);
	$usr =  "centerac_" . $username;
	$connctn = new PDO($DB , $usr, $password, array('charset'=>'utf8'));
	
	//sets the incoming stat_id to this var
	$stat_val = $_REQUEST['stat_id'];
	$dbw_val = $_REQUEST['dbw'];
	$public_val = $_REQUEST['public'];
	$loc_val = $_REQUEST['location'];
	$cat_val = $_REQUEST['cat'];
	
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
	
	if($loc_val == 'ca')
	{
		$loc_val = " and A.loc_id = '1'";
	}
	elseif($loc_val == 'hbac')
	{
		$loc_val = " and A.loc_id = '2'";
	}
	else
	{
		$loc_val = "";
	}
	
	
	if($cat_val == 0)
	{
		$cat_val = "";
	}
	else
	{
		$cat_val = " and C.cat_id = " . $cat_val;
	}

	$display = array();
	//so, if the selection vlaue = 0 I want the information to remain uncahnged.
	if($stat_val [0] == '0')
	{
		$select_item = $connctn->prepare("SELECT item_Backid, item_size, item_modeltype, inv_name, cat_name, item_Frontid, public, D.stat_name
					FROM Item A, Inventory B, Category C, Status D
					WHERE A.inv_id = B.inv_id and B.cat_id = C.cat_id and A.stat_id = D.stat_id" . $dbw_val . $public_val . $loc_val . $cat_val . "
					ORDER BY inv_name, item_modeltype, item_Frontid");

		$select_item->execute();
		$display_array = $select_item->fetchAll();
		foreach($display_array as $row)
		{
			
			$number_of_use = $connctn->prepare("select count(A.rent_id)
												from Rental A, CheckOut B
												where A.rent_id = B.rent_id and B.item_Backid = :a");
			$number_of_use->bindValue(':a', $row['item_Backid'], PDO::PARAM_INT);
			$number_of_use->execute();
			$number_of_use = $number_of_use->fetchAll();
			$curr_number_of_use = $number_of_use[0][0];
			
			$array = [
				"item_Backid" => $row["item_Backid"],
				"item_size" => $row["item_size"],
				"inv_name" => $row["inv_name"],
				"item_modeltype" => $row["item_modeltype"],
				"item_Frontid" => $row["item_Frontid"],
				"public" => $row["public"],
				"stat_name" => $row["stat_name"],
				"usage" => $curr_number_of_use
			];
			$display[] = $array;
		}
		$connctn = null; //Also remember to close the Database Connection
	}
	//else lets change that information according to the value of the status. 1-5
	else
	{
		$select_item = $connctn->prepare("select A.item_Backid, item_size, item_modeltype, inv_name, cat_name, item_Frontid, public, D.stat_name
				from Item A, Inventory B, Category C, Status D, StatusChange E
				where A.inv_id = B.inv_id and B.cat_id = C.cat_id and A.stat_id = D.stat_id and E.item_Backid = A.item_Backid and D.stat_id = :a" . $dbw_val . $public_val . $loc_val . $cat_val . "
				GROUP BY item_Backid
				ORDER BY MAX(timestamp), inv_name, item_modeltype, item_Frontid");

		$select_item->bindValue(':a', $stat_val, PDO::PARAM_INT);
		$select_item->execute();
		$display_array = $select_item->fetchAll();
		foreach($display_array as $row)
		{
			
			$number_of_use = $connctn->prepare("select count(A.rent_id)
												from Rental A, CheckOut B
												where A.rent_id = B.rent_id and B.item_Backid = :a");
			$number_of_use->bindValue(':a', $row['item_Backid'], PDO::PARAM_INT);
			$number_of_use->execute();
			$number_of_use = $number_of_use->fetchAll();
			$curr_number_of_use = $number_of_use[0][0];
			
			$array = [
				"item_Backid" => $row["item_Backid"],
				"item_size" => $row["item_size"],
				"inv_name" => $row["inv_name"],
				"item_modeltype" => $row["item_modeltype"],
				"item_Frontid" => $row["item_Frontid"],
				"public" => $row["public"],
				"stat_name" => $row["stat_name"],
				"usage" => $curr_number_of_use
			];
			$display[] = $array;
		}
		$connctn = null;
	}
	echo json_encode($display); //Returns the data back to the AJAX call in JSON format
 ?>
