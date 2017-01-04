<?php
include('inc/inc.php');
function createRPTfromDate($date){
    $date_array = explode("/", $date);
    $rpt_period = "$date_array[2]$date_array[0]";
    return $rpt_period;
}
//truncateTable("fmm_evms", "201610");
if ($_FILES[csv][size] > 0) {
    //get the csv file
    $file = $_FILES[csv][tmp_name];
    $handle = fopen($file,"r");

    //remove headers from the file.
    fgetcsv($handle, 10000, ",");

    //loop through the csv file and insert into database
    $sql = "INSERT INTO fmm_evms.201610 (wbs, ev_description, period, cost, cost_uom, cost_type) VALUES ";
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
    {
        $cost_type  = $data[0];
        $wbs        = $data[1];
        $ev_description  = $data[3];
        $date       = $data[4];
        $cost       = str_replace("$","",$data[5]);
        $cost       = str_replace(",","",$cost);
        $cost       = str_replace("(","-",$cost);
        $cost       = str_replace(")","",$cost);
        $currency   = $data[6];
        if($currency == "TRUE")
        {
            $cost_uom = "dollars";
        }
        else
        {
            $cost_uom = "hours";
        }
        $rpt_period = createRPTfromDate($date);

        /*move past the summary lines*/
        if($wbs=="")
        {
            continue;
        }

        $sql.=
            "(
                '$wbs',
                '$ev_description',
                '$date',
                 $cost,
                '$cost_uom',
                '$cost_type'
            ),";
    }
    print $sql;
    $sql = substr($sql, 0, -1);
    $junk = dbCall($sql, "fmm_evms");
    //now flatten the data.



    $sql = "INSERT  INTO  fmm_evms.`201610again`
    (
    wbs,
     s_cum_labor_d,
     s_r_labor_d,
     s_cur_material_d,
     s_cum_material_d,
     s_r_material_d,
     s_cur_labor_h,
     s_cum_labor_h,
     s_r_labor_h,

     p_cur_labor_d,
     p_cum_labor_d,
     p_cur_material_d,
     p_cum_material_d,
     p_cur_labor_h,
     p_cum_labor_h,

     a_cur_labor_d,
     a_cum_labor_d,
     a_cur_material_d,
     a_cum_material_d,
     a_cur_labor_h,
     a_cum_labor_h,

     eac_cur_labor_d,
     eac_cum_labor_d,
     eac_r_labor_d,
     eac_cur_material_d,
     eac_cum_material_d,
     eac_r_material_d,
     eac_cur_labor_h,
     eac_cum_labor_h,
     eac_r_labor_h)
     VALUES (

        ";


    //print $sql;
    die("this worked");
    header('Location: import.php?success=1'); die;
}

?>

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

</body>
</html>