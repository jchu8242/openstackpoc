<!DOCTYPE HTML>
<html>
<head>
  <title>Interfaces</title>
</head>
<body>
	<table border="1">
		<tr>
			<th> Network Name </th>
			<th> Network address </th>
			<th> Device type </th>
			<th> OpenStack port ID </th>
			<th> Linux Device Name Prefix </th>
			<th> Linux Device Name Suffix </th>
		</tr>

	<?php
		//declare server details
		$host='192.168.1.24';
		$username='root';
		$password='root';
		$dbname='neutron';

		//create new connection
		$conn = new mysqli($host, $username, $password, $dbname);
		//check connection
		if (!$conn) {
			die("connection failed");
		}

		//declare results of SQL query
		$result= mysqli_query ($conn, 'SELECT networks.name, subnets.cidr, ports.device_owner , ports.id, SUBSTRING(ports.id, 1, 11) AS LDN
			FROM networks 
			INNER JOIN subnets 
			ON networks.id=subnets.network_id
			INNER JOIN ports 
			ON subnets.network_id=ports.network_id 
			WHERE device_owner IN ("compute:nova", "network:dhcp", "network:router_interface", "network:router_gateway")
			ORDER BY ports.device_owner');

			//validate Linux Device Prefix
			function validatePrefix($deviceType) {

				switch($deviceType) {
					case "compute:nova":
						echo "qvo, qvb, tap";
						break;
					case "network:router_gateway":
						echo "qg";
						break;
					case "network:router_interface":
						echo "qr";
						break;
					case "network:dhcp":
						echo "tap";
						break;
				}

			}


		while ($row=mysqli_fetch_array($result)) {

			//assigning variables to row values
			$networkName=$row['name'];
			$networkAddress=$row['cidr'];
			$deviceType=$row['device_owner'];
			$openstackPortId=$row['id'];
			$LinuxDeviceNameSuffix=$row['LDN'];

				echo "<tr>";
				echo "<td>" . $networkName . "</td>";
				echo "<td>" . $networkAddress . "</td>";
				echo "<td>" . $deviceType . "</td>";
				echo "<td>" . $openstackPortId . "</td>";
				echo "<td>" . validatePrefix($deviceType) . "</td>";
				echo "<td>" . $LinuxDeviceNameSuffix . "</td>";
				echo "</tr>";

		}
		echo '</table>';

		mysqli_close($conn);

	?>
</body>



</html>
