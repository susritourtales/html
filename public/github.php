<?php
/* $gitCommand = 'sudo /usr/bin/git pull https://github.com/susritourtales/html.git';
$output = shell_exec($gitCommand . ' 2>&1');  // to Capture both STDOUT and STDERR
if ($output === null) {
  echo "Command execution failed or disabled in your server configuration.";
} else {
  echo "<pre>$output</pre>";
} */
?>

<?php
// Change the current working directory to the Git repository
chdir('/var/www/html');

// Execute a shell command and capture the last line of the output
exec('git pull https://github.com/susritourtales/html.git', $output, $returnCode);

// Display the output and return code
echo "<pre>" . implode("\n", $output) . "</pre>";
echo "Return Code: $returnCode";

/* $output = shell_exec('sudo git pull https://github.com/susritourtales/html.git');
echo "<pre>$output</pre>";
if ($output === null)
  echo "unknown issue";
else
  echo "<pre>$output</pre>"; */
?>
