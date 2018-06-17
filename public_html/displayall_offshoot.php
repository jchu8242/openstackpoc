<html>

	<html>
	<head>
		<title> Our Site </title>
	</head>
	<body>
		<center>
		<h1>Main Page</h1>

		<?php

		#Keystone - project/tenant query
		$keystone_project = mysqli_connect("192.168.1.24", "root", "root");
		if (!$keystone_project)
			die("Could not connect to server");
		mysqli_select_db($keystone_project,"keystone");
		$result1 = mysqli_query($keystone_project,"select id,name from project;");
		$num_rows_result1 = mysqli_num_rows($result1);

		#Neutron - networks query
		$neutron_networks = mysqli_connect("192.168.1.24", "root", "root");
		if (!$neutron_networks)
			die("Could not connect to server");
		mysqli_select_db($neutron_networks,"neutron");
		$result2 = mysqli_query($neutron_networks,"select name,id,project_id from networks;");

		#Neutron - subnets/cidr query
		$neutron_subnets = mysqli_connect("192.168.1.24", "root", "root");
		if (!$neutron_networks)
			die("Could not connect to server");
		mysqli_select_db($neutron_networks,"neutron");
		$result3 = mysqli_query($neutron_networks,"select network_id,cidr from subnets;");

		#Neutron - ports query
		$neutron_ports_ip = mysqli_connect("192.168.1.24", "root", "root");
		if (!$neutron_ports_ip)
			die("Could not connect to server");
		mysqli_select_db($neutron_ports_ip,"neutron");
		$result4 = mysqli_query($neutron_ports_ip,"select ports.id,ports.network_id,ports.device_owner,ports.device_id, ipallocations.ip_address from ports inner join ipallocations on ports.id=ipallocations.port_id");

		#Neutron - routers query
		$neutron_routers = mysqli_connect("192.168.1.24", "root", "root");
		if (!$neutron_routers)
			die("Could not connect to server");
		mysqli_select_db($neutron_routers,"neutron");
		$result5 = mysqli_query($neutron_routers,"select id,name from routers;");

		#Nova - instances query
		$nova_instances = mysqli_connect("192.168.1.24", "root", "root");
		if (!$nova_instances)
			die("Could not connect to server");
		mysqli_select_db($nova_instances,"nova");
		$result6 = mysqli_query($nova_instances,"select uuid,display_name from instances;");


		function SSH_func($ssh_command) {

			$control_ip_add = '192.168.1.24';
			$control_ssh_username = 'root';
			$control_ssh_pw = 'root';

			$connection = ssh2_connect($control_ip_add, '22');
			ssh2_auth_password($connection, $control_ssh_username, $control_ssh_pw);
			$stream = ssh2_exec($connection, $ssh_command);
			stream_set_blocking($stream, true);
			$stream_output = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);
			$ssh_output = stream_get_contents($stream_output);
			return $ssh_output;
		}

		function Get_first_11_char($string) {
			return substr($string, 0, 11);
		}

		if ($num_rows_result1 > 0) {
		##ALSO PUT HERE AND IF NETWORKS > 0 AS WELL
			print "<table><tr><td><b>Project Name:</b></td><td><b>Network Name:</b></td><td><b>Network Address/Prefix:</b></td><td><b>Interfaces Type:</b></td><td><b>Name:</b></td><td><b>IP Address:</b></td><td><b>OpenStack Port ID:</b></td><td><b>Port Type (or system that its connected to):</b></td><td><b>Linux Device Name:</b></td><td><b>TCPDUMP:</b></td><td><b>Comments:</b></tr></tr>";

			while ($row_results1 = mysqli_fetch_array($result1,MYSQLI_NUM)) {
				if ($row_results1[1] !== "<<keystone.domain.root>>") {
					while ($row_results2 = mysqli_fetch_array($result2,MYSQLI_NUM)) {
						if ($row_results2[2] == $row_results1[0]) {
							while ($row_results3 = mysqli_fetch_array($result3,MYSQLI_NUM)) {
								if ($row_results3[0] == $row_results2[1]) {
									while ($row_results4 = mysqli_fetch_array($result4,MYSQLI_NUM)) {
										if ($row_results4[1] == $row_results2[1]) {
											if (strpos($row_results4[2],'nova') == true) {
												print "<tr><td>$row_results1[1]</td><td>$row_results2[0]</td><td>$row_results3[1]</td><td>Instance</td>";
												while ($row_results6 = mysqli_fetch_array($result6,MYSQLI_NUM)) {
													if ($row_results6[0] == $row_results4[3]) {
														print "<td>$row_results6[1]</td><td>$row_results4[4]</td><td>$row_results4[0]</td>";
														$ssh_output1 = SSH_func('brctl show');
														$ssh_output2 = SSH_func('ovs-vsctl show');
														$append_char_str = Get_first_11_char($row_results4[0]);
														$lb_tap_string = 'tap'.$append_char_str;												
														$qbr_string = 'qbr'.$append_char_str;
														$qvb_string = 'qvb'.$append_char_str;
														$qvo_string = 'qvo'.$append_char_str;												
														if (strpos($ssh_output1, $lb_tap_string) == true) {
															print "<td>Linux Bridge</td><td>$lb_tap_string</td></tr>";
														}
														else {
															print "<td>N/A - Error</td><td>$lb_tap_string</td><td>It appears that the Tap interface for Instance: $row_results6[1] does not exist, or the instance is not powered on</td></tr>";
														}
														if (strpos($ssh_output1, $qbr_string) == true) {
															print "<tr><td>$row_results1[1]</td><td>$row_results2[0]</td><td>$row_results3[1]</td><td>Instance</td><td>$row_results6[1]</td><td>$row_results4[4]</td><td>$row_results4[0]</td><td>Linux Bridge</td><td>$qbr_string</td></tr>";
														}
														else {
															print "<tr><td>$row_results1[1]</td><td>$row_results2[0]</td><td>$row_results3[1]</td><td>Instance</td><td>$row_results6[1]</td><td>$row_results4[4]</td><td>$row_results4[0]</td><td>N/A - Error</td><td>It appears that the Linux Bridge for Instance: $row_results6[1] does not exist</td></tr>";
														}
														if (strpos($ssh_output1, $qvb_string) == true) {
															print "<tr><td>$row_results1[1]</td><td>$row_results2[0]</td><td>$row_results3[1]</td><td>Instance</td><td>$row_results6[1]</td><td>$row_results4[4]</td><td>$row_results4[0]</td><td>Linux Bridge</td><td>$qvb_string</td></tr>";
														}
														else {
															print "<tr><td>$row_results1[1]</td><t>$row_results2[0]</td><td>$row_results3[1]</td><td>Instance</td><td>$row_results6[1]</td><td>$row_results4[4]</td><td>$row_results4[0]</td><td>N/A - Error</td><td>It appears that the qvb-vEth for Instance: $row_results6[1] does not exist</td></tr>";
														}
														if (strpos($ssh_output2, $qvo_string) == true) {
															print "<tr><td>$row_results1[1]</td><td>$row_results2[0]</td><td>$row_results3[1]</td><td>Instance</td><td>$row_results6[1]</td><td>$row_results4[4]</td><td>$row_results4[0]</td><td>Open vSwitch</td><td>$qvo_string</td></tr>";
														}
														else {
															print "<tr><td>$row_results1[1]</td><td>$row_results2[0]</td><td>$row_results3[1]</td><td>Instance</td><td>$row_results6[1]</td><td>$row_results4[4]</td><td>$row_results4[0]</td><td>N/A - Error</td><td>It appears that the qvo-vEth for Instance: $row_results6[1] does not exist</td></tr>";												
														}
													}	
												}
												mysqli_data_seek($result6, 0);
											}

											elseif (strpos($row_results4[2],'router') == true) {
												print "<tr><td>$row_results1[1]</td><td>$row_results2[0]</td><td>$row_results3[1]</td><td>Router</td>";
												while ($row_results5 = mysqli_fetch_array($result5,MYSQLI_NUM)) {
													if ($row_results5[0] == $row_results4[3]) {
														print "<td>$row_results5[1]</td><td>$row_results4[4]</td><td>$row_results4[0]</td>";
														$ssh_command = 'ip netns exec qrouter-'.$row_results5[0].' ip a';
														$ssh_output = SSH_func($ssh_command);
														$append_char_str = Get_first_11_char($row_results4[0]);
														$qg_string = 'qg-'.$append_char_str;
														$qr_string = 'qr-'.$append_char_str;
														if (strpos($ssh_output,$qg_string) == true) {
															print "<td>Linux Namespace</td><td>$qg_string</td></tr>";

														}
														elseif (strpos($ssh_output,$qr_string) == true) {

															print "<td>Linux Namespace</td><td>$qr_string</td></tr>";
														}														
													}
												}
												mysqli_data_seek($result5, 0);
											}
											elseif (strpos($row_results4[2], 'dhcp') == true) {
												print "<tr><td>$row_results1[1]</td><td>$row_results2[0]</td><td>$row_results3[1]</td><td>DHCP</td><td>DHCP Agent</td>";
												$ssh_command = 'ip netns exec qdhcp-'.substr($row_results4[3], 41).' ip a';
												$ssh_output = SSH_func($ssh_command);
												$append_char_str = Get_first_11_char($row_results4[0]);
												$dhcp_tap_string = 'tap'.$append_char_str;
												if (strpos($ssh_output,$dhcp_tap_string) == true) {
													print "<td>$row_results4[4]</td><td>$row_results4[0]</td><td>Linux Namespace</td><td>$dhcp_tap_string</td></tr>";
												}
											}
										}
									}
								}
								mysqli_data_seek($result4, 0);
							}
						}
						mysqli_data_seek($result3, 0);
					}
				}
				mysqli_data_seek($result2, 0);
			}
			print "</table><br>";
		}

		mysqli_close($keystone_project);
		mysqli_close($neutron_networks);
		mysqli_close($neutron_subnets);
		mysqli_close($neutron_ports_ip);
		mysqli_close($neutron_routers);
		mysqli_close($nova_instances);


		$ovs_br_str = SSH_func('ovs-vsctl list-br');
		$ovs_br_array = explode(PHP_EOL,$ovs_br_str);
		if ($ovs_br_str !== '') {
			print "<h1>Open vSwitch Bridges</h1>It is not possible to directly monitor an internal Open vSwitch interface/bridge, therefore we must use a dummy network device and 'snoop' the interface instead<br><br>";
			print "<table><tr><td><b>OVS Name:</b></td><td><b>Description:</b></td><td><b>Attached Ports:</b></td><td><b>TCPDUMP:</b></td></tr>";
			foreach ($ovs_br_array as $line) {
				$ovs_br_port = substr(str_replace("\n",", ", SSH_func('ovs-vsctl list-ports '.$line)),0,-2);
				print "<tr>";
				if ($line == 'br-int') {
					print "<td>$line</td><td>OvS internal bridge that is at the core of OpenStack Neutron</td><td>$ovs_br_port</td></tr>";
				}
				elseif ($line == 'br-tun') {
					print "<td>$line</td><td>OvS tunnel bridge that is used to stage/facilitate transportation to other nodes</td><td>$ovs_br_port</td></tr>";	
				}
				elseif ($line !== '') {
					print "<td>$line</td><td>OvS Layer-3 bridge used to communicate with an external network</td><td>$ovs_br_port</td></tr>";
				}
			}

			print "</table>";

		}


		?>				

		<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>

        Jonathan Chu (12214639) <br>
        Michael Gemin (12676553)<br>

    </center>

	</body>
  
  
</html>


