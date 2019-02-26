<!DOCTYPE>
<?php

     function NavBar()
     {

?>
	     <html>
			<head>
				<meta name="viewport" content="width=device-width, initial-scale=1">
				<style>
					body {margin:0;}

					.navbar
					{
						overflow: hidden;
						background-color: #333;
						position: fixed;
						top: 0;
						width: 100%;
						opacity: 0.6;
						filter: alpha(opacity=80);
					}
					
					.navbar:hover 
					{
						opacity: 0.9;
						filter: alpha(opacity=100); /* For IE8 and earlier */
					}

					.navbar input
					{
						float: left;
						display: block;
						color: #f2f2f2;
						text-align: center;
						padding: 14px 16px;
						text-decoration: none;
						font-size: 13px;
						background: #333;
					}

					.navbar input:hover
					{
						background: #008000;
						color: black;
					}
					
					.highlight
					{ 
						background:#008000;
					}
				</style>
				
				<style type="text/css" media="print">
				   .no-print { display: none; }
				</style>
			</head>
			<body>

<?php
				$empl_user = $_SESSION['empl_user'];
				$empl_pass = $_SESSION['empl_pass'];

				$username = $_SESSION['username'];
				$password = $_SESSION['password'];
					
				$conn = hsu_conn_sess($username, $password);
				
				$login_lvl = $conn->prepare("SELECT access_lvl
												FROM Employee
												WHERE empl_fname = :f_name and empl_lname = :l_name");
				$login_lvl->bindValue(':f_name', $empl_user, PDO::PARAM_STR);
				$login_lvl->bindValue(':l_name', $empl_pass, PDO::PARAM_STR);
				$login_lvl->execute();
				$display_array = $login_lvl->fetchAll();
				
				if($display_array[0][0] == "4")
				{
					$lvl_4 = "type = 'submit'";
				}
				else
				{
					$lvl_4 = "type = 'hidden'";
				}
				
?>
				<div class="no_print">
					<form action ="<?= htmlentities($_SERVER['PHP_SELF'], ENT_QUOTES) ?>" method= "post">
						<div class="navbar">
							<input type="submit" name="HomePage" id="HomePage" value="Home Page" />
							<input type="submit" name="Cust" id="Cust" value="Customer" />
							<input type="submit" name="ViewInv" id="ViewInv" value="View/Edit Inventory" />
							<input type="submit" name="ViewVen" id="ViewVen" value="View/Edit Vendors" />
							<input type="submit" name="ReturnI" id="ReturnI" value="Item Return" />
							<input <?= $lvl_4 ?>  name="Empl" id="Empl" value="Employees" />
							<input type="submit" name="LogOut" id="log_out" value="Log Out" />
						</div>
					</form>
				</div>
				
				</br>
				</br>
				</br>
				</br>
				</br>
				</br>
				</br>

            </body>
        </html>
<?php
     }
?>
