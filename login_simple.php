<?php
// Redirect 302 permanen sementara ke login.php sebagai entry point resmi
header('Location: login.php', true, 302);
exit;
