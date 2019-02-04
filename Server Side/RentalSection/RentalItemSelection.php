<?php
	//Item Selection Page. Where users are going to select the items the customers want to rent out 
	function RentalItemSelect()
	{
?>
<html>
<head>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<link rel="stylesheet" type="text/css" href="../CSS/multi-select.css">
	<link rel="stylesheet" type="text/css" href="../CSS/toggle.css">
	<link rel="stylesheet" type="text/css" href="../CSS/Mult_column_select.css">
</head>
<body>

	<label for="location"> Location </label>
	<select name="location" class="location" id="location">
		<option value="Center Activities" selected="selected"> Center Activities </option>
		<option value="Humboldt Bay Aquatic Center"> Humboldt Bay Aquatic Center </option>
	</select>
	
    <div id="pageHeader" style="font-weight:bold; font-size:40px;"> Items Selection </div>
    <div> 
<?php
		//PDO Connection to the Databse
        $username = $_SESSION['username'];
		$password = $_SESSION['password'];
        $conn = hsu_conn_sess($username, $password);
		
		//Grabs the selected customer
		$sel_cust = $_SESSION['sel_user'];
		
		//MYSQl select query to see if the selected customer is a student or not
		$is_student = $conn->prepare("SELECT is_student
										FROM Customer
										WHERE cust_id = :sel_cust");
		$is_student->bindValue(':sel_cust', $sel_cust, PDO::PARAM_INT);
		$is_student->execute();
		$is_student = $is_student->fetchAll();
		
		/*$padding_date = date('Y-m-d');
		$padding_date = date('Y-m-d', strtotime($padding_date. ' + 2 days'));
		if(date('D', strtotime($padding_date)) == "Sat" || date('D', strtotime($padding_date)) == "Sun")*/

 ?>
 
	<form method= "post" action ="<?= htmlentities($_SERVER['PHP_SELF'], ENT_QUOTES) ?>">
		<fieldset>
			<legend> Select A Item </legend>
				<select id="item_selection" name="item_selection" multiple="multiple" required>
<?php
					//Query for item selection with Items status 'Ready'
					foreach($conn->query("SELECT item_Backid, inv_name, item_size, item_Frontid, item_modeltype 
											FROM Item a, Inventory c, Status b 
											WHERE a.inv_id = c.inv_id and a.stat_id = b.stat_id and a.stat_id = 1") as $row)
					{
						//Check if the selected customer is a student or not.
						if($is_student[0][0] == 'no' || $is_student[0][0] == 'No')
						{
							//If the selected customer is not a student, then check each item is the item is Outdoor Nation or not
							//Basically we're filtering out the Outdoor Nation items when the selected customer is not a student
							if(!(strpos($row["inv_name"], "Outdoor Nation") !== false))
							{
								$curr_item_backid = $row["item_Backid"];
								$curr_inv_name = $row["inv_name"];
								$curr_item_size = $row["item_size"];
								$curr_item_frontid = $row["item_Frontid"];
								$curr_item_modeltype = $row["item_modeltype"];
								if($curr_item_size == null)   
								{
									$curr_item_size = "No Size";
								}
?>
								<option value="<?= $curr_item_backid ?>">
									<?= $curr_inv_name ?> <?= $curr_item_modeltype ?> : <?= $curr_item_size ?> : <?= $curr_item_frontid ?>
								</option>
<?php
							}
						}
						else
						{
							$curr_item_backid = $row["item_Backid"];
							$curr_inv_name = $row["inv_name"];
							$curr_item_size = $row["item_size"];
							$curr_item_frontid = $row["item_Frontid"];
							$curr_item_modeltype = $row["item_modeltype"];
							if($curr_item_size == null)   
							{
								$curr_item_size = "No Size";
							}
?>
							<option value="<?= $curr_item_backid ?>">
								<?= $curr_inv_name ?> <?= $curr_item_modeltype ?> : <?= $curr_item_size ?> : <?= $curr_item_frontid ?>
							</option>
<?php
						}
					}
?>
				</select>
				
			<input type="hidden" name="reserve_list" id="reserve_list" value=<?= $reserve_list ?>/>  <!-- input tag for the keeping track of items that already reserved that may or may not be aviable for rental -->
				
			<!-- JavaScript Starts here -->
			<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"> </script>
			<script type="text/javascript" src="jquery.multi-select.js"></script> <!-- Plugin for the multi-select function -->
			<script type="text/javascript" src="jquery.quicksearch.js"></script>  <!-- Plugin for the item Search function -->
			<script type="text/javascript">
				
				$("#pack option").hover(function() 
				{
					$(".image").css({"position":"absolute","left":event.clientX ,"top":event.clientY }).show();    
				});
				
				$("#item_array").val(''); 
				$("#cart_array").val(''); 
			
				//Inside the multi-select function code with searchable functions. Got it from http://loudev.com/#project with a bit changes for return aspects
				$('#item_selection').multiSelect(
				{
					//Here are the headers, theres two separate divs. One for Selectable and the other for Selection
					selectionHeader: "<div class='custom-header'>Cart</div>",  //Selection
					selectableHeader: "<input type='text' style='width: 315px;' class='search-input' autocomplete='off' placeholder='Item Search'>", //Selectable. In the header of this, we have a input box for the search function
					afterInit: function(ms)  //Heres the Search part. 
					{
						var that = this,  //Grabs the current selection of items and saves it
							$selectableSearch = that.$selectableUl.prev(),
							selectableSearchString = '#'+that.$container.attr('id')+' .ms-elem-selectable:not(.ms-selected)';
						
						that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
						.on('keydown', function(e) //Once user start typing, do search
						{
							//If nothing comes up, return false
							if (e.which === 40)
							{
								that.$selectableUl.focus();
								return false;
							}
						});
						},
					//Here everytime something get selected or deselected, we pretty much keep a track of it all in basely a string of everything.
					//In string format because we can't get a array submit through the forms
					//Found this how to do this on https://stackoverflow.com/questions/13243417/jquery-multiselect-selected-data-order
					afterSelect: function(value, text) //Once a item is selected
					{
						this.qs1.cache(); //Cache for the search functionally 
						
						//The following lines is to store the most currently items value for processing the item throught the rest of the rental
						var get_val = $("#item_array").val();  //Grabs current string values from the input tag "item_array"
						var hidden_val = (get_val != "") ? get_val+"," : get_val; //Adds the item to the string with "," separating the items
						var value_array = value[0].split(",");
						$("#item_array").val(hidden_val+""+value_array[0]); //Have the input tag "item_array" hold/keep the string
						console.log($("#item_array").val());
						
						
						//These next lines is for displaying the cart purposes 
						var a = $.trim($("#item_selection option[value=" + value + "]").text()); //We grab the text of the option that been selected
						var input_cart = value+"-"+a; //Bind the value of the select with the text
						var curr_cart = $("#cart_array").val(); //Grabs the current value of the cart_array input tag
						var cart_hidden_val = (curr_cart != "") ? curr_cart+"|||" : curr_cart; //Separates each item with "|||" (remember this is all in one string)
						$("#cart_array").val(cart_hidden_val+""+input_cart); //Assign the current cart string to the tag
					},
					afterDeselect: function(value, text) //Once a item is deselected
					{
						this.qs1.cache();
						
						//Like the selecting the item, here the following lines to for when the user deselect a item from cart.
						var get_val = $("#item_array").val(); //Grabs current string values from the input tag "item_array"
						var new_val = get_val.replace(value, "");  //Finds it in the string and replace it with just a emply one, ""
						$("#item_array").val(new_val); //Have the input tag "item_array" hold/keep the new string
					
						var a = $.trim($("#item_selection option[value=" + value + "]").text()); //First couple of lines are the same as the selection from above
						var input_cart = value+"-"+a;
						var curr_cart = $("#cart_array").val();
						var new_cart = curr_cart.replace(input_cart, "");  //Just here where instead of adding on the cart, we just replace the item that was deselected spot in the string with a blank space
						$("#cart_array").val(new_cart);
						console.log($("#item_array").val());
					
					}
				});
				
				//The following lines is a AJAX script that changes the item selection field whenever the user select a different package.
				$(function() 
				{
					$('.pack').change(function() //Starts the script when the user select a package from the package selection field
					{
						$.ajax(
						{
							url: "../RentalSection/Helper.php", //The file where the php select query is at
							type: "post",
							data: 
							{
								'pack_value': $(this).val() //assigning the value of the selected package to "pack_value"
							},
							success: function(data) //When the AJAX call is successful, the script does the following
							{
								console.log("Selection of Package:" + data); //Tells the console log that the AJAX call was good
								
								var json_object = JSON.parse(data); //Grabs the data that is in JSON format and parse it so it is usable
								
								//Empties the select for the new inserts
								$('#item_selection')
									.empty()
								;
								
								var item_display = document.getElementById('item_selection'); //Grabs the "item_selection" select tag
								var cart = $("#cart_array").val(); //Also grab the "cart_array" input tag for processing the cart for display
								var cart_array = cart.split("|||"); //Turns the cart string into a cart array by separating the string by the "|||" char
								
								//
								//The next following lines creates a filtered cart for easy use for displaying purposes
								//
								
								var filtered_cart_array = {}; //Creates the cart
								
								for(var u = 0; u < cart_array.length; u++) //Loops through the cart_array
								{
									if(cart_array[u] !== 'undefined' || cart_array[u] !=="") //Makes sure the current spot in the array isn't undefined or empty
									{
										//The format of each string in the cart_array before filtering anything
										//     Example: 41-Plastic Tarp : 5x7 : S14
										var item_Backid_splited = cart_array[u].split("-"); //Splits the item_Backid from the rest of the string
										filtered_cart_array[item_Backid_splited[0]] = item_Backid_splited[1]; //Sets the item_Backid as the key to the rest of the string
									}
								}
								
								//Here the script starts processing all the item data it got from the AJAX call by looping through it in a FOR loop
								for(var i = 0; i < json_object.length; i++)
								{
									var obj = json_object[i]; //First it grabs the current item in the data
									
									//Sets all the item data to its corresponding fields
									item_Backid = obj['item_Backid'];
									inv_name = obj['inv_name'];
									item_size = obj['item_size'];
									item_Frontid = obj['item_Frontid'];
									item_modeltype = obj['item_modeltype'];
									
									
									//Creats a new option for inserting into the "item_selection" select field
									var opt = document.createElement('option');
									
									//Sees if the item_size from the item data is null or blank, if is then it sets the item_size field to "No Size"
									if(item_size == null || item_size == "")
									{
										item_size = "No Size";
									}
									
									//Sets the option innerHTML or text to display to the following format: "inv_name : item_size : item_Frontid"
									opt.innerHTML = inv_name + "\xa0" + item_modeltype + " : " + item_size + " : " + item_Frontid;
									
									//Sets the item back id to the option value
									opt.value = item_Backid;
									
									//
									//The next following lines checks if the option that is being diplayed is already in the cart
									//
									
									//Does the check here. The if statement reads if current item that is about to be displayed is also in the cart, then return true else false
									if(item_Backid in filtered_cart_array)
									{
										opt.selected = 'selected'; //If true then set the option selected to true
										delete filtered_cart_array[item_Backid]; //and delete the item from the cart display so we don't display it twice
									}
									
									//Finalize the option and displays it on the "item_selection" select field
									item_display.appendChild(opt);
								}
								
								//Checks if the rest of the cart or the cart isn't 'undefined' or null
								if(!('' in filtered_cart_array))
								{
									//The next following lines is to display the rest of the cart, the ones that haven't been display yet
									for(var key in filtered_cart_array)
									{
										var opt = document.createElement('option'); //Creates the option that will be displayed
										opt.innerHTML = filtered_cart_array[key]; //Set the innerHTML or text of the option to the value that comes up with the key
										opt.value = key; //Set the option value to the key value itself which should be the item_Backid
										opt.selected = 'selected'; //Have the option selected equal true
										item_display.appendChild(opt); //And finalize the option to display it on cart
									}
								}
								
								//Have the multiSelect plugin to refresh, need to do this so the user can see the new options that corresponds to the package they selected
								$('#item_selection').multiSelect('refresh');
							}
						});
						
					});
				});
				
			</script>
		<fieldset>
			<legend> Packages </legend>
			<select name="pack" multiple="multiple" class="pack">
				<option value=0 selected="selected"> None </option>
<?php
				//Query for package name
				foreach($conn->query("SELECT * FROM Packages") as $row)
				{
					$curr_pack_name = $row['pack_name'];
					$curr_pack_id = $row['pack_id'];
					echo $curr_pack_id;
?>
					<!-- Pushing the fetch information to the screen -->
					<option value="<?= $curr_pack_id ?>">
						<?= $curr_pack_name ?> 
					</option>
<?php
				}
?>
			</select>
		</fieldset>
		</fieldset>
		</div>
	    <fieldset >
			<input type="hidden" name="cart_array" id="cart_array"/>  <!-- input tag for the keeping track of cart display -->
			<input type="hidden" name="item_array" id="item_array"/>  <!-- input tag for the returning the list of selected items from Cart -->
            <input type="submit" name="calPay" id="calPay" value="Continue to Payments"/><br />    <!-- Sends the user onto the next page, the CalculatePayments page -->
	</form>
	<form method= "post" action ="<?= htmlentities($_SERVER['PHP_SELF'], ENT_QUOTES) ?>">
		<input type="submit" name="cancel" id="cancel" value="Cancel" /><br />     <!-- Sends the user back to the main menu -->
	</form>
	    </fieldset>
</body>
</html>


<?php
		$conn = null;   //also remember to close the connection to the database
	}
?>