<?php
include('inc/inc.php');

truncateTable("cost", "201610");
truncateTable("fmm_evms", "master_ca");

$insert_stmt = "INSERT  INTO cost.`201610` (pmid, cmid,wp, evt, status, pc, s_dollars, a_dollars, p_dollars, bac_dollars, eac_dollars, 
        percent_spent, s_hours,a_hours, p_hours, bac_hours, eac_hours, budget_rate, a_rate, baseline_start, baseline_finish, a_start, 
        a_finish, forecast_start, forecast_finish, pending_start, pending_finish) VALUES";


if ($_FILES[csv][size] > 0) {
    //get the csv file
    $file = $_FILES[csv][tmp_name];
    $handle = fopen($file, "r");

    //remove headers from the file.
    fgetcsv($handle, 10000, ",");
    fgetcsv($handle, 10000, ",");

    //loop through the csv file and insert into database
    $sql = $insert_stmt;
    /*create counter so insert 1000 rows at a time.*/
    $i = 0;
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
    {
        $ca_name = $data[0];
        $cam     = $data[44];
        $wp_name = addslashes($data[1]);
        $wbs_id  = $data[45];

        /*
         * Exclude the summary rows
         * */
        if ($ca_name != "" and $wbs_id!="") {
            insertMasterCA(2, $ca_name, $wbs_id, $cam);
            $cmid = getLastId("fmm_evms", "master_ca", "id");
            continue;
        }
        $evt              = $data[2];
        $status           = $data[3];
        $percent_complete = floatval($data[4]);
        $s_dollars        = fixCostField($data[5]);
        $a_dollars        = fixCostField($data[6]);
        $p_dollars        = fixCostField($data[7]);
        $bac_dollars      = fixCostField($data[8]);
        $eac_dollars      = fixCostField($data[9]);
        $percent_spent    = floatval($data[11]);
        $s_hours          = fixCostField($data[20]);
        $p_hours          = fixCostField($data[22]);
        $a_hours          = fixCostField($data[21]);
        $bac_hours        = fixCostField($data[23]);
        $eac_hours        = fixCostField($data[24]);
        $budgeted_rate    = floatval($data[33]);
        $a_rate           = floatval($data[34]);
        $baseline_start   = fixExcelDate($data[35]);
        $baseline_finish  = fixExcelDate($data[36]);
        $a_start          = fixExcelDate($data[37]);
        $a_finish         = fixExcelDate($data[38]);
        $forecast_start   = fixExcelDate($data[39]);
        $forecast_finish  = fixExcelDate($data[40]);
        $pending_start    = fixExcelDate($data[41]);
        $pending_finish   = fixExcelDate($data[42]);
        $wbs_id           = $data[45];


        $sql .=
            "(
            2,
            $cmid,
        '$wp_name',
        '$evt',
        '$status',
        '$percent_complete',
        $s_dollars,
        $a_dollars, 
        $p_dollars, 
        $bac_dollars, 
        $eac_dollars, 
        '$percent_spent', 
        $s_hours,
        $a_hours,  
        $p_hours, 
        $bac_hours, 
        $eac_hours, 
        $budgeted_rate, 
        $a_rate, 
        '$baseline_start',
        '$baseline_finish',
        '$a_start',
        '$a_finish',
        '$forecast_start', 
        '$forecast_finish', 
        '$pending_start', 
        '$pending_finish'
            ),";

        if ($i == 1000) {
            $sql = substr($sql, 0, -1);
            //print $sql;
            $junk = dbCall($sql, "cost");
            //die("made it");
            $i = 0;
            //clear out the sql stmt.
            $sql = $insert_stmt;
        }
        $i++;
    }
    //only insert remaining lines if the total number is not divisble by 1000.
    if ($i != 1000) {
        $sql = substr($sql, 0, -1);
        print $sql;
        $junk = dbCall($sql, "cost");
    }

    //print $sql;
    die("this worked");
    header('Location: import.php?success=1');
    die;
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
    <title>Import a CSV File with PHP & MySQL</title>
</head>

<body>

<?php if (!empty($_GET[success])) {
    echo "<b>Your file has been imported.</b><br><br>";
} //generic success notice ?>

<form action="" method="post" enctype="multipart/form-data" name="form1" id="form1">
    Choose your file: <br/>
    <input name="csv" type="file" id="csv"/>
    <input type="submit" name="Submit" value="Submit"/>
</form>

</body>
</html>