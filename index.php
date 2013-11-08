<?php
/* CONFIGURATION *
*
* Enter your node family here, in single quotes. Alternatively,
* put your nodes' fingerprints into the array below, and comment out 
* this top section.
*/
$family = '$60D48F04F1EE0E22966DF0DAA8234329F5BF74AF,$12CCC11328055D9189F744D2DFED4DA9AA3FA484,$F867F38D806BAE52FC77BDFCF02B7D76DFB451ED,$B8B980E2684BB9966AF0A78ADD499BB6B4EF8552,$0158AD024335D0089AC64850409A475A6A817DD0,$9D5D7BB3E276A890E46EF934EACB1756FA2F4D96,$0770A13FDB928233827D38FDB2181CFAEABC3E5D,$F7C66C585A20A459AB7119ECA65A7C8FF896D901,$7A2BBC83A3F582BD16C77D3FD2EB44C559927260,$42FACDD8566382C19654F152F48196670F263228,$A252E766BC858B4D38C5C45383BB3374F37D4070,$F41CA13853AAC988EF2F1A378FB3C9F66CDEAFCA';

$family = str_replace("$","",$family);
$nodes = explode(",",$family);

/* Alternative array for node fingerprints */
// $nodes = array("f41ca13853aac988ef2f1a378fb3c9f66cdeafca", "f41ca13853aac988ef2f1a378fb3c9f66cdeafcb");

function getNodeStatus($fingerprint) {
	$url = "http://torstatus.blutmagie.de/router_detail.php?FP=" . $fingerprint;
	
	$data = file_get_contents($url);
	if ($data === false) {
		throw new Exception("Could not connect to TorStatus.");
	}
	$data = str_replace(array("\r\n", "\n", "\r"),"",$data);
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
$error = false;
$data = Array();
try {
	foreach ($nodes as $node) {
		$data[$node] = getNodeStatus($node);
	}
} catch (Exception $e) {
	$error = "Could not fetch node status";
}

?>
<html>
<head>
	<title></title>
	<style>
		body { font-family: Helvetica, Arial, sans-serif; }
		.error { border: 1px solig #ff0000; background: #ff9999; padding: 0.5em; margin: 0.5em; }
		#footer { margin-top: 20px; font-size: 0.8em; }
	</style>
</head>
<body>
	<h1>Tor Node Status</h1>
	<?php if ($error) { ?>
		<div class="error"><?= $error ?></div>
	<?php } else {?>
		<h3>Nodes:</h3>
		<ul>
			<?php foreach ($data as $status) { 
				if ($status["status"] == "online") {
				?>
				<li><?= $status["name"] ?> (<?= $status["fingerprint"] ?>): online (<?= $status["bandwidth_observed"] ?> bps)</li>
				<?php } else { ?>
					<li><?= $status["fingerprint"] ?>: offline</li>
			<?php } } ?>
		</ul>
	<?php } ?>
	<div id="footer">Powered by <a href="https://github.com/tiefpunkt/TorNodeMon">TorNodeMon</a></div>
</body>
</html>
