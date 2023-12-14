<?php

$user="u1087935_TsZeKa2eauMwDa4QpaMGgqYA956a7h";
$password="848HMbVhJKfKqZDyz3P4fS6cDB5qgP";
$db="u1087935_skpbz";
$con=mysqli_connect('localhost',$user,$password);

if(!$con){
    echo 'tidak berhasil terhubung ke server :(';
}else{
    //echo 'berhasil!';
}
if(!mysqli_select_db($con, $db)){
    echo 'tidak berhasil terhubung ke database';
}else{
    //echo 'berhasil!';
}

?>