<!DOCTYPE html>
<html>
	<head>
		<title>Compute Instance TCP DUMP</title>
	</head>

	<body>
		<?php

			//assign variables	
			$prefix="qvb";
			$suffix="xxxxxx";
			$interface = $prefix . $suffix;
			$user="root";
			$host="jonochu90.mynetgear.com";
			$password="Sickcunts3411";
			$port="2222";
			$cmd="tcpdump ";
			$finalcmd=$cmd . $prefix . $suffix;
			
			function ssh_func($user, $host, $password, $port){
				$connection = ssh2_connect($host, $port);
				if (ssh2_auth_password($connection, $user, $password)) {
					echo "Authentication Successful!\n";
				  } else {
					die('Authentication Failed...');
				  }
			}

			ssh_func($user, $host, $password, $port);

			




		?>
	</body>

</html>