<?php
  $db_server = "localhost";
  $db_user = "SmeyKh";
  $db_pass = "hello123(*)";
  $dbName = "student_management_system";
   try{
    
    $conn = mysqli_connect($db_server, $db_user,
                         $db_pass, $dbName);
   }catch(mysqli_sql_exception ){
    echo "Counld not connect to the database! <br>";
   }
?>