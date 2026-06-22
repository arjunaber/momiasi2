<?php
echo 'Writable: ' . (is_writable('bootstrap/cache') ? 'Yes' : 'No');
echo "\n";
echo 'Directory exists: ' . (is_dir('bootstrap/cache') ? 'Yes' : 'No');
echo "\n";
?>
