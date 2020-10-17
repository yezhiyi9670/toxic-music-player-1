<?php

header('Content-Type: text/plain');
header('Encoding: UTF-8');
echo file_get_contents('changelog.txt');

?>