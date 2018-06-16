<!DOCTYPE HTML>
<html>
<head>
  <title>Interfaces</title>
</head>
<body>
	<table border="1">
		<tr>
			<th> Network Name <th>
			<th> Network address <th>
			<th> Device type <th>
			<th> OpenStack port ID <th>
			<th> Linux Device Name Suffix <th>
		</tr>;

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


		while ($row = mysqli_fetch_array($result)) {
			echo "<tr>";
			echo "<td>" . $row['name'] . "</td>";
			echo "<td>" . $row['cidr'] . "</td>";
			echo "<td>" . $row['device_owner'] . "</td>";
			echo "<td>" . $row['id'] . "</td>";
			echo "<td>" . $row['LDN'] . "</td>";
			echo "</tr>";
		}
		echo '</table>';

		mysqli_close($conn);

	?>
</body>



</html>