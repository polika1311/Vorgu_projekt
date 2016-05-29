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
function hangi_user($id){
 global $connection;
$vaartus = mysqli_real_escape_string($connection, $id);
 	$sql ="SELECT * FROM ppopova_kliendid WHERE id='$id'";
 	$result = mysqli_query($connection, $sql)or die("Ei saanud looma kätte");
 	$looma_andmed=array();
 	while($rida = mysqli_fetch_assoc($result)) {
		 $looma_andmed=$rida;
	}
	
	return $looma_andmed;
}
  
  		 
   


function kuva_info(){
	if (empty($_SESSION['user'])) {
		header("Location: ?page=login");
	}
	
	global $connection;
	$kliendid=array();

	
    $sql = "SELECT * FROM ppopova_kliendid ORDER BY ID DESC LIMIT 1";

    $result = mysqli_query($connection, $sql);
    $row = mysqli_fetch_assoc($result);
    $kliendid=$row;

    $nimi=$row['kliendi_nimi'];
    $auto = $row['autonumber'];
    $email = $row['email'];
    $pesu = $row['pesu'];
    $date = $row['date'];

	include_once('views/tulemus.html');//tulemus.html
	
	
}
function kuva_all(){
	if (empty($_SESSION['user'])) {
		header("Location: ?page=login");
	}
	
	global $connection;
	$kliendid=array();

	
    $sql = "SELECT id, kliendi_nimi,autonumber, email, date, pesu, aeg FROM ppopova_kliendid ORDER BY date ASC";

    $result = mysqli_query($connection, $sql);
    if (mysqli_num_rows($result) > 0) {
    // output data of each row
    while($row = mysqli_fetch_assoc($result)) {
	    $kliendid []=$row;
    
    }
    
} else {
    echo "0 results";
}



	include_once('views/halda.html');//tulemus.html
	
	
}

function logi(){
	
	
	// siia on vaja funktsionaalsust (13. nädalal)
	
	if (isset($_SESSION['user'])) {
		header ('Location:?page=bronn');
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
			$kasutaja = mysqli_real_escape_string($connection,( $_POST['user']));
			$parool = mysqli_real_escape_string($connection, ( $_POST['pass']));
		
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
					
		if (isset ($_SESSION ['user']) && $_SESSION['user']=='admin') {
		header("Location: ?page=halda");
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

	if (empty($_SESSION['user'])) {
		header("Location: ?page=login");
	}else {
		if ($_SESSION ['roll']=='user') 
		{
	if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
		//kui meetodiks oli POST, tuleb kontrollida, kas kõik vormiväljad olid täidetud ja tekitada vajadusel vastavaid veateateid (massiiv $errors).
		$errors = array();
		if (empty($_POST['nimi'])) {
			$errors[] = "Palun sisetage oma nimi!";
		}
		if (empty($_POST['auto'])) {
			$errors[] = "Palun sisetage autonumber!";
		}
		if (empty($_POST['email'])) {
			$errors[] = "Palun sisetage oma e-mail!";
		}
		if (empty($_POST['date'])) {
			$errors[] = "Palun valige sobiva kuupäeva";
		}
		if (empty($_POST['teenus'])) {
			$errors[] = "Palun valige teenus";
		}
		if (empty($errors)) {
			global $connection;
			$nimi = mysqli_real_escape_string($connection,$_POST['nimi']);
			$auto = mysqli_real_escape_string($connection,$_POST['auto']);
			$email = mysqli_real_escape_string($connection, $_POST['email']);
			$teenus = mysqli_real_escape_string($connection,$_POST['teenus']);
			$date = mysqli_real_escape_string($connection,  $_POST['date']);
			$time = mysqli_real_escape_string($connection,  $_POST['aeg']);
			$comment = mysqli_real_escape_string($connection,  $_POST['comment']);
			
			$sql = "INSERT INTO ppopova_kliendid (kliendi_nimi, autonumber, email, pesu, date, aeg, comment) VALUES ('$nimi', '$auto', '$email','$teenus',
			 '$date', '$time','$comment')";
			$result = mysqli_query($connection, $sql) or die("Looma lisamine ebaõnnestus");
			
			if ($result>0){
				if (mysqli_insert_id($connection) > 0) {
					header("Location: ?page=result");//nagu puurid
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
	}else{
	if ($_SESSION['roll'] == 'admin') {
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			if($_POST["id"] == ''){
				header("Location: ?page=halda");
				}else{
					$muutuja = hangi_user($_POST["id"]);
				
					$eraldi_id = $_POST["id"];
					
					
					$date = mysqli_real_escape_string ($connection,  $_POST["date"]);
					$time = mysqli_real_escape_string($connection, $_POST['aeg']);
					
					$muutuja = [
						"date" => $date,
						"aeg" => $time,
						
						
					];
					$sql = "UPDATE ppopova_kliendid SET aeg='$time', date='$date' WHERE id='$eraldi_id'";
					$result = mysqli_query($connection, $sql);
					$rida = mysqli_affected_rows($connection);
					if($rida){
						header("Location: ?page=halda");
					}else{
						header("Location: ?page=pealeht");
					}
}
}
}else{
	header("Location: ?page=halda");
 }
}
include_once('views/editvorm.html');

}
?>