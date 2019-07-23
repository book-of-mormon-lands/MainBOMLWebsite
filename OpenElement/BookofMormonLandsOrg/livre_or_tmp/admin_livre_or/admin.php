
				<?php 
				session_start();
				if(!isset($_SESSION["adminOk"])){
					header("location:index.php");
					die();
				}


				if(isset($_GET["supp"])){
					$id = $_GET["supp"];
					$ok = true;

					if(!is_numeric($id)){
						$ok = false;
					}

					if($ok){
						$contenu = file("livre.txt");
						unset($contenu[$id-1]);
						$open = fopen("livre.txt","c");
						ftruncate($open,0);
						fwrite ($open, implode("\r", $contenu)); 
						fclose($open); 
						header("location:admin.php");
					}
				}

				if(isset($_GET["switch"])){
					$id = $_GET["switch"];
					$ok = true;
					if(is_numeric($id)){
						$contenu = file("livre.txt");								
						$ligne = explode("/", $contenu[$id-1]);	
						if($ligne[4] == 0){
							$ligne[4] = str_replace(0, 1, $ligne[4]);
							$ligne = implode("/",$ligne);
							$contenu  = str_replace($contenu[$id-1], $ligne, $contenu);	
							$contenu = implode("\r", $contenu);							
							file_put_contents("livre.txt",$contenu);
							header("location:admin.php");
						}else{
							$ligne[4] = str_replace(1, 0, $ligne[4]);
							$ligne = implode("/",$ligne);
							$contenu  = str_replace($contenu[$id-1], $ligne, $contenu);
							$contenu = implode("\r", $contenu);
							file_put_contents("livre.txt",$contenu);
							header("location:admin.php");
						}				
					}			
				}
			?>
			<!DOCTYPE html>
			<html>
			<head>
				<title>Guestbook Control Panel</title>
				<meta charset="utf-8">
				<style type="text/css">		
					#admin h1 {
						background-color: #6f0a17;
						text-align: center;
						padding: 20px;
						color: #fff;
						margin-bottom: 30px;
					}
					.wrap {
						max-width: 1200px;
						margin: 0 auto;
						padding: 0 20px;
					}
					* {
						margin: 0;
						padding: 0;
						box-sizing: border-box;
					}
					#admin a {
						color: #6f0a17;
					}
					table {
						width: 760px;
						margin: 60px auto 0 auto;
						text-align: center;
					}
					table {
						border-collapse: collapse;
						border-spacing: 0;
					}
					th, td {
						border: 1px solid #6f0a17;
						padding: 10px;
					}
					th {
						background-color: #6f0a17;
						color: #fff;
						border-right: 1px solid #48060e !important;
					}
				</style> 
			</head>
			<body id="admin">
				<h1>Guestbook Control Panel</h1>

				<div class="wrap">
				<p><a href="logout.php"></a></p>
				<?php
				if(file_exists("livre.txt")){
					$contenu = file("livre.txt");
					$nb = count($contenu);
					if($nb == 1){
						echo "There is 1 comment";
					}elseif($nb > 1){
						echo "There are  comments";
					}else{
						echo "There are no comments"; 
					}
					if(!empty($contenu)){
				?>			
						<table>
							<tr>
								<th>Id</th>	
								<th>Name</th>			
								<th>Subject</th>
								<th>Message</th>
								<th>Email</th>
								<th>Approved</th>
								<th>Delete</th>
							</tr>		

							<?php
							//$open = fopen("admin.php","c");


								$i = 0;
								foreach($contenu as $ligne){
									$explode = explode("/",$ligne);
									$i++;
									if($explode[4] == 0){
										$switch = "Non-published";
									}else{
										$switch = "Published";
									}

									echo "<tr>";
										echo "<td>$i</td>";
										echo "<td>".$explode[1]."</td>";
										echo "<td>".$explode[2]."</td>";
										echo "<td>".$explode[3]."</td>";
										echo "<td>".$explode[0]."</td>";
										echo "<td><a href=\"admin.php?switch=".$i."\">".$switch."</a></td>";
										echo "<td><a href=\"admin.php?supp=$i\">Delete</a></td>";
									echo "</tr>";
								}
							//fclose($open);
							?>	
						</table>
				<?php
					}
				}else{
					echo "<p>No comments currently posted.</p>";
				}		
				?>
				</div>
			</body>
			</html>
		