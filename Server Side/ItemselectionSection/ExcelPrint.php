<?php
	require_once '../PHPExcel-1.8/Classes/PHPExcel.php';
	
	//Connecting to the database through a PDO connection
	require('/home/centerac/public_html/connect_info.php');
	require('/home/centerac/public_html/DB.php');
    error_reporting(E_ERROR | E_PARSE);
	$usr =  "centerac_" . $username;
	$connctn = new PDO($DB , $usr, $password, array('charset'=>'utf8'));
	$stat_val = $_POST['status_hidden'];
	$dbw_val = $_POST['dbw_hidden'];
	$public_val = $_POST['public_hidden'];
	$loc_val = $_POST['location_hidden'];
	$cat_val = $_POST['cat_hidden'];
	$search_bar = $_POST['search_bar'];
	
	$search_bar = explode(' ', $search_bar);
	$search_bar = array_filter($search_bar);
	$search_bar = array_values($search_bar);
	
	function contain($string, array $array) 
	{
		foreach($array as $value)
		{
			if(strpos($string,$value) == true)
			{
				return true;
			};
		}
		return false;
	}
	
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
	
	$objPHPExcel = new PHPExcel();
	$objPHPExcel->getActiveSheet()->setCellValue('A1', 'Front ID');
	$objPHPExcel->getActiveSheet()->setCellValue('B1', 'Item Size');
	$objPHPExcel->getActiveSheet()->setCellValue('C1', 'Model');
	$objPHPExcel->getActiveSheet()->setCellValue('D1', 'Item Name');
	$objPHPExcel->getActiveSheet()->setCellValue('E1', 'Public Use');
	$objPHPExcel->getActiveSheet()->setCellValue('F1', 'Status');
	$objPHPExcel->getActiveSheet()->setCellValue('G1', 'Usage');
	
	$row_count = 2;

	//so, if the selection vlaue = 0 I want the information to remain uncahnged.
	if($stat_val[0] == '0')
	{
		$select_item = $connctn->prepare("SELECT item_Backid, item_size, item_modeltype, inv_name, cat_name, item_Frontid, public, D.stat_name
					FROM Item A, Inventory B, Category C, Status D
					WHERE A.inv_id = B.inv_id and B.cat_id = C.cat_id and A.stat_id = D.stat_id" . $dbw_val . $public_val . $loc_val . $cat_val . "
					ORDER BY inv_name, item_modeltype, item_Backid");

		$select_item->execute();
		$display_array = $select_item->fetchAll();
		for($i = 0; $i < count($display_array); $i++)
		{
			$count = 0;
			foreach($display_array[$i] as $item)
			{
				if(contain($item, $search_bar))
				{
					$count++;
				}
			}
			
			//echo "amount counted: " . $count;
			//echo "</br>" . "</br>";
			//echo "Array length: " . count($search_bar);
			//echo "</br>" . "</br>";
			
			
			
			if($count >= (2 * count($search_bar)))
			{
				$number_of_use = $connctn->prepare("select count(A.rent_id)
													from Rental A, CheckOut B
													where A.rent_id = B.rent_id and 
													B.item_Backid = :a");
				$number_of_use->bindValue(':a', $display_array[$i]["item_Backid"], PDO::PARAM_INT);
				$number_of_use->execute();
				$number_of_use = $number_of_use->fetchAll();
				$curr_number_of_use = $number_of_use[0][0];
				
				$curr_item_backid = $display_array[$i]["item_Backid"];
				$curr_item_size = $display_array[$i]["item_size"];
				$curr_inv_name = $display_array[$i]["inv_name"];
				$curr_item_model = $display_array[$i]["item_modeltype"];
				$curr_item_frontid = $display_array[$i]["item_Frontid"];
				$curr_pub_use = $display_array[$i]["public"];
				$curr_stat_info = $display_array[$i]["stat_name"];

				$item_backid = (int)$curr_item_backid;
			
				if($curr_pub_use == "1")
				{
					$curr_pub_use = "Yes";
				}
				else
				{
					$curr_pub_use = "No";
				}
				
				if($curr_item_size == NULL)
				{
					$curr_item_size = "";
				}
				
				$objPHPExcel->getActiveSheet()->setCellValue('A' . $row_count, $curr_item_frontid);
				$objPHPExcel->getActiveSheet()->setCellValue('B' . $row_count, $curr_item_size);
				$objPHPExcel->getActiveSheet()->setCellValue('C' . $row_count, $curr_item_model);
				$objPHPExcel->getActiveSheet()->setCellValue('D' . $row_count, $curr_inv_name);
				$objPHPExcel->getActiveSheet()->setCellValue('E' . $row_count, $curr_pub_use);
				$objPHPExcel->getActiveSheet()->setCellValue('F' . $row_count, $curr_stat_info);
				$objPHPExcel->getActiveSheet()->setCellValue('G' . $row_count, $curr_number_of_use);
				
				$row_count++;
			}
		}
		$connctn = null; //Also remember to close the Database Connection
	}
	//else lets change that information according to the value of the status. 1-5
	else
	{
		$int_value_stat = (int)$stat_val[0];

		$select_item = $connctn->prepare("select item_Backid, item_size, item_modeltype, inv_name, cat_name, item_Frontid, public, D.stat_name
				from Item A, Inventory B, Category C, Status D
				where A.inv_id = B.inv_id and B.cat_id = C.cat_id and A.stat_id = D.stat_id and D.stat_id = :a" . $dbw_val . $public_val . $loc_val . $cat_val . "
				ORDER BY inv_name, item_modeltype, item_Backid");

		$select_item->bindValue(':a', $int_value_stat, PDO::PARAM_INT);
		$select_item->execute();
		$display_array = $select_item->fetchAll();
		
		for($i = 0; $i < count($display_array); $i++)
		{
			$count = 0;
			foreach($display_array[$i] as $item)
			{
				if(contain($item, $search_bar))
				{
					$count++;
				}
			}
			if($count == (2 * count($search_bar)))
			{
				$number_of_use = $connctn->prepare("select count(A.rent_id)
													from Rental A, CheckOut B
													where A.rent_id = B.rent_id and 
													B.item_Backid = :a");
				$number_of_use->bindValue(':a', $display_array[$i]["item_Backid"], PDO::PARAM_INT);
				$number_of_use->execute();
				$number_of_use = $number_of_use->fetchAll();
				$curr_number_of_use = $number_of_use[0][0];
				
				$curr_item_backid = $display_array[$i]["item_Backid"];
				$curr_item_size = $display_array[$i]["item_size"];
				$curr_inv_name = $display_array[$i]["inv_name"];
				$curr_item_model = $display_array[$i]["item_modeltype"];
				$curr_item_frontid = $display_array[$i]["item_Frontid"];
				$curr_pub_use = $display_array[$i]["public"];
				$curr_stat_info = $display_array[$i]["stat_name"];

				$item_backid = (int)$curr_item_backid;
			
				if($curr_pub_use == "1")
				{
					$curr_pub_use = "Yes";
				}
				else
				{
					$curr_pub_use = "No";
				}
				
				if($curr_item_size == NULL)
				{
					$curr_item_size = "";
				}
				
				$objPHPExcel->getActiveSheet()->setCellValue('A' . $row_count, $curr_item_frontid);
				$objPHPExcel->getActiveSheet()->setCellValue('B' . $row_count, $curr_item_size);
				$objPHPExcel->getActiveSheet()->setCellValue('C' . $row_count, $curr_item_model);
				$objPHPExcel->getActiveSheet()->setCellValue('D' . $row_count, $curr_inv_name);
				$objPHPExcel->getActiveSheet()->setCellValue('E' . $row_count, $curr_pub_use);
				$objPHPExcel->getActiveSheet()->setCellValue('F' . $row_count, $curr_stat_info);
				$objPHPExcel->getActiveSheet()->setCellValue('G' . $row_count, $curr_number_of_use);
				
				$row_count++;
			}
		}
		$connctn = null;
	}
	
	$objPHPExcel->getActiveSheet()->setTitle('Item Information');
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="Item_List.xlsx"');
	header('Cache-Control: max-age=0');
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
?>