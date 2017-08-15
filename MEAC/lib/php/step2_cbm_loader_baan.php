<?php
include("../../../inc/inc.php");
include("../../../inc/inc.PHPExcel.php");
include("inc.insert_data.php");
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 3/9/2017
 * Time: 4:02 PM
 */
$files = array();


function insertCBMFromBaan($ship_code){

    $sql ="select 
        b.t_cprj as Project,
        LTRIM(RTRIM(a.t_bitm)) as Activity,
        c.t_prip as Budget,
        c.t_aamt as Assigned_Amount,
        a.t_pono as Position,
        a.t_item as Item,
        d.t_prip as Price,
        a.t_qana as Qty
        from ttipcs952490 as a
        join ttipcs950490 as b on a.t_bdgt = b.t_bdgt
        join ttipcs951490 as c on a.t_bdgt = c.t_bdgt and LTRIM(RTRIM(a.t_bitm)) = LTRIM(RTRIM(c.t_bitm))
        join ttipcs951490 as d on a.t_bdgt = d.t_bdgt and LTRIM(RTRIM(a.t_item)) = LTRIM(RTRIM(d.t_bitm))
        Where b.t_cprj like '%$ship_code%'
    Order by 1, 2, 3, 6";
    $rs = dbCallBaan($sql);

    $insert_sql = " insert into meac.cbm (program, ship_code, wp, material, budget, assigned_amt, price, qty, pos) values ";
    $i=0;
    $sql = $insert_sql;
    while (!$rs->EOF)
    {
        $ship_code  = intval($rs->fields["Project"]);
        $wp         = trim($rs->fields["Activity"]);
        $budget     = formatNumber4decNoComma($rs->fields["Budget"]);
        $assign_amt = formatNumber4decNoComma($rs->fields["Assigned_Amount"]);
        $pos        = formatNumber4decNoComma($rs->fields["Position"]);
        $item       = trim($rs->fields["Item"]);
        $price      = formatNumber4decNoComma($rs->fields["Price"]);
        $qty        = formatNumber4decNoComma($rs->fields["Qty"]);

        $sql.= "(                                                 
        'LCS',
        $ship_code,
        '$wp',
        '$item',
        $budget,
        $assign_amt,
        $price,
        $qty,
        $pos
        ),";
        if($i == 1000)
        {
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql, "meac");

            $i=0;
            //clear out the sql stmt.
            $sql = $insert_sql;
        }
        $i++;
        $rs->MoveNext();
    }
    //only insert remaining lines if the total number is not divisble by 1000.
    if($i !=1000)
    {
        $sql = substr($sql, 0, -1);
        print $sql;
        $junk = dbCall($sql, "meac");
    }
}

$array = array();
$array[] = 465;
$array[] = 467;
$array[] = 469;
$array[] = 471;
$array[] = 473;
$array[] = 475;
$array[] = 477;
$array[] = 479;
$array[] = 481;
$array[] = 483;
$array[] = 485;
foreach ($array as $value){
    if(strlen($value)==3)
    {
        $ship_code = "0".$value;
    }
    deleteFromTable("MEAC", "cbm", "ship_code", $ship_code);
    print $ship_code;
    insertCBMFromBaan($ship_code);
}
deleteFromTable("meac", "cbm", "material", "");