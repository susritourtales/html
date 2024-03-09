<?php
$gitCommand = 'pwd';
//$gitCommand = 'sudo /usr/bin/git pull https://github.com/susritourtales/html.git';
$output = shell_exec($gitCommand . ' 2>&1');  // Capture both STDOUT and STDERR
if ($output === null) {
  echo "Command execution failed or disabled in your server configuration.";
} else {
  echo "<pre>$output</pre>";
}
?>

<?php
/* $output = shell_exec('sudo git pull https://github.com/susritourtales/html.git');
if ($output === null)
  echo "unknown issue";
else
  echo "<pre>$output</pre>"; */
?>