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
			
			function SSH_func($ssh_command) {

				$control_ip_add = '192.168.1.24';
				$control_ssh_username = 'root';
				$control_ssh_pw = 'root';
	
				$connection = ssh2_connect($control_ip_add, '2222');
				ssh2_auth_password($connection, $control_ssh_username, $control_ssh_pw);
				$stream = ssh2_exec($connection, $ssh_command);
				stream_set_blocking($stream, true);
				$stream_output = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);
				$ssh_output = stream_get_contents($stream_output);
				return $ssh_output;
			}

			$ssh_command='ls -la';
			SSH_func($ssh_command);

			




		?>
	</body>

</html>