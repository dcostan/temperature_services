<?php
   $pwd = "sec";

   if($_COOKIE["access"] == md5($pwd)) {
     $tempFile = fopen("/var/www/temperature.txt", "r");
     $enabled = explode("*", fgets($tempFile))[1];
     fclose($tempFile);
     echo $enabled;
   }
?>
