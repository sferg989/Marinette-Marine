<?php
include('../../../../inc/inc.php');
include('../../../../inc/inc.PHPExcel.php');
function insertOutSourceData($table_name, $path2file){

    $handle = fopen($path2file,"r");
    print $path2file;
    //remove headers from the file.
    //loop through the csv file and insert into database
    $insert_sql = "
    insert into outsource.$table_name (ship_code, parent_item, a_hours)
      VALUES 
   ";
    $sql = $insert_sql;
    /*create counter so insert 1000 rows at a time.*/
    $i=0;
    /*skip header*/
    fgetcsv($handle);
    //$data = fgetcsv($handle);
    //var_dump($data);

    while (($data = fgetcsv($handle)) !== FALSE)
    {
        $ship_code   = intval(trim($data[0]));
        $parent_item = trim($data[10]);
        //$a_hours = trim($data[17]);
        $a_hours     = formatNumberNoComma(trim($data[17]));
        print "Parent".$parent_item."<br>";
        //print "A Hours".$a_hours."<br>";
        $sql.=
            "(
            $ship_code,
            '$parent_item',
            $a_hours
            ),";
        if($i == 500)
        {
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql, "outsource");
            print $sql;

            $i=0;
            //clear out the sql stmt.
            $sql = $insert_sql;
        }
        $i++;
    }
    //only insert remaining lines if the total number is not divisble by 1000.
    if($i !=500)
    {
        $sql = substr($sql, 0, -1);
        print $sql;
        $junk = dbCall($sql, "outsource");
    }
    //unlink($path2file);
}
function saveListOfFileNamesPHPExcel($file_name_array,$directory2Copy,$rel_path2_desitnation, $table_name)
{
    foreach ($file_name_array as $value) {
        $path2XLSX    = $directory2Copy."/".$value;
        $csv_filename = savePHPEXCELCSV1WorkSheetByIndex($value,$path2XLSX,$rel_path2_desitnation,1);
        //$csv_filename = "0481.ace.xlsx.csv";
        print $csv_filename."<br>";
        //$csv_filename = substr($csv_filename, 0, -9);
        //$csv_filename = $csv_filename.".csv";
        print $csv_filename."<br>";

        $path2file    = $rel_path2_desitnation."/".$csv_filename;

        insertOutSourceData($table_name,$path2file);
        flush();
    }
}
function copyListOfDirectoryToCSV($g_path2_outsourcing_dir,$dest_dir,$table_name){
    $file_name_array = getListOfFileNamesInDirectory($g_path2_outsourcing_dir);
    saveListOfFileNamesPHPExcel($file_name_array,$g_path2_outsourcing_dir,$dest_dir, $table_name);
}
$schema = "outsource";
if($control=="load_outsource")
{
    /*Create Acutals Load File.
        1.  combine Material/Labor Sheets into 1.
        2.  Combine all Estimate Actuals /Activity.
        3.  Combine all Actuals By Activity.
        ******Estimated Actuals***
        4.  Outsourcing.
        5.  material Adjustments.
        6.  Other Estimate Actuals.
        ******Claims Actuals****

        ACE - Load the accural file from the Y Drive.
        - Need the Parent Item, Hours, .
        - get WP from MFG performance report.
        - get PO Line ITem from Committed PO.
        - Get rate from rate table. by Billing group
        -  SUm all WP's together.  And APpend to Actuals Load File.

    */

    $table_name  = $rpt_period . "_out";
    $dir_csv_out = "../../../../util/csv_outsource";
    $create_table = checkIfTableExists($schema, $table_name);
    if($create_table== "create_table"){
        createTableFromBase($schema,"template_out", $table_name);
    }
    truncateTable("outsource", $table_name);
    copyListOfDirectoryToCSV($g_path2_outsourcing_dir,$dir_csv_out, $table_name);

}
if($control=="load_committed_po"){

}


