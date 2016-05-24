
<?php

if (file_exists("people.txt"))
{
	$fil=fopen("people.txt",'r');
	$dat=fread($fil, filesize("people.txt"));
	echo $dat+1;
	fclose($fil);
	$fil=fopen("people.txt",'w');
	fwrite($fil,$dat+1);
	
	}
	else {
	$fil=fopen("people.txt",w);
	fwrite($fil,1);
	echo"1";
	fclose($fil);
	}
?>