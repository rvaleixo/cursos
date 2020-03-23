<?php
 $link = mysqli_connect('localhost', 'deapoioc_cursos', 'rva12021952','deapoioc_cursos');
 if (!$link){
 	die('NÃO CONECTOU: ' . mysqli_error($link));}
 // echo 'CONECTOU AO MYSQL COM SUCESSO';

//  bool mysqli_set_charset ( "latin1", $link) 

?>
