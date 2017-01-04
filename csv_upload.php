<?php
include('inc/inc.php');

if ($_FILES[csv][size] > 0) {

    //get the csv file
    $file = $_FILES[csv][tmp_name];
    $handle = fopen($file,"r");

    //loop through the csv file and insert into database
    do {
        if ($data[0]) {
            mysql_query("INSERT INTO contacts (contact_first, contact_last, contact_email) VALUES
                (
                    '".addslashes($data[0])."',
                    '".addslashes($data[1])."',
                    '".addslashes($data[2])."'
                )
            ");
        }
    } while ($data = fgetcsv($handle,1000,",","'"));
    //

    //redirect
    header('Location: import.php?success=1'); die;

}

$csv_file = realpath($csv_file);
print $csv_file;
print realpath($csv_file);
die();
$filename = $csv_file;
$myfile = fopen($filename, "r") or die("Unable to open file!");
echo fread($myfile,filesize($filename));
fclose($myfile);
var_dump($_REQUEST);

/*
$sql = "select * from loader";
$rs = dbCall($sql);
while (!$rs->EOF) {

    $wbs = $rs->fields["wbs"];
    print "This query worked ".$wbs."<br><br><br>";

    $rs->MoveNext();
}
*/
