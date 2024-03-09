<?php //`git pull`; 
?>
<?php
$output = shell_exec('sudo /var/www/html git pull https://github.com/susritourtales/html.git');
if ($output === null)
  echo "unknown issue";
else
  echo "<pre>$output</pre>";
?>