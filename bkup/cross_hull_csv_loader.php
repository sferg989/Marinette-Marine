

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <title>Import a CSV File with PHP & MySQL</title>
</head>

<body>

<?php if (!empty($_GET[success])) { echo "<b>Your file has been imported.</b><br><br>"; } //generic success notice ?>

<form action="" method="post" enctyrpe="multipart/form-data" name="form1" id="form1">
    Choose your file: <br />
    <input name="csv" type="file" id="csv" />
    <input type="submit" name="Submit" value="Submit" />
</form>
<?php
include('inc/inc.php');
$ship = "25";
$code = "485";
if ($_FILES[csv][size] > 0) {
    set_time_limit (120);
    //get the csv file
    $file = $_FILES[csv][tmp_name];
    $handle = fopen($file,"r");

    //remove headers from the file.
    truncateTable("fmm_evms", "cross_hull_".$ship);
    fgetcsv($handle, 10000, ",");

    //loop through the csv file and insert into database
    $insert_sql = "insert into fmm_evms.cross_hull_".$ship." (op,swbs, `group`, soc, item, scope, bac, etc, estimate, a, target, eac) values ";
    $sql = $insert_sql;
    /*create counter so insert 1000 rows at a time.*/
    $i=0;
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
    {
        $project  = $data[0];
        $group    = $data[1];
        $soc      = $data[2];
        $item     = trim($data[3]);
        $scope    = addslashes(trim($data[4]));
        $bac      = $data[5];
        $etc      = $data[6];
        $estimate = $data[7];
        $a        = $data[8];
        $target   = $data[9];
        $eac      = $data[10];
        $swbs     = $data[11];
        $op     = $data[12];
        if($project=="")
        {
            continue;
        }
        if($project!=$code)
        {
            continue;
        }
        $sql.=
            "(
                '$op',
                '$swbs',
                '$group',
                '$soc',
                '$item',
                '$scope',
                '$bac',
                '$etc',
                '$estimate',
                '$a',
                '$target',
                '$eac'
            ),";
        if($i == 1000)
        {
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql, "fmm_evms");
            $i=0;
            //clear out the sql stmt.
            $sql = $insert_sql;
        }
        $i++;
    }

    //only insert remaining lines if the total number is not divisble by 1000.
    if($i !=1000)
    {
        $sql = substr($sql, 0, -1);
        print $sql;
        $junk = dbCall($sql, "fmm_evms");
    }

}

?>
</body>
</html>