<?php
// jalalin ini sih sekali aja, copy outputnya
// paste ke kolom password di SWL INSERT admin

$password = 'admin123'; // ntar ganti bae sing mas abenk mau
$hash = password_hash($password, PASSWORD_BCRYPT);
echo $hash; // copy aje outputnya ke code sql kite

?>