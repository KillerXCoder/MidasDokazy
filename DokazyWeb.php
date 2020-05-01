<?php
/*
#============================================================================================
# Názov: Web interface pre nahrávanie / editovanie / prezeranie dôkazov pre banlist Litebans
# Autor: KillerXCoder (Peter Federl)
# E-Mail: peter.federl@gmail.com
#============================================================================================
*/ 
date_default_timezone_set ("Europe/Bratislava");
$servername = 'localhost';
$username = '';
$password = '';
$dbname = '';
$conn = new mysqli($servername, $username, $password, $dbname);
$conn -> set_charset("utf8");
$conn2 = new mysqli("localhost", "", "", "litebans");
$conn2 -> set_charset("utf8");
$conn3 = new mysqli("localhost", "", "");
$conn3 -> set_charset("utf8");
$sql = "SELECT * FROM dokazy ORDER BY id DESC";
$result = $conn->query($sql);
$celkovo_dokazy = $result->num_rows;
if (isset($_GET['strana'])) {
$strana = $_GET['strana'];
} else {
	$strana = 1;
}
$pocet_na_stranu = 10;
$offset = ($strana-1) * $pocet_na_stranu; 
$celkovo_stranky = ceil($result->num_rows / $pocet_na_stranu);


$sql = "SELECT * FROM litebans_bans WHERE active=1 ORDER BY id DESC LIMIT 0,18 ";
$result = $conn2->query($sql);


echo '<style>

.padd th, .padd td { padding: 10px 10px; vertical-align: middle }
.lh { line-height: 24px; }

.color tr:nth-child(even) { background: #ebebeb; }
.color tr:nth-child(odd) { background: #FFF; border: none;}

.mnu th { padding: 0; color: white; transition: 0.25s ease-out; background: #2A2A2A; border: none;  vertical-align: middle }
.mnu th:first-child { border-right: 1px solid rgba(255, 255, 255, 0.1); }
.mnu th:hover { background: #E64946 }
.mnu a, .mnu a:hover { padding: 10px 10px; color: white; text-decoration: none; display: block; height: 100% width: 100%; }

</style>';
echo '
<style>
.pagination {

}
.pagination a {
  color: black;
  float: left;
  padding: 8px 16px;
  text-decoration: none;
  transition: background-color .3s;
  border: 1px solid #ddd;
}

.pagination a.active {
  background-color: #4CAF50;
  color: white;
  border: 1px solid #4CAF50;
 }
.hladat{
  width: 20%;
  padding: 10px 20px;
  margin: 8px 0;
  display: inline-block;
  border: 1px solid #ccc;
  border-radius: 4px;
  box-sizing: border-box;
}
.potvrdit{
  min-width: 23% !IMPORTANT;
  max-width: 30% !IMPORTANT;
  background-color: #4CAF50;
  color: white;
  padding: 14px 20px;
  margin: 8px 0;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
.pravidla{

  width: 10%;
  background-color: #303030;
  color: white;
  padding: 14px 20px;
  margin: 8px 0;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  color:white !IMPORTANT;
  text-decoration:none !IMPORTANT;
}
.pravidla:hover{

  width: 10%;
  background-color: #ff0000;
  color: white;
  padding: 14px 20px;
  margin: 8px 0;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  color:white !IMPORTANT;
  text-decoration:none !IMPORTANT;
}

@media all and (max-width: 694px) {
	.pravidla{

	display:block;
	margin:0px;
	width: 90%;
	}
	.potvrdit{
	min-width:95% !IMPORTANT;
	max-width:95% !IMPORTANT;
	}
	
	.pravidla:hover{

	background-color: #ff0000;
	display:block;
	margin:0px;
	width: 90%;

	}
	.hladat{
	width:100%;
	}
	.mobil{
	width:95% !IMPORTANT;
	}
}

</style>';

$n = 0;
$d = 0;
if (isset($_POST['zmazat_obrazok'])){
	$sql4 = "DELETE FROM dokazy_link WHERE id=\"". $_POST['id'] . "\"";
	$conn->query($sql4);
	unlink("/var/www/midascraft.sk/Dokazy/". $_POST["link"] );
}
if (isset($_POST['pridat']) and file_exists($_FILES["upload"]["tmp_name"][0]) ) {
	if($_POST['meno']!="" and $_POST['popis']!=""){
		$sql = "SELECT `AUTO_INCREMENT` FROM  INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'MidasCraft' AND TABLE_NAME = 'dokazy'";
		$result = $conn3->query($sql);
		$row = $result->fetch_assoc();
		$dokaz_id = $row["AUTO_INCREMENT"];
		$celkovo_dokazy_link = $result->num_rows + 1;
		$total = count($_FILES['upload']['name']);
		for( $i=0 ; $i < $total ; $i++ ) {
		  $tmpFilePath = $_FILES['upload']['tmp_name'][$i];
		  if ($tmpFilePath != ""){
			$newFilePath = "/var/www/midascraft.sk/Dokazy/" . preg_replace('/\s+/', '_', $_FILES['upload']['name'][$i]);
			if (file_exists($newFilePath)) {
				while(file_exists($newFilePath)){
					$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
					$charactersLength = strlen($characters);
					$randomString = '';
					for ($d = 0; $d < 40; $d++) {
						$randomString .= $characters[rand(0, $charactersLength - 1)];
					}
					
					$newFilePath = "/var/www/midascraft.sk/Dokazy/" . preg_replace('/\s+/', '_', $randomString.'.'. strtolower(pathinfo($_FILES['upload']['name'][$i],PATHINFO_EXTENSION)));
				}
				if(move_uploaded_file($tmpFilePath, $newFilePath)) {
					$sql2 = "INSERT INTO dokazy_link (id_dokaz, link) VALUES ('". $dokaz_id ."', '". $randomString.'.'. strtolower(pathinfo($_FILES['upload']['name'][$i],PATHINFO_EXTENSION)) ."')";
					$conn->query($sql2);
					if($n != 1 ){
						$n = 1;
					}
				}
				
			}
			else{
				if(move_uploaded_file($tmpFilePath, $newFilePath)) {
					$sql2 = "INSERT INTO dokazy_link (id_dokaz, link) VALUES ('". $dokaz_id ."', '". preg_replace('/\s+/', '_', $_FILES['upload']['name'][$i]) ."')";
					$conn->query($sql2);
					if($n != 1 ){
						$n = 1;
					}
				}
			}
		  }
		}
		if($n == 1){
			$sql2 = "INSERT INTO dokazy (nick, popis, datum, banujuci, ban_id) VALUES ('". $_POST['nick'] ."', '". $_POST['popis']."', '". $_POST['datum'] ."', '". $_POST['meno'] ."', '". $_POST['ban_id'] ."')";;
			$conn->query($sql2);
			header('Location: https://midascraft.sk/administracia-serveru-crossout/dokazy/');
		}
		else{
		}
	}
	else{
		echo"Nevyplnil si niektoré z polí nižšie!";
	}
}
elseif (isset($_POST['pridat']) and ! file_exists($_FILES["upload"]["tmp_name"][0])){
	if($_POST['meno']!="" and $_POST['popis']!=""){
		$sql2 = "INSERT INTO dokazy (nick, popis, datum, banujuci, ban_id) VALUES ('". $_POST['nick'] ."', '". $_POST['popis']."', '". $_POST['datum'] ."', '". $_POST['meno'] ."', '". $_POST['ban_id'] ."')";
		$conn->query($sql2);
		header('Location: https://midascraft.sk/administracia-serveru-crossout/dokazy/');
	}
	else{
		echo"Nevyplnil si niektoré z polí nižšie!";
	}
}
if (isset($_POST['edit2']) and file_exists($_FILES["upload"]["tmp_name"][0]) ) {
	if($_POST['meno']!="" and $_POST['popis']!=""){
		$sql = "SELECT `AUTO_INCREMENT` FROM  INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'MidasCraft' AND TABLE_NAME = 'dokazy'";
		$result = $conn3->query($sql);
		$row = $result->fetch_assoc();
		$dokaz_id = $_POST['id'];
		$celkovo_dokazy_link = $result->num_rows + 1;
		$total = count($_FILES['upload']['name']);
		for( $i=0 ; $i < $total ; $i++ ) {
		  $tmpFilePath = $_FILES['upload']['tmp_name'][$i];
		  if ($tmpFilePath != ""){
			$newFilePath = "/var/www/midascraft.sk/Dokazy/" .  preg_replace('/\s+/', '_', $_FILES['upload']['name'][$i]);
			if (file_exists($newFilePath)) {
				while(file_exists($newFilePath)){
					$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
					$charactersLength = strlen($characters);
					$randomString = '';
					for ($d = 0; $d < 40; $d++) {
						$randomString .= $characters[rand(0, $charactersLength - 1)];
					}
					
					$newFilePath = "/var/www/midascraft.sk/Dokazy/" . preg_replace('/\s+/', '_', $randomString.'.'. strtolower(pathinfo($_FILES['upload']['name'][$i],PATHINFO_EXTENSION)));
				}
				if(move_uploaded_file($tmpFilePath, $newFilePath)) {
					$sql2 = "INSERT INTO dokazy_link (id_dokaz, link) VALUES ('". $dokaz_id ."', '". $randomString.'.'. strtolower(pathinfo($_FILES['upload']['name'][$i],PATHINFO_EXTENSION)) ."')";
					$conn->query($sql2);
					if($n != 1 ){
						$n = 1;
					}
				}
			}
			else{
				if(move_uploaded_file($tmpFilePath, $newFilePath)) {
					$sql2 = "INSERT INTO dokazy_link (id_dokaz, link) VALUES ('". $dokaz_id ."', '". preg_replace('/\s+/', '_', $_FILES['upload']['name'][$i]) ."')";
					$conn->query($sql2);
					if($n != 1 ){
						$n = 1;
					}
				}
			}
		  }
		}
		if($n == 1){
			$sql2 = "UPDATE dokazy SET nick='".$_POST['nick']."', popis='".$_POST['popis']."', datum='".$_POST['datum']."', banujuci='".$_POST['meno']."', ban_id='".$_POST['ban_id']."' WHERE id='". $_POST['id'] ."'";
			$conn->query($sql2);
			header('Location: https://midascraft.sk/administracia-serveru-crossout/dokazy/');
		}
		else{
		}
	}
	else{
		echo"Nevyplnil si niektoré z polí nižšie!";
	}
}
elseif (isset($_POST['edit2']) and ! file_exists($_FILES["upload"]["tmp_name"][0])){
	if($_POST['meno']!="" and $_POST['popis']!=""){
		$sql2 = "UPDATE dokazy SET nick='".$_POST['nick']."', popis='".$_POST['popis']."', datum='".$_POST['datum']."', banujuci='".$_POST['meno']."', ban_id='".$_POST['ban_id']."' WHERE id='". $_POST['id'] ."'";
		$conn->query($sql2);
		header('Location: https://midascraft.sk/administracia-serveru-crossout/dokazy/');
	}
	else{
		echo"Nevyplnil si niektoré z polí nižšie!";
	}
}
if (isset($_GET['nick'])) {
	$nickname = $_GET['nick'];
	if (isset($_GET['nove'])) {
		echo "<h1 style='text-align:center;'>Nahrávanie dôkazu pre hráča</h1><br>";
		echo '<h2 style="color:red;text-align:center;">'. $nickname .'</h2><br><br>';
		echo "<h5 style='text-align:center;color:red;'>!!! Pre nahratie videa použi službu YouTube a vlož link do popisu banu !!!</h5><br>";
		$sql2 = "SELECT * FROM litebans_bans WHERE id=\"". $_GET['ban_id'] . "\"";
		$result2 = $conn2->query($sql2);
		$row2 = $result2->fetch_assoc();
		if($row2['until'] == -1){
			$koniec = "Trvalý ban";
		}
		else{
			$koniec = date("d.m.Y H:i",$row2['until']/1000);
		}
		echo '
		<h4 style="display:inline-block">Nick: </h4>&nbsp;&nbsp;&nbsp;' . $_GET["nick"]. '<br>
		<h4 style="display:inline-block">Nick banujúceho: </h4>&nbsp;&nbsp;&nbsp;' . $row2["banned_by_name"]. '<br>
		<h4 style="display:inline-block">Dôvod banu: </h4>&nbsp;&nbsp;&nbsp;' . $row2["reason"]. '<br>
		<h4 style="display:inline-block">Dátum zabanovania: </h4>&nbsp;&nbsp;&nbsp;' . date("d.m.Y H:i",$row2['time']/1000). '<br>
		<h4 style="display:inline-block">Dátum vypršania trestu: </h4>&nbsp;&nbsp;&nbsp;' . $koniec. '<br><hr><br>
		<form style="text-align:center" method="post" enctype="multipart/form-data">
		  <input type="hidden" name="pridat" value="1">
		  <input type="hidden" name="nick" value="'. $nickname .'">
		  <input type="hidden" name="datum" value="'. date("Y-m-d H:i:s") .'">
		  <input type="hidden" name="ban_id" value="'. $_GET['ban_id'] .'">
		  <br><h4>Tvoj nickname:</h4>
		  <input type="text" class="mobil" name="meno" value="" style="width:20%;height:2em !IMPORTANT;"><br><br>
		  <h4>Popis banu:</h4>
		  <textarea name="popis" class="mobil" cols="10" rows="10" style="width:60%;resize:none;"></textarea><br>
		  <h4>Screenshoty: </h4>
		  <input name="upload[]" type="file" multiple="multiple" /><br>
		  <input type="submit" class="potvrdit" value="Uložiť" style="float:center">
		</form>';
	} 
	elseif(isset($_GET['zobraz'])) {
		$sql2 = "SELECT * FROM litebans_bans WHERE id=\"". $_GET['ban_id'] . "\"";
		$result2 = $conn2->query($sql2);
		$sql3 = "SELECT * FROM dokazy WHERE ban_id=\"". $_GET['ban_id'] . "\"";
		$result3 = $conn->query($sql3);
		$row3 = $result3->fetch_assoc();
		$sql4 = "SELECT * FROM dokazy_link WHERE id_dokaz=\"". $row3['id'] . "\"";
		$result4 = $conn->query($sql4);
		if($result2->num_rows > 0){
			while($row2 = $result2->fetch_assoc()) {
				if($row2['until'] == -1){
					$koniec = "Trvalý ban";
				}
				else{
					$koniec = date("d.m.Y H:i",$row2['until']/1000);
				}
				echo'
				<h4 style="display:inline-block">Nick: </h4>&nbsp;&nbsp;&nbsp;' . $_GET["nick"]. '<br>
				<h4 style="display:inline-block">Nick banujúceho: </h4>&nbsp;&nbsp;&nbsp;' . $row2["banned_by_name"]. '<br>
				<h4 style="display:inline-block">Dôvod banu: </h4>&nbsp;&nbsp;&nbsp;' . $row2["reason"]. '<br>
				<h4 style="display:inline-block">Dátum zabanovania: </h4>&nbsp;&nbsp;&nbsp;' . date("d.m.Y H:i",$row2['time']/1000). '<br>
				<h4 style="display:inline-block">Dátum vypršania trestu: </h4>&nbsp;&nbsp;&nbsp;' . $koniec. '<br><hr><br>
				<h4 style="display:inline-block">Dôkaz nahral: </h4>&nbsp;&nbsp;&nbsp;'. $row3["banujuci"] .'<br>
				<h4 style="display:inline-block">Dátum nahrania: </h4>&nbsp;&nbsp;&nbsp;'. date("d.m.Y H:i", strtotime($row3["datum"])) .'<br>
				<h4 style="display:inline-block">Popis banu: </h4>&nbsp;&nbsp;&nbsp;'. $row3["popis"] .'<br><br>
				<h4 style="display:inline-block">Screenshoty: </h4><br>
				
				';
			}
		}
		else{
		}
		if($result4->num_rows > 0){
			while($row4 = $result4->fetch_assoc()) {
				echo '<br><img src=https://www.midascraft.sk/Dokazy/'. $row4["link"] .'>';
			}
		}
		else{
			echo '<br>Nie je priložený žiaden screenshot';
		}
		echo '
		<form style="text-align:center" method="get">
		  <input type="hidden" name="edit" value="1">
		  <input type="hidden" name="nick" value="'. $_GET["nick"] .'">
		  <input type="hidden" name="ban_id" value="'. $_GET['ban_id'] .'">
		  <input type="submit" class="potvrdit" value="Upraviť" style="margin:5px;float:center;background-color:#bf2e2e !IMPORTANT;text-transform: none !IMPORTANT;">
		</form>';
	} 
	elseif(isset($_GET['edit'])) {
		$sql2 = "SELECT * FROM litebans_bans WHERE id=\"". $_GET['ban_id'] . "\"";
		$result2 = $conn2->query($sql2);
		$sql3 = "SELECT * FROM dokazy WHERE ban_id=\"". $_GET['ban_id'] . "\"";
		$result3 = $conn->query($sql3);
		$row3 = $result3->fetch_assoc();
		$sql4 = "SELECT * FROM dokazy_link WHERE id_dokaz=\"". $row3['id'] . "\"";
		$result4 = $conn->query($sql4);
		if($result2->num_rows > 0){
			while($row2 = $result2->fetch_assoc()) {
				if($row2['until'] == -1){
					$koniec = "Trvalý ban";
				}
				else{
					$koniec = date("d.m.Y H:i",$row2['until']/1000);
				}
				echo'
				<h4 style="display:inline-block">Nick: </h4>&nbsp;&nbsp;&nbsp;' . $_GET["nick"]. '<br>
				<h4 style="display:inline-block">Nick banujúceho: </h4>&nbsp;&nbsp;&nbsp;' . $row2["banned_by_name"]. '<br>
				<h4 style="display:inline-block">Dôvod banu: </h4>&nbsp;&nbsp;&nbsp;' . $row2["reason"]. '<br>
				<h4 style="display:inline-block">Dátum zabanovania: </h4>&nbsp;&nbsp;&nbsp;' . date("d.m.Y H:i",$row2['time']/1000). '<br>
				<h4 style="display:inline-block">Dátum vypršania trestu: </h4>&nbsp;&nbsp;&nbsp;' . $koniec. '<br><hr><br>
				
				<form style="text-align:center" method="post" enctype="multipart/form-data">
				  <input type="hidden" name="edit2" value="1">
				  <input type="hidden" name="id" value="'. $row3['id'].'">
				  <input type="hidden" name="nick" value="'. $nickname .'">
				  <input type="hidden" name="datum" value="'. date("Y-m-d H:i:s") .'">
				  <input type="hidden" name="ban_id" value="'. $_GET['ban_id'] .'">
				  <br><h4>Tvoj nickname:</h4>
				  <input type="text" class="mobil" name="meno" value="'.$row3["banujuci"].'" style="width:20%;height:2em !IMPORTANT;"><br><br>
				  <h4>Popis banu:</h4>
				  <textarea name="popis" class="mobil" cols="10" rows="10" style="width:60%;resize:none;" >'. $row3["popis"] .'</textarea><br>
				  <h4>Screenshoty: </h4>
				  <input name="upload[]" type="file" multiple="multiple" /><br>
				  <input type="submit" class="potvrdit" value="Uložiť" style="float:center">
				</form>
				<h4 style="display:inline-block">Nahrané screenshoty: </h4><br>
				
				';
			}
		}
		else{
		}
		if($result4->num_rows > 0){
			while($row4 = $result4->fetch_assoc()) {
				echo'
				<form style="text-align:center" method="post">
				  <input type="hidden" name="id" value="'. $row4["id"] .'">
				  <input type="hidden" name="zmazat_obrazok" value="1">
				  <input type="hidden" name="link" value="'. $row4["link"] .'">
				  <input type="submit" class="potvrdit" value="&#8595;  ZMAZAŤ  &#8595;" style="margin:5px;float:center;background-color:#bf2e2e !IMPORTANT;text-transform: none !IMPORTANT;">
				</form>';
				echo '<br><img src=https://www.midascraft.sk/Dokazy/'. $row4["link"] .'>';
			}
		}
		else{
			echo '<br>Nie je priložený žiaden screenshot';
		}
	} 
} 
elseif(isset($_GET['nick2'])){
	echo '<h3 style="display:inline-block">Nájdené výsledky pre: ' . $_GET["nick2"]. '</h3><br>';
	$sql2 = "SELECT * FROM litebans_history WHERE name=\"". $_GET['nick2'] . "\"";
	$result2 = $conn2->query($sql2);
	if ($result2->num_rows > 0) {
		$row = $result2->fetch_assoc();
		$sql3 = "SELECT * FROM litebans_bans WHERE uuid=\"". $row['uuid'] . "\" ORDER BY id DESC";
		$result3 = $conn2->query($sql3);
		if ($result3->num_rows > 0) {
			echo '<div style="overflow-x:auto !IMPORTANT;">';
			echo '<table class="widefat color padd"><tbody>';
			while($row2 = $result3->fetch_assoc()) {
				$sql4 = "SELECT * FROM dokazy WHERE ban_id=\"". $row2['id'] . "\"";
				$result4 = $conn->query($sql4);
				$dokaz_je = $result4->num_rows;
				
				if($dokaz_je >= 1){
					echo '
					<form target="_blank" style="text-align:center" method="get">
					  <input type="hidden" name="nick" value="'. $_GET['nick2'] .'">
					  <input type="hidden" name="zobraz" value="1">
					  <input type="hidden" name="ban_id" value="'. $row2['id'] .'">
					  <input type="submit" class="potvrdit" value="'. $_GET['nick2'].' - '. date("d.m.Y H:i",$row2['time']/1000) .'" style="margin:5px;float:center;background-color:green !IMPORTANT;text-transform: none !IMPORTANT;">
					</form>';
				}
				else{
					echo '
					<form target="_blank" style="text-align:center" method="get">
					  <input type="hidden" name="nove" value="1">
					  <input type="hidden" name="nick" value="'. $_GET['nick2'] .'">
					  <input type="hidden" name="ban_id" value="'. $row2['id'] .'">
					  <input type="submit" class="potvrdit" value="'. $_GET['nick2'].' - '. date("d.m.Y H:i",$row2['time']/1000) .'" style="margin:5px;float:center;background-color:#bf2e2e !IMPORTANT;text-transform: none !IMPORTANT; !IMPORTANT;">
					</form>';
				}
			}
			echo '</tbody></table>';
			echo '</div>';
		}
		else{
			echo "Zadaný nick nedostal ešte žiaden ban!";
		}
	}
	else{
		echo "Zadaný nick sa ešte nepripojil na server!";
	}
}
else {
	echo "<h1 style='text-align:center;'>Dôkazy banov</h1><br>";
	echo "<br><p style='text-align:center;'>Celkový počet dôkazov: ";
	echo "<h2 style='text-align:center; font-weight:bold;'><i class='fas fa-images'></i> ". $celkovo_dokazy . "</h2></p>";
	echo '<br>
	<form style="text-align:center" method="get">
	  <input type="search" class="hladat" placeholder="Meno…" value="" name="nick2" style="float:center">
	  <input type="submit" class="potvrdit" value="Nájdi" style="float:center">
	</form>';
	echo '<br><h3>Posledné bany:</h3>';
	if ($result->num_rows > 0) {
		echo '<div style="overflow-x:auto !IMPORTANT;">';
		echo '<table class="widefat color padd"><tbody>';
		while($row = $result->fetch_assoc()) {
			if(!empty($row['uuid'])){
				$sql2 = "SELECT * FROM litebans_history WHERE uuid=\"". $row['uuid'] . "\"";
				$result2 = $conn2->query($sql2);
				if($result2->num_rows > 0){
					while($row2 = $result2->fetch_assoc()) {
						$meno2 = $row2['name'];
					}
				}
				else{
					$meno2 = "Neznámy nick";
				}
			}
			else{
				$meno2 = "---";
			}
			$sql3 = "SELECT * FROM dokazy WHERE ban_id=\"". $row['id'] . "\"";
			$result3 = $conn->query($sql3);
			$dokaz_je = $result3->num_rows;
			
			if($dokaz_je >= 1){
				echo '
				<form target="_blank" style="text-align:center" method="get">
				  <input type="hidden" name="nick" value="'. $meno2 .'">
				  <input type="hidden" name="zobraz" value="1">
				  <input type="hidden" name="ban_id" value="'. $row['id'] .'">
				  <input type="submit" class="potvrdit" value="'. $meno2.'" style="margin:5px;float:center;background-color:green !IMPORTANT;text-transform: none !IMPORTANT;">
				</form>';
			}
			else{
				echo '
				<form target="_blank" style="text-align:center" method="get">
				  <input type="hidden" name="nove" value="1">
				  <input type="hidden" name="nick" value="'. $meno2 .'">
				  <input type="hidden" name="ban_id" value="'. $row['id'] .'">
				  <input type="submit" class="potvrdit" value="'. $meno2.'" style="margin:5px;float:center;background-color:#bf2e2e !IMPORTANT;text-transform: none !IMPORTANT;">
				</form>';
			}
		}
		echo '</tbody></table>';
		echo '</div>';	
	} 
	else
	{
	echo "prazdna tabulka";
	}



}




  

$conn->close();
$conn2->close();
$conn3->close();
?>