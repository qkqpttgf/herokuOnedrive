<?php
  $declare_c=0;
    if (!class_exists('mydbreader')) {
        $declare_c=1;
        class mydbreader extends SQLite3
        {
            function __construct()
            {
                $this->open( __DIR__ .'/poem.db');
            }
        }
    }
    $db = new mydbreader();
    if(!$db){
        echo $db->lastErrorMsg();
    } else {
        //echo "Opened database successfully<br>\n";
        //$id=23;
        $id=rand(1,309);
        $sql="select * from tang300 where id=".$id.";";
        $ret = $db->query($sql);
        if(!$ret){
            echo $db->lastErrorMsg();
        } else {
            $row = $ret->fetchArray(SQLITE3_ASSOC);
            $poet = $row['poet'];
            $poemtitle = $row['poemtitle'];
            $poem = $row['poem'];
            if (substr($poem,-4)=="<br>") $poem = substr($poem,0,-4);
        }
        $db->close();
    }
?>
