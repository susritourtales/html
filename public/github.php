<?php //`git pull`; ?>
<?php 
$output = shell_exec('sudo git pull https://github.com/susritourtales/html.git');
echo "<pre>$output</pre>";
?>