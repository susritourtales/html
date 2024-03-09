<?php //`git pull`; 
?>
<?php
$output = shell_exec('sudo git pull https://github.com/susritourtales/html.git');
if (!$output)
  echo "<pre>$output</pre>";
else
  echo "unknown issue";
?>