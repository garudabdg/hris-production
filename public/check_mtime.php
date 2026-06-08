<?php
echo "web.php: " . date('Y-m-d H:i:s', filemtime('/var/www/hris.didimax.online/routes/web.php')) . "\n";
echo "auth.php: " . date('Y-m-d H:i:s', filemtime('/var/www/hris.didimax.online/routes/auth.php')) . "\n";
