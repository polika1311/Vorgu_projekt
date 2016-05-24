<?php


function connect_db(){
	global $connection;
	$host="localhost";
	$user="test";
	$pass="t3st3r123";
	$db="test";
	$connection = mysqli_connect($host, $user, $pass, $db) or die("ei saa ühendust mootoriga- ".mysqli_error());
	mysqli_query($connection, "SET CHARACTER SET UTF8") or die("Ei saanud baasi utf-8-sse - ".mysqli_error($connection));
}

function reg(){
	global $connection;
	if(!empty($_POST)){
		$errors=array();
		if (empty($_POST['user'])){
			$errors[]="kasutajanimi vajalik!";
		}
		if (empty($_POST['passw'])){
			$errors[]="parool vajalik!";
		}
		if (empty($_POST['passw2'])){
			$errors[]="parooli peab 2 korda panema!";
		}
		if(!empty($_POST['passw']) && !empty($_POST['passw2']) && $_POST['passw']!=$_POST['passw2']) {
			// m?lemad on olemas, aga ei v?rdu
			$errors[]="paroolid peavad olema samad!";
		}
		if (empty($errors)){
			// turva
		
			$user=mysqli_real_escape_string($connection,$_POST['user']);
			$pass=mysqli_real_escape_string($connection,$_POST['passw']);
			
			$sql="INSERT INTO ppopova_kylastajad (username, passw) VALUES ('$user', SHA1('$pass'))";
			$result = mysqli_query($connection, $sql);
			if ($result){
				// kõik ok, 
				$_SESSION['message']="Registreerumine õnnestus, logi sisse";
				header("Location: ?page=bronn");
				exit(0);
			} else {
				$errors[]="Registreerumine luhtus, proovi hiljem jälle...";
			}
		}
	}
	include("views/registreeri.html");
}
function hangi_loom($id){
 global $connection;
 $tulemused = array();
 
 $vaartus = mysqli_real_escape_string($connection, $id);
 	$sql ="SELECT nimi, vanus, liik, PUUR FROM polina1311_loomaaed WHERE id='$id'";
 	$result = mysqli_query($connection, $sql) or die("Sellist looma baasis pole
 	");
 
 while($r = mysqli_fetch_assoc($result)){
 	$tulemused=$r;
 }
  		  
  return $tulemused;
  }		 
   


function kuva_puurid(){
	// siia on vaja funktsionaalsust
	//Kontrollida, kas kasutaja on sisse logitud. Kui pole, suunata sisselogimise vaatesse
	if (empty($_SESSION['user'])) {
		header("Location: ?page=login");
	}
	
	global $connection;
	$puurid=array();
	
	$p= mysqli_query($connection, "SELECT DISTINCT puur FROM polina1311_loomaaed");
	
	while ($r=mysqli_fetch_assoc($p)){
		$sql2 = "select * from polina1311_loomaaed WHERE puur = {$r['puur']}";
      $result2 = mysqli_query($connection, $sql2) or die("$sql2 - ".mysqli_error($connection));
		while ($row=mysqli_fetch_assoc($result2)) {
			$puurid[$r['puur']][]=$row;
		}
	}
	include_once('views/puurid.html');//tulemus.html
	
	
}

function logi(){
	
	
	// siia on vaja funktsionaalsust (13. nädalal)
	
	if (isset($_SESSION['user'])) {
		header ('Location:?page=loomad');
	}else {
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			// Kui meetodiks oli POST, kontrollida kas vormiväljad olid täidetud. Vastavalt vajadusele tekitada veateateid (massiiv $errors)
			$errors = array();
			if (empty($_POST['user'])) {
				$errors[] = "Kasutajatunnus puudu!";
			}
			if (empty($_POST['pass'])) {
				$errors[] = "Parool puudu!";
			}
			
			if (empty($errors)) {
				global $connection;
			$kasutaja = mysqli_real_escape_string($connection,($_POST['user']));
			$parool = mysqli_real_escape_string($connection, ($_POST['pass']));
		
				//$parool = sha1($parool);
				$query = "SELECT roll FROM ppopova_kylastajad WHERE username = '$kasutaja' and passw= SHA1('$parool')";
				$result = mysqli_query($connection, $query) or die("$query - ".mysqli_error($connection));
				$rows = mysqli_num_rows($result);
				$roll=mysqli_fetch_assoc($result);
				if ($rows>=1) {
					$_SESSION["roll"]=$roll["roll"];
					$_SESSION["user"] = $kasutaja;
					header("Location:?page=bronn");
				}else{
					header("Location:?page=login");
					}
				
			} 
		}
	
}

	include_once('views/login.html');
}

function logout(){
	$_SESSION=array();
	session_destroy();
	header("Location: ?");
}

function lisa(){
	// siia on vaja funktsionaalsust (13. nädalal)
	//Kontrollib, kas kasutaja on sisse logitud. Kui pole, suunab sisselogimise vaatesse
	if (empty($_SESSION['user'])) {
		header("Location: ?page=login");
	}else {
		if ($_SESSION ['roll']=='user') 
		{
	if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
		//kui meetodiks oli POST, tuleb kontrollida, kas kõik vormiväljad olid täidetud ja tekitada vajadusel vastavaid veateateid (massiiv $errors).
		$errors = array();
		if (empty($_POST['nimi'])) {
			$errors[] = "Nimi sisestamata!";
		}
		if (empty($_POST['puur'])) {
			$errors[] = "Puur sisestamata!";
		}
		$pilt = upload("liik");
		if ($pilt == "") {
			$errors[] = "Liik sisestamata!";
		}
		if (empty($errors)) {
			global $connection;
			$nimi = mysqli_real_escape_string($connection, $_POST["nimi"]);
			$puur = mysqli_real_escape_string($connection, $_POST["puur"]);
			
			$sql = "INSERT INTO polina1311_loomaaed (nimi, puur, liik) VALUES ('$nimi', '$puur', '$pilt')";
			$result = mysqli_query($connection, $sql) or die("Looma lisamine ebaõnnestus");
			
			if ($result>0){
				if (mysqli_insert_id($connection) > 0) {
					header("Location: ?page=tulemus");//nagu puurid
				}else {
					header("Location: ?page=bronn");
					
				}
			}
		}
	}
}
	}
	
	include_once('views/bronn.html');
	
}


function muuda(){
	global $connection;
	if (empty($_SESSION['user'])) {
		header("Location: ?page=login");
	}else {
		if ($_SESSION ['roll']=='admin') 	{
	if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
		if ($_POST["id"]== ''){
		header("Location:?page=loomad");
	}else{
		$vaartus=hangi_loom($_POST["id"]);
		$vaartus1=$_POST["id"];
		
		
			$nimi = mysqli_real_escape_string($connection, $_POST["nimi"]);
			$puur = mysqli_real_escape_string($connection, $_POST["puur"]);
			$vaartus=[
			"nimi" => $nimi,
			"puur" => $puur,

					];
			
			$sql = "UPDATE polina1311_loomaaed SET nimi=$nimi', puur='$puur'WHERE id='$vaartus1'";
			$result = mysqli_query($connection, $sql);
			$rida=mysqli_affected_rows($connection);
			
			if ($rida){
				
					header("Location: ?page=loomad");
				}else {
					header("Location: ?page=pealeht");
					
				}
			}
		}
	
}
else{
	
	
	header("Location: ?page=loomad");
	
}
}

include_once ("views/editvorm.html");
}
function upload($name){

		$allowedExts = array("jpg", "jpeg", "gif", "png");
	$allowedTypes = array("image/gif", "image/jpeg", "image/png","image/pjpeg");
	$extension = end(explode(".", $_FILES[$name]["name"]));

	if ( in_array($_FILES[$name]["type"], $allowedTypes)
		&& ($_FILES[$name]["size"] < 100000)
		&& in_array($extension, $allowedExts)) {
    // fail õiget tüüpi ja suurusega
		if ($_FILES[$name]["error"] > 0) {
			$_SESSION['notices'][]= "Return Code: " . $_FILES[$name]["error"];
			return "";
		} else {
      // vigu ei ole
			if (file_exists("pildid/" . $_FILES[$name]["name"])) {
        // fail olemas ära uuesti lae, tagasta failinimi
				$_SESSION['notices'][]= $_FILES[$name]["name"] . " juba eksisteerib. ";
				return "pildid/" .$_FILES[$name]["name"];
			} else {
        // kõik ok, aseta pilt
				move_uploaded_file($_FILES[$name]["tmp_name"], "pildid/" . $_FILES[$name]["name"]);
				return "pildid/" .$_FILES[$name]["name"];
			}
		}
	} else {
		return "";
	}
}

?>