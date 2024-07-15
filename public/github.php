<?php
// Change the current working directory to the Git repository
chdir('/var/www/html');

// Execute a shell command and capture the last line of the output
exec('sudo git pull https://github.com/susritourtales/html.git', $output, $returnCode);

// Display the output and return code
echo "<pre>" . implode("\n", $output) . "</pre>";
echo "\n Return Code: $returnCode";
