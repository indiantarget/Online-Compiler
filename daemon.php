<?php
include("constants.php");

	$dbhost = DB_SERVER;
	 $dbuser = DB_USER;
 	  $dbpass = DB_PASS;;
   $conn = mysql_connect($dbhost, $dbuser, $dbpass);

if(! $conn )
{
 die('Could not connect: ' . mysql_error());
}
mysql_select_db(DB);


//UDP socket programming
//Reduce errors
error_reporting(~E_WARNING);
 
//Create a UDP socket
if(!($sock = socket_create(AF_INET, SOCK_DGRAM, 0)))
{
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);
     
    die("Couldn't create socket: [$errorcode] $errormsg \n");
}
 
echo "Socket created \n";
// Bind the source address
if( !socket_bind($sock, SERVER ,PORT) )
{
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);
    die("Could not bind socket : [$errorcode] $errormsg \n");
}
 
echo "Socket bind OK \n";

//recieve message from socket $f1[0];
while(1)
{
    echo "Waiting for data ... \n";
     
    //Receive some data
    $r = socket_recvfrom($sock, $buf, 512, 0, $remote_ip, $remote_port);
    echo "$remote_ip : $remote_port -- " . $buf;
    echo "\nRequest for id=$buf";
$sql1 = "SELECT * FROM submissions where id='$buf'";
$result =  mysql_query($sql1);

$f=mysql_fetch_array($result);

$source=$f[1];
$source=str_ireplace("&amp;","&",$source);
$source=str_ireplace("&lt;","<",$source);
$source=str_ireplace("&gt;",">",$source);
$source=str_ireplace("\\\\","\\",$source);
file_put_contents("source.cpp","$source");
file_put_contents("input.txt","$f[2]");

//batch file to compile
shell_exec('sh compilescript.sh');


$file="error.txt";
if(file_exists($file))
{
$osource=file_get_contents("error.txt","r");
}
else
{
$osource=file_get_contents ("output.txt","r");
}
$osource=str_ireplace("&","&amp;","$osource");
$osource=str_ireplace("<","&lt;","$osource");
$osource=str_ireplace(">","&gt;","$osource");
$osource=str_ireplace("\\","\\\\","$osource");
$osource=str_ireplace("'","''","$osource");
$sql = "UPDATE  submissions SET output='$osource',checkbit='1' WHERE id='$buf'";//update check bit
mysql_query($sql);
//send reply
//socket_sendto($sock, "OK " . $buf , 100 , 0 , $remote_ip , $remote_port);

//batch file to delete
shell_exec('sh delete.sh');

echo "\n";

}//infinite loop closed
 
socket_close($sock);


mysql_close($conn);

?>
