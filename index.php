<?php
$nodes = array("f41ca13853aac988ef2f1a378fb3c9f66cdeafca", "f41ca13853aac988ef2f1a378fb3c9f66cdeafcb");

function getNodeStatus($fingerprint) {
	$url = "http://torstatus.blutmagie.de/router_detail.php?FP=" . $fingerprint;
	$data = file_get_contents($url);
	$result = array("fingerprint" => $fingerprint);
	if (strpos($data, "ERROR -- No Descriptor Available") === false) {
		$result["status"] = "online";

		$pattern = "/<tr><td class='TRAR'><b>Router Name:<\/b><\/td><td class='TRSB'>([^<]*)<\/td><\/tr>/";
		preg_match($pattern, $data,$matches);
		$result["name"] = $matches[1];
		
		$pattern = "/<tr><td class='TRAR'><b>Last Descriptor Published \(GMT\):<\/b><\/td><td class='TRSB'>([^<]*)<\/td><\/tr>/";
		preg_match($pattern, $data,$matches);
		$result["lastupdate"] = $matches[1];

		$pattern = "/<tr><td class='TRAR'><b>Current Uptime:<\/b><\/td><td class='TRSB'>([^<]*)<\/td><\/tr>/";
		preg_match($pattern, $data,$matches);
		$result["uptime"] = $matches[1];
		
		$pattern = "/<tr><td class='TRAR'><b>Bandwidth \(Max\/Burst\/Observed - In Bps\):<\/b><\/td><td class='TRSB'>(\d*)&nbsp;\/&nbsp;(\d*)&nbsp;\/&nbsp;(\d*)<\/td><\/tr>/";
		preg_match($pattern, $data,$matches);
		$result["bandwidth_max"] = $matches[1];
		$result["bandwidth_burst"] = $matches[2];
		$result["bandwidth_observed"] = $matches[3];
		
		$pattern = "/<tr class='nr'><td class='TRAR'><b>Exit:<\/b><\/td><td class='F([01])'><\/td><\/tr>/";
		preg_match($pattern, $data,$matches);
		$result["exit_node"] = ( $matches[1] == "1" );
		
		$pattern = "/<tr class='nr'><td class='TRAR'><b>Running:<\/b><\/td><td class='F([01])'><\/td><\/tr>/";
		preg_match($pattern, $data,$matches);
		$result["running"] = ( $matches[1] == "1" );
		
		$pattern = "/<tr class='nr'><td class='TRAR'><b>Stable:<\/b><\/td><td class='F([01])'><\/td><\/tr>/";
		preg_match($pattern, $data,$matches);
		$result["stable"] = ( $matches[1] == "1" );
		
	} else {
		$result["status"] = "offline";
	}
	return $result;
}

?>
<html>
<body>
	<h1>Tor Node Status</h1>
<ul>
	<?php foreach ($nodes as $node) { 
		$status = getNodeStatus($node);
		if ($status["status"] == "online") {
		?>
		<li><?= $status["fingerprint"] ?>(<?= $status["name"] ?>): online (<?= $status["bandwidth_observed"] ?>bps)</li>
		<?php } else { ?>
			<li><?= $status["fingerprint"] ?>: offline</li>
	<?php } } ?>
</ul>
</body>
</html>
