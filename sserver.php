<html>
<body>
<?php
  include("constants.php");
  $input=$_POST["input"];
  $source=$_POST["source"]; 
	$dbhost = DB_SERVER;
	 $dbuser = DB_USER;
 	  $dbpass = DB_PASS;

$i=4;
$bytes = openssl_random_pseudo_bytes($i, $cstrong);
    $hex   = bin2hex($bytes);

//Sanitisation source code
$source=str_ireplace("&","&amp;","$source");
$source=str_ireplace("<","&lt;","$source");
$source=str_ireplace(">","&gt;","$source");
$source=str_ireplace("\\","\\\\","$source");
$source=str_ireplace("'","''","$source");

//Creating datbase connection
$conn = mysql_connect($dbhost, $dbuser, $dbpass);

if(! $conn )
{
 die('Could not connect: ' . mysql_error());
}
//Inserting source code into database
$sql = "INSERT INTO submissions (id,source,input) VALUES ( '$hex','$source', '$input' )";
mysql_select_db(DB);
$retval = mysql_query( $sql, $conn );
if(! $retval )
{
 die('Could not enter source: ' . mysql_error());
}
/*
//Getting Id
$sql = "INSERT INTO counter (dummy) VALUES ( 6 )";
mysql_query($sql,$conn);
$sql1 = "SELECT * FROM counter ";
$result1=  mysql_query($sql1);
$sql="DELETE FROM counter";
mysql_query($sql);
$f1=mysql_fetch_array($result1);*/

//UDP Socket programming
//send message $f1[0];
error_reporting(~E_WARNING);

$server = SERVER;
$port = PORT;

if(!($sock = socket_create(AF_INET, SOCK_DGRAM, 0)))
{
	$errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);
     die("Couldn't create socket: [$errorcode] $errormsg \n");
}
         //Id of submission to be compiled
        $data=$hex;
	

	$input = $data;
	
	//Send the message to the daemon server
	if( ! socket_sendto($sock, $input , strlen($input) , 0 , $server , $port))
	{
		$errorcode = socket_last_error();
		$errormsg = socket_strerror($errorcode);
		
		die("Could not send data: [$errorcode] $errormsg \n");
	}

$sql1 = "SELECT * FROM submissions where id=$data";
$result =  mysql_query($sql1);
$f=mysql_fetch_array($result);

        while ( $f[4]!= 1 ) 
	{
            sleep(1);
         $sql1 = "SELECT * FROM submissions where id='$data'";
       	$result =  mysql_query($sql1);
	   $f=mysql_fetch_array($result);            
        }
mysql_close($conn);
//echo $f[3];
$output=$f[3];
$output1 = explode("\n",$output);
	   $acount = count($output1);
//echo $output;
//<textarea cols="400" rows="300" name="output" id="output">
 
echo "Output:  ";
if(!$output) {
    echo "Error!!!";
	}
   else {
        for($i=0;$i<$acount;$i++)
            {
            echo "<br>$output1[$i]";
          } 
    }

//</textarea>

?>
</body>
</html>

