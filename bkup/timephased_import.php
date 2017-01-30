

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Import a CSV File with PHP & MySQL</title>
</head>

<body>

<?php if (!empty($_GET[success])) { echo "<b>Your file has been imported.</b><br><br>"; } //generic success notice ?>

<form action="" method="post" enctype="multipart/form-data" name="form1" id="form1">
  Choose your file: <br />
  <input name="csv" type="file" id="csv" />
  <input type="submit" name="Submit" value="Submit" />
</form>
<?php
include('inc/inc.php');

if ($_FILES[csv][size] > 0) {
    set_time_limit (120);
    //get the csv file
    $file = $_FILES[csv][tmp_name];
    $handle = fopen($file,"r");

    //remove headers from the file.
    truncateTable("fmm_evms", "timephased");
    fgetcsv($handle, 10000, ",");

    //loop through the csv file and insert into database
    $insert_sql = "INSERT  into fmm_evms.timephased (wbs_id, wp, ca, cost_set, period, cost) VALUES ";
    $sql = $insert_sql;
    /*create counter so insert 1000 rows at a time.*/
    $i=0;
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
    {
        $wbs_id   = $data[0];
        $ca       = $data[1];
        $wp       = $data[2];
        $cost_set = $data[3];
        $date     = fixExcelDate($data[4]);
        $cost     = fixCostField($data[5]);
        $date = createRPTfromDate($date);
        $sql.=
            "(
                '$wbs_id',
                '$wp',
                '$ca',
                '$cost_set',
                '$date',
                 $cost
            ),";
        if($i == 1000)
        {
            $sql = substr($sql, 0, -1);
            //print $sql;
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
        //print $sql;
        $junk = dbCall($sql, "fmm_evms");
    }
    echo "<script>window.location = 'timephase_step2.php'</script>";

}

?>
</body>
</html>