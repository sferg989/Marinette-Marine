<?php
define('ADODB_ASSOC_CASE', 0);

// ------------------------------------------------------------------
function determineTable($type,$level)
{
    $debug = false;

    // returns array(table_name,alias,maps_to)
    $sql = "
        select
            table_name,
            alias,
            maps_to
        from
            admin_table_info i
        where
            data_type='$type'
            and lowest_level='$level'
    ";
    $rs = dbCall($sql,$debug,'premier_core');

    $table_name = $rs->fields['table_name'];
    $alias      = $rs->fields['alias'];
    $maps_to    = $rs->fields['maps_to'];

    if($table_name=='')
    {
        //select id of current level
        $sql = "select id from admin_filter_listing where filter_friendly_name='$level' limit 1";
        $rs2 = dbCall($sql,$debug);
        $level_id = $rs2->fields['id'];

        $sql = "select lowest_level from
        admin_table_info where data_type='$type'
        and lowest_level<>'base' group by lowest_level";
        $rs2 = dbCall($sql,$debug,'premier_core');

        $lowest_level_wo_base = $rs2->fields['lowest_level'];

        //currently schedule will have a blank rs
        if($lowest_level_wo_base!='')
        {
            while(!$rs2->EOF)
            {
                $ll = $rs2->fields['lowest_level'];
                $lls .= "'$ll',";
                $rs2->MoveNext();
            }
            $lls = stripLastCharacter($lls);

            $sql = "select filter_friendly_name from
            admin_filter_listing where filter_friendly_name in ($lls)
            and id>=$level_id order by id limit 1";
            $rs2 = dbCall($sql,$debug,'premier_core');
            $lowest_level = $rs2->fields['filter_friendly_name'];
        }

        if($lowest_level!='')
        {
            $sql = "select table_name,alias,maps_to from
        admin_table_info where data_type='$type' and
        lowest_level='$lowest_level'";

        }
        else
        {
            $sql = "select table_name,alias,maps_to from
        admin_table_info where data_type='$type' and
        lowest_level='base'";
        }

        $rs2 = dbCall($sql,$debug,'premier_core');

        $table_name = $rs2->fields['table_name'];
        $alias      = $rs2->fields['alias'];
        $maps_to    = $rs2->fields['maps_to'];
    }

    $r = array($table_name,$alias,$maps_to);

    return $r;
}
// ------------------------------------------------------------------
function dbCall($sql,$debug=false,$db_name='premier_core',$db_server='',
                $db_user='',$db_pass='',$db_type='',$fetch_mode=2)
{
    global $g_db_server,$g_db_name,$g_db_user,$g_db_pass,$g_db_type;

    // set the db server
    // server:port
    if($db_server!='')
    {
        $temp   = explode(':',$db_server);
        $server = $temp[0];
        $port   = $temp[1];

        // default port
        if($port=='') $port = '3306';

        // ib      = TXBDBSHUR016:5029
        // wk      = af807461
        // premier = TXBAPPHUR212
        // dev     = TXBAPPHUR213
        // tsc     = txsappnwh008.systems.textron.com:3306
        // tsc ib  = txsappnwh008.systems.textron.com:5029
        if($server=='ib')
        {
           $server = 'TXBDBSHUR016';
           $port      = '5029';
        }

        if($server=='wk')
        {
           $server = 'AF807461';
        }

        if($server=='dev')
        {
           $db_server = 'TXBAPPHUR213';
        }

        if($server=='premier')
        {
           $server = 'TXBAPPHUR212';
        }

        if($server=='tsc')
        {
           $server = 'txsappnwh008.systems.textron.com';
        }

        if($server=='tsc ib')
        {
           $server = 'txsappnwh008.systems.textron.com';
           $port      = '5029';
        }

        // now combine the server and port
        $db_server = "$server:$port";
    }
    else
    {
        $db_server = 'localhost:3306';
    }
    // set some globals
         
    if($db_name=='') $db_name = $g_db_name;
    if($db_user=='') $db_user = $g_db_user;
    if($db_pass=='') $db_pass = $g_db_pass;
    if($db_type=='') $db_type = $g_db_type;

    // debug attribs
    if($debug==true) print "db_server=$db_server|<br>";

    // do the adodb connections/queries
    $conn = ADONewConnection($db_type);
    $conn->PConnect($db_server,$db_user,$db_pass,$db_name);
    $conn->debug=$debug;
    $conn->SetFetchMode($fetch_mode);
    $rs = $conn->Execute($sql);

    return $rs;
}
// ------------------------------------------------------------------
function dbCall_IB_AF807461_Premier($sql,$debug=false,$db_name='cache_data',$db_server='AF807461:5029',
        $db_user='',$db_pass='',$db_type='',$fetch_mode=3)
{
    global $g_db_server,$g_db_name,$g_db_user,$g_db_pass,$g_db_type;

    if($db_name=='') $db_name = $g_db_name;
    if($db_server=='') $db_server = $g_db_server;
    if($db_user=='') $db_user = $g_db_user;
    if($db_pass=='') $db_pass = $g_db_pass;
    if($db_type=='') $db_type = $g_db_type;

    $conn = ADONewConnection($db_type);
    $conn->Connect('AF807461:5029',$db_user,$db_pass,$db_name);
    $conn->debug=$debug;
    $conn->SetFetchMode($fetch_mode);
    $rs = $conn->Execute($sql);

    return $rs;
}
// ------------------------------------------------------------------
function dbCall_IB_16_Premier($sql,$debug=false,$db_name='cache_data',$db_server='txbdbshur016:5029',
        $db_user='',$db_pass='',$db_type='',$fetch_mode=3)
{
    global $g_db_server,$g_db_name,$g_db_user,$g_db_pass,$g_db_type;

    if($db_name=='') $db_name = $g_db_name;
    if($db_server=='') $db_server = $g_db_server;
    if($db_user=='') $db_user = $g_db_user;
    if($db_pass=='') $db_pass = $g_db_pass;
    if($db_type=='') $db_type = $g_db_type;

    $conn = ADONewConnection($db_type);
    $conn->Connect('txbdbshur016:5029',$db_user,$db_pass,$db_name);
    $conn->debug=$debug;
    $conn->SetFetchMode($fetch_mode);
    $rs = $conn->Execute($sql);

    return $rs;
}
// ------------------------------------------------------------------
function dbCall_IB($sql,$debug=false,$db_name='cache_data',$db_server='',
                $db_user='',$db_pass='',$db_type='',$fetch_mode=3)
{
    global $g_db_server,$g_db_name,$g_db_user,$g_db_pass,$g_db_type;

    if($db_name=='') $db_name = $g_db_name;
    if($db_user=='') $db_user = $g_db_user;
    if($db_pass=='') $db_pass = $g_db_pass;
    if($db_type=='') $db_type = $g_db_type;

    if($db_server!='16')
    {
        if($db_server=='')
        {
            if($db_server=='') $db_server = $g_db_server;
            $db_server = 'txbdbshur016:5029';

            $host          = strtolower($_SERVER['HTTP_HOST']);
            $computer_name = strtolower($_SERVER["COMPUTERNAME"]);
            if(
                $host=='localhost'
                or $host=='ag100570'
                or $host==''
                or strtolower($computer_name)=='af836407'
                or strtolower($computer_name)=='af830664'
                or strtolower($computer_name)=='af836401'
                or strtolower($computer_name)=='af847160'
            )
            {
                $company = strtolower($_SESSION['s_user']['company']);

                $db_server = 'localhost:5029';
                if(($company=='tsc' or $company=='tds' or $company=='tmls' or $company=='aai' or $company=='ow' or $company=='lycoming') and $computer_name=='ag100570') $db_server = 'localhost:5031';

                //hook the newbies up with IB
                if(
                    strtolower($computer_name)=='af836407'
                    or strtolower($computer_name)=='af830664'
                    or strtolower($computer_name)=='af836401'
                    or strtolower($computer_name)=='af847160'
                )
                {
                    $db_server = 'AF807461:5029';
                    //if($company=='tsc' or $company=='tds' or $company=='tmls' or $company=='aai' or $company=='ow' or $company=='lycoming') $db_server = 'AF807461:5031';
                }
            }

            if(left($host,3)=='pre') // premier
            {
                $db_server = 'txbdbshur016:5029';
            }

            if(left($host,3)=='tsc') // tscpremier
            {
                $db_server = 'txsappnwh008.systems.textron.com:5029';
            }

            if(left($host,8)=='af807461') // workstation
            {
                $db_server = 'af807461:5029';
            }
        }
    }
    else
    {
        $db_server = 'localhost:5029';
    }
    //$db_server = 'AF807461:5029';

    $conn = ADONewConnection($db_type);
    $conn->Connect($db_server,$db_user,$db_pass,$db_name);
    $conn->debug=$debug;
    $conn->SetFetchMode($fetch_mode);
    $rs = $conn->Execute($sql);

    return $rs;
}
// ------------------------------------------------------------------
function dbCallIBMMEV($sql,$debug=false,$db_name='mmev',$db_server='localhost:5029',
        $db_user='',$db_pass='',$db_type='',$fetch_mode=3)
{
    global $g_db_server,$g_db_name,$g_db_user,$g_db_pass,$g_db_type;

    if($db_name=='') $db_name = $g_db_name;
    if($db_server=='') $db_server = $g_db_server;
    if($db_user=='') $db_user = $g_db_user;
    if($db_pass=='') $db_pass = $g_db_pass;
    if($db_type=='') $db_type = $g_db_type;

    $db_name   = 'mmev';
    $db_server = 'txbdbshur016:5029';

    $host          = strtolower($_SERVER['HTTP_HOST']);
    $computer_name = strtolower($_SERVER["COMPUTERNAME"]);
    if(
        $host=='localhost'
        or $host=='ag100570'
        or $host==''
        or strtolower($computer_name)=='af836407'
        or strtolower($computer_name)=='af830664'
        or strtolower($computer_name)=='af836401'
        or strtolower($computer_name)=='af847160'
    )
    {
        $company = strtolower($_SESSION['s_user']['company']);

        $db_server = 'localhost:5029';
        if(($company=='tsc' or $company=='tds' or $company=='tmls' or $company=='aai' or $company=='ow' or $company=='lycoming') and $computer_name=='ag100570') $db_server = 'localhost:5031';

        //hook the newbies up with IB
        if(
            strtolower($computer_name)=='af836407'
            or strtolower($computer_name)=='af830664'
            or strtolower($computer_name)=='af836401'
            or strtolower($computer_name)=='af847160'
        )
        {
            $db_server = 'AF807461:5029';
            //if($company=='tsc' or $company=='tds' or $company=='tmls' or $company=='aai' or $company=='ow' or $company=='lycoming') $db_server = 'AF807461:5031';
        }
    }

    if(left($host,3)=='pre') // premier
    {
        $db_server = 'txbdbshur016:5029';
    }

    if(left($host,3)=='tsc') // tscpremier
    {
        $db_server = 'txsappnwh008.systems.textron.com:5029';
    }

    if(left($host,8)=='af807461') // workstation
    {
        $db_server = 'af807461:5029';
    }

    $conn = ADONewConnection($db_type);
    $conn->Connect($db_server,$db_user,$db_pass,$db_name);
    $conn->debug=$debug;
    $conn->SetFetchMode($fetch_mode);
    $rs = $conn->Execute($sql);

    return $rs;
}
// ------------------------------------------------------------------
function dbCall213($sql,$debug=false,$db_name='sugarcrm2',$db_server='',
                $db_user='',$db_pass='',$db_type='')
{
    global $g_db_server,$g_db_name,$g_db_user,$g_db_pass,$g_db_type;

    if($db_name=='') $db_name = $g_db_name;
    if($db_user=='') $db_user = $g_db_user;
    if($db_pass=='') $db_pass = $g_db_pass;
    if($db_type=='') $db_type = $g_db_type;

    //print "$g_db_server|$g_db_name|$g_db_user|$g_db_pass|$g_db_type<br>";
    $conn = &ADONewConnection($db_type);
    $conn->PConnect('txbapphur213',$db_user,$db_pass,$db_name);
    $conn->debug=$debug;
    $conn->SetFetchMode(3);
    $rs = $conn->Execute($sql);
    //if($debug) array_debug($rs);
    return $rs;
}
// ------------------------------------------------------------------
function dbCall_Premier($sql,$debug=false,$db_name='sugarcrm2',$db_server='',
                $db_user='',$db_pass='',$db_type='')
{
    global $g_db_server,$g_db_name,$g_db_user,$g_db_pass,$g_db_type;

    if($db_name=='') $db_name = $g_db_name;
    if($db_user=='') $db_user = $g_db_user;
    if($db_pass=='') $db_pass = $g_db_pass;
    if($db_type=='') $db_type = $g_db_type;

    //print "$g_db_server|$g_db_name|$g_db_user|$g_db_pass|$g_db_type<br>";
    $conn = &ADONewConnection($db_type);
    $conn->PConnect('premier',$db_user,$db_pass,$db_name);
    $conn->debug=$debug;
    $conn->SetFetchMode(3);
    $rs = $conn->Execute($sql);
    //if($debug) array_debug($rs);
    return $rs;
}
// ------------------------------------------------------------------
function dbCall_DB2($sql,$debug=false,$db_server='Shadow Direct DB2P 32-bit',
                $db_user='premier',$db_pass='bel#1914')
{
    //mvsb1
    //print "$g_db_server|$g_db_name|$g_db_user|$g_db_pass|$g_db_type<br>";
    $conn = & NewADOConnection('odbc_db2');
    $conn->curMode = SQL_CUR_USE_ODBC;
    $conn->debug=$debug;
    //$db =& ADONewConnection('db2');
    $conn->Connect($db_server,$db_user,$db_pass);
    //$dsn = "driver={IBM db2 odbc DRIVER};Database=letar;hostname=$db_server;port=6800;protocol=TCPIP;".
    //        "uid=ii38538; pwd=Xsw25tgb;";
    //$conn->Connect($dsn);

    //$conn->SetFetchMode(3);
    $rs = $conn->Execute($sql);
    if($debug) array_debug($rs);
    return $rs;
}
// ------------------------------------------------------------------
function dbCall_Oracle($sql,$debug=false,$db_server='')
{
    //print "$g_db_server|$g_db_name|$g_db_user|$g_db_pass|$g_db_type<br>";
    $conn = &ADONewConnection('oci8');
    $conn->debug=$debug;

    //,'','',$db_server,'','',$db_server

    // production:  premier / Prem195625081979
    // development:  premier / Prem19796056
    // test:  premier / Prem195623967
    // should have access to all ev data on a019, a021, a022, a035

    switch(strtoupper($db_server))
    {
        case 'A010PROD':
            $sid = "a010prod.world";
            $sr = "txbdbshur601.bh.textron.com:1521";
            //$un = 'ops$ii38538';
            //$pw = 'bhtis';
            $un = 'app_simmerman';
            $pw = 'bell186';
            break;
        case 'A015PROD':
            $sid = "a015prod";
            //$sr = "evmsora.bh.textron.com:1521";
            $sr="txbdbshur008v.bh.textron.com:1521";
            //$un = 'ops$ii38538';
            //$pw = 'bhtis';
            $un = 'premier';
            $pw = 'bel#1607';
            break;
        case 'A019PROD':
            $sid = "a019prod";
            //$sr = "evmsora.bh.textron.com:1521";
            $sr="txbdbshur010v.bh.textron.com:1521";
            //$un = 'ops$ii38538';
            //$pw = 'bhtis';
            $un = 'premier';
            $pw = 'Prem195625081979';
            break;
        case 'A019TEST':
            $sid = "a019test";
            //$sr = "evmsora.bh.textron.com:1521";
            $sr="txbdbshur901v.bh.textron.com:1521";
            //$un = 'ops$ii38538';
            //$pw = 'bhtis';
            $un = 'premier';
            $pw = 'Prem195625081979';
            break;
        case 'A019DVLP':
            $sid  = "a019dvlp.world";
            //$sr = "evmsora.bh.textron.com:1521";
            $sr   ="txbdbshur901v.bh.textron.com:1521";
            //$un = 'ops$ii38538';
            //$pw = 'bhtis';
            $un   = 'premier';
            $pw   = 'Prem195625081979';
            break;
        case 'A021PROD':
            $sid = "a021prod.world";
            $sr = "evmsora2.bh.textron.com:1521";
            //$un = 'millennium';
            //$pw = 'cmmm0901';
            $un = 'premier';
            $pw = 'Prem195625081979';
            break;
        case 'A021PROD-FULL-ACCESS':
            $sid = "a021prod.world";
            $sr  = "evmsora2.bh.textron.com:1521";
            $un  = 'millennium';
            $pw  = 'cmmm0901';
            break;
        case 'A021TEST':
            $sid = "a021test.world";
            $sr = "txbdbshur901v.bh.textron.com:1521";
            //$un = 'millennium';
            //$pw = 'cmmm0901';
            $un = 'premier';
            $pw = 'Prem195625081979';
            break;
        case 'A021TEST-FULL-ACCESS':
            $sid = "a021test.world";
            $sr  = "txbdbshur901v.bh.textron.com:1521";
            $un  = 'millennium';
            $pw  = 'cmmm0901';
            break;
        case 'A021DVLP-FULL-ACCESS':
            $sid = "a021dvlp.world";
            $sr  = "txbdbshur901v.bh.textron.com:1521";
            $un  = 'millennium';
            $pw  = 'cmmm0901';
            break;
        case 'A021DVLP-FULL-ACCESS-COMM':
            $sid = "a021dvlp.world";
            $sr  = "txbdbshur901v.bh.textron.com:1521";
            $un  = 'comm';
            $pw  = 'cmco0108';
            break;
        case 'A022PROD':
            $sid = "a022prod";
            $sr = "evmsora2:1523";
            //$un = 'applws';
            //$pw = 'wsap0704';
            $un = 'premier';
            $pw = 'Prem195625081979';
            break;
         case 'DWPROD':
            $sid = "dwprod";
            $sr = "txbdbshur008v:1521";
            $un = 'premier';
            $pw = 'Prem195625081979';
            break;
         case 'HTMLPROD':
            $sid = "htmlprod";
            //$sr = "bhtiora3.bh.textron.com:1521";
            $sr = "txbdbshur009v.bh.textron.com:1521";
            $un = 'premier';
            $pw = 'Prem195625081979';
            break;
        case 'A021PREP':
            $sid = "a021prep.world";
            $sr = "tevmsora.bh.textron.com:1521";
            //$un = 'applbaca';
            //$pw = 'baca0103';
            $un = 'premier';
            $pw = 'Prem195625081979';
            break;
        case 'A019PROD-WRITEACCESS':
            $sid = "a019prod.world";
            $sr = "evmsora.bh.textron.com:1521";
            //$un = 'ops$ii38538';
            //$pw = 'bhtis';
            //$un = 'appltp';
            //$pw = 'appm1990';
            $un = 'premier';
            $pw = 'Prem195625081979';
            break;
        case 'A039PROD':
            $sid = "a039prod.world";
            $sr = "bhtiora2.bh.textron.com:1521";
            $un = 'premier';
            $pw = 'bhtis123';
            //$un = 'premier';
            //$pw = 'Prem195625081979';
            break;
        case 'A049PROD':
            $sid = "a049prod.world";
            $sr = "txasrsnwh021.ent.textron.com:1521";
            $un = 'ops$ii38538';
            $pw = 'bhtis123';
            //$un = 'premier';
            //$pw = 'Prem195625081979';
            break;
        case 'A014PROD':
            $sid = "a014prod.world";
            $sr = "txbdbshur601.bh.textron.com:1521";
            $un = 'premier';
            $pw = 'bel#1933';
            break;
        case 'A060PROD':
            $sid = "a060prod.world";
            $sr = "txbdbshur008v.bh.textron.com:1521";
            $un = 'premier';
            $pw = 'bel#4170';
            break;
        case 'A057PROD':
            $sid = "a057prod";
            $sr = "txbdbshur009v.bh.textron.com:1521";
            $un = 'premier';
            $pw = 'bel#1219';
            break;
        case 'A064PROD':
            $sid = "a064prod";
            $sr = "txbdbshur008v.bh.textron.com:1521";
            $un = 'premier';
            $pw = 'bel#65183319582';
            break;
    }


    //PConnect(false, 'scott', 'tiger', $oraname).
    //print "un=$un|pw=$pw";
    $conn->PConnect($sr,$un,$pw,$sid);
    //print "conn=$conn";
    //die("done");

    $conn->SetFetchMode(3);
    //$junk = $conn->Execute("ALTER SESSION SET CURSOR_SHARING=FORCE");
    $myrs = $conn->Execute($sql);
    //array_debug($myrs);
    return $myrs;
}
// ------------------------------------------------------------------
function dbCall_Oracle2($sql,$debug=false,$db_server='')
{
    //print "$g_db_server|$g_db_name|$g_db_user|$g_db_pass|$g_db_type<br>";
    $conn = &ADONewConnection('oci8');
    $conn->debug=$debug;

    //,'','',$db_server,'','',$db_server

    // production:  premier / Prem195625081979
    // development:  premier / Prem19796056
    // test:  premier / Prem195623967
    // should have access to all ev data on a019, a021, a022, a035

    switch(strtoupper($db_server))
    {
        case 'A010PROD':
            $sid = "a010prod.world";
            $sr = "txbdbshur601.bh.textron.com:1521";
            //$un = 'ops$ii38538';
            //$pw = 'bhtis';
            $un = 'app_simmerman';
            $pw = 'bell186';
            break;
        case 'A019PROD':
            $sid = "a019prod.world";
            $sr = "evmsora.bh.textron.com:1521";
            //$un = 'ops$ii38538';
            //$pw = 'bhtis';
            $un = 'premier';
            $pw = 'Prem195625081979';
            break;
        case 'A021PROD':
            $sid = "a021prod.world";
            $sr = "evmsora2.bh.textron.com:1521";
            //$un = 'millennium';
            //$pw = 'cmmm0901';
            $un = 'premier';
            $pw = 'Prem195625081979';
            break;
        case 'A021PROD-FULL-ACCESS':
            $sid = "a021prod.world";
            $sr  = "evmsora2.bh.textron.com:1521";
            $un  = 'millennium';
            $pw  = 'cmmm0901';
            break;
        case 'A021TEST':
            $sid = "a021test.world";
            $sr = "txbdbshur901v.bh.textron.com:1521";
            //$un = 'millennium';
            //$pw = 'cmmm0901';
            $un = 'premier';
            $pw = 'Prem195625081979';
            break;
        case 'A021TEST-FULL-ACCESS':
            $sid = "a021test.world";
            $sr  = "txbdbshur901v.bh.textron.com:1521";
            $un  = 'millennium';
            $pw  = 'cmmm0901';
            break;
        case 'A022PROD':
            $sid = "a022prod";
            $sr = "evmsora2:1523";
            //$un = 'applws';
            //$pw = 'wsap0704';
            $un = 'premier';
            $pw = 'Prem195625081979';
            break;
         case 'DWPROD':
            $sid = "dwprod.world";
            $sr = "txbdbshur008v:1521";
            $un = 'premier';
            $pw = 'Prem195625081979';
            break;
        case 'A021PREP':
            $sid = "a021prep.world";
            $sr = "tevmsora.bh.textron.com:1521";
            //$un = 'applbaca';
            //$pw = 'baca0103';
            $un = 'premier';
            $pw = 'Prem195625081979';
            break;
        case 'A019PROD-WRITEACCESS':
            $sid = "a019prod.world";
            $sr = "evmsora.bh.textron.com:1521";
            //$un = 'ops$ii38538';
            //$pw = 'bhtis';
            //$un = 'appltp';
            //$pw = 'appm1990';
            $un = 'premier';
            $pw = 'Prem195625081979';
            break;
        case 'A039PROD':
            $sid = "a039prod.world";
            $sr = "bhtiora2.bh.textron.com:1521";
            $un = 'premier';
            $pw = 'bhtis123';
            //$un = 'premier';
            //$pw = 'Prem195625081979';
            break;
        case 'A049PROD':
            $sid = "a049prod.world";
            $sr = "txasrsnwh021.ent.textron.com:1521";
            $un = 'ops$ii38538';
            $pw = 'bhtis123';
            //$un = 'premier';
            //$pw = 'Prem195625081979';
            break;
        case 'A060PROD':
            $sid = "a060prod.world";
            $sr = "txbdbshur008v.bh.textron.com:1521";
            $un = 'premier';
            $pw = 'bel#4170';
            break;
        case 'A064PROD':
            $sid = "a064prod";
            $sr = "txbdbshur008v.bh.textron.com:1521";
            $un = 'premier';
            $pw = 'bel#65183319582';
            break;
    }


    $conn->PConnect(false, $un, $pw, $db_server);
    //print "un=$un|pw=$pw";
    //$conn->PConnect($sr,$un,$pw,$sid);
    //print "conn=$conn";
    //die("done");

    $conn->SetFetchMode(3);
    //$junk = $conn->Execute("ALTER SESSION SET CURSOR_SHARING=FORCE");
    $myrs = $conn->Execute($sql);
    //array_debug($myrs);
    return $myrs;
}
// ------------------------------------------------------------------
function dbCall_ODBC($sql,$debug=false,$dsn='',$db_user='',$db_pass='',$fetch_mode=3)
{
    //print "$dsn|$db_user|$db_pass<br>";
    //exit();
    $conn = ADONewConnection('odbc');
    $conn->debug=$debug;
    $conn->Connect($dsn,$db_user,$db_pass);
    $conn->SetFetchMode($fetch_mode);
    $rs = $conn->Execute($sql);
    //if($debug) array_debug($rs);
    return $rs;
}
// ------------------------------------------------------------------
function dbCon_Oracle($db_server='',$debug=false)
{
    //print "$g_db_server|$g_db_name|$g_db_user|$g_db_pass|$g_db_type<br>";
    $conn = &ADONewConnection('oci8');
    //,'','',$db_server,'','',$db_server

    switch(strtoupper($db_server))
    {
        case 'A010PROD':
            $sid = "a010prod.world";
            $sr = "txbdbshur601.bh.textron.com:1521";
            //$un = 'ops$ii38538';
            //$pw = 'bhtis';
            $un = 'app_simmerman';
            $pw = 'bell186';
            break;
        case 'A019PROD':
            $sid = "a019prod.world";
            $sr = "evmsora.bh.textron.com:1521";
            //$un = 'ops$ii38538';
            //$pw = 'bhtis';
            $un = 'premier';
            $pw = 'Prem195625081979';
            break;
        case 'A021PROD':
            $sid = "a021prod.world";
            $sr = "evmsora2.bh.textron.com:1521";
            //$un = 'millennium';
            //$pw = 'cmmm0901';
            $un = 'premier';
            $pw = 'Prem195625081979';
            break;
        case 'A021PROD-FULL-ACCESS':
            $sid = "a021prod.world";
            $sr  = "evmsora2.bh.textron.com:1521";
            $un  = 'millennium';
            $pw  = 'cmmm0901';
            break;
        case 'A021TEST':
            $sid = "a021test.world";
            $sr = "txbdbshur901v.bh.textron.com:1521";
            //$un = 'millennium';
            //$pw = 'cmmm0901';
            $un = 'premier';
            $pw = 'Prem195625081979';
            break;
        case 'A021TEST-FULL-ACCESS':
            $sid = "a021test.world";
            $sr  = "txbdbshur901v.bh.textron.com:1521";
            $un  = 'millennium';
            $pw  = 'cmmm0901';
            break;
        case 'A022PROD':
            $sid = "a022prod";
            $sr = "evmsora2:1523";
            //$un = 'applws';
            //$pw = 'wsap0704';
            $un = 'premier';
            $pw = 'Prem195625081979';
            break;
         case 'DWPROD':
            $sid = "dwprod";
            $sr = "txbdbshur008v:1521";
            $un = 'premier';
            $pw = 'Prem195625081979';
            break;
        case 'A021PREP':
            $sid = "a021prep.world";
            $sr = "tevmsora.bh.textron.com:1521";
            //$un = 'applbaca';
            //$pw = 'baca0103';
            $un = 'premier';
            $pw = 'Prem195625081979';
            break;
        case 'A019PROD-WRITEACCESS':
            $sid = "a019prod.world";
            $sr = "evmsora.bh.textron.com:1521";
            //$un = 'ops$ii38538';
            //$pw = 'bhtis';
            //$un = 'appltp';
            //$pw = 'appm1990';
            $un = 'premier';
            $pw = 'Prem195625081979';
            break;
        case 'A039PROD':
            $sid = "a039prod.world";
            $sr = "bhtiora2.bh.textron.com:1521";
            $un = 'premier';
            $pw = 'bhtis123';
            //$un = 'premier';
            //$pw = 'Prem195625081979';
            break;
        case 'A049PROD':
            $sid = "a049prod.world";
            $sr = "txasrsnwh021.ent.textron.com:1521";
            $un = 'ops$ii38538';
            $pw = 'bhtis123';
            //$un = 'premier';
            //$pw = 'Prem195625081979';
            break;
        case 'A060PROD':
            $sid = "a060prod.world";
            $sr = "txbdbshur008v.bh.textron.com:1521";
            $un = 'premier';
            $pw = 'bel#4170';
            break;
        case 'A064PROD':
            $sid = "a064prod";
            $sr = "txbdbshur008v.bh.textron.com:1521";
            $un = 'premier';
            $pw = 'bel#65183319582';
            break;
    }

    //print "un=$un|pw=$pw";

    $conn->PConnect($sr,$un,$pw,$sid);
    $conn->debug=$debug;
    $conn->SetFetchMode(3);
    return $conn;
}
// ------------------------------------------------------------------
function dbPrep($conn,$sql)
{
    $psql = $conn->Prepare($sql);
    return $psql;
}
// ------------------------------------------------------------------
function dbRS($conn,$sql,$the_array,$debug=false)
{
    if($debug==true) $conn->debug=true;
    $rs = $conn->Execute($sql,$the_array);
    return $rs;
}
// ------------------------------------------------------------------
function getFieldNamesFromRS($theRS)
{
    // theRS is an ADODB recordset
    $i=0;
    $fields = array();
    while($i < $theRS->FieldCount())
    {
        $fld = $theRS->FetchField($i);
        $fields[] = $fld->name;
        $i++;
    }
    return $fields;
}
// ------------------------------------------------------------------
function getFieldsStringForTable($schema,$table,$limit_wc=" limit 1",$fields_to_exclude='',$db_server='')
{
    $sql = "select * from $schema.$table $limit_wc";
    if($db_server=='txbapphur213')
    {
        $theRS  = dbCall213($sql,false,$schema,$db_server);
    }
    else
    {
        $theRS  = dbCall($sql,false,$schema,$db_server);
    }

    $i=0;
    $fields = '';
    while($i < $theRS->FieldCount())
    {
        $fld = $theRS->FetchField($i);
        $fn  = $fld->name;
        if((isItemInArray($fields_to_exclude,trim($fn),true)==false) or $fields_to_exclude=='') $fields .= $fn .",";
        $i++;
    }
    $fields=stripLastCharacter($fields);
    return $fields;
}
// ------------------------------------------------------------------
function getMetaDataFromRS($theRS)
{
    // theRS is an ADODB recordset
    $i=0;
    $field_info = array();
    while($i < $theRS->FieldCount())
    {
        $fld = $theRS->FetchField($i);
        $field_info[] = array($fld->name,$fld->type,$fld->max_length);
        $i++;
    }
    return $field_info;
}
// ------------------------------------------------------------------
function getArrayFromRS($rs)
{
    $fields = getFieldNamesFromRS($rs);
    $rc = $rs->RecordCount();
    $i=0;
    $a = array();
    while($i<$rc)
    {
        $f=0;
        $t = array();
        while($f<count($fields))
        {
            $field = $fields[$f];
            $t["$field"] = $rs->fields["$field"];
            $f++;
        }
        $a[] = $t;
        $rs->MoveNext();
        $i++;
    }
    return $a;
}
// ------------------------------------------------------------------
function rs2json($rs)
{
    if($rs)
    {
        $fields = getFieldNamesFromRS($rs);
        $rc = $rs->RecordCount();
        $i=0;
        $z = "[";
            /* THIS CODE GOES IN LOOP BELOW
            while($f<count($fields))
            {
                $field = $fields[$f];

                $f++;
            }
            */
        while($i<$rc)
        {
            $f=0;
            $t = array();
            $z .= "{";

            foreach($fields as $field)
            {
                $z .= "$field:'" . str_replace("'","",$rs->fields["$field"]) . "',";
            }
            //$z = stripLastCharacter($z);
            $z = stripLastCharacter($z);
            $z .= "},";
            $rs->MoveNext();
            $i++;
        }
        //$z = stripLastCharacter($z);
        $z = stripLastCharacter($z);
        $z .= "]";
        return $z;
    }
    else
    {
        return '';
    }
}
// ------------------------------------------------------------------
function rs2json2($rs)
{
    if($rs)
    {
        $fields = getFieldNamesFromRS($rs);
        //die(array_debug($fields));
        $rc = $rs->RecordCount();
        $i=0;
        $z = "[";
            /* THIS CODE GOES IN LOOP BELOW
            while($f<count($fields))
            {
                $field = $fields[$f];

                $f++;
            }
            */
        while($i<$rc)
        {
            $f=0;
            $t = array();
            //$z .= "";

            $z .= json_encode($rs->fields[$i]);
            //$z = stripLastCharacter($z);
            $z = stripLastCharacter($z);
            $z .= "},";
            $rs->MoveNext();
            $i++;
        }
        //$z = stripLastCharacter($z);
        $z = stripLastCharacter($z);
        $z .= "]";
        return $z;
    }
    else
    {
        return '';
    }
}
// ------------------------------------------------------------------
function getRS2JSONUniqueValues($myRS,$field)
{
    $myRS->MoveFirst();
    $i=0;
    $f = getFieldNamesFromRS($myRS);
    $numFields = count($f);
    //print "eof=".$myRS->EOF . "<br>";
    $temp = array();
    while(!$myRS->EOF)
    {
        $j=0;
        while($j < $numFields)
        {
            //print "f[$j] = ". $f[$j] . "<br>";
            $value = $myRS->fields[$f[$j]];
            if($f[$j]==$field and $value!='') $temp[] = $value;
            $j++;
        }
        $myRS->MoveNext();
    }
    //print "temp=$temp<br>";
    $ar = array_unique($temp);
    $i=0;
    //array_debug($ar);
    while (list($key, $value) = each($ar)) {
        $value = str_replace("'","",$value);
        $z .= "{id:\"$value\", title:\"$value\", align:\"left\"},";
    }
    return stripLastCharacter($z);
}
// ------------------------------------------------------------------
function getVarsFromRS($rs)
{
    $f = getFieldNamesFromRS($rs);
    $i=0;
    while($i < count($f))
    {
        eval("\$$f = \$rs->fields['$f'];");
        $i++;
    }

}
// ------------------------------------------------------------------
function getUpdateSQL($sql,$updateArray,$debug=false,$db_name='')
{
    global $g_db_server,$g_db_name,$g_db_user,$g_db_pass,$g_db_type;
    $conn = &ADONewConnection($g_db_type);
    $conn->PConnect($g_db_sever,$g_db_user,$g_db_pass,$db_name);
    $conn->debug=$debug;
    $rs = $conn->Execute($sql);
    $sql = $conn->getUpdateSQL($rs,$updateArray,true,true);
    return $sql;
}
// ------------------------------------------------------------------
function getInsertSQL($sql,$insertArray,$debug=false,$db_name='',$db_server='')
{
    global $g_db_server,$g_db_name,$g_db_user,$g_db_pass,$g_db_type;
    if($db_name=='') $db_name = $g_db_name;
    $conn = &ADONewConnection($g_db_type);
    if($db_server=='') $db_server = $g_db_server;
    $conn->PConnect($db_server,$g_db_user,$g_db_pass,$db_name);
    $conn->debug=$debug;
    $rs = $conn->Execute($sql);
    $sql = $conn->getInsertSQL($rs,$insertArray,true);
    return $sql;
}

// ------------------------------------------------------------------

function getReplaceSQL($table,$dataArray,$indexName='id',$debug=false,$db_name='',$null_string_2_null=false,$db_server='')
{
    global $g_db_server,$g_db_name,$g_db_user,$g_db_pass,$g_db_type;
    if($db_server=='')$db_server = $g_db_server;
    $conn = &ADONewConnection($g_db_type);
    if($db_name=='') $db_name = $g_db_name;
    $conn->PConnect($db_server,$g_db_user,$g_db_pass,$db_name);
    $conn->debug=$debug;
    //$rs = $conn->Execute("select $indexName from $table");
    if($null_string_2_null==true)
    {
        $i=0;
        $keys = array_keys($dataArray);
        foreach($dataArray as $d)
        {
            $key = $keys[$i];
            $dataArray[$key] = stripslashes($dataArray[$key]);
            if($d=='') $dataArray[$key]='null';
            $i++;
        }
    }
    $sql = $conn->Replace($table,$dataArray,$indexName,true,$debug);
    return $sql;
}
function getReplaceSQLIB($table,$dataArray,$indexName='id',$debug=false,$db_name='',$null_string_2_null=false,$db_server='')
{
    global $g_db_server,$g_db_name,$g_db_user,$g_db_pass,$g_db_type;
    if($db_server=='')$db_server = $g_db_server;
    $conn = &ADONewConnection($g_db_type);
    if($db_name=='') $db_name = $g_db_name;
    $conn->Connect($db_server,$g_db_user,$g_db_pass,$db_name);
    $conn->debug=$debug;
    //$rs = $conn->Execute("select $indexName from $table");
    if($null_string_2_null==true)
    {
        $i=0;
        $keys = array_keys($dataArray);
        foreach($dataArray as $d)
        {
            $key = $keys[$i];
            $dataArray[$key] = stripslashes($dataArray[$key]);
            if($d=='') $dataArray[$key]='null';
            $i++;
        }
    }
    $sql = $conn->Replace($table,$dataArray,$indexName,true,$debug);
    return $sql;
}
// ------------------------------------------------------------------

function getBHUserData($id)
{
    //$sql = "select bh_program,bh_role,user_name,page_style from users where id='$id'";
    //$rs  = dbCall($sql);
    return array($_SESSION['s_user']["bh_program"],$_SESSION['s_user']["bh_role"],$_SESSION['s_user']["user_name"],$_SESSION['s_user']['page_style']);
}

function getUserData($id)
{
    $sql = "select * from master_user where id='$id'";
    $rs  = dbCall($sql);
    $ar = getArrayFromRS($rs);
    return $ar[0];
}

// ------------------------------------------------------------------

function setTempData($id,$data,$description='',$type='',$delete_before=false,$debug=false)
{
    global $current_user,$s_user_id;
    $cb = $s_user_id;
    $data = addslashes($data);

    if($delete_before)
    {
        $sql = "delete from temp_data where (description='$description' and created_by='$cb')";
         $j = dbCall($sql,$debug);
    }

    $sql = "
        insert into temp_data (id,created_by,data,description,type,entered_on) values
        ('$id','$cb',\"$data\",'$description','$type',sysdate())
    ";
    $j = dbCall($sql,$debug);
    return true;
}

// ------------------------------------------------------------------

function getTempData($id, $description='', $delete_after=false, $debug=false)
{
    global $s_user_id, $current_user;
    $cb = $s_user_id;

    $sql     = "select data from temp_data where id='$id' and description='$description'";
    $rs      = dbCall($sql,$debug);
    $data     = $rs->fields["data"];

    //print "data=$data<br>";


    if($delete_after)
    {
        $sql = "delete from temp_data where id='$id' or
            (description='$description' and created_by='$cb')";
         $j = dbCall($sql,$debug);
    }

    // delete old entries (more than one day old)
    $sql = "delete from temp_data where datediff(sysdate(),entered_on)>1";
       $j = dbCall($sql,$debug);

    return stripslashes($data);
}

// ------------------------------------------------------------------

function tempRecordExists($description,$type)
{
    $sql = "select id from temp_data where description='$description'
     and type='$type' ";
    $rs = dbCall($sql,false);
    $f = false;
    if($rs->fields['id']!='') $r = $rs->fields['id'];
    return $r;
}

// ------------------------------------------------------------------

function logVisit($name,$page,$uid,$program_group,$server)
{
    global $HTTP_SERVER_VARS;
    $rip = $HTTP_SERVER_VARS["REMOTE_ADDR"];
    $lip = $HTTP_SERVER_VARS["LOCAL_ADDR"];
    if(trim($name)=="") $name = "Anonymous";
    if($server=='') $server='premier';
    if($page=='') $page = 'Login';
    if($uid=='') $uid='0';
    $page = addslashes($page);
    $name = addslashes($name);
    $user_company = $_SESSION['s_user']['company'];
    $sql = "
        insert into admin_history (name,access_moment,page,user_id,program_group,uid,rip,lip,server,company)
        values ('$name',now(),'$page','$uid','$program_group',0,'$rip','$lip','$server','$user_company')
    ";
    $rs = dbCall($sql);
    return true;
}

// ------------------------------------------------------------------

function getFullName($id,$debug=false,$data_return='l, f')
{
    // valid data returns:
    // l, f
    // f
    // l
    // f l
    switch($data_return)
    {
        case 'l, f':
            $sql = "select concat(trim(last_name),concat(', ',trim(first_name))) as full_name from master_user where id='$id'";
            break;
        case 'f l':
            $sql = "select concat(trim(first_name),concat(' ',trim(last_name))) as full_name from master_user where id='$id'";
            break;
        case 'f':
            $sql = "select trim(first_name) as full_name from master_user where id='$id'";
            break;
        case 'l':
            $sql = "select trim(last_name) as full_name from master_user where id='$id'";
            break;
    }

    $rs = dbCall($sql,$debug);
    $name = $rs->fields['full_name'];
    if($debug and $name=='') array_debug($rs);
    return $name;
}

// ------------------------------------------------------------------

function getEMail($id)
{
    $sql = "select email1 as email from master_user where id='$id'";
    $rs = dbCall($sql);
    return $rs->fields['email'];
}

function getLastProgram($id)
{
    $sql = "select last_program from master_user where id='$id'";
    $rs = dbCall($sql);
    return $rs->fields['last_program'];
}

function setLastProgram($id,$program)
{
    $sql  = "update master_user set last_program='$program' where id='$id'";
    $junk = dbCall($sql,false);
    return true;
}

// ------------------------------------------------------------------

function getWorkPhone($id)
{
    $sql = "select phone_work from master_user where id='$id'";
    $rs = dbCall($sql);
    return $rs->fields['phone_work'];
}

// ------------------------------------------------------------------

function nslookup ($ip) {
 $res=`nslookup -timeout=3 -retry=1 $ip`;
 if (preg_match('/\nName:(.*)\n/', $res, $out)) {
   return trim($out[1]);
 } else {
   return $ip;
 }
}

// ------------------------------------------------------------------

function getProgramByIP()
{
    global $HTTP_SERVER_VARS,$HTTP_ENV_VARS;
    $ip = $HTTP_SERVER_VARS['REMOTE_ADDR'];
    $pc_name = $HTTP_ENV_VARS['COMPUTERNAME'];
    if(strlen($ip)>4 and $pc_name=='') $pc_name = nslookup($ip);
    //print "ip=$ip<br>pc_name=$pc_name<br>";
    if($pc_name!='') $ip = $pc_name;
    $sql = "select program_group from ip_program where ip='$ip'";
    $rs = dbCall($sql);
    return $rs->fields['program_group'];
}

// ------------------------------------------------------------------

function setProgramByIP($prog)
{
    global $HTTP_SERVER_VARS,$HTTP_ENV_VARS,$current_user;
    //array_debug($HTTP_SERVER_VARS);
    $ip = $HTTP_SERVER_VARS['REMOTE_ADDR'];
    $pc_name = $HTTP_ENV_VARS['COMPUTERNAME'];
    if(strlen($ip)>4 and $pc_name=='') $pc_name = nslookup($ip);
    //print "ip=$ip<br>pc_name=$pc_name<br>";
    if($pc_name!='') $ip = $pc_name;
    $user_id = $s_user_id;
    $sql = "delete from ip_program where ip='$ip'";
    $junk = dbCall($sql);

    $sql = "insert into ip_program (ip,program_group,user_id,pc_name) values ('$ip','$prog','$user_id','$pc_name')";
    $junk = dbCall($sql);
}


// ------------------------------------------------------------------

function setDBCookie($name,$uid,$value='',$expires='0000-00-00',$debug=false)
{
    //if($uid=='1') $debug=true;
    if($value!='')
    {
        $sql = "delete from db_cookie where name='$name' and user_id='$uid'";
        $junk = dbCall($sql,$debug,'tools_data');

        $sql = "insert into db_cookie (name,user_id,value,expires) values
                ('$name','$uid','$value','$expires')";
        $junk = dbCall($sql,$debug,'tools_data');
    }
    //if($debug==true) exit();
    return true;
}

// ------------------------------------------------------------------

function getDBCookie($name,$uid)
{
    $sql = "select value from db_cookie where name='$name' and user_id='$uid'";
    $rs  = dbCall($sql,false,'tools_data');
    return $rs->fields['value'];
}

// ------------------------------------------------------------------

function checkUserData()
{
    global $s_user_id;

    // if missing/null user info, send user to input/update info page at this point
    $user_sql = "
        select
            coalesce(user_name,'') as user_name,
            coalesce(first_name,'') as first_name,
            coalesce(last_name,'') as last_name,
            coalesce(bh_program,'') as bh_program,
            coalesce(bh_role,'') as bh_role,
            coalesce(email1,'') as email1
        from master_user
        where id = '$s_user_id'
    ";

    $user_rs = dbCall($user_sql);

    $user_user_name  = $user_rs->fields['user_name'];
    $user_first_name = $user_rs->fields['first_name'];
    $user_last_name  = $user_rs->fields['last_name'];
    $user_bh_program = $user_rs->fields['bh_program'];
    $user_bh_role    = $user_rs->fields['bh_role'];
    $user_email1     = $user_rs->fields['email1'];

    $verified = true;
    if ($user_user_name == ''  or $user_first_name == '' or $user_last_name == '' or $user_bh_program == '' or $user_bh_role == '' or $user_email1 == '') {$verified = false;}
    // if not true, then need to get info - i.e. send user to the update page
    if($verified != true)
    {
        navigate("admin.user.update.info.php?old_bh_program=$bh_program&old_bh_role=$bh_role");
        exit();
    }
}

function getQuote()
{
    $sql = "select count(id) from admin_quotes where quote is not null and quote<>''";
    $rs = dbCall($sql);
    $max = $rs->fields['count(id)'];
    $id = getRandom(1,$max);
    $sql = "select quote,author from admin_quotes where id=$id and quote is not null and quote<>''";
    $rs = dbCall($sql);
    $author = $rs->fields['author'];
    $quote = stripslashes($rs->fields['quote']);
    $r = "$quote - $author";
    //array_debug($rs);
    while(trim($r)=='-')
    {
        $r = getQuote();
    }
    return $r;
}

// ------------------------------------------------------------------

function getRecentlyUsed($uid,$type='table')
{
    $sql = "
    select
        h.page,
        h.name,
        concat(u.last_name,', ',u.first_name) as uname
    from
        history h,
        users u
    where
        h.user_id = u.id and
        h.user_id='$uid'
    group by
        h.page,
        h.name,
        concat(u.last_name,', ',u.first_name)
    order by
        max(h.access_moment) desc
    limit 15
    ";
    $rs = dbCall($sql);

    if($type=='darkTable')
    {
        $t = "
            <table border='1' class='darkTable' cellspacing='0' cellpadding='0'>
                <tr>
                    <th class='darkTH'>Your Favorite Pages</th>
                </tr>
        ";
    }
    else
    {
        $t = "
            <table border='1' cellspacing='0' cellpadding='0'>
                <tr>
                    <th bgcolor='#e8e8e8'>Your Favorite Pages</th>
                </tr>
        ";
    }
    $pages = array('');
    $links = array('');

    while(!$rs->EOF)
    {
        //print
        $page = $rs->fields['page'];
        if($page!='Login')
        {
            $uname     = $rs->fields['uname'];
            $name     = $rs->fields['name'];
            $data     = explode('|',$name);
            $tab      = $data[0];
            $title  = $data[1];

            $title = capitalize($title);
            $link = "<a href='$page'>$title</a>";

            $pages[] = $title;
            $links[] = $page;

            if($type=='darkTable')
            {
                $t .= "<tr><td class='darkTD'>$link</td></tr>\n";
            }
            else
            {
                $t .= "<tr><td>$link</td></tr>\n";
            }
        }
        $rs->MoveNext();
    }
    $t .= "</table>";

    if($type=='table' or $type=='darkTable') $temp = $t;
    if($type=='dropDown') $temp = arraySelect('ru_pages',"size='1' onChange='document.location=this.value;'",$links,$pages,$ps);

    return $temp;
}

function getRecentlyUsedJSON($uid)
{
    $sql = "
    select
        h.page,
        h.name,
        concat(u.last_name,', ',u.first_name) as uname
    from
        admin_history h,
        master_user u
    where
        h.user_id = u.id and
        h.user_id='$uid'
    group by
        h.page,
        h.name,
        concat(u.last_name,', ',u.first_name)
    order by
        max(h.access_moment) desc
    limit 15
    ";
    $rs = dbCall($sql);

    /*
            {title: 'data.xml', checked: true},
            {title: 'Component Guide.doc'},
            {title: 'SmartClient.doc', checked: true},
            {title: 'AJAX.doc'}

    */

    while(!$rs->EOF)
    {
        //print
        $page = $rs->fields['page'];
        if($page!='Login' and trim($page)!='')
        {
            $uname     = $rs->fields['uname'];
            $name     = $rs->fields['name'];
            $data     = explode('|',$name);
            $tab      = $data[0];
            $title  = $data[1];

            $title = addslashes(capitalize($title));
            if(trim($title)=='') $title=$page;
            $z .= "{title: '$title', click:\"location.href='$page';\"},\n";
        }
        $rs->MoveNext();
    }

    $z = stripLastCharacter($z);
    $z = stripLastCharacter($z);
    return $z;
}

// ------------------------------------------------------------------

function getOneCalendarItem($id)
{
    $sql = "select * from calendar where id=$id";
    return dbCall($sql,false,'tools_data');
}

// ------------------------------------------------------------------

function getUnstatusedTaskInfoDetail($yw='',$prog='',$proj_id='',$ca='',$cam='')
{
    // returns an array (unstatused, total, percentage)

    if($prog<>'') $wc = " and program_group='$prog' ";
    if(strtoupper(trim($prog))=='ALL' or $prog=='%')
    {
        $wc = " and program_group like '%' ";
    }

    if($proj_id<>'') $wc .= " and proj_id='$proj_id' ";
    if($cam<>'') $wc .= " and cam='$cam' ";
    if($ca<>'') $wc .= " and ca='$ca' ";
    $table = 'unstatused_' . $yw;
    if($yw=='') $table = 'unstatused';
    $sql = "select count(task_code) as num from $table u where status='Unstatused' and ((select dashboard from tools_data.project_master where ppm_ap_id=u.proj_id limit 1)=1) $wc";
    $rs  = dbCall($sql,false,'unstatused');
    $unstatused = $rs->fields['num'];
    $sql = "select count(task_code) as num from $table u where status='Statused' and ((select dashboard from tools_data.project_master where ppm_ap_id=u.proj_id limit 1)=1) $wc";
    $rs  = dbCall($sql,false,'unstatused');
    $statused = $rs->fields['num'];
    $percentage = round(($unstatused / ($statused + $unstatused))*100);
    return array($unstatused,($statused + $unstatused),$percentage);
}

// ------------------------------------------------------------------

function getUnstatusedTaskInfo($yw='',$wc,$table,$debug=false)
{
    // returns an array (unstatused, total, percentage)

    $wc = str_replace('sc.pmid=mv.pmid and sc.cmid=mv.cmid',' mv.ppm_ap_id=u.proj_id and u.ca=mv.ca ',$wc);
    $wc = str_replace('sc.','u.',$wc);

    //$table = str_replace('data_schedule sc','unstatused u',$table);
    if($yw!='') $table  = str_replace('unstatused u',"unstatused_".$yw." u",$table);

    $sql        = "select count(u.task_code) as num from unstatused u,premier_core.main mv where $wc and u.status='Unstatused' and mv.dashboard=1";
    $rs         = dbCall($sql,$debug,'unstatused');
    $unstatused = $rs->fields['num'];

    $sql        = "select count(u.task_code) as num from unstatused u,premier_core.main mv where $wc and u.status='Statused' and mv.dashboard=1";
    $rs         = dbCall($sql,$debug,'unstatused');
    $statused   = $rs->fields['num'];

    $percentage = round(($unstatused / ($statused + $unstatused))*100);

    return array($unstatused,($statused + $unstatused),$percentage);
}

// ------------------------------------------------------------------

// ------------------------------------------------------------------
/*
function getUnstatusedTaskInfoDetail($yw='',$prog='',$proj_id='',$ca='',$cam='')
{
    // returns an array (unstatused, total, percentage)

    if($prog<>'') $wc = " and program_group='$prog' ";
    if(strtoupper(trim($prog))=='ALL' or $prog=='%')
    {
        $wc = " and program_group like '%' ";
    }

    if($proj_id<>'') $wc .= " and $proj_id ";
    if($cam<>'') $wc .= " and cam='$cam' ";
    if($ca<>'') $wc .= " and ca='$ca' ";
    $table = 'unstatused_' . $yw;
    if($yw=='') $table = 'unstatused';
    $sql = "select count(task_code) as num from $table u where status='Unstatused' and ((select dashboard from tools_data.project_master where ppm_ap_id=u.proj_id limit 1)=1) $wc";
    $rs  = dbCall($sql,false,'unstatused');
    $unstatused = $rs->fields['num'];
    $sql = "select count(task_code) as num from $table u where status='Statused' and ((select dashboard from tools_data.project_master where ppm_ap_id=u.proj_id limit 1)=1) $wc";
    $rs  = dbCall($sql,false,'unstatused');
    $statused = $rs->fields['num'];
    $percentage = round(($unstatused / ($statused + $unstatused))*100);
    return array($unstatused,($statused + $unstatused),$percentage);
}

// ------------------------------------------------------------------

function getUnstatusedTaskInfo($yw='',$prog='',$proj_id='',$ca='',$cam='')
{
    // returns an array (unstatused, total, percentage)

    if($prog<>'') $wc = " and program_group='$prog' ";
    if(strtoupper(trim($prog))=='ALL' or $prog=='%')
    {
        $wc = " and program_group like '%' ";
    }

    if($proj_id<>'') $wc .= " and $proj_id ";
    if($cam<>'') $wc .= " and cam='$cam' ";
    if($ca<>'') $wc .= " and ca='$ca' ";
    $table = 'unstatused_' . $yw;
    if($yw=='') $table = 'unstatused';
    $sql = "select count(task_code) as num from $table u where status='Unstatused' and ((select dashboard from tools_data.project_master where ppm_ap_id=u.proj_id limit 1)=1) $wc";
    $rs  = dbCall($sql,false,'unstatused');
    $unstatused = $rs->fields['num'];
    $sql = "select count(task_code) as num from $table u where status='Statused' and ((select dashboard from tools_data.project_master where ppm_ap_id=u.proj_id limit 1)=1) $wc";
    $rs  = dbCall($sql,false,'unstatused');
    $statused = $rs->fields['num'];
    $percentage = round(($unstatused / ($statused + $unstatused))*100);
    return array($unstatused,($statused + $unstatused),$percentage);
}
*/
// ------------------------------------------------------------------
function _cp($user,$area)
{
    if(left($area,3)=='var') return true;
    if($user=='')
    {
        $sql = "select id,user_id from permissions where area='$area'";
        $rs  = dbCall($sql,false,'sugarcrm2');
        if($rs)
        {
            $r = array();
            while(!$rs->EOF)
            {
                if($rs->fields['id']!='') $r[] = $rs->fields['user_id'];
                $rs->MoveNext();
            }
        }
    }
    else
    {
        $sql = "select id from permissions where user_id='$user' and area='$area'";
        $rs  = dbCall($sql,false,'sugarcrm2');
        $r = false;
        if((int)$rs->fields['id']>0) $r = true;

    }
    return $r;
}
// ------------------------------------------------------------------
function _cpAdd($user,$area)
{
    $sql = "insert into permissions (user_id,area) values ('$user','$area')";
    $rs  = dbCall($sql,false,'sugarcrm2');
    return true;
}
// ------------------------------------------------------------------
function _cpDelete($user,$area)
{
    if($user=='')
    {
        $sql = "delete from permissions where area='$area'";
    }
    else
    {
        $sql = "delete from permissions where user_id='$user' and area='$area'";
    }
    $rs  = dbCall($sql,false,'sugarcrm2');
    return true;
}
// ------------------------------------------------------------------
function getArrayKeyFromValue($ar,$match_value)
{
    foreach($ar as $key=>$value)
    {
        if($value==$match_value) return $key;
    }
    return false;
}
// ------------------------------------------------------------------
function getWSContrname($premier_name)
{
    $project_wc = breakoutFilter($premier_name,'premier_name',"'","|","like");
    $sql = "select ws_contract_name from project_master where $project_wc group by ws_contract_name";
    $rs = dbCall($sql,false,'tools_data');

    $r = '';
    if($rs->RecordCount()>1) $r = array();

    if($rs->RecordCount()>1)
    {
        while(!$rs->EOF)
        {
            $name = $rs->fields['ws_contract_name'];
            $r[] = $name;
            $rs->MoveNext();
        }
    }
    else
    {
        $r = $rs->fields['ws_contract_name'];
    }
    //print "r=$r<br>";
    return $r;
}
// ------------------------------------------------------------------
function getWSContrID($contrname)
{
    $sql = "select ws_contract_id from project_master where ws_contract_name='$contrname'";
    $rs = dbCall($sql,false,'tools_data');
    return $rs->fields['ws_contract_id'];
}
// ------------------------------------------------------------------
function setPageStyle($uid,$ns)
{
    if($ns=='') $ns = 'blackops';
    $junk = dbCall("update users set page_style='$ns' where id='$uid'");
    return true;
}
// ------------------------------------------------------------------
function saveComputerName($cname,$uname)
{
    // first, try to insert
    // this will fail if it exists
    $sql = "insert into computernames (computername,username) values
    ('$cname','$uname')";
    $junk = dbCall($sql,false,'sugarcrm2');

    // now do update
    $sql = "update computernames set computername='$cname',username='$uname'";
    $junk = dbCall($sql,false,'sugarcrm2');

    //exit();

    return true;
}
// ------------------------------------------------------------------
function getUsernameFromComputerName($cname)
{
    $sql = "select username from computernames where computername='$cname'";
    $rs = dbCall($sql);
    return $rs->fields['username'];
}
// ------------------------------------------------------------------
function checkCitizenship($id)
{
    $us_array = array('njtw','jadavis','jkane','jpedigo','bway');   // array of users with US citizenship, but having non-clock number login ids
    if(isItemInArray($us_array,$id)) return true;

    if(left(strtolower(trim($id)),2)=='gv' or left(strtolower(trim($id)),2)=='kp' or trim($id)=='admin') return true;
    $id = right(trim($id),5);
    //print "<br><br>id=$id<br>";
    //exit();
    $rs = dbCall_IB("select country,citizenship_status from data_hr_citizenship where emplid='$id' and country='USA'",$debug);

    $country = trim($rs->fields['country']);
    $status  = trim($rs->fields['citizenship_status']);

    $us_citizen = false;
    if($country=='USA' and ($status=='1' or $status=='2' or $status=='3')) $us_citizen = true;
    return $us_citizen;
}
// ------------------------------------------------------------------
function getPCMBasisNames($wc,$date_filter)
{
    $date_filter = trim($date_filter);

    //year
    $y = left($date_filter,4);
    // month
    if(strlen($date_filter)==5)
    {
        // month is one digit
        $m = "0" . right($date_filter,1);
    }
    else
    {
        $m = right($date_filter,2);
    }

    $eac_name           = "EAC-$y-$m";
    $baseline_name      = "Baseline-$y-$m";

    if(trim($wc)!='') $wc = "and $wc";
    $sql = "select basisname from millennium.basis where (basisname='$eac_name' or basisname='$baseline_name') $wc group by basisname";
    $rs  = dbCall_Oracle($sql,false,'A021PROD');
    $rc  = $rs->RecordCount();
    if($rc == 2)
    {
        // return names as is
        $r = array($baseline_name,$eac_name);
    }
    elseif($rc ==1)
    {
        if($rs->fields['basisname']==$baseline_name)
        {
            $r = array($baseline_name,'EAC');
        }
        else
        {
            $r = array('Baseline',$eac_name);
        }
    }
    else
    {
        $r = array('Baseline','EAC');
    }

    // return array with baseline name, eac name
    return $r;
}
// ------------------------------------------------------------------
function getPM($name,$type)
{
    if($type=='ws')
    {
        $sql = "select premier_name from tools_data.project_master where ws_contract_name='$name'";
    }
    if($type=='pcm')
    {
        $sql = "select premier_name from tools_data.project_master where pcm_name='$name'";
    }
    if($type=='ppm_ap')
    {
        $sql = "select premier_name from tools_data.project_master where ppm_ap_name like '%$name%'";
    }
    if($type=='ppm_bl')
    {
        $sql = "select premier_name from tools_data.project_master where ppm_bl_name like '%$name%'";
    }
    if($type=='ppm_apprv')
    {
        $sql = "select premier_name from tools_data.project_master where ppm_apprv_proj_name='$name'";
    }

    $rs     = dbCall($sql,false);
    $pm     = $rs->fields['premier_name'];
    return $pm;
}
// ------------------------------------------------------------------
function getWSPCMPPM($name,$type)
{
    if (strpos($name,'|'))
    {
        $name_break_out = explode('|',$name);

        foreach($name_break_out as $name)
        {
            $name = trim($name);

            if($type=='ws')
            {
                $field = "ws_contract_name";
            }
            if($type=='pcm')
            {
                $field = "pcm_name";
            }
            if($type=='ppm_ap')
            {
                $field = "ppm_ap_name";
            }
            if($type=='ppm_bl')
            {
                $field = "ppm_bl_name";
            }
            if($type=='ppm_apprv')
            {
                $field = "ppm_apprv_proj_name";
            }
            $sql = "select $field from master_project where premier_name='$name'";
            $rs     = dbCall($sql,false,'premier_core');

            if($rs->fields["$field"]!='') $new_name  .= $rs->fields["$field"] . "|";

        }

        $new_name = stripLastCharacter($new_name);
    }
    else
    {
        if($type=='ws')
        {
            $field = "ws_contract_name";
        }
        if($type=='pcm')
        {
            $field = "pcm_name";
        }
        if($type=='ppm_ap')
        {
            $field = "ppm_ap_name";
        }
        if($type=='ppm_bl')
        {
            $field = "ppm_bl_name";
        }
        if($type=='ppm_apprv')
        {
            $field = "ppm_apprv_proj_name";
        }
        $sql = "select $field from master_project where premier_name='$name'";
        $rs     = dbCall($sql,false,'premier_core');
        $new_name  = $rs->fields["$field"];
    }
    return $new_name;
}
// ------------------------------------------------------------------
function getFilterVisibility($page)
{
    global $s_user_id;
    $sql = "select filter_visible as fv from admin_filter_visibility where user_id='$s_user_id' and page='$page'";
    $rs  = dbCall($sql,false);
    $fv  = $rs->fields['fv'];
    if($fv=='') $fv = 'yes';
    return $fv;
}
// ------------------------------------------------------------------
function removeRememberedFilters()
{
    global $s_user_id;
    $sql  = "delete from temp_data where description like 'c_%' and id='$s_user_id'";
    $junk = dbCall($sql,false,'sugarcrm2');
    //exit();
}
// ------------------------------------------------------------------
function getPPMIDFromPCMID($pcm_id)
{
    $sql = "select ppm_ap_id from project_master where pcm_project_id=$pcm_id";
    $rs = dbCall($sql,false,'tools_data');
    return $rs->fields['ppm_ap_id'];
}
// ------------------------------------------------------------------
function getPPMIDFromPCMIDFromPremier($pcm_id)
{
    $sql = "select ppm_ap_id from project_master where pcm_project_id=$pcm_id";
    $rs = dbCall_Premier($sql,false,'tools_data');
    return $rs->fields['ppm_ap_id'];
}
// ------------------------------------------------------------------
function getLags($proj_id,$task_code,$task_id,$pred_or_succ,$debug=false,$db_server='localhost',$oracle_database='A019PROD')
{
    // this function is used in the function schedLag()

    if($pred_or_succ=='predecessor')    {
        $sql = "
            SELECT
                TASK_CODE
                ,STATUS_CODE
                ,TASK_TYPE
                ,ADMUSER.TASKPRED.PRED_TASK_ID AS UNIQUE_ID
                ,CASE WHEN LAG_HR_CNT = 0 THEN LAG_HR_CNT ELSE (LAG_HR_CNT/8) END AS LAG
                ,PRED_TYPE AS LINK_TYPE
            FROM ADMUSER.TASK, ADMUSER.TASKPRED
            WHERE ADMUSER.TASKPRED.PRED_TASK_ID=ADMUSER.TASK.TASK_ID
            AND ADMUSER.TASKPRED.TASK_ID IN (SELECT TASK_ID FROM ADMUSER.TASK WHERE TASK_CODE='$task_code' AND PROJ_ID=$proj_id)
        ";
    }
    else    {   // successor
        $sql = "
            SELECT
                TASK_CODE
                ,STATUS_CODE
                ,TASK_TYPE
                ,ADMUSER.TASKPRED.TASK_ID AS UNIQUE_ID
                ,CASE WHEN LAG_HR_CNT = 0 THEN LAG_HR_CNT ELSE (LAG_HR_CNT/8) END AS LAG
                ,PRED_TYPE AS LINK_TYPE
            FROM ADMUSER.TASK,ADMUSER.TASKPRED
            WHERE ADMUSER.TASK.TASK_ID=ADMUSER.TASKPRED.TASK_ID
            AND ADMUSER.TASKPRED.PRED_TASK_ID IN (SELECT TASK_ID FROM ADMUSER.TASK WHERE TASK_CODE='$task_code' AND PROJ_ID=$proj_id)
        ";
    }

    $ors = dbCall_Oracle($sql,$debug,$oracle_database);

    while(!$ors->EOF)    {

        $unique_id   = $ors->fields['UNIQUE_ID'];
        $lag         = $ors->fields['LAG'];
        $link_type   = $ors->fields['LINK_TYPE'];
        $status_code = $ors->fields['STATUS_CODE'];
        $rel_task_type   = $ors->fields['TASK_TYPE'];
        $rel_task_code   = $ors->fields['TASK_CODE'];

        $lag_sql = "insert into sched_lag (proj_id,task_id,pred_or_succ,uid,lag,link_type,status_code,task_type,task_code) values($proj_id,$task_id,'$pred_or_succ',$unique_id,$lag,'$link_type','$status_code','$rel_task_type','$rel_task_code')";
        $junk  = dbCall($lag_sql,$debug,'schedule_data',$db_server);

        $ors->MoveNext();
    }
}
// ------------------------------------------------------------------
function schedLag($proj_id='%',$debug=false,$db_server='localhost',$oracle_database='A019PROD')
{

    if(trim($proj_id)!='%') {
        $project_wc = "proj_id = $proj_id AND";
        $delsql = "delete from sched_lag where proj_id = $proj_id";
    }
    else {
        $project_wc = '';
        $delsql = "truncate table sched_lag";
    }

    $junk  = dbCall($delsql,$debug,'schedule_data',$db_server);

    $psql = "
            SELECT
            proj_id,task_code,task_id
            FROM schedule_data.schedule
                WHERE
                $project_wc
                (EV_Method <> 'LE' OR ev_method='' OR ev_method IS NULL)
                AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL)
                AND (task_type NOT IN ('TT_LOE','TT_WBS') OR task_type='' OR task_type IS NULL)
                AND (status_code='TK_NotStart')
            ORDER BY proj_id,task_code
            ";

    $rs  = dbCall($psql,$debug,'schedule_data',$db_server);

    while(!$rs->EOF)
    {
        $proj_id   = $rs->fields['proj_id'];
        $task_code = $rs->fields['task_code'];
        $task_id   = $rs->fields['task_id'];

        //predecessor
        getLags($proj_id,$task_code,$task_id,'predecessor',$debug,$db_server,$oracle_database);

        //successor
        getLags($proj_id,$task_code,$task_id,'successor',$debug,$db_server,$oracle_database);

         //echo "Project: $proj_id Task Code: $task_code\n<br>\n";

    // ---------

        $p_or_s = "predecessor";

        $sql_ct_pred = "select count(id) as pred_ct from sched_lag where task_id=$task_id and proj_id=$proj_id and pred_or_succ='predecessor'";
        $rs_pred = dbCall($sql_ct_pred,$debug,'schedule_data',$db_server);
        $pred_ct = $rs_pred->fields['pred_ct'];

        $sql_ct_succ = "select count(id) as succ_ct from sched_lag where task_id=$task_id and proj_id=$proj_id and pred_or_succ='successor'";
        $rs_succ = dbCall($sql_ct_succ,$debug,'schedule_data',$db_server);
        $succ_ct = $rs_succ->fields['succ_ct'];

        //$sql_ct_lag = "select count(id) as lag from sched_lag where task_id=$task_id and proj_id=$proj_id and pred_or_succ='$p_or_s' and status_code='TK_Active' and lag > 5";
        $sql_ct_lag = "select count(id) as lag from sched_lag where task_id=$task_id and proj_id=$proj_id and pred_or_succ='$p_or_s' and lag > 5";
        $rs_lag = dbCall($sql_ct_lag,$debug,'schedule_data',$db_server);
        $lag_ct = $rs_lag->fields['lag'];

        //$sql_ct_lead = "select count(id) as lead from sched_lag where task_id=$task_id and proj_id=$proj_id and pred_or_succ='$p_or_s' and status_code='TK_Active' and lag < 0";
        $sql_ct_lead = "select count(id) as lead from sched_lag where task_id=$task_id and proj_id=$proj_id and pred_or_succ='$p_or_s' and lag < 0";
        $rs_lead = dbCall($sql_ct_lead,$debug,'schedule_data',$db_server);
        $lead_ct = $rs_lead->fields['lead'];

    // ---------

        //$sql_ct_lt = "select count(id) as link_type from sched_lag where task_id=$task_id and proj_id=$proj_id and pred_or_succ='$p_or_s' AND link_type IS NOT NULL and link_type <> '' and link_type <> 'PR_FS'";
        $sql_ct_lt = "
            SELECT count(sl.id) AS link_type
            FROM sched_lag sl LEFT JOIN `schedule` s ON (s.proj_id=sl.proj_id AND s.task_id=sl.task_id)
                WHERE
                sl.proj_id=$proj_id
                AND sl.task_id=$task_id

                AND (EV_Method <> 'LE' OR ev_method='' OR ev_method IS NULL)
                AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL)
                AND (s.status_code='TK_NotStart')

                AND (sl.pred_or_succ = 'predecessor')

                AND
                (
                    (   s.task_type IN ('TT_Task','TT_Rsrc','TT_Mile') AND
                            (
                                (sl.task_type IN ('TT_Task','TT_Rsrc','TT_FinMile') AND sl.link_type NOT IN ('PR_FS'))
                                OR
                                (sl.task_type IN ('TT_Mile') AND sl.link_type NOT IN ('PR_SS','PR_FS'))
                            )
                    )
                    OR
                    (   s.task_type IN ('TT_FinMile') AND
                            (
                                (sl.task_type IN ('TT_Task','TT_Rsrc','TT_FinMile') AND sl.link_type NOT IN ('PR_FF','PR_FS'))
                                OR
                                (sl.task_type IN ('TT_Mile') AND sl.link_type NOT IN ('PR_SF','PR_FS'))
                            )
                    )
                )
        ";

        $rs_lt = dbCall($sql_ct_lt,$debug,'schedule_data',$db_server);
        $lt_ct = $rs_lt->fields['link_type'];

    // ---------

        //$sql_update = "update schedule set num_pred=$pred_ct, num_succ=$succ_ct,lag=$lag_ct,lead=$lead_ct,link_type=$lt_ct where task_id=$task_id and proj_id=$proj_id";
        $sql_update = "update schedule set lag=$lag_ct,lead=$lead_ct,link_type=$lt_ct where task_id=$task_id and proj_id=$proj_id";
        $junk = dbCall($sql_update,$debug,'schedule_data',$db_server);

        $rs->MoveNext();
    }
}
// ------------------------------------------------------------------

function getPCMSchema($pcm_name_or_id,$by='name',$debug=false)
{
    if($by=='name')
    {
        $sql = "select pcm_schema from master_project where pcm_name='$pcm_name_or_id'";
    }
    else
    {
        $sql = "select pcm_schema from master_project where pcm_project_id='$pcm_name_or_id'";
    }
    $rs  = dbCall($sql,$debug,'premier_core');
    $r = $rs->fields['pcm_schema'];
    return $r;
}
// ------------------------------------------------------------------
function saveFormData($schema,$table,$pk='id',$debug=false)
{
    // this function will insert into the table
    // if the request variable matches the exact name of a
    // field in the database.  it will also update if the
    // primary key is not null
    $pk_value = $_REQUEST["$pk"];
    $rs = dbCall("select * from $table where $pk='$pk_value'",$debug,$schema);
    $fields = getFieldNamesFromRS($rs);
    $data = array();
    $keys = array_keys($_REQUEST);
    foreach($fields as $field)
    {
        $value = $_REQUEST["$field"];
        $character = left(right($value,5),1);
        if(isItemInArray($keys,$field,false)==true)
        {
            if($character=='/')
            {
                $data["$field"] = USDate2UnixDate($value);
            }
            else
            {
                $data["$field"] = $value;
            }
        }

    }

    $result = getReplaceSQL($table,$data,$pk,$debug,$schema,true);
    //exit();
    return $result;
}
// ------------------------------------------------------------------
function createHTMLFormFromTable($form_name,$schema,$table,$page,$new_page,$pk,$pk_value,$columns=3,$drop_down_array,$wasSubmitted_value='yes')
{
    $sql = "SELECT COALESCE(character_maximum_length,11) as character_maximum_length,data_type,column_name,column_comment FROM columns
    WHERE table_schema='$schema' AND table_name='$table' and column_comment<>''";
    $rs  = dbCall($sql,false,'information_schema');

    $sql = "SELECT * FROM $table where $pk='$pk_value'";
    $rs_values  = dbCall($sql,false,$schema);

    $z = "<form name='$form_name' action='#' method='post'>\n
        <table cellspacing=0 cellpadding=3 valign='top' border=0>\n
        <tr>";
    $i=1;
    $token = getToken();
    while(!$rs->EOF)
    {
        $length     = $rs->fields['character_maximum_length'];
        $data_type  = $rs->fields['data_type'];
        $name       = $rs->fields['column_name'];
        $comment    = $rs->fields['column_comment'];
        $value      = $rs_values->fields["$name"];

        //print "value=$value<br>";

        if($drop_down_array["$name"]=='')
        {
            // place date box, textbox, or textarea
            if($data_type=='date')
            {
                // show a date box
                $z .= "<td valign='top'><b>$comment:</b></td><td valign='top'>".datebox($name,$form_name,UnixDate2USDate($value)) . "</td>";
            }
            else
            {
                // show a textbox or text area
                if($data_type=='text' or $data_type=='longtext' or $data_type=='mediumtext')
                {
                    $z .= "<td valign='top'><b>$comment:</b></td><td valign='top'>".textbox($name,$value,50,5) . "</td>";
                }
                else
                {
                    // show a textbox
                    if($length>75) $length = 75;
                    $z .= "<td valign='top'><b>$comment:</b></td><td valign='top'>".textbox($name,$value,$length) . "</td>";
                }
            }
        }
        else
        {
            // place a drop down box
            // if value of ddarray begins with select, then
            // it is a database query, otherwise, a | separated
            // listing to show in the drop down
            if(left(strtolower($drop_down_array["$name"]),6)=='select')
            {
                // do sql query
                $z .= "<td valign='top'><b>$comment:</b></td><td valign='top'>".arraySelect($name,"size=1",'','',array($value),'','',$drop_down_array["$name"],$schema) . "</td>";
            }
            else
            {
                $ar = explode("|",$drop_down_array["$name"]);
                $z .= "<td valign='top'><b>$comment:</b></td><td valign='top'>".arraySelect($name,"size=1",$ar,$ar,array($value)) . "</td>";
            }
        }

        if($i%$columns==0) $z .= "</tr><tr>\n";

        $i++;
        $rs->MoveNext();
    }

    if($pk_value=='')
    {
        $button = htmlButton($token.'_Submit',$form_name.".submit()","Add");
    }
    else
    {
        $button = htmlButton($token.'_Submit',$form_name.".submit()","Update");
        $del_button = htmlButton($token.'_Delete',"document.location.href='$page?delete_record=yes&pk=$pk&pk_value=$pk_value&schema=$schema&table=$table&new_page=$new_page'","Delete");
    }


    $button_place = $columns *2;
    $z .= "<tr><td colspan='$button_place' align='right'>$button&nbsp;&nbsp;$del_button</td></tr>";

    $z .= "</tr></table>\n<input name='wasSubmitted' value='$wasSubmitted_value' type='hidden'></input>
    <input name='$pk' value='$pk_value' type='hidden'></input></form>\n";
    return $z;
}
// ------------------------------------------------------------------
// ------------------------------------------------------------------
// below are 3 functions for 'early_morning' and 'Load Schedule...'
// ------------------------------------------------------------------
function fd($d)
{
    if($d=='') $d='0000-00-00';
    return $d;
}
// ------------------------------------------------------------------
function loadScheduleDataNew($pmid,$db_server='localhost',$oracle_instance='A019PROD',$debug=false)
{
    updateMRPPNTable($db_server,$debug);

    $ev_method = ".EV Method";
    $ev_method = ".EV Technique";
    if($oracle_instance=='A059PROD')
    {
        //BSM
        $ev_method = "EV Method";
        $ev_method = "EV Technique";
    }

    //get ppm ap id from pmid
    $sql = "select ppm_ap_id from master_project where id=$pmid limit 1";
    $rs = dbCall($sql,$debug,'premier_core',$db_server);
    $cid = $rs->fields['ppm_ap_id'];

    updatePPMAPDataDate($cid,$debug,$db_server);

    $sql  = "delete from data_schedule where pmid=$pmid";
    $junk = dbCall_IB($sql,$debug);

    $sql  = "delete from data_schedule_resources where pmid=$pmid";
    $junk = dbCall_IB($sql,$debug);

    $sql  = "delete from data_schedule_steps where pmid=$pmid";
    $junk = dbCall($sql,$debug);

    $sql = "select program_group,ppm_bl_id from master_project where id=$pmid";
    $rs = dbCall($sql,$debug,'premier_core',$db_server);
    $bid = $rs->fields['ppm_bl_id'];
    $program_group = $rs->fields['program_group'];

    //if($bid=='') die('Baseline Info is missing from the Project Master table.  Please contact Scott Hathaway (x6687).');
    if($bid=='')
    {
        $bid = $cid;
        /*
        //string2file($log_file,"\nBaseline Info is missing from the Project Master table for ppm_ap_id $cid.  Please contact Tim Allums (x6687). - ".date("Y-m-d H:i:s")."\n",'a');
        $to   = "premierteam@bh.com;";
        $sub  = "Baseline Info Missing";
        $msg  = "Baseline Info is missing from the Project Master table for ppm_ap_id $cid
                ";
        myMail($to,$sub,$msg);
        */
    }

    if($total_recs=='')
    {
        $sql = "SELECT count(*) as recs FROM privuser.task where proj_id=$cid";
        $rs = dbCall_Oracle($sql,$debug,$oracle_instance);
        $total_recs = $rs->fields['RECS'];
        //print " count=$total_recs\n";
    }

    //exit();
    $recs_per_page = 4000;
    $total_pages   = $total_recs / $recs_per_page;
    $temp          = explode(".","$total_pages");
    $total_pages   = (int)$temp[0];
    if((int)$temp[1]>0) $total_pages++;

    $cur_page = 1;

    while($cur_page<=$total_pages)
    {
        $l = ($recs_per_page*$cur_page)-$recs_per_page+1;
        $u = $l + $recs_per_page-1;
        if($u>$total_recs) $u=$total_recs;

        if($debug) print "l=$l|u=$u|bid=$bid|cur_page=$cur_page|total_pages=$total_pages\n<br><br>";
        //exit();

        //print "\n      Page $cur_page\n";

        /*
        $sql = "
        SELECT PARTCODE_201, PARTNO_201, MDCNNO_275, RQDATE8_275 FROM
        (SELECT PARTCODE_201, PARTNO_201, MDCNNO_275, RQDATE8_275, ROWNUM r FROM FRH_MRP.PSK02275_OPEN)
        WHERE r BETWEEN $l AND $u
        ";
        //print "<hr>$sql<hr>";
        ///*
        $rs = dbCall_Oracle($sql,true,'DWPROD');
        */

        $sql = "
                    select
                        a2.*
                        ,(
                            SELECT
                                COUNT(p.task_id)
                            FROM
                                privuser.taskproc p
                            WHERE
                                p.proj_id=a2.proj_id
                                AND p.task_id=a2.task_id
                            GROUP BY
                                p.proj_id,p.task_id
                        ) AS num_steps

                        ,(
                            SELECT
                                COUNT(p.task_id)
                            FROM
                                privuser.taskproc p
                            WHERE
                                complete_pct>=100
                                AND p.proj_id=a2.proj_id
                                AND p.task_id=a2.task_id
                            GROUP BY
                                p.proj_id
                                ,p.task_id
                        ) AS num_steps_complete

                        ,(
                            SELECT
                                count(task_code)
                            FROM
                                privuser.TASK
                                , privuser.TASKPRED
                            WHERE
                                pred_task_id=privuser.TASK.task_id
                                AND privuser.TASKPRED.task_id=
                                (
                                    SELECT
                                        task_id
                                    FROM
                                        privuser.TASK
                                    WHERE
                                        task_code=a2.task_code
                                        AND proj_id=a2.proj_id
                                )
                        ) as num_pred

                        ,(
                            select
                                count(task_code)
                            from
                                privuser.TASK
                                ,privuser.TASKPRED
                            where
                                privuser.TASK.task_id=privuser.TASKPRED.task_id
                                AND privuser.TASKPRED.pred_task_id=
                                (
                                    select
                                        task_id
                                    from
                                        privuser.TASK
                                    where
                                        task_code=a2.task_code
                                        and proj_id=a2.proj_id
                                )
                        ) as num_succ
                    from
                    (
                        select
                            a.*
                            , rownum rnum
                        from
                        (
                            with d as
                            (
                                SELECT
                                    t.proj_id
                                    ,p.proj_short_name
                                    ,t.wbs_id
                                    ,t.clndr_id
                                    ,t.rsrc_id
                                    ,t.phys_complete_pct AS pc
                                    ,t.COMPLETE_PCT_TYPE
                                    ,t.task_id
                                    ,t.task_code
                                    ,t.task_name
                                    ,t.task_type
                                    ,t.total_float_hr_cnt
                                    ,t.status_code
                                    ,t.free_float_hr_cnt
                                    ,t.remain_drtn_hr_cnt
                                    ,t.act_work_qty
                                    ,t.remain_work_qty
                                    ,t.target_work_qty
                                    ,t.target_drtn_hr_cnt
                                    ,t.target_equip_qty
                                    ,t.act_equip_qty
                                    ,t.remain_equip_qty
                                    ,t.cstr_date
                                    ,t.act_start_date
                                    ,t.act_end_date
                                    ,t.late_start_date
                                    ,t.late_end_date
                                    ,t.expect_end_date
                                    ,t.early_start_date
                                    ,t.early_end_date
                                    ,t.restart_date
                                    ,t.reend_date
                                    ,t.target_start_date
                                    ,t.target_end_date
                                    ,t.review_end_date
                                    ,t.rem_late_start_date
                                    ,t.rem_late_end_date
                                    ,t.cstr_type
                                    ,t.priority_type
                                    ,t.cstr_date2
                                    ,t.cstr_type2
                                    ,t.act_this_per_work_qty
                                    ,t.act_this_per_equip_qty
                                    ,t.driving_path_flag
                                    ,t.float_path
                                    ,t.float_path_order
                                    ,t.suspend_date
                                    ,t.resume_date
                                    ,t.external_early_start_date
                                    ,t.external_late_end_date
                                    ,t.delete_date
                                    ,nvl(nvl(t.act_start_date,t.restart_date),t.target_start_date) startx  /* AP */
                                    ,nvl(nvl(t.act_end_date,t.reend_date),t.target_end_date) finishx       /* AP */
                                    ,nvl(nvl(bl.act_start_date,bl.restart_date),bl.target_start_date) bl_startx /* BL */
                                    ,nvl(nvl(bl.act_end_date,bl.reend_date),bl.target_end_date) bl_finishx /* BL */
                                FROM
                                    privuser.project p
                                    ,privuser.task t
                                    ,privuser.task bl
                                WHERE
                                    p.proj_id = $cid
                                    AND t.proj_id = p.proj_id
                                    and bl.proj_id (+) = $bid
                                    and bl.task_code (+) = t.task_code
                            )
                            select
                                *
                            FROM
                                (
                                    SELECT
                                        d.proj_id
                                        ,d.proj_short_name
                                        ,d.wbs_id
                                        ,d.clndr_id
                                        ,d.rsrc_id
                                        ,d.pc
                                        ,d.COMPLETE_PCT_TYPE
                                        ,d.task_id x
                                        ,d.task_code
                                        ,d.task_name
                                        ,d.task_type
                                        ,d.total_float_hr_cnt
                                        ,d.status_code
                                        ,d.free_float_hr_cnt
                                        ,d.remain_drtn_hr_cnt
                                        ,d.act_work_qty
                                        ,d.remain_work_qty
                                        ,d.target_work_qty
                                        ,d.target_drtn_hr_cnt
                                        ,d.target_equip_qty
                                        ,d.act_equip_qty
                                        ,d.remain_equip_qty
                                        ,d.cstr_date
                                        ,d.act_start_date
                                        ,d.act_end_date
                                        ,d.late_start_date
                                        ,d.late_end_date
                                        ,d.expect_end_date
                                        ,d.early_start_date
                                        ,d.early_end_date
                                        ,d.restart_date
                                        ,d.reend_date
                                        ,d.target_start_date
                                        ,d.target_end_date
                                        ,d.review_end_date
                                        ,d.rem_late_start_date
                                        ,d.rem_late_end_date
                                        ,d.cstr_type
                                        ,d.priority_type
                                        ,d.cstr_date2
                                        ,d.cstr_type2
                                        ,d.act_this_per_work_qty
                                        ,d.act_this_per_equip_qty
                                        ,d.driving_path_flag
                                        ,d.float_path
                                        ,d.float_path_order
                                        ,d.suspend_date
                                        ,d.resume_date
                                        ,d.external_early_start_date
                                        ,d.external_late_end_date
                                        ,d.delete_date
                                        ,d.startx
                                        ,d.finishx
                                        ,d.bl_startx
                                        ,d.bl_finishx
                                        ,ta.*
                                        ,wpkg
                                        ,ipt_level_3
                                        ,ipt_level_4
                                        ,csa
                                        ,program_code
                                        ,team_code
                                        ,drawing_num
                                    FROM
                                        (
                                            SELECT
                                                d.*
                                            FROM d
                                        ) d
                            ,(
                                SELECT
                                    /*+ ORDERED use_hash(c) */
                                    ta.task_id
                                    ,MAX(CASE WHEN at.actv_code_type = '.CAM' THEN c.short_name ELSE NULL END) AS cam
                                    ,MAX(CASE WHEN at.actv_code_type = '.CA #' THEN c.short_name ELSE NULL END) AS ca
                                    ,MAX(CASE WHEN at.actv_code_type = 'A/C#' THEN c.short_name ELSE NULL END) AS aircraft
                                    ,MAX(CASE WHEN at.actv_code_type = 'Watch Part (APS)' THEN c.short_name ELSE NULL END) AS aps
                                    ,MAX(CASE WHEN at.actv_code_type = '$ev_method' THEN c.short_name ELSE NULL END) AS ev_method
                                    ,MAX(CASE WHEN at.actv_code_type = '$ev_technique' THEN c.short_name ELSE NULL END) AS ev_technique
                                    ,MAX(CASE WHEN at.actv_code_type = 'ICP' THEN c.short_name ELSE NULL END) AS icp
                                    ,MAX(CASE WHEN at.actv_code_type = 'IPS' THEN c.short_name ELSE NULL END) AS ips
                                    ,MAX(CASE WHEN at.actv_code_type = '.IPT' THEN c.short_name ELSE NULL END) AS ipt
                                    ,MAX(CASE WHEN at.actv_code_type = '.Work Package Lead' THEN c.short_name ELSE NULL END) AS work_package_lead
                                    ,MAX(CASE WHEN at.actv_code_type = '.Work Package Status' THEN c.short_name ELSE NULL END) AS work_package_status
                                    ,MAX(CASE WHEN at.actv_code_type = '.Work Package Type' THEN c.short_name ELSE NULL END) AS work_package_type
                                    ,MAX(CASE WHEN at.actv_code_type = 'RIO ID' THEN c.short_name ELSE NULL END) AS rio_id
                                    ,MAX(CASE WHEN at.actv_code_type = 'RIO PHASE' THEN c.short_name ELSE NULL END) AS rio_status
                                    ,MAX(CASE WHEN at.actv_code_type = 'RIO CATEGORY' THEN c.short_name ELSE NULL END) AS rio_type
                                    ,MAX(CASE WHEN at.actv_code_type = 'RIO LEVEL' THEN c.short_name ELSE NULL END) AS rio_visibility_level
                                    ,MAX(CASE WHEN at.actv_code_type = 'RIO ASSESSMENT (AP)' THEN c.short_name ELSE NULL END) AS rio_severity_assessment_ap
                                    ,MAX(CASE WHEN at.actv_code_type = 'RIO ASSESSMENT (BL)' THEN c.short_name ELSE NULL END) AS rio_severity_assessment_bl
                                    ,MAX(CASE WHEN at.actv_code_type = 'RIO OWNER' THEN c.short_name ELSE NULL END) AS rio_owner
                                FROM
                                    privuser.actvtype at
                                    ,privuser.taskactv ta
                                    ,privuser.actvcode c
                                WHERE
                                    at.actv_code_type_scope = 'AS_Global'
                                    AND at.actv_code_type IN (
                                        '.CAM'
                                        , '.CA #'
                                        , 'A/C#'
                                        , 'Watch Part (APS)'
                                        , '.EV Method'
                                        , 'ICP'
                                        , 'IPS'
                                        , '.IPT'
                                        , '.Work Package Lead'
                                        , '.Work Package Status'
                                        , '.Work Package Type'
                                        , 'RIO ID'
                                        , 'RIO PHASE'
                                        , 'RIO STATUS'
                                        , 'RIO LEVEL'
                                        , 'RIO CATEGORY'
                                        , 'RIO ASSESSMENT (AP)'
                                        , 'RIO ASSESSMENT (BL)'
                                        , 'RIO OWNER'
                                    )
                                    AND ta.proj_id = $cid
                                    AND ta.actv_code_id = c.actv_code_id
                                    AND ta.actv_code_type_id = at.actv_code_type_id
                                GROUP BY
                                    ta.task_id) ta
                            ,(
                                SELECT
                                    /*+ USE_HASH (u) */
                                    fk_id wbs_id
                                    ,max(CASE WHEN udf_type_label = '06-Charge #' then udf_text else null end) wpkg
                                    ,max(CASE WHEN udf_type_label = '12-CM Task User 6' then udf_text else null end) ipt_level_3
                                    ,max(CASE WHEN udf_type_label = '13-CM Task User 7' then udf_text else null end) ipt_level_4
                                    ,max(CASE WHEN udf_type_label = '15-CM Task User 9' then udf_text else null end) csa
                                    ,max(CASE WHEN udf_type_label = '10-CM Task User 3' then udf_text else null end) program_code
                                    ,max(CASE WHEN udf_type_label = '16-CM Task User 10' then udf_text else null end) team_code
                                    ,max(CASE WHEN udf_type_label = 'Drawing #' then udf_text else null end) drawing_num
                                FROM
                                    (
                                        SELECT
                                            DISTINCT
                                                wbs_id
                                                , udf_type_id
                                                , udf_type_label
                                        FROM
                                            d
                                            ,privuser.udftype ut
                                        WHERE
                                            udf_type_label IN (
                                                '06-Charge #'
                                                , '12-CM Task User 6'
                                                , '13-CM Task User 7'
                                                , '15-CM Task User 9'
                                                , '10-CM Task User 3'
                                                , '16-CM Task User 10'
                                                , 'Drawing #'
                                            )
                                            AND table_name = 'PROJWBS'
                                    ) x
                                    ,privuser.udfvalue u
                                WHERE
                                    x.wbs_id  = u.fk_id (+)
                                    and x.udf_type_id  = u.udf_type_id (+)
                                    and u.proj_id (+) = $cid
                                group by fk_id
                            ) uv
                        WHERE
                            d.task_id = ta.task_id(+)
                            and uv.wbs_id (+) = d.wbs_id )
                    ) a
                where rownum <= $u
            ) a2
            where rnum >= $l
        ";

        //string2file("e:/sql.txt",$sql,'w');
        //print "done";
        //exit();
        $rs = null;
        $rs = dbCall_Oracle($sql,$debug,$oracle_instance);
        //exit();
        //array_debug($rs);
        //print "rc = " . $rs->RecordCount() . "\n";
        //exit();
        //$rs=false;
        if($rs)
        {
            $i=1;
            while(!$rs->EOF)
            {
                $ca         = $rs->fields['CA'];

                //get cmids
                $cmid_sql   = "select cmid from master_ca where ca='$ca' limit 1 ";
                $cmid_rs    = dbCall($cmid_sql,$debug,'premier_core',$db_server);
                $cmid       = $cmid_rs->fields['cmid'];
                $cmid_rs    = NULL;
/*
                $db = array();

                $db['pmid']                         = $pmid;
                $db['cmid']                         = $cmid;
                $db['wp']                           = addslashes($rs->fields['WPKG']);
                $db['task_id']                      = $rs->fields['TASK_ID'];
                $db['task_code']                    = $rs->fields['TASK_CODE'];
                $db['task_name']                    = addslashes($rs->fields['TASK_NAME']);
                if(strlen($db['task_name'])>149) $db['task_name']=left($db['task_name'],150);
                $db['num_pred']                     = $rs->fields['NUM_PRED'];
                $db['num_succ']                     = $rs->fields['NUM_SUCC'];
                $db['ev_method']                    = $rs->fields['EV_METHOD'];
                $db['phys_complete_pct']            = (float)$rs->fields['PC'];
                $db['remain_drtn_hr_cnt']           = $rs->fields['REMAIN_DRTN_HR_CNT'];
                $db['remain_work_qty']              = $rs->fields['REMAIN_WORK_QTY'];
                $db['total_float_hr_cnt']           = (float)$rs->fields['TOTAL_FLOAT_HR_CNT'];
                $db['start']                        = fd($rs->fields['STARTX']);
                $db['finish']                       = fd($rs->fields['FINISHX']);
                $db['baseline_start']               = fd($rs->fields['BL_STARTX']);
                $db['baseline_finish']              = fd($rs->fields['BL_FINISHX']);
                //$db['program_code']                = $rs->fields['PROGRAM_CODE'];
                //$db['team_code']                    = $rs->fields['TEAM_CODE'];
                $db['wbs_id']                       = $rs->fields['WBS_ID'];
                $db['clndr_id']                     = $rs->fields['CLNDR_ID'];
                $db['complete_pct_type']            = $rs->fields['COMPLETE_PCT_TYPE'];
                $db['act_start_date']               = fd($rs->fields['ACT_START_DATE']);
                $db['act_end_date']                 = fd($rs->fields['ACT_END_DATE']);
                $db['cstr_date']                    = fd($rs->fields['CSTR_DATE']);
                $db['remain_equip_qty']             = $rs->fields['REMAIN_EQUIP_QTY'];
                $db['target_work_qty']              = $rs->fields['TARGET_WORK_QTY'];
                $db['target_equip_qty']             = $rs->fields['TARGET_EQUIP_QTY'];
                $db['active']                       = 1;
                $db['status_code']                  = $rs->fields['STATUS_CODE'];
                $db['driving_path_flag']            = $rs->fields['DRIVING_PATH_FLAG'];
                $db['float_path']                   = $rs->fields['FLOAT_PATH'];
                $db['float_path_order']             = $rs->fields['FLOAT_PATH_ORDER'];
                $db['expect_end_date']              = fd($rs->fields['EXPECT_END_DATE']);
                $db['cstr_type']                    = $rs->fields['CSTR_TYPE'];
                $db['priority_type']                = $rs->fields['PRIORITY_TYPE'];
                $db['aps_code']                     = $rs->fields['APS'];
                $db['aircraft']                     = $rs->fields['AIRCRAFT'];
                $db['icp']                          = $rs->fields['ICP'];
                //$db['ipt']                          = $rs->fields['IPT'];
                $db['work_package_type']            = $rs->fields['WORK_PACKAGE_TYPE'];
                $db['early_start_date']             = fd($rs->fields['EARLY_START_DATE']);
                $db['early_end_date']               = fd($rs->fields['EARLY_END_DATE']);
                $db['work_package_status']          = $rs->fields['WORK_PACKAGE_STATUS'];
                $db['free_float_hr_cnt']            = (float)$rs->fields['FREE_FLOAT_HR_CNT'];
                $db['budgeted_hours']               = 0;
                $db['budgeted_dollars']             = 0;
                $db['work_package_lead']            = addslashes($rs->fields['WORK_PACKAGE_LEAD']);
                $db['rio_id']                       = $rs->fields['RIO_ID'];
                $db['rio_type']                     = $rs->fields['RIO_TYPE'];
                $db['rio_visibility_level']         = $rs->fields['RIO_VISIBILITY_LEVEL'];
                $db['rio_severity_assessment']      = $rs->fields['RIO_SEVERITY_ASSESSMENT_AP'];
                $db['rio_owner']                    = $rs->fields['RIO_OWNER'];
                $db['rio_status']                   = $rs->fields['RIO_STATUS'];
                $db['rio_severity_assessment_bl']   = $rs->fields['RIO_SEVERITY_ASSESSMENT_BL'];
                $db['task_type']                    = $rs->fields['TASK_TYPE'];
                $db['evt']                          = $rs->fields['EV_TECHNIQUE'];
                $db['num_steps']                    = $rs->fields['NUM_STEPS'];
                $db['num_steps_complete']           = $rs->fields['NUM_STEPS_COMPLETE'];
                $db['restart']                 = $rs->fields['RESTART_DATE'];
                $db['reend']                   = $rs->fields['REEND_DATE'];
                $db['drawing_num']                  = $rs->fields['DRAWING_NUM'];
                $db['num_pred']                     = (int)$db['num_pred'];
                $db['num_succ']                     = (int)$db['num_succ'];
                $db['float_path']                   = (int)$db['float_path'];
                $db['float_path_order']             = (int)$db['float_path_order'];

                if(trim($db['wp'])==''or trim($db['wp'])=='na' or trim($db['wp'])=='NA' or trim($db['wp'])=='N/A' or trim($db['wp'])=='n/a') $db['wp']='NOWP';

                $in_mrp = 0;
                if($dn!='') $in_mrp = checkMRP($dn,$aircraft,$debug);

                $db['in_mrp'] = $in_mrp;

*/
                $wp                           = addslashes($rs->fields['WPKG']);
                $task_id                      = $rs->fields['TASK_ID'];
                $task_code                   = $rs->fields['TASK_CODE'];
                $task_name                   = addslashes($rs->fields['TASK_NAME']);
                if(strlen($task_name)>149) $task_name=left($task_name,150);
                $num_pred                    = $rs->fields['NUM_PRED'];
                $num_succ                    = $rs->fields['NUM_SUCC'];
                $ev_method                   = $rs->fields['EV_METHOD'];
                $phys_complete_pct           = (float)$rs->fields['PC'];
                $remain_drtn_hr_cnt          = $rs->fields['REMAIN_DRTN_HR_CNT'];
                $remain_work_qty             = $rs->fields['REMAIN_WORK_QTY'];
                $total_float_hr_cnt          = (float)$rs->fields['TOTAL_FLOAT_HR_CNT'];
                $start                       = fd($rs->fields['STARTX']);
                $finish                      = fd($rs->fields['FINISHX']);
                $baseline_start              = fd($rs->fields['BL_STARTX']);
                $baseline_finish             = fd($rs->fields['BL_FINISHX']);
                //$program_code               = $rs->fields['PROGRAM_CODE'];
                //$team_code                   = $rs->fields['TEAM_CODE'];
                $wbs_id                      = $rs->fields['WBS_ID'];
                $clndr_id                    = $rs->fields['CLNDR_ID'];
                $complete_pct_type           = $rs->fields['COMPLETE_PCT_TYPE'];
                $act_start_date              = fd($rs->fields['ACT_START_DATE']);
                $act_end_date                = fd($rs->fields['ACT_END_DATE']);
                $cstr_date                   = fd($rs->fields['CSTR_DATE']);
                $remain_equip_qty            = $rs->fields['REMAIN_EQUIP_QTY'];
                $target_work_qty             = $rs->fields['TARGET_WORK_QTY'];
                $target_equip_qty            = $rs->fields['TARGET_EQUIP_QTY'];
                $active                      = 1;
                $status_code                 = $rs->fields['STATUS_CODE'];
                $driving_path_flag           = $rs->fields['DRIVING_PATH_FLAG'];
                $float_path                  = $rs->fields['FLOAT_PATH'];
                $float_path_order            = $rs->fields['FLOAT_PATH_ORDER'];
                $expect_end_date             = fd($rs->fields['EXPECT_END_DATE']);
                $cstr_type                   = $rs->fields['CSTR_TYPE'];
                $priority_type               = $rs->fields['PRIORITY_TYPE'];
                $aps_code                    = $rs->fields['APS'];
                $aircraft                    = $rs->fields['AIRCRAFT'];
                $icp                         = $rs->fields['ICP'];
                //$ipt                         = $rs->fields['IPT'];
                $work_package_type           = $rs->fields['WORK_PACKAGE_TYPE'];
                $early_start_date            = fd($rs->fields['EARLY_START_DATE']);
                $early_end_date              = fd($rs->fields['EARLY_END_DATE']);
                $work_package_status         = $rs->fields['WORK_PACKAGE_STATUS'];
                $free_float_hr_cnt           = (float)$rs->fields['FREE_FLOAT_HR_CNT'];
                $budgeted_hours              = 0;
                $budgeted_dollars            = 0;
                $work_package_lead           = addslashes($rs->fields['WORK_PACKAGE_LEAD']);
                $rio_id                      = $rs->fields['RIO_ID'];
                $rio_type                    = $rs->fields['RIO_TYPE'];
                $rio_visibility_level        = $rs->fields['RIO_VISIBILITY_LEVEL'];
                $rio_severity_assessment     = $rs->fields['RIO_SEVERITY_ASSESSMENT_AP'];
                $rio_owner                   = $rs->fields['RIO_OWNER'];
                $rio_status                  = $rs->fields['RIO_STATUS'];
                $rio_severity_assessment_bl  = $rs->fields['RIO_SEVERITY_ASSESSMENT_BL'];
                $task_type                   = $rs->fields['TASK_TYPE'];
                $evt                         = $rs->fields['EV_TECHNIQUE'];
                $num_steps                   = $rs->fields['NUM_STEPS'];
                $num_steps_complete          = $rs->fields['NUM_STEPS_COMPLETE'];
                $restart                = $rs->fields['RESTART_DATE'];
                $reend                  = $rs->fields['REEND_DATE'];
                $drawing_num                 = $rs->fields['DRAWING_NUM'];
                $num_pred                    = (int)$num_pred;
                $num_succ                    = (int)$num_succ;
                $float_path                  = (int)$float_path;
                $float_path_order            = (int)$float_path_order;

                if(trim($wp)==''or trim($wp)=='na' or trim($wp)=='NA' or trim($wp)=='N/A' or trim($wp)=='n/a') $wp='NOWP';

                $in_mrp = 0;
                if($dn!='') $in_mrp = checkMRP($dn,$aircraft,$debug);

                //$result = getReplaceSQL('data_schedule',$db,'id',false,'cache_data',false,'localhost:5029');

                $insert_sql = "insert into data_schedule (pmid
, cmid
, `work_package_lead`
, wp
, task_id
, task_code
, task_name
, num_pred
, num_succ
, ev_method
, phys_complete_pct
, remain_drtn_hr_cnt
, remain_work_qty
, total_float_hr_cnt
, START
, finish
, baseline_start
, baseline_finish
, wbs_id
, clndr_id
, complete_pct_type
, act_start_date
, act_end_date
, cstr_date
, remain_equip_qty
, target_work_qty
, target_equip_qty
, active
, status_code
, driving_path_flag
, float_path
, float_path_order
, expect_end_date
, cstr_type
, priority_type
, aps_code
, aircraft
, icp
, work_package_type
, early_start_date
, early_end_date
, work_package_status
, free_float_hr_cnt
, budgeted_hours
, budgeted_dollars
, work_package_lead
, rio_id
, rio_type
, rio_visibility_level
, rio_severity_assessment
, rio_owner
, rio_status
, rio_severity_assessment_bl
, task_type
, lag
, lead
, link_type
, status_date
, duration
, evt
, num_steps
, num_steps_complete
, drawing_num
, in_mrp
, restart
, reend) values ();";
                $junk = dbCall_IB($insert_sql,$debug);

                $sql = "
                        select
                            partno
                        from
                            mrp_partno
                        where
                            partno='$drawing_num'
                            and fromeff >= concat('0','$aircraft')
                            and thrueff <= concat('0','$aircraft')
                            and substr(fromeff,0,2) = concat('0',substr('$aircraft',0,1))
                            and substr(thrueff,0,2) = concat('0',substr('$aircraft',0,1))
                ";
                //$rs2 = dbCall($sql,$debug,'premier_core_mtl',$db_server);
                $rs2 = dbCall($sql,$debug,'mtl_data2',$db_server);

                if($debug and ($i%1000)==0) print ".";

                $i++;
                $rs->MoveNext();
            }
        }
        $cur_page++;
    }

    // RESOURCES
    $sql = "
        select
            tr.*,
            (select rsrc_short_name from privuser.rsrc where rsrc_id=tr.rsrc_id) as rsrc_short_name,
            (select rsrc_name from privuser.rsrc where rsrc_id=tr.rsrc_id) as rsrc_name
        from
            privuser.taskrsrc tr
        where
            tr.proj_id=$cid
    ";

    $rs = dbCall_Oracle($sql,false,$oracle_instance);

    if($rs)
    {
        while(!$rs->EOF)
        {

            $db                                = array();
            $db['taskrsrc_id']            = $rs->fields['TASKRSRC_ID'];
            $db['task_id']               = $rs->fields['TASK_ID'];
            $db['pmid']                  = $pmid;
            $db['cost_qty_link_flag']    = $rs->fields['COST_QTY_LINK_FLAG'];
            $db['role_id']                   = $rs->fields['ROLE_ID'];
            $db['acct_id']                    = $rs->fields['ACCT_ID'];
            $db['rsrc_id']                  = $rs->fields['RSRC_ID'];
            $db['skill_level']                = $rs->fields['SKILL_LEVEL'];
            $db['pend_complete_pct']     = $rs->fields['PEND_COMPLETE_PCT'];
            $db['remain_qty']               = $rs->fields['REMAIN_QTY'];
            $db['pend_remain_qty']         = $rs->fields['PEND_REMAIN_QTY'];
            $db['target_qty']             = $rs->fields['TARGET_QTY'];
            $db['remain_qty_per_hr']        = $rs->fields['REMAIN_QTY_PER_HR'];
            $db['pend_act_reg_qty']        = $rs->fields['PEND_ACT_REG_QTY'];
            $db['target_lag_drtn_hr_cnt']= $rs->fields['TARGET_LAG_DRTN_HR_CNT'];
            $db['target_qty_per_hr']       = $rs->fields['TARGET_QTY_PER_HR'];
            $db['act_ot_qty']              = $rs->fields['ACT_OT_QTY'];
            $db['pend_act_ot_qty']          = $rs->fields['PEND_ACT_OT_QTY'];
            $db['act_reg_qty']           = $rs->fields['ACT_REG_QTY'];
            $db['relag_drtn_hr_cnt']     = $rs->fields['RELAG_DRTN_HR_CNT'];
            $db['ot_factor']                   = $rs->fields['OT_FACTOR'];
            $db['cost_per_qty']             = $rs->fields['COST_PER_QTY'];
            $db['target_cost']           = $rs->fields['TARGET_COST'];
            $db['act_reg_cost']             = $rs->fields['ACT_REG_COST'];
            $db['act_ot_cost']             = $rs->fields['ACT_OT_COST'];
            $db['remain_cost']               = $rs->fields['REMAIN_COST'];
            $db['act_start_date']         = $rs->fields['ACT_START_DATE'];
            $db['act_end_date']            = $rs->fields['ACT_END_DATE'];
            $db['restart_date']           = $rs->fields['RESTART_DATE'];
            $db['reend_date']              = $rs->fields['REEND_DATE'];
            $db['target_start_date']           = $rs->fields['TARGET_START_DATE'];
            $db['target_end_date']        = $rs->fields['TARGET_END_DATE'];
            $db['rem_late_start_date']     = $rs->fields['REM_LATE_START_DATE'];
            $db['rem_late_end_date']        = $rs->fields['REM_LATE_END_DATE'];
            $db['guid']                       = $rs->fields['GUID'];
            $db['rate_type']                 = $rs->fields['RATE_TYPE'];
            $db['act_this_per_cost']        = $rs->fields['ACT_THIS_PER_COST'];
            $db['act_this_per_qty']        = $rs->fields['ACT_THIS_PER_QTY'];
            $db['curv_id']                  = $rs->fields['CURV_ID'];
            $db['rsrc_request_data']     = $rs->fields['RSRC_REQUEST_DATA'];
            $db['rsrc_type']                  = $rs->fields['RSRC_TYPE'];
            $db['rollup_dates_flag']        = $rs->fields['ROLLUP_DATES_FLAG'];
            $db['cost_per_qty_source_type'] = $rs->fields['COST_PER_QTY_SOURCE_TYPE'];
            $db['update_date']               = $rs->fields['UPDATE_DATE'];
            $db['update_user']              = $rs->fields['UPDATE_USER'];
            $db['create_date']              = $rs->fields['CREATE_DATE'];
            $db['create_user']              = $rs->fields['CREATE_USER'];
            $db['delete_session_id']        = $rs->fields['DELETE_SESSION_ID'];
            $db['delete_date']             = $rs->fields['DELETE_DATE'];
            $db['rsrc_short_name']          = $rs->fields['RSRC_SHORT_NAME'];
            $db['rsrc_name']              = $rs->fields['RSRC_NAME'];

            $result = getReplaceSQL('data_schedule_resources',$db,'id',true,'cache_data',false,'localhost:5029');
die();
            $rs->MoveNext();
        }
    }

    // STEPS
    $sql = "
        select
            PROC_ID,
            TASK_ID,
            SEQ_NUM,
            COMPLETE_FLAG,
            PROC_NAME,
            PROC_WT,
            COMPLETE_PCT,
            PROC_DESCR,
            UPDATE_DATE,
            UPDATE_USER,
            CREATE_DATE,
            CREATE_USER,
            DELETE_SESSION_ID,
            DELETE_DATE
        from
            privuser.taskproc
        where
            proj_id=$cid
    ";

    $rs = dbCall_Oracle($sql,$debug,$oracle_instance);

    if($rs)
    {
        while(!$rs->EOF)
        {
           $db                                = array();
           $db['proc_id']                = $rs->fields['PROC_ID'];
           $db['task_id']               = $rs->fields['TASK_ID'];
           $db['seq_num']                  = $rs->fields['SEQ_NUM'];
           $db['pmid']                  = $pmid;
           $db['complete_flag']            = $rs->fields['COMPLETE_FLAG'];
           $db['proc_name']                = $rs->fields['PROC_NAME'];
           $db['proc_wt']                  = $rs->fields['PROC_WT'];
           $db['complete_pct']                = $rs->fields['COMPLETE_PCT'];
           $db['proc_descr']             = $rs->fields['PROC_DESCR'];
           $db['update_date']               = $rs->fields['UPDATE_DATE'];
           $db['update_user']             = $rs->fields['UPDATE_USER'];
           $db['create_date']             = $rs->fields['CREATE_DATE'];
           $db['create_user']              = $rs->fields['CREATE_USER'];
           $db['delete_session_id']        = $rs->fields['DELETE_SESSION_ID'];
           $db['delete_date']            = $rs->fields['DELETE_DATE'];

           $result = getReplaceSQL('data_schedule_steps',$db,'id',false,'cache_data',false,'localhost:5029');

           $rs->MoveNext();
        }

    }

    // TASKPRED
    $sql = "
        SELECT
            TASK_PRED_ID,
            TASK_ID,
            PRED_TASK_ID,
            PRED_PROJ_ID,
            PRED_TYPE,
            LAG_HR_CNT,
            UPDATE_DATE,
            UPDATE_USER,
            CREATE_DATE,
            CREATE_USER,
            DELETE_SESSION_ID,
            DELETE_DATE
        FROM
            PRIVUSER.TASKPRED
        where
            proj_id=$cid";
    $rs = dbCall_Oracle($sql,$debug,$oracle_instance);
    if($rs)
    {
        while(!$rs->EOF)
        {
            $pred_proj_id = $rs->fields['PRED_PROJ_ID'];
            //get ppm ap id from pmid
            $sql = "select id from master_project where ppm_ap_id=$pred_proj_id limit 1";
            $rs = dbCall($sql,$debug,'premier_core',$db_server);
            $pred_pmid = $rs->fields['id'];

            $db                             = array();
            $db['TASK_PRED_ID']             = $rs->fields['TASK_PRED_ID'];
            $db['TASK_ID']                  = $rs->fields['TASK_ID'];
            $db['PRED_TASK_ID']             = $rs->fields['PRED_TASK_ID'];
            $db['pmid']                  = $pmid;
            $db['pred_pmid']             = $pred_pmid;
            $db['PRED_TYPE']                = $rs->fields['PRED_TYPE'];
            $db['LAG_HR_CNT']               = $rs->fields['LAG_HR_CNT'];
            $db['UPDATE_DATE']              = $rs->fields['UPDATE_DATE'];
            $db['UPDATE_USER']              = $rs->fields['UPDATE_USER'];
            $db['CREATE_DATE']              = $rs->fields['CREATE_DATE'];
            $db['CREATE_USER']              = $rs->fields['CREATE_USER'];
            $db['DELETE_SESSION_ID']        = $rs->fields['DELETE_SESSION_ID'];
            $db['DELETE_DATE']              = $rs->fields['DELETE_DATE'];

            $result = getReplaceSQL('data_schedule_taskpred',$db,'id',false,'cache_data',true,'localhost:5029');

            $rs->MoveNext();
        }
    }


    return true;
}

// ------------------------------------------------------------------
function loadScheduleData($cid,$db_server='localhost',$debug=false)
{
    //global $log_file;

    updatePPMAPDataDate($cid,$debug,$db_server);

    $sql  = "delete from schedule where proj_id=$cid";
    $junk = dbCall($sql,$debug,'schedule_data',$db_server);

    //$sql  = "delete from schedule_zrio where proj_id=$cid";
    //$junk = dbCall($sql,$debug,'schedule_data',$db_server);

    $sql  = "delete from schedule_resources where proj_id=$cid";
    $junk = dbCall($sql,$debug,'schedule_data',$db_server);

    $sql  = "delete from schedule_steps where proj_id=$cid";
    $junk = dbCall($sql,$debug,'schedule_data',$db_server);

    $sql  = "delete from schedule_taskpred where proj_id=$cid";
    $junk = dbCall($sql,$debug,'schedule_data',$db_server);

    $sql = "select program_group,ppm_bl_id from project_master where ppm_ap_id=$cid";
    $rs = dbCall($sql,$debug,'tools_data',$db_server);
    $bid = $rs->fields['ppm_bl_id'];
    $program_group = $rs->fields['program_group'];

    //if($bid=='') die('Baseline Info is missing from the Project Master table.  Please contact Scott Hathaway (x6687).');
    if($bid=='')
    {
        $bid = $cid;
        /*
        //string2file($log_file,"\nBaseline Info is missing from the Project Master table for ppm_ap_id $cid.  Please contact Tim Allums (x6687). - ".date("Y-m-d H:i:s")."\n",'a');
        $to   = "SHathaway@bh.com;PBell@bh.com;tallums@bh.com;";
        $sub  = "Baseline Info Missing";
        $msg  = "Baseline Info is missing from the Project Master table for ppm_ap_id $cid
                ";
        myMail($to,$sub,$msg);
        */
    }

    if($total_recs=='')
    {
        $sql = "SELECT count(*) as recs FROM admuser.task where proj_id=$cid";
        $rs = dbCall_Oracle($sql,$debug,'A019PROD');
        $total_recs = $rs->fields['RECS'];
        //print " count=$total_recs\n";
    }
    //exit();
    $recs_per_page = 4000;
    $total_pages   = $total_recs / $recs_per_page;
    $temp          = explode(".","$total_pages");
    $total_pages   = (int)$temp[0];
    if((int)$temp[1]>0) $total_pages++;

    $cur_page = 1;

    while($cur_page<=$total_pages)
    {
        $l = ($recs_per_page*$cur_page)-$recs_per_page+1;
        $u = $l + $recs_per_page-1;
        if($u>$total_recs) $u=$total_recs;

        if($debug) print "l=$l|u=$u|bid=$bid|cur_page=$cur_page|total_pages=$total_pages\n";
        //exit();

        //print "\n      Page $cur_page\n";

        /*
        $sql = "
        SELECT PARTCODE_201, PARTNO_201, MDCNNO_275, RQDATE8_275 FROM
        (SELECT PARTCODE_201, PARTNO_201, MDCNNO_275, RQDATE8_275, ROWNUM r FROM FRH_MRP.PSK02275_OPEN)
        WHERE r BETWEEN $l AND $u
        ";
        //print "<hr>$sql<hr>";
        ///*
        $rs = dbCall_Oracle($sql,true,'DWPROD');
        */


$sql = "
select a2.*


                      ,(SELECT count(task_code) FROM privuser.TASK, privuser.TASKPRED
                WHERE pred_task_id=privuser.TASK.task_id AND privuser.TASKPRED.task_id=
                  (SELECT task_id FROM privuser.TASK WHERE task_code=a2.task_code AND proj_id=a2.proj_id)) as num_pred

                        ,(select count(task_code) from privuser.TASK,privuser.TASKPRED where
                privuser.TASK.task_id=privuser.TASKPRED.task_id AND privuser.TASKPRED.pred_task_id=
                 (select task_id from privuser.TASK where task_code=a2.task_code and proj_id=a2.proj_id)) as num_succ

  from ( select a.*, rownum rnum
           from (
with d as (
SELECT t.proj_id     ,p.proj_short_name
                      ,t.wbs_id
                      ,t.clndr_id
                      ,t.rsrc_id
                      ,t.phys_complete_pct AS pc
                      ,t.COMPLETE_PCT_TYPE
                      ,t.task_id
                      ,t.task_code
                      ,t.task_name
                      ,t.task_type
                      ,t.total_float_hr_cnt
                      ,t.status_code
                      ,t.free_float_hr_cnt
                      ,t.remain_drtn_hr_cnt
                      ,t.act_work_qty
                      ,t.remain_work_qty
                      ,t.target_work_qty
                      ,t.target_drtn_hr_cnt
                      ,t.target_equip_qty
                      ,t.act_equip_qty
                      ,t.remain_equip_qty
                      ,t.cstr_date
                      ,t.act_start_date
                      ,t.act_end_date
                      ,t.late_start_date
                      ,t.late_end_date
                      ,t.expect_end_date
                      ,t.early_start_date
                      ,t.early_end_date
                      ,t.restart_date
                      ,t.reend_date
                      ,t.target_start_date
                      ,t.target_end_date
                      ,t.review_end_date
                      ,t.rem_late_start_date
                      ,t.rem_late_end_date
                      ,t.cstr_type
                      ,t.priority_type
                      ,t.cstr_date2
                      ,t.cstr_type2
                      ,t.act_this_per_work_qty
                      ,t.act_this_per_equip_qty
                      ,t.driving_path_flag
                      ,t.float_path
                      ,t.float_path_order
                      ,t.suspend_date
                      ,t.resume_date
                      ,t.external_early_start_date
                      ,t.external_late_end_date
                      ,t.delete_date
                      ,nvl(nvl(t.act_start_date,t.restart_date),t.target_start_date) startx  -- AP
                      ,nvl(nvl(t.act_end_date,t.reend_date),t.target_end_date) finishx       -- AP
                      ,nvl(nvl(bl.act_start_date,bl.restart_date),bl.target_start_date) bl_startx -- BL
                      ,nvl(nvl(bl.act_end_date,bl.reend_date),bl.target_end_date) bl_finishx      -- BL
                  FROM privuser.project p
                      ,privuser.task    t
                      ,privuser.task bl
                 WHERE p.proj_id = $cid
                   AND t.proj_id = p.proj_id
                   and bl.proj_id (+) = $bid
                   and bl.task_code (+) = t.task_code)
  select * FROM (SELECT d.proj_id
              ,d.proj_short_name
              ,d.wbs_id
              ,d.clndr_id
              ,d.rsrc_id
              ,d.pc
              ,d.COMPLETE_PCT_TYPE
              ,d.task_id x
              ,d.task_code
              ,d.task_name
              ,d.task_type
              ,d.total_float_hr_cnt
              ,d.status_code
              ,d.free_float_hr_cnt
              ,d.remain_drtn_hr_cnt
              ,d.act_work_qty
              ,d.remain_work_qty
              ,d.target_work_qty
              ,d.target_drtn_hr_cnt
              ,d.target_equip_qty
              ,d.act_equip_qty
              ,d.remain_equip_qty
              ,d.cstr_date
              ,d.act_start_date
              ,d.act_end_date
              ,d.late_start_date
              ,d.late_end_date
              ,d.expect_end_date
              ,d.early_start_date
              ,d.early_end_date
              ,d.restart_date
              ,d.reend_date
              ,d.target_start_date
              ,d.target_end_date
              ,d.review_end_date
              ,d.rem_late_start_date
              ,d.rem_late_end_date
              ,d.cstr_type
              ,d.priority_type
              ,d.cstr_date2
              ,d.cstr_type2
              ,d.act_this_per_work_qty
              ,d.act_this_per_equip_qty
              ,d.driving_path_flag
              ,d.float_path
              ,d.float_path_order
              ,d.suspend_date
              ,d.resume_date
              ,d.external_early_start_date
              ,d.external_late_end_date
              ,d.delete_date
              ,d.startx
              ,d.finishx
              ,d.bl_startx
              ,d.bl_finishx
              ,ta.*
              ,wpkg
              ,ipt_level_3
              ,ipt_level_4
              ,csa
              ,program_code
              ,team_code
          FROM (SELECT d.*
                  FROM d) d
              ,(SELECT /*+ ORDERED use_hash(c) */ ta.task_id
                      ,MAX(CASE
                             WHEN at.actv_code_type = '.CAM' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS cam
                      ,MAX(CASE
                             WHEN at.actv_code_type = '.CA #' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS ca
                      ,MAX(CASE
                             WHEN at.actv_code_type = 'A/C#' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS aircraft
                      ,MAX(CASE
                             WHEN at.actv_code_type = 'Watch Part (APS)' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS aps
                      ,MAX(CASE
                             WHEN at.actv_code_type = '.EV Method' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS ev_method
                      ,MAX(CASE
                             WHEN at.actv_code_type = 'ICP' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS icp
                      ,MAX(CASE
                             WHEN at.actv_code_type = 'IPS' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS ips
                      ,MAX(CASE
                             WHEN at.actv_code_type = '.IPT' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS ipt
                      ,MAX(CASE
                             WHEN at.actv_code_type = '.Work Package Lead' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS work_package_lead
                      ,MAX(CASE
                             WHEN at.actv_code_type = '.Work Package Status' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS work_package_status
                      ,MAX(CASE
                             WHEN at.actv_code_type = '.Work Package Type' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS work_package_type
                      ,MAX(CASE
                             WHEN at.actv_code_type = 'RIO ID' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS rio_id
                       ,MAX(CASE
                             WHEN at.actv_code_type = 'RIO PHASE' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS rio_status
                      ,MAX(CASE
                             WHEN at.actv_code_type = 'RIO CATEGORY' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS rio_type
                      ,MAX(CASE
                             WHEN at.actv_code_type = 'RIO LEVEL' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS rio_visibility_level
                      ,MAX(CASE
                             WHEN at.actv_code_type =
                                  'RIO ASSESSMENT (AP)' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS rio_severity_assessment_ap
                      ,MAX(CASE
                             WHEN at.actv_code_type =
                                  'RIO ASSESSMENT (BL)' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS rio_severity_assessment_bl
                      ,MAX(CASE
                             WHEN at.actv_code_type = 'RIO OWNER' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS rio_owner
                  FROM privuser.actvtype at
                      ,privuser.taskactv ta
                      ,privuser.actvcode c
                 WHERE at.actv_code_type_scope = 'AS_Global'
                   AND at.actv_code_type IN
                       ('.CAM', '.CA #', 'A/C#', 'Watch Part (APS)',
                        '.EV Method', 'ICP', 'IPS', '.IPT',
                        '.Work Package Lead', '.Work Package Status',
                        '.Work Package Type', 'RIO ID', 'RIO PHASE','RIO STATUS',
                        'RIO LEVEL', 'RIO CATEGORY',
                        'RIO ASSESSMENT (AP)',
                        'RIO ASSESSMENT (BL)', 'RIO OWNER')
                   AND ta.proj_id = $cid
                   AND ta.actv_code_id = c.actv_code_id
                   AND ta.actv_code_type_id = at.actv_code_type_id
                 GROUP BY ta.task_id) ta ,(SELECT /*+ USE_HASH (u) */ fk_id wbs_id
      ,max(CASE WHEN udf_type_label = '06-Charge #' then udf_text else null end) wpkg
      ,max(CASE WHEN udf_type_label = '12-CM Task User 6' then udf_text else null end) ipt_level_3
      ,max(CASE WHEN udf_type_label = '13-CM Task User 7' then udf_text else null end) ipt_level_4
      ,max(CASE WHEN udf_type_label = '15-CM Task User 9' then udf_text else null end) csa
      ,max(CASE WHEN udf_type_label = '10-CM Task User 3' then udf_text else null end) program_code
      ,max(CASE WHEN udf_type_label = '16-CM Task User 10' then udf_text else null end) team_code
  FROM (SELECT DISTINCT wbs_id
                       ,udf_type_id, udf_type_label
          FROM d
              ,privuser.udftype ut
         WHERE udf_type_label IN ('06-Charge #', '12-CM Task User 6',
                '13-CM Task User 7', '15-CM Task User 9',
                '10-CM Task User 3', '16-CM Task User 10')
           AND table_name = 'PROJWBS') x
      ,privuser.udfvalue u
 WHERE x.wbs_id  = u.fk_id (+)
   and x.udf_type_id  = u.udf_type_id (+)
   and u.proj_id (+) = $cid
   group by fk_id) uv
       WHERE d.task_id = ta.task_id(+)
         and uv.wbs_id (+) = d.wbs_id )
) a
          where rownum <= $u ) a2
 where rnum >= $l

";

    //string2file("e:/sql.txt",$sql,'w');
    //print "done";
    //exit();
        $rs = null;
        $rs = dbCall_Oracle($sql,false,'A019PROD');
        //exit();
        //array_debug($rs);
        //print "rc = " . $rs->RecordCount() . "\n";
        //exit();
        //$rs=false;
        if($rs)
        {
            $i=1;
            while(!$rs->EOF)
            {
               $db                            = array();
               $db['program_group']     = $program_group;
               $db['proj_id']            = $rs->fields['PROJ_ID'];
               $db['proj_short_name']   = $rs->fields['PROJ_SHORT_NAME'];
               $db['controlaccount']      = $rs->fields['CA'];
               $db['cam']                   = addslashes($rs->fields['CAM']);
               $db['csa']                 = addslashes($rs->fields['CSA']);
               $db['ips']                 = addslashes($rs->fields['IPS']);
               $db['workpackage']         = addslashes($rs->fields['WPKG']);
               $db['task_id']               = $rs->fields['TASK_ID'];
               $db['task_code']               = $rs->fields['TASK_CODE'];
               $db['task_name']             = addslashes($rs->fields['TASK_NAME']);
               if(strlen($db['task_name'])>119) $db['task_name']=left($db['task_name'],120);
               $db['num_pred']              = $rs->fields['NUM_PRED'];
               $db['num_succ']            = $rs->fields['NUM_SUCC'];
               $db['ev_method']          = $rs->fields['EV_METHOD'];
               $db['phys_complete_pct']    = (float)$rs->fields['PC'];
               $db['remain_drtn_hr_cnt'] = $rs->fields['REMAIN_DRTN_HR_CNT'];
               $db['remain_work_qty']   = $rs->fields['REMAIN_WORK_QTY'];
               $db['total_float_hr_cnt']    = (float)$rs->fields['TOTAL_FLOAT_HR_CNT'];
               $db['start']                = fd($rs->fields['STARTX']);
               $db['finish']            = fd($rs->fields['FINISHX']);
               $db['baseline_start']       = fd($rs->fields['BL_STARTX']);
               $db['baseline_finish']      = fd($rs->fields['BL_FINISHX']);
               $db['ipt_level_3']          = addslashes($rs->fields['IPT_LEVEL_3']);
               $db['ipt_level_4']         = addslashes($rs->fields['IPT_LEVEL_4']);
               $db['program_code']         = $rs->fields['PROGRAM_CODE'];
               $db['team_code']            = $rs->fields['TEAM_CODE'];
               $db['wbs_id']            = $rs->fields['WBS_ID'];
               $db['clndr_id']            = $rs->fields['CLNDR_ID'];
               $db['complete_pct_type']    = $rs->fields['COMPLETE_PCT_TYPE'];
               $db['act_start_date']       = fd($rs->fields['ACT_START_DATE']);
               $db['act_end_date']      = fd($rs->fields['ACT_END_DATE']);
               $db['cstr_date']            = fd($rs->fields['CSTR_DATE']);
               $db['remain_equip_qty']    = $rs->fields['REMAIN_EQUIP_QTY'];
               $db['target_work_qty']    = $rs->fields['TARGET_WORK_QTY'];
               $db['target_equip_qty']    = $rs->fields['TARGET_EQUIP_QTY'];
               $db['active']               = 1;
               $db['status_code']         = $rs->fields['STATUS_CODE'];
               $db['driving_path_flag']    = $rs->fields['DRIVING_PATH_FLAG'];
               $db['float_path']        = $rs->fields['FLOAT_PATH'];
               $db['float_path_order']    = $rs->fields['FLOAT_PATH_ORDER'];
               $db['expect_end_date']    = fd($rs->fields['EXPECT_END_DATE']);
               $db['cstr_type']         = $rs->fields['CSTR_TYPE'];
               $db['priority_type']        = $rs->fields['PRIORITY_TYPE'];
               $db['aps_code']             = $rs->fields['APS'];
               $db['aircraft']           = $rs->fields['AIRCRAFT'];
               $db['icp']                 = $rs->fields['ICP'];
               $db['ipt']                 = $rs->fields['IPT'];
               $db['work_package_type']    = $rs->fields['WORK_PACKAGE_TYPE'];
               $db['early_start_date']    = fd($rs->fields['EARLY_START_DATE']);
               $db['early_end_date']    = fd($rs->fields['EARLY_END_DATE']);
               $db['work_package_status'] = $rs->fields['WORK_PACKAGE_STATUS'];
               $db['free_float_hr_cnt']    = (float)$rs->fields['FREE_FLOAT_HR_CNT'];
               $db['budgeted_hours']    = 0;
               $db['budgeted_dollars']    = 0;
               $db['work_package_lead']    = addslashes($rs->fields['WORK_PACKAGE_LEAD']);
               $db['rio_id']               = $rs->fields['RIO_ID'];
               $db['rio_type']             = $rs->fields['RIO_TYPE'];
               $db['rio_visibility_level']    = $rs->fields['RIO_VISIBILITY_LEVEL'];
               $db['rio_severity_assessment']    = $rs->fields['RIO_SEVERITY_ASSESSMENT_AP'];
               $db['rio_owner']                    = $rs->fields['RIO_OWNER'];
               $db['rio_status']                  = $rs->fields['RIO_STATUS'];
               $db['rio_severity_assessment_bl']    = $rs->fields['RIO_SEVERITY_ASSESSMENT_BL'];
               $db['task_type']                        = $rs->fields['TASK_TYPE'];

                if(trim($db['controlaccount'])==''or trim($db['controlaccount'])=='na' or trim($db['controlaccount'])=='NA' or trim($db['controlaccount'])=='N/A' or trim($db['controlaccount'])=='n/a') $db['controlaccount']='NOCA';
                if(trim($db['workpackage'])==''or trim($db['workpackage'])=='na' or trim($db['workpackage'])=='NA' or trim($db['workpackage'])=='N/A' or trim($db['workpackage'])=='n/a') $db['workpackage']='NOWP';

                $db['num_pred'] = (int)$db['num_pred'];
                $db['num_succ'] = (int)$db['num_succ'];
                $db['float_path'] = (int)$db['float_path'];
                $db['float_path_order'] = (int)$db['float_path_order'];


               $debug=false;
               //if($db['task_code']=='KT5LR14369' or $db['task_code']=='L510910' or $db['task_code']=='L510510' or $db['task_code']=='KT5LH0689' or $db['task_code']=='KT5LD0212') $debug=true;
               $result = getReplaceSQL('schedule',$db,'id',$debug,'schedule_data',false,$db_server);
               //print "result=".(int)$result."\n";
               //if((int)$result<>1 and (int)$result<>2) print $db['task_code'] . "\n";
               //if($debug) exit();
               $debug=false;
               //if($rs->fields['RIO_ID']!='') $result = getReplaceSQL('schedule_zrio',$db,'id',false,'schedule_data',true);
               //if($rs->fields['RIO_ID']!='') $result = getReplaceSQL('schedule_zrio',$db,'id',$debug,'schedule_data',false,$db_server);

               //exit();
               //print "l=$l|u=$u<br>";
               if($debug and ($i%1000)==0) print ".";

               $i++;
               $rs->MoveNext();
            }
        }
        $cur_page++;
    }
    //exit();


    // RESOURCES
    $sql = "
        select
            tr.*,
            (select rsrc_short_name from privuser.rsrc where rsrc_id=tr.rsrc_id) as rsrc_short_name,
            (select rsrc_name from privuser.rsrc where rsrc_id=tr.rsrc_id) as rsrc_name
        from
            privuser.taskrsrc tr
        where
            tr.proj_id=$cid
    ";

    $rs = dbCall_Oracle($sql,false,'A019PROD');

    if($rs)
    {
        while(!$rs->EOF)
        {
           $db                                = array();
           $db['taskrsrc_id']            = $rs->fields['TASKRSRC_ID'];
           $db['task_id']               = $rs->fields['TASK_ID'];
           $db['proj_id']                  = $rs->fields['PROJ_ID'];
           $db['cost_qty_link_flag']    = $rs->fields['COST_QTY_LINK_FLAG'];
           $db['role_id']                   = $rs->fields['ROLE_ID'];
           $db['acct_id']                    = $rs->fields['ACCT_ID'];
           $db['rsrc_id']                  = $rs->fields['RSRC_ID'];
           $db['skill_level']                = $rs->fields['SKILL_LEVEL'];
           $db['pend_complete_pct']     = $rs->fields['PEND_COMPLETE_PCT'];
           $db['remain_qty']               = $rs->fields['REMAIN_QTY'];
           $db['pend_remain_qty']         = $rs->fields['PEND_REMAIN_QTY'];
           $db['target_qty']             = $rs->fields['TARGET_QTY'];
           $db['remain_qty_per_hr']        = $rs->fields['REMAIN_QTY_PER_HR'];
           $db['pend_act_reg_qty']        = $rs->fields['PEND_ACT_REG_QTY'];
           $db['target_lag_drtn_hr_cnt']= $rs->fields['TARGET_LAG_DRTN_HR_CNT'];
           $db['target_qty_per_hr']       = $rs->fields['TARGET_QTY_PER_HR'];
           $db['act_ot_qty']              = $rs->fields['ACT_OT_QTY'];
           $db['pend_act_ot_qty']          = $rs->fields['PEND_ACT_OT_QTY'];
           $db['act_reg_qty']           = $rs->fields['ACT_REG_QTY'];
           $db['relag_drtn_hr_cnt']     = $rs->fields['RELAG_DRTN_HR_CNT'];
           $db['ot_factor']                   = $rs->fields['OT_FACTOR'];
           $db['cost_per_qty']             = $rs->fields['COST_PER_QTY'];
           $db['target_cost']           = $rs->fields['TARGET_COST'];
           $db['act_reg_cost']             = $rs->fields['ACT_REG_COST'];
           $db['act_ot_cost']             = $rs->fields['ACT_OT_COST'];
           $db['remain_cost']               = $rs->fields['REMAIN_COST'];
           $db['act_start_date']         = $rs->fields['ACT_START_DATE'];
           $db['act_end_date']            = $rs->fields['ACT_END_DATE'];
           $db['restart_date']           = $rs->fields['RESTART_DATE'];
           $db['reend_date']              = $rs->fields['REEND_DATE'];
           $db['target_start_date']           = $rs->fields['TARGET_START_DATE'];
           $db['target_end_date']        = $rs->fields['TARGET_END_DATE'];
           $db['rem_late_start_date']     = $rs->fields['REM_LATE_START_DATE'];
           $db['rem_late_end_date']        = $rs->fields['REM_LATE_END_DATE'];
           $db['guid']                       = $rs->fields['GUID'];
           $db['rate_type']                 = $rs->fields['RATE_TYPE'];
           $db['act_this_per_cost']        = $rs->fields['ACT_THIS_PER_COST'];
           $db['act_this_per_qty']        = $rs->fields['ACT_THIS_PER_QTY'];
           $db['curv_id']                  = $rs->fields['CURV_ID'];
           $db['rsrc_request_data']     = $rs->fields['RSRC_REQUEST_DATA'];
           $db['rsrc_type']                  = $rs->fields['RSRC_TYPE'];
           $db['rollup_dates_flag']        = $rs->fields['ROLLUP_DATES_FLAG'];
           $db['cost_per_qty_source_type'] = $rs->fields['COST_PER_QTY_SOURCE_TYPE'];
           $db['update_date']               = $rs->fields['UPDATE_DATE'];
           $db['update_user']              = $rs->fields['UPDATE_USER'];
           $db['create_date']              = $rs->fields['CREATE_DATE'];
           $db['create_user']              = $rs->fields['CREATE_USER'];
           $db['delete_session_id']        = $rs->fields['DELETE_SESSION_ID'];
           $db['delete_date']             = $rs->fields['DELETE_DATE'];
           $db['rsrc_short_name']          = $rs->fields['RSRC_SHORT_NAME'];
           $db['rsrc_name']              = $rs->fields['RSRC_NAME'];

           $result = getReplaceSQL('schedule_resources',$db,'id',$debug,'schedule_data',true,$db_server);

           $rs->MoveNext();
        }
    }

    // STEPS
    $sql = "
        select
            *
        from
            admuser.taskproc
        where
            proj_id=$cid
    ";

    $rs = dbCall_Oracle($sql,$debug,'A019PROD');

    if($rs)
    {
        while(!$rs->EOF)
        {
           $db                                = array();
           $db['proc_id']                = $rs->fields['PROC_ID'];
           $db['task_id']               = $rs->fields['TASK_ID'];
           $db['seq_num']                  = $rs->fields['SEQ_NUM'];
           $db['proj_id']                  = $rs->fields['PROJ_ID'];
           $db['complete_flag']            = $rs->fields['COMPLETE_FLAG'];
           $db['proc_name']                = $rs->fields['PROC_NAME'];
           $db['proc_wt']                  = $rs->fields['PROC_WT'];
           $db['complete_pct']                = $rs->fields['COMPLETE_PCT'];
           $db['proc_descr']             = $rs->fields['PROC_DESCR'];
           $db['update_date']               = $rs->fields['UPDATE_DATE'];
           $db['update_user']             = $rs->fields['UPDATE_USER'];
           $db['create_date']             = $rs->fields['CREATE_DATE'];
           $db['create_user']              = $rs->fields['CREATE_USER'];
           $db['delete_session_id']        = $rs->fields['DELETE_SESSION_ID'];
           $db['delete_date']            = $rs->fields['DELETE_DATE'];

           $result = getReplaceSQL('schedule_steps',$db,'id',$debug,'schedule_data',true,$db_server);

           $rs->MoveNext();
        }

    }



    // TASKPRED
    $sql = "SELECT * FROM ADMUSER.TASKPRED where proj_id=$cid";
    $rs = dbCall_Oracle($sql,$debug,'A019PROD');
    if($rs)
    {
        while(!$rs->EOF)
        {
            $db                             = array();
            $db['TASK_PRED_ID']             = $rs->fields['TASK_PRED_ID'];
            $db['TASK_ID']                  = $rs->fields['TASK_ID'];
            $db['PRED_TASK_ID']             = $rs->fields['PRED_TASK_ID'];
            $db['PROJ_ID']                  = $rs->fields['PROJ_ID'];
            $db['PRED_PROJ_ID']             = $rs->fields['PRED_PROJ_ID'];
            $db['PRED_TYPE']                = $rs->fields['PRED_TYPE'];
            $db['LAG_HR_CNT']               = $rs->fields['LAG_HR_CNT'];
            $db['UPDATE_DATE']              = $rs->fields['UPDATE_DATE'];
            $db['UPDATE_USER']              = $rs->fields['UPDATE_USER'];
            $db['CREATE_DATE']              = $rs->fields['CREATE_DATE'];
            $db['CREATE_USER']              = $rs->fields['CREATE_USER'];
            $db['DELETE_SESSION_ID']        = $rs->fields['DELETE_SESSION_ID'];
            $db['DELETE_DATE']              = $rs->fields['DELETE_DATE'];


           $result = getReplaceSQL('schedule_taskpred',$db,'id',$debug,'schedule_data',true,$db_server);

           $rs->MoveNext();
        }
    }


    return true;
}

// ------------------------------------------------------------------
function loadRIOData($cid,$db_server='localhost',$debug=false,$log_file='')
{
    if(trim($log_file)!='') string2file($log_file,"\nStep 2.2.1 - Delete from schedule_zrio for $cid - ".date("Y-m-d H:i:s")."\n",'a');
    $sql  = "delete from schedule_zrio where proj_id=$cid";
    $junk = dbCall($sql,$debug,'schedule_data',$db_server);
    if(trim($log_file)!='') string2file($log_file,"\nStep 2.2.1 - Delete from schedule_zrio for $cid Completed - ".date("Y-m-d H:i:s")."\n",'a');

    if(trim($log_file)!='') string2file($log_file,"\nStep 2.2.2 - select program_group,bid for $cid - ".date("Y-m-d H:i:s")."\n",'a');
    $sql            = "select program_group,ppm_bl_id from rio_project_master where ppm_ap_id=$cid";
    $rs             = dbCall($sql,$debug,'rio',$db_server);
    $bid            = $rs->fields['ppm_bl_id'];
    $program_group  = $rs->fields['program_group'];
    if(trim($log_file)!='') string2file($log_file,"\nStep 2.2.2 - select program_group($program_group),bid($bid) for $cid Complete - ".date("Y-m-d H:i:s")."\n",'a');

    if($bid=='')
    {
        $bid = $cid;
    }

    if(trim($log_file)!='') string2file($log_file,"\nStep 2.2.3 - get total rec_count for $cid - ".date("Y-m-d H:i:s")."\n",'a');
    if($total_recs=='')
    {
        $sql = "SELECT count(*) as recs FROM admuser.task where proj_id=$cid";
        $rs = dbCall_Oracle($sql,$debug,'A019PROD');
        $total_recs = $rs->fields['RECS'];
    }
    if(trim($log_file)!='') string2file($log_file,"\nStep 2.2.3 - get total rec_count($total_recs) for $cid Complete - ".date("Y-m-d H:i:s")."\n",'a');

    $recs_per_page = 4000;
    $total_pages   = $total_recs / $recs_per_page;
    $temp          = explode(".","$total_pages");
    $total_pages   = (int)$temp[0];

    if((int)$temp[1]>0) $total_pages++;

    $cur_page = 1;

    while($cur_page<=$total_pages)
    {
        if(trim($log_file)!='') string2file($log_file,"\nStep 2.2.4 - looping through $cur_page/$total_pages pages for $cid - ".date("Y-m-d H:i:s")."\n",'a');
        $l = ($recs_per_page*$cur_page)-$recs_per_page+1;
        $u = $l + $recs_per_page-1;
        if($u>$total_recs) $u=$total_recs;

        if($debug) print "l=$l|u=$u|bid=$bid|cur_page=$cur_page|total_pages=$total_pages\n";

        if(trim($log_file)!='') string2file($log_file,"\nStep 2.2.5 - getting rio schedule data for $cid - ".date("Y-m-d H:i:s")."\n",'a');
        $sql = "
            select
                a2.*
                ,(SELECT count(task_code) FROM privuser.TASK, privuser.TASKPRED
                WHERE pred_task_id=privuser.TASK.task_id AND privuser.TASKPRED.task_id=
                  (SELECT task_id FROM privuser.TASK WHERE task_code=a2.task_code AND proj_id=a2.proj_id)) as num_pred

                ,(select count(task_code) from privuser.TASK,privuser.TASKPRED where
                privuser.TASK.task_id=privuser.TASKPRED.task_id AND privuser.TASKPRED.pred_task_id=
                 (select task_id from privuser.TASK where task_code=a2.task_code and proj_id=a2.proj_id)) as num_succ
            from
            (
                select
                    a.*,
                    rownum rnum
               from
               (
                    with d as
                    (
                        SELECT
                            t.proj_id
                            ,p.proj_short_name
                            ,t.wbs_id
                            ,t.clndr_id
                            ,t.rsrc_id
                            ,t.phys_complete_pct AS pc
                            ,t.COMPLETE_PCT_TYPE
                            ,t.task_id
                            ,t.task_code
                            ,t.task_name
                            ,t.task_type
                            ,t.total_float_hr_cnt
                            ,t.status_code
                            ,t.free_float_hr_cnt
                            ,t.remain_drtn_hr_cnt
                            ,t.act_work_qty
                            ,t.remain_work_qty
                            ,t.target_work_qty
                            ,t.target_drtn_hr_cnt
                            ,t.target_equip_qty
                            ,t.act_equip_qty
                            ,t.remain_equip_qty
                            ,t.cstr_date
                            ,t.act_start_date
                            ,t.act_end_date
                            ,t.late_start_date
                            ,t.late_end_date
                            ,t.expect_end_date
                            ,t.early_start_date
                            ,t.early_end_date
                            ,t.restart_date
                            ,t.reend_date
                            ,t.target_start_date
                            ,t.target_end_date
                            ,t.review_end_date
                            ,t.rem_late_start_date
                            ,t.rem_late_end_date
                            ,t.cstr_type
                            ,t.priority_type
                            ,t.cstr_date2
                            ,t.cstr_type2
                            ,t.act_this_per_work_qty
                            ,t.act_this_per_equip_qty
                            ,t.driving_path_flag
                            ,t.float_path
                            ,t.float_path_order
                            ,t.suspend_date
                            ,t.resume_date
                            ,t.external_early_start_date
                            ,t.external_late_end_date
                            ,t.delete_date
                            ,nvl(nvl(t.act_start_date,t.restart_date),t.target_start_date) startx  -- AP
                            ,nvl(nvl(t.act_end_date,t.reend_date),t.target_end_date) finishx       -- AP
                            ,nvl(nvl(bl.act_start_date,bl.restart_date),bl.target_start_date) bl_startx -- BL
                            ,nvl(nvl(bl.act_end_date,bl.reend_date),bl.target_end_date) bl_finishx      -- BL
                        FROM privuser.project p
                            ,privuser.task    t
                            ,privuser.task bl
                        WHERE
                            p.proj_id = $cid
                            AND t.proj_id = p.proj_id
                            and bl.proj_id (+) = $bid
                            and bl.task_code (+) = t.task_code
                    )
                    select
                        *
                    FROM
                    (
                        SELECT
                            d.proj_id
                            ,d.proj_short_name
                            ,d.wbs_id
                            ,d.clndr_id
                            ,d.rsrc_id
                            ,d.pc
                            ,d.COMPLETE_PCT_TYPE
                            ,d.task_id x
                            ,d.task_code
                            ,d.task_name
                            ,d.task_type
                            ,d.total_float_hr_cnt
                            ,d.status_code
                            ,d.free_float_hr_cnt
                            ,d.remain_drtn_hr_cnt
                            ,d.act_work_qty
                            ,d.remain_work_qty
                            ,d.target_work_qty
                            ,d.target_drtn_hr_cnt
                            ,d.target_equip_qty
                            ,d.act_equip_qty
                            ,d.remain_equip_qty
                            ,d.cstr_date
                            ,d.act_start_date
                            ,d.act_end_date
                            ,d.late_start_date
                            ,d.late_end_date
                            ,d.expect_end_date
                            ,d.early_start_date
                            ,d.early_end_date
                            ,d.restart_date
                            ,d.reend_date
                            ,d.target_start_date
                            ,d.target_end_date
                            ,d.review_end_date
                            ,d.rem_late_start_date
                            ,d.rem_late_end_date
                            ,d.cstr_type
                            ,d.priority_type
                            ,d.cstr_date2
                            ,d.cstr_type2
                            ,d.act_this_per_work_qty
                            ,d.act_this_per_equip_qty
                            ,d.driving_path_flag
                            ,d.float_path
                            ,d.float_path_order
                            ,d.suspend_date
                            ,d.resume_date
                            ,d.external_early_start_date
                            ,d.external_late_end_date
                            ,d.delete_date
                            ,d.startx
                            ,d.finishx
                            ,d.bl_startx
                            ,d.bl_finishx
                            ,ta.*
                            ,wpkg
                            ,ipt_level_3
                            ,ipt_level_4
                            ,csa
                            ,program_code
                            ,team_code
                        FROM
                        (
                            SELECT
                                d.*
                            FROM d
                        ) d
                      ,(
                        SELECT
                            /*+ ORDERED use_hash(c) */ ta.task_id
                              ,MAX(CASE
                                     WHEN at.actv_code_type = '.CAM' THEN
                                      c.short_name
                                     ELSE
                                      NULL
                                   END) AS cam
                              ,MAX(CASE
                                     WHEN at.actv_code_type = '.CA #' THEN
                                      c.short_name
                                     ELSE
                                      NULL
                                   END) AS ca
                              ,MAX(CASE
                                     WHEN at.actv_code_type = 'A/C#' THEN
                                      c.short_name
                                     ELSE
                                      NULL
                                   END) AS aircraft
                              ,MAX(CASE
                                     WHEN at.actv_code_type = 'Watch Part (APS)' THEN
                                      c.short_name
                                     ELSE
                                      NULL
                                   END) AS aps
                              ,MAX(CASE
                                     WHEN at.actv_code_type = '.EV Method' THEN
                                      c.short_name
                                     ELSE
                                      NULL
                                   END) AS ev_method
                              ,MAX(CASE
                                     WHEN at.actv_code_type = 'ICP' THEN
                                      c.short_name
                                     ELSE
                                      NULL
                                   END) AS icp
                              ,MAX(CASE
                                     WHEN at.actv_code_type = 'IPS' THEN
                                      c.short_name
                                     ELSE
                                      NULL
                                   END) AS ips
                              ,MAX(CASE
                                     WHEN at.actv_code_type = '.IPT' THEN
                                      c.short_name
                                     ELSE
                                      NULL
                                   END) AS ipt
                              ,MAX(CASE
                                     WHEN at.actv_code_type = '.Work Package Lead' THEN
                                      c.short_name
                                     ELSE
                                      NULL
                                   END) AS work_package_lead
                              ,MAX(CASE
                                     WHEN at.actv_code_type = '.Work Package Status' THEN
                                      c.short_name
                                     ELSE
                                      NULL
                                   END) AS work_package_status
                              ,MAX(CASE
                                     WHEN at.actv_code_type = '.Work Package Type' THEN
                                      c.short_name
                                     ELSE
                                      NULL
                                   END) AS work_package_type
                              ,MAX(CASE
                                     WHEN at.actv_code_type = 'RIO ID' THEN
                                      c.short_name
                                     ELSE
                                      NULL
                                   END) AS rio_id
                               ,MAX(CASE
                                     WHEN at.actv_code_type = 'RIO PHASE' THEN
                                      c.short_name
                                     ELSE
                                      NULL
                                   END) AS rio_status
                              ,MAX(CASE
                                     WHEN at.actv_code_type = 'RIO CATEGORY' THEN
                                      c.short_name
                                     ELSE
                                      NULL
                                   END) AS rio_type
                              ,MAX(CASE
                                     WHEN at.actv_code_type = 'RIO LEVEL' THEN
                                      c.short_name
                                     ELSE
                                      NULL
                                   END) AS rio_visibility_level
                              ,MAX(CASE
                                     WHEN at.actv_code_type =
                                          'RIO ASSESSMENT (AP)' THEN
                                      c.short_name
                                     ELSE
                                      NULL
                                   END) AS rio_severity_assessment_ap
                              ,MAX(CASE
                                     WHEN at.actv_code_type =
                                          'RIO ASSESSMENT (BL)' THEN
                                      c.short_name
                                     ELSE
                                      NULL
                                   END) AS rio_severity_assessment_bl
                              ,MAX(CASE
                                     WHEN at.actv_code_type = 'RIO OWNER' THEN
                                      c.short_name
                                     ELSE
                                      NULL
                                   END) AS rio_owner
                              ,MAX(CASE
                                     WHEN at.actv_code_type = 'RIO Sequence' THEN
                                      c.short_name
                                     ELSE
                                      NULL
                                   END) AS rio_sequence
                          FROM privuser.actvtype at
                              ,privuser.taskactv ta
                              ,privuser.actvcode c
                         WHERE at.actv_code_type_scope = 'AS_Global'
                           AND at.actv_code_type IN
                               ('.CAM', '.CA #', 'A/C#', 'Watch Part (APS)',
                                '.EV Method', 'ICP', 'IPS', '.IPT',
                                '.Work Package Lead', '.Work Package Status',
                                '.Work Package Type', 'RIO ID', 'RIO PHASE','RIO STATUS',
                                'RIO LEVEL', 'RIO CATEGORY',
                                'RIO ASSESSMENT (AP)',
                                'RIO ASSESSMENT (BL)', 'RIO OWNER', 'RIO Sequence')
                           AND ta.proj_id = $cid
                           AND ta.actv_code_id = c.actv_code_id
                           AND ta.actv_code_type_id = at.actv_code_type_id
                         GROUP BY ta.task_id) ta ,(SELECT /*+ USE_HASH (u) */ fk_id wbs_id
              ,max(CASE WHEN udf_type_label = '06-Charge #' then udf_text else null end) wpkg
              ,max(CASE WHEN udf_type_label = '12-CM Task User 6' then udf_text else null end) ipt_level_3
              ,max(CASE WHEN udf_type_label = '13-CM Task User 7' then udf_text else null end) ipt_level_4
              ,max(CASE WHEN udf_type_label = '15-CM Task User 9' then udf_text else null end) csa
              ,max(CASE WHEN udf_type_label = '10-CM Task User 3' then udf_text else null end) program_code
              ,max(CASE WHEN udf_type_label = '16-CM Task User 10' then udf_text else null end) team_code
          FROM (SELECT DISTINCT wbs_id
                               ,udf_type_id, udf_type_label
                  FROM d
                      ,privuser.udftype ut
                 WHERE udf_type_label IN ('06-Charge #', '12-CM Task User 6',
                        '13-CM Task User 7', '15-CM Task User 9',
                        '10-CM Task User 3', '16-CM Task User 10')
                   AND table_name = 'PROJWBS') x
              ,privuser.udfvalue u
         WHERE x.wbs_id  = u.fk_id (+)
           and x.udf_type_id  = u.udf_type_id (+)
           and u.proj_id (+) = $cid
           group by fk_id) uv
               WHERE d.task_id = ta.task_id(+)
                 and uv.wbs_id (+) = d.wbs_id )
        ) a
                  where rownum <= $u ) a2
         where rnum >= $l

        ";

        $rs = null;
        $rs = dbCall_Oracle($sql,$debug,'A019PROD');
        if(trim($log_file)!='') string2file($log_file,"\nStep 2.2.5 - getting rio schedule data for $cid Completed - ".date("Y-m-d H:i:s")."\n",'a');

        if($rs)
        {
            $i=1;
            while(!$rs->EOF)
            {
                $db = array();

                $db['program_group']                 = $program_group;
                $db['proj_id']                       = $rs->fields['PROJ_ID'];
                $db['proj_short_name']               = $rs->fields['PROJ_SHORT_NAME'];
                $db['controlaccount']                = $rs->fields['CA'];
                $db['cam']                           = addslashes($rs->fields['CAM']);
                $db['csa']                           = addslashes($rs->fields['CSA']);
                $db['ips']                           = addslashes($rs->fields['IPS']);
                $db['workpackage']                   = addslashes($rs->fields['WPKG']);
                $db['task_id']                       = $rs->fields['TASK_ID'];
                $db['task_code']                     = $rs->fields['TASK_CODE'];
                $db['num_pred']                      = (int)$rs->fields['NUM_PRED'];
                $db['num_succ']                      = (int)$rs->fields['NUM_SUCC'];
                $db['ev_method']                     = $rs->fields['EV_METHOD'];
                $db['phys_complete_pct']             = (float)$rs->fields['PC'];
                $db['remain_drtn_hr_cnt']            = $rs->fields['REMAIN_DRTN_HR_CNT'];
                $db['remain_work_qty']               = $rs->fields['REMAIN_WORK_QTY'];
                $db['total_float_hr_cnt']            = (float)$rs->fields['TOTAL_FLOAT_HR_CNT'];
                $db['start']                         = fd($rs->fields['STARTX']);
                $db['finish']                        = fd($rs->fields['FINISHX']);
                $db['baseline_start']                = fd($rs->fields['BL_STARTX']);
                $db['baseline_finish']               = fd($rs->fields['BL_FINISHX']);
                $db['ipt_level_3']                   = addslashes($rs->fields['IPT_LEVEL_3']);
                $db['ipt_level_4']                   = addslashes($rs->fields['IPT_LEVEL_4']);
                $db['program_code']                  = $rs->fields['PROGRAM_CODE'];
                $db['team_code']                     = $rs->fields['TEAM_CODE'];
                $db['wbs_id']                        = $rs->fields['WBS_ID'];
                $db['clndr_id']                      = $rs->fields['CLNDR_ID'];
                $db['complete_pct_type']             = $rs->fields['COMPLETE_PCT_TYPE'];
                $db['act_start_date']                = fd($rs->fields['ACT_START_DATE']);
                $db['act_end_date']                  = fd($rs->fields['ACT_END_DATE']);
                $db['cstr_date']                     = fd($rs->fields['CSTR_DATE']);
                $db['remain_equip_qty']              = $rs->fields['REMAIN_EQUIP_QTY'];
                $db['target_work_qty']               = $rs->fields['TARGET_WORK_QTY'];
                $db['target_equip_qty']              = $rs->fields['TARGET_EQUIP_QTY'];
                $db['active']                        = 1;
                $db['status_code']                   = $rs->fields['STATUS_CODE'];
                $db['driving_path_flag']             = $rs->fields['DRIVING_PATH_FLAG'];
                $db['float_path']                    = (int)$rs->fields['FLOAT_PATH'];
                $db['float_path_order']              = (int)$rs->fields['FLOAT_PATH_ORDER'];
                $db['expect_end_date']               = fd($rs->fields['EXPECT_END_DATE']);
                $db['cstr_type']                     = $rs->fields['CSTR_TYPE'];
                $db['priority_type']                 = $rs->fields['PRIORITY_TYPE'];
                $db['aps_code']                      = $rs->fields['APS'];
                $db['aircraft']                      = $rs->fields['AIRCRAFT'];
                $db['icp']                           = $rs->fields['ICP'];
                $db['ipt']                           = $rs->fields['IPT'];
                $db['work_package_type']             = $rs->fields['WORK_PACKAGE_TYPE'];
                $db['early_start_date']              = fd($rs->fields['EARLY_START_DATE']);
                $db['early_end_date']                = fd($rs->fields['EARLY_END_DATE']);
                $db['work_package_status']           = $rs->fields['WORK_PACKAGE_STATUS'];
                $db['free_float_hr_cnt']             = (float)$rs->fields['FREE_FLOAT_HR_CNT'];
                $db['budgeted_hours']                = 0;
                $db['budgeted_dollars']              = 0;
                $db['work_package_lead']             = addslashes($rs->fields['WORK_PACKAGE_LEAD']);
                $db['rio_id']                        = $rs->fields['RIO_ID'];
                $db['rio_type']                      = $rs->fields['RIO_TYPE'];
                $db['rio_visibility_level']          = $rs->fields['RIO_VISIBILITY_LEVEL'];
                $db['rio_severity_assessment']       = $rs->fields['RIO_SEVERITY_ASSESSMENT_AP'];
                $db['rio_owner']                     = $rs->fields['RIO_OWNER'];
                $db['rio_sequence']                  = (int)$rs->fields['RIO_SEQUENCE'];
                $db['rio_status']                    = $rs->fields['RIO_STATUS'];
                $db['rio_severity_assessment_bl']    = $rs->fields['RIO_SEVERITY_ASSESSMENT_BL'];
                $db['task_type']                     = $rs->fields['TASK_TYPE'];

                $db['task_name'] = addslashes($rs->fields['TASK_NAME']);
                if(strlen($db['task_name'])>119) $db['task_name']=left($db['task_name'],120);

                if(trim($db['controlaccount'])==''or trim($db['controlaccount'])=='na' or trim($db['controlaccount'])=='NA' or trim($db['controlaccount'])=='N/A' or trim($db['controlaccount'])=='n/a') $db['controlaccount']='NOCA';
                if(trim($db['workpackage'])==''or trim($db['workpackage'])=='na' or trim($db['workpackage'])=='NA' or trim($db['workpackage'])=='N/A' or trim($db['workpackage'])=='n/a') $db['workpackage']='NOWP';

                $rio_id_for_string2file = $rs->fields['RIO_ID'];
                if(trim($log_file)!='') string2file($log_file,"\nStep 2.2.6 - looping through rio schedule data and replacing data if rio_id ($rio_id_for_string2file)<>'' for $cid - ".date("Y-m-d H:i:s")."\n",'a');
                if($rs->fields['RIO_ID']!='') $result = getReplaceSQL('schedule_zrio',$db,'id',$debug,'schedule_data',false,$db_server);
                if(trim($log_file)!='') string2file($log_file,"\nStep 2.2.6 - looping through rio schedule data and replacing data if rio_id ($rio_id_for_string2file)<>'' for $cid Completed - ".date("Y-m-d H:i:s")."\n",'a');

                if($debug and ($i%1000)==0) print ".";

                $i++;
                $rs->MoveNext();
            }
        }
        $cur_page++;
    }
    return true;
}

// ------------------------------------------------------------------
function loadBSMScheduleData($cid,$db_server='localhost',$debug=false)
{
    //global $log_file;

    $oracle_database = 'A057PROD';

    updatePPMAPDataDate($cid,$debug,$db_server,$oracle_database);

    $sql  = "delete from schedule where proj_id=$cid";
    $junk = dbCall($sql,$debug,'schedule_data',$db_server);

    $sql  = "delete from schedule_resources where proj_id=$cid";
    $junk = dbCall($sql,$debug,'schedule_data',$db_server);

    $sql  = "delete from schedule_steps where proj_id=$cid";
    $junk = dbCall($sql,$debug,'schedule_data',$db_server);

    $sql = "select program_group,ppm_bl_id from project_master where ppm_ap_id=$cid";
    $rs = dbCall($sql,$debug,'tools_data',$db_server);
    $bid = $rs->fields['ppm_bl_id'];
    $program_group = $rs->fields['program_group'];

    //if($bid=='') die('Baseline Info is missing from the Project Master table.  Please contact Scott Hathaway (x6687).');
    if($bid=='')
    {
        $bid = $cid;
        /*
        //string2file($log_file,"\nBaseline Info is missing from the Project Master table for ppm_ap_id $cid.  Please contact Tim Allums (x6687). - ".date("Y-m-d H:i:s")."\n",'a');
        $to   = "premierteam@bh.com;";
        $sub  = "Baseline Info Missing";
        $msg  = "Baseline Info is missing from the Project Master table for ppm_ap_id $cid
                ";
        myMail($to,$sub,$msg);
        */
    }

    if($total_recs=='')
    {
        $sql = "SELECT count(*) as recs FROM admuser.task where proj_id=$cid";
        $rs = dbCall_Oracle($sql,$debug,$oracle_database);
        $total_recs = $rs->fields['RECS'];
        //print " count=$total_recs\n";
    }
    //exit();
    $recs_per_page = 4000;
    $total_pages   = $total_recs / $recs_per_page;
    $temp          = explode(".","$total_pages");
    $total_pages   = (int)$temp[0];
    if((int)$temp[1]>0) $total_pages++;

    $cur_page = 1;

    while($cur_page<=$total_pages)
    {
        $l = ($recs_per_page*$cur_page)-$recs_per_page+1;
        $u = $l + $recs_per_page-1;
        if($u>$total_recs) $u=$total_recs;

        if($debug) print "l=$l|u=$u|bid=$bid|cur_page=$cur_page|total_pages=$total_pages\n";
        //exit();

        //print "\n      Page $cur_page\n";

        /*
        $sql = "
        SELECT PARTCODE_201, PARTNO_201, MDCNNO_275, RQDATE8_275 FROM
        (SELECT PARTCODE_201, PARTNO_201, MDCNNO_275, RQDATE8_275, ROWNUM r FROM FRH_MRP.PSK02275_OPEN)
        WHERE r BETWEEN $l AND $u
        ";
        //print "<hr>$sql<hr>";
        ///*
        $rs = dbCall_Oracle($sql,true,'DWPROD');
        */


$sql = "
select a2.*


                      ,(SELECT count(task_code) FROM admuser.TASK, admuser.TASKPRED
                WHERE pred_task_id=admuser.TASK.task_id AND admuser.TASKPRED.task_id=
                  (SELECT task_id FROM admuser.TASK WHERE task_code=a2.task_code AND proj_id=a2.proj_id)) as num_pred

                        ,(select count(task_code) from admuser.TASK,admuser.TASKPRED where
                admuser.TASK.task_id=admuser.TASKPRED.task_id AND admuser.TASKPRED.pred_task_id=
                 (select task_id from admuser.TASK where task_code=a2.task_code and proj_id=a2.proj_id)) as num_succ

  from ( select a.*, rownum rnum
           from (
with d as (
SELECT t.proj_id     ,p.proj_short_name
                      ,t.wbs_id
                      ,t.clndr_id
                      ,t.rsrc_id
                      ,t.phys_complete_pct AS pc
                      ,t.COMPLETE_PCT_TYPE
                      ,t.task_id
                      ,t.task_code
                      ,t.task_name
                      ,t.task_type
                      ,t.total_float_hr_cnt
                      ,t.status_code
                      ,t.free_float_hr_cnt
                      ,t.remain_drtn_hr_cnt
                      ,t.act_work_qty
                      ,t.remain_work_qty
                      ,t.target_work_qty
                      ,t.target_drtn_hr_cnt
                      ,t.target_equip_qty
                      ,t.act_equip_qty
                      ,t.remain_equip_qty
                      ,t.cstr_date
                      ,t.act_start_date
                      ,t.act_end_date
                      ,t.late_start_date
                      ,t.late_end_date
                      ,t.expect_end_date
                      ,t.early_start_date
                      ,t.early_end_date
                      ,t.restart_date
                      ,t.reend_date
                      ,t.target_start_date
                      ,t.target_end_date
                      ,t.review_end_date
                      ,t.rem_late_start_date
                      ,t.rem_late_end_date
                      ,t.cstr_type
                      ,t.priority_type
                      ,t.cstr_date2
                      ,t.cstr_type2
                      ,t.act_this_per_work_qty
                      ,t.act_this_per_equip_qty
                      ,t.driving_path_flag
                      ,t.float_path
                      ,t.float_path_order
                      ,t.suspend_date
                      ,t.resume_date
                      ,t.external_early_start_date
                      ,t.external_late_end_date
                      ,t.delete_date
                      ,nvl(nvl(t.act_start_date,t.restart_date),t.target_start_date) startx  -- AP
                      ,nvl(nvl(t.act_end_date,t.reend_date),t.target_end_date) finishx       -- AP
                      ,nvl(nvl(bl.act_start_date,bl.restart_date),bl.target_start_date) bl_startx -- BL
                      ,nvl(nvl(bl.act_end_date,bl.reend_date),bl.target_end_date) bl_finishx      -- BL
                  FROM admuser.project p
                      ,admuser.task    t
                      ,admuser.task bl
                 WHERE p.proj_id = $cid
                   AND t.proj_id = p.proj_id
                   and bl.proj_id (+) = $bid
                   and bl.task_code (+) = t.task_code)
  select * FROM (SELECT d.proj_id
              ,d.proj_short_name
              ,d.wbs_id
              ,d.clndr_id
              ,d.rsrc_id
              ,d.pc
              ,d.COMPLETE_PCT_TYPE
              ,d.task_id x
              ,d.task_code
              ,d.task_name
              ,d.task_type
              ,d.total_float_hr_cnt
              ,d.status_code
              ,d.free_float_hr_cnt
              ,d.remain_drtn_hr_cnt
              ,d.act_work_qty
              ,d.remain_work_qty
              ,d.target_work_qty
              ,d.target_drtn_hr_cnt
              ,d.target_equip_qty
              ,d.act_equip_qty
              ,d.remain_equip_qty
              ,d.cstr_date
              ,d.act_start_date
              ,d.act_end_date
              ,d.late_start_date
              ,d.late_end_date
              ,d.expect_end_date
              ,d.early_start_date
              ,d.early_end_date
              ,d.restart_date
              ,d.reend_date
              ,d.target_start_date
              ,d.target_end_date
              ,d.review_end_date
              ,d.rem_late_start_date
              ,d.rem_late_end_date
              ,d.cstr_type
              ,d.priority_type
              ,d.cstr_date2
              ,d.cstr_type2
              ,d.act_this_per_work_qty
              ,d.act_this_per_equip_qty
              ,d.driving_path_flag
              ,d.float_path
              ,d.float_path_order
              ,d.suspend_date
              ,d.resume_date
              ,d.external_early_start_date
              ,d.external_late_end_date
              ,d.delete_date
              ,d.startx
              ,d.finishx
              ,d.bl_startx
              ,d.bl_finishx
              ,ta.*
              ,wpkg
              ,ipt_level_3
              ,ipt_level_4
              ,csa
              ,program_code
              ,team_code
          FROM (SELECT d.*
                  FROM d) d
              ,(SELECT /*+ ORDERED use_hash(c) */ ta.task_id
                      ,MAX(CASE
                             WHEN at.actv_code_type = 'CAM' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS cam
                      ,MAX(CASE
                             WHEN at.actv_code_type = 'CA #' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS ca
                      ,MAX(CASE
                             WHEN at.actv_code_type = 'A/C#' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS aircraft
                      ,MAX(CASE
                             WHEN at.actv_code_type = 'Watch Part (APS)' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS aps
                      ,MAX(CASE
                             WHEN at.actv_code_type = 'EV Method' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS ev_method
                      ,MAX(CASE
                             WHEN at.actv_code_type = 'ICP' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS icp
                      ,MAX(CASE
                             WHEN at.actv_code_type = 'IPS' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS ips
                      ,MAX(CASE
                             WHEN at.actv_code_type = 'IPT' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS ipt
                      ,MAX(CASE
                             WHEN at.actv_code_type = 'Work Package Lead' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS work_package_lead
                      ,MAX(CASE
                             WHEN at.actv_code_type = 'Work Package Status' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS work_package_status
                      ,MAX(CASE
                             WHEN at.actv_code_type = 'Work Package Type' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS work_package_type
                      ,MAX(CASE
                             WHEN at.actv_code_type = 'RIO ID' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS rio_id
                       ,MAX(CASE
                             WHEN at.actv_code_type = 'RIO PHASE' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS rio_status
                      ,MAX(CASE
                             WHEN at.actv_code_type = 'RIO CATEGORY' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS rio_type
                      ,MAX(CASE
                             WHEN at.actv_code_type = 'RIO LEVEL' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS rio_visibility_level
                      ,MAX(CASE
                             WHEN at.actv_code_type =
                                  'RIO ASSESSMENT (AP)' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS rio_severity_assessment_ap
                      ,MAX(CASE
                             WHEN at.actv_code_type =
                                  'RIO ASSESSMENT (BL)' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS rio_severity_assessment_bl
                      ,MAX(CASE
                             WHEN at.actv_code_type = 'RIO OWNER' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS rio_owner
                  FROM admuser.actvtype at
                      ,admuser.taskactv ta
                      ,admuser.actvcode c
                 WHERE at.actv_code_type_scope = 'AS_Global'
                   AND at.actv_code_type IN
                       ('CAM', 'CA #', 'A/C#', 'Watch Part (APS)',
                        'EV Method', 'ICP', 'IPS', 'IPT',
                        'Work Package Lead', 'Work Package Status',
                        'Work Package Type', 'RIO ID', 'RIO PHASE','RIO STATUS',
                        'RIO LEVEL', 'RIO CATEGORY',
                        'RIO ASSESSMENT (AP)',
                        'RIO ASSESSMENT (BL)', 'RIO OWNER')
                   AND ta.proj_id = $cid
                   AND ta.actv_code_id = c.actv_code_id
                   AND ta.actv_code_type_id = at.actv_code_type_id
                 GROUP BY ta.task_id) ta ,(SELECT /*+ USE_HASH (u) */ fk_id wbs_id
      ,max(CASE WHEN udf_type_label = '06-Charge #' then udf_text else null end) wpkg
      ,max(CASE WHEN udf_type_label = '12-CM Task User 6' then udf_text else null end) ipt_level_3
      ,max(CASE WHEN udf_type_label = '13-CM Task User 7' then udf_text else null end) ipt_level_4
      ,max(CASE WHEN udf_type_label = '15-CM Task User 9' then udf_text else null end) csa
      ,max(CASE WHEN udf_type_label = '10-CM Task User 3' then udf_text else null end) program_code
      ,max(CASE WHEN udf_type_label = '16-CM Task User 10' then udf_text else null end) team_code
  FROM (SELECT DISTINCT wbs_id
                       ,udf_type_id, udf_type_label
          FROM d
              ,admuser.udftype ut
         WHERE udf_type_label IN ('06-Charge #', '12-CM Task User 6',
                '13-CM Task User 7', '15-CM Task User 9',
                '10-CM Task User 3', '16-CM Task User 10')
           AND table_name = 'PROJWBS') x
      ,admuser.udfvalue u
 WHERE x.wbs_id  = u.fk_id (+)
   and x.udf_type_id  = u.udf_type_id (+)
   and u.proj_id (+) = $cid
   group by fk_id) uv
       WHERE d.task_id = ta.task_id(+)
         and uv.wbs_id (+) = d.wbs_id )
) a
          where rownum <= $u ) a2
 where rnum >= $l

";

    //string2file("e:/sql.txt",$sql,'w');
    //print "done";
    //exit();
        $rs = null;
        $rs = dbCall_Oracle($sql,$debug,$oracle_database);
        //array_debug($rs);
        //print "rc = " . $rs->RecordCount() . "\n";
        //exit();
        //$rs=false;
        if($rs)
        {
            $i=1;
            while(!$rs->EOF)
            {
               $db                            = array();
               $db['program_group']                 = $program_group;
               $db['proj_id']                       = $rs->fields['PROJ_ID'];
               $db['proj_short_name']               = $rs->fields['PROJ_SHORT_NAME'];
               $db['controlaccount']                = $rs->fields['CA'];
               $db['cam']                           = addslashes($rs->fields['CAM']);
               $db['csa']                           = addslashes($rs->fields['CSA']);
               $db['ips']                           = addslashes($rs->fields['IPS']);
               $db['workpackage']                   = addslashes($rs->fields['WPKG']);
               $db['task_id']                       = $rs->fields['TASK_ID'];
               $db['task_code']                     = $rs->fields['TASK_CODE'];
               $db['task_name']                     = addslashes($rs->fields['TASK_NAME']);
               if(strlen($db['task_name'])>119) $db['task_name']=left($db['task_name'],120);
               $db['num_pred']                      = $rs->fields['NUM_PRED'];
               $db['num_succ']                      = $rs->fields['NUM_SUCC'];
               $db['ev_method']                     = $rs->fields['EV_METHOD'];
               $db['phys_complete_pct']             = (float)$rs->fields['PC'];
               $db['remain_drtn_hr_cnt']            = $rs->fields['REMAIN_DRTN_HR_CNT'];
               $db['remain_work_qty']               = $rs->fields['REMAIN_WORK_QTY'];
               $db['total_float_hr_cnt']            = (float)$rs->fields['TOTAL_FLOAT_HR_CNT'];
               $db['start']                         = fd($rs->fields['STARTX']);
               $db['finish']                        = fd($rs->fields['FINISHX']);
               $db['baseline_start']                = fd($rs->fields['BL_STARTX']);
               $db['baseline_finish']               = fd($rs->fields['BL_FINISHX']);
               $db['ipt_level_3']                   = addslashes($rs->fields['IPT_LEVEL_3']);
               $db['ipt_level_4']                   = addslashes($rs->fields['IPT_LEVEL_4']);
               $db['program_code']                  = $rs->fields['PROGRAM_CODE'];
               $db['team_code']                     = $rs->fields['TEAM_CODE'];
               $db['wbs_id']                        = $rs->fields['WBS_ID'];
               $db['clndr_id']                      = $rs->fields['CLNDR_ID'];
               $db['complete_pct_type']             = $rs->fields['COMPLETE_PCT_TYPE'];
               $db['act_start_date']                = fd($rs->fields['ACT_START_DATE']);
               $db['act_end_date']                  = fd($rs->fields['ACT_END_DATE']);
               $db['cstr_date']                     = fd($rs->fields['CSTR_DATE']);
               $db['remain_equip_qty']              = $rs->fields['REMAIN_EQUIP_QTY'];
               $db['target_work_qty']               = $rs->fields['TARGET_WORK_QTY'];
               $db['target_equip_qty']              = $rs->fields['TARGET_EQUIP_QTY'];
               $db['active']                = 1;
               $db['status_code']                   = $rs->fields['STATUS_CODE'];
               $db['driving_path_flag']             = $rs->fields['DRIVING_PATH_FLAG'];
               $db['float_path']                    = $rs->fields['FLOAT_PATH'];
               $db['float_path_order']              = $rs->fields['FLOAT_PATH_ORDER'];
               $db['expect_end_date']               = fd($rs->fields['EXPECT_END_DATE']);
               $db['cstr_type']                     = $rs->fields['CSTR_TYPE'];
               $db['priority_type']                 = $rs->fields['PRIORITY_TYPE'];
               $db['aps_code']                      = $rs->fields['APS'];
               $db['aircraft']                      = $rs->fields['AIRCRAFT'];
               $db['icp']                           = $rs->fields['ICP'];
               $db['ipt']                           = $rs->fields['IPT'];
               $db['work_package_type']             = $rs->fields['WORK_PACKAGE_TYPE'];
               $db['early_start_date']              = fd($rs->fields['EARLY_START_DATE']);
               $db['early_end_date']                = fd($rs->fields['EARLY_END_DATE']);
               $db['work_package_status']           = $rs->fields['WORK_PACKAGE_STATUS'];
               $db['free_float_hr_cnt']             = (float)$rs->fields['FREE_FLOAT_HR_CNT'];
               $db['budgeted_hours']        = 0;
               $db['budgeted_dollars']      = 0;
               $db['work_package_lead']             = addslashes($rs->fields['WORK_PACKAGE_LEAD']);
               $db['rio_id']                        = $rs->fields['RIO_ID'];
               $db['rio_type']                      = $rs->fields['RIO_TYPE'];
               $db['rio_visibility_level']          = $rs->fields['RIO_VISIBILITY_LEVEL'];
               $db['rio_severity_assessment']       = $rs->fields['RIO_SEVERITY_ASSESSMENT_AP'];
               $db['rio_owner']                     = $rs->fields['RIO_OWNER'];
               $db['rio_status']                    = $rs->fields['RIO_STATUS'];
               $db['rio_severity_assessment_bl']    = $rs->fields['RIO_SEVERITY_ASSESSMENT_BL'];
               $db['task_type']                     = $rs->fields['TASK_TYPE'];

                if(trim($db['controlaccount'])==''or trim($db['controlaccount'])=='na' or trim($db['controlaccount'])=='NA' or trim($db['controlaccount'])=='N/A' or trim($db['controlaccount'])=='n/a') $db['controlaccount']='NOCA';
                if(trim($db['workpackage'])==''or trim($db['workpackage'])=='na' or trim($db['workpackage'])=='NA' or trim($db['workpackage'])=='N/A' or trim($db['workpackage'])=='n/a') $db['workpackage']='NOWP';
                if(trim($db['cam'])==''or trim($db['cam'])=='na' or trim($db['cam'])=='NA' or trim($db['cam'])=='N/A' or trim($db['cam'])=='n/a') $db['cam']='NOCAM';

                $db['num_pred']         = (int)$db['num_pred'];
                $db['num_succ']         = (int)$db['num_succ'];
                $db['float_path']       = (int)$db['float_path'];
                $db['float_path_order'] = (int)$db['float_path_order'];

                if (trim($db['task_id'])=='') $db['task_id'] = $rs->fields['X'];

               $debug=false;
               //if($db['task_code']=='KT5LR14369' or $db['task_code']=='L510910' or $db['task_code']=='L510510' or $db['task_code']=='KT5LH0689' or $db['task_code']=='KT5LD0212') $debug=true;
               $result = getReplaceSQL('schedule',$db,'id',$debug,'schedule_data',false,$db_server);
               //print "result=".(int)$result."\n";
               //if((int)$result<>1 and (int)$result<>2) print $db['task_code'] . "\n";
               //if($debug) exit();
               //$debug=false;
               //if($rs->fields['RIO_ID']!='') $result = getReplaceSQL('schedule_zrio',$db,'id',false,'schedule_data',true);
               //if($rs->fields['RIO_ID']!='') $result = getReplaceSQL('schedule_zrio',$db,'id',$debug,'schedule_data',false,$db_server);


               //exit();
               //print "l=$l|u=$u<br>";
               if($debug and ($i%1000)==0) print ".";

               $i++;
               $rs->MoveNext();
            }
        }

        //print "\ncur_page:$cur_page\n";
        $cur_page++;
    }
    //exit();


    // RESOURCES
    $sql = "
        select
            tr.*,
            (select rsrc_short_name from admuser.rsrc where rsrc_id=tr.rsrc_id) as rsrc_short_name,
            (select rsrc_name from admuser.rsrc where rsrc_id=tr.rsrc_id) as rsrc_name
        from
            admuser.taskrsrc tr
        where
            tr.proj_id=$cid
    ";

    $rs = dbCall_Oracle($sql,$debug,$oracle_database);

    if($rs)
    {
        while(!$rs->EOF)
        {
           $db                                = array();
           $db['taskrsrc_id']            = $rs->fields['TASKRSRC_ID'];
           $db['task_id']               = $rs->fields['TASK_ID'];
           $db['proj_id']                  = $rs->fields['PROJ_ID'];
           $db['cost_qty_link_flag']    = $rs->fields['COST_QTY_LINK_FLAG'];
           $db['role_id']                   = $rs->fields['ROLE_ID'];
           $db['acct_id']                    = $rs->fields['ACCT_ID'];
           $db['rsrc_id']                  = $rs->fields['RSRC_ID'];
           $db['skill_level']                = $rs->fields['SKILL_LEVEL'];
           $db['pend_complete_pct']     = $rs->fields['PEND_COMPLETE_PCT'];
           $db['remain_qty']               = $rs->fields['REMAIN_QTY'];
           $db['pend_remain_qty']         = $rs->fields['PEND_REMAIN_QTY'];
           $db['target_qty']             = $rs->fields['TARGET_QTY'];
           $db['remain_qty_per_hr']        = $rs->fields['REMAIN_QTY_PER_HR'];
           $db['pend_act_reg_qty']        = $rs->fields['PEND_ACT_REG_QTY'];
           $db['target_lag_drtn_hr_cnt']= $rs->fields['TARGET_LAG_DRTN_HR_CNT'];
           $db['target_qty_per_hr']       = $rs->fields['TARGET_QTY_PER_HR'];
           $db['act_ot_qty']              = $rs->fields['ACT_OT_QTY'];
           $db['pend_act_ot_qty']          = $rs->fields['PEND_ACT_OT_QTY'];
           $db['act_reg_qty']           = $rs->fields['ACT_REG_QTY'];
           $db['relag_drtn_hr_cnt']     = $rs->fields['RELAG_DRTN_HR_CNT'];
           $db['ot_factor']                   = $rs->fields['OT_FACTOR'];
           $db['cost_per_qty']             = $rs->fields['COST_PER_QTY'];
           $db['target_cost']           = $rs->fields['TARGET_COST'];
           $db['act_reg_cost']             = $rs->fields['ACT_REG_COST'];
           $db['act_ot_cost']             = $rs->fields['ACT_OT_COST'];
           $db['remain_cost']               = $rs->fields['REMAIN_COST'];
           $db['act_start_date']         = $rs->fields['ACT_START_DATE'];
           $db['act_end_date']            = $rs->fields['ACT_END_DATE'];
           $db['restart_date']           = $rs->fields['RESTART_DATE'];
           $db['reend_date']              = $rs->fields['REEND_DATE'];
           $db['target_start_date']           = $rs->fields['TARGET_START_DATE'];
           $db['target_end_date']        = $rs->fields['TARGET_END_DATE'];
           $db['rem_late_start_date']     = $rs->fields['REM_LATE_START_DATE'];
           $db['rem_late_end_date']        = $rs->fields['REM_LATE_END_DATE'];
           $db['guid']                       = $rs->fields['GUID'];
           $db['rate_type']                 = $rs->fields['RATE_TYPE'];
           $db['act_this_per_cost']        = $rs->fields['ACT_THIS_PER_COST'];
           $db['act_this_per_qty']        = $rs->fields['ACT_THIS_PER_QTY'];
           $db['curv_id']                  = $rs->fields['CURV_ID'];
           $db['rsrc_request_data']     = $rs->fields['RSRC_REQUEST_DATA'];
           $db['rsrc_type']                  = $rs->fields['RSRC_TYPE'];
           $db['rollup_dates_flag']        = $rs->fields['ROLLUP_DATES_FLAG'];
           $db['cost_per_qty_source_type'] = $rs->fields['COST_PER_QTY_SOURCE_TYPE'];
           $db['update_date']               = $rs->fields['UPDATE_DATE'];
           $db['update_user']              = $rs->fields['UPDATE_USER'];
           $db['create_date']              = $rs->fields['CREATE_DATE'];
           $db['create_user']              = $rs->fields['CREATE_USER'];
           $db['delete_session_id']        = $rs->fields['DELETE_SESSION_ID'];
           $db['delete_date']             = $rs->fields['DELETE_DATE'];
           $db['rsrc_short_name']          = $rs->fields['RSRC_SHORT_NAME'];
           $db['rsrc_name']              = $rs->fields['RSRC_NAME'];

           $result = getReplaceSQL('schedule_resources',$db,'id',$debug,'schedule_data',true,$db_server);

           $rs->MoveNext();
        }
    }

    // STEPS
    $sql = "
        select
            *
        from
            admuser.taskproc
        where
            proj_id=$cid
    ";

    $rs = dbCall_Oracle($sql,$debug,$oracle_database);

    if($rs)
    {
        while(!$rs->EOF)
        {
           $db                                = array();
           $db['proc_id']                = $rs->fields['PROC_ID'];
           $db['task_id']               = $rs->fields['TASK_ID'];
           $db['seq_num']                  = $rs->fields['SEQ_NUM'];
           $db['proj_id']                  = $rs->fields['PROJ_ID'];
           $db['complete_flag']            = $rs->fields['COMPLETE_FLAG'];
           $db['proc_name']                = $rs->fields['PROC_NAME'];
           $db['proc_wt']                  = $rs->fields['PROC_WT'];
           $db['complete_pct']                = $rs->fields['COMPLETE_PCT'];
           $db['proc_descr']             = $rs->fields['PROC_DESCR'];
           $db['update_date']               = $rs->fields['UPDATE_DATE'];
           $db['update_user']             = $rs->fields['UPDATE_USER'];
           $db['create_date']             = $rs->fields['CREATE_DATE'];
           $db['create_user']              = $rs->fields['CREATE_USER'];
           $db['delete_session_id']        = $rs->fields['DELETE_SESSION_ID'];
           $db['delete_date']            = $rs->fields['DELETE_DATE'];

           $result = getReplaceSQL('schedule_steps',$db,'id',$debug,'schedule_data',true,$db_server);

           $rs->MoveNext();
        }

    }



    // TASKPRED
    $sql = "SELECT * FROM ADMUSER.TASKPRED where proj_id=$cid";
    $rs = dbCall_Oracle($sql,$debug,$oracle_database);
    if($rs)
    {
        while(!$rs->EOF)
        {
            $db                             = array();
            $db['TASK_PRED_ID']             = $rs->fields['TASK_PRED_ID'];
            $db['TASK_ID']                  = $rs->fields['TASK_ID'];
            $db['PRED_TASK_ID']             = $rs->fields['PRED_TASK_ID'];
            $db['PROJ_ID']                  = $rs->fields['PROJ_ID'];
            $db['PRED_PROJ_ID']             = $rs->fields['PRED_PROJ_ID'];
            $db['PRED_TYPE']                = $rs->fields['PRED_TYPE'];
            $db['LAG_HR_CNT']               = $rs->fields['LAG_HR_CNT'];
            $db['UPDATE_DATE']              = $rs->fields['UPDATE_DATE'];
            $db['UPDATE_USER']              = $rs->fields['UPDATE_USER'];
            $db['CREATE_DATE']              = $rs->fields['CREATE_DATE'];
            $db['CREATE_USER']              = $rs->fields['CREATE_USER'];
            $db['DELETE_SESSION_ID']        = $rs->fields['DELETE_SESSION_ID'];
            $db['DELETE_DATE']              = $rs->fields['DELETE_DATE'];


           $result = getReplaceSQL('schedule_taskpred',$db,'id',$debug,'schedule_data',true,$db_server);

           $rs->MoveNext();
        }
    }


    return true;
}

// ------------------------------------------------------------------
// functions for sched_xtra below
// ------------------------------------------------------------------
function loadSchedXtraData($cid,$db_server='localhost',$debug=false,$oracle_database='A019PROD')
{

    schedXtraCreateTable($db_server,$debug);

    schedXtraGetPPMData($cid,$db_server,$debug,$oracle_database);

    copySchedXtraTable($cid,$db_server,$debug);

    return true;
}
  // ------------------------------------------------------------------
function schedXtraCreateTable($db_server='localhost',$debug=false)
{
    $table_date = date('mdY',time());

    $sql = "select table_name from tables where table_name like 'z_sched_xtra_{$table_date}' limit 1";
    $rs = dbCall($sql,$debug,'information_schema',$db_server);
    $table_name = $rs->fields['table_name'];

    if($table_name=='')
    {
        $sqls = "
            /*!40101 SET NAMES utf8 */;
            /*!40101 SET SQL_MODE=''*/;
            /*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
            /*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
            drop table `z_sched_xtra_{$table_date}`;
            CREATE TABLE `z_sched_xtra_{$table_date}` (
              `id` int(11) NOT NULL auto_increment primary key,
              `proj_id` int(11) default NULL,
              `task_code` varchar(15)  collate utf8_unicode_ci default NULL,
              `evt` varchar(5) collate utf8_unicode_ci default NULL,
              `pc` int(11) default NULL,
              `num_steps` int(11) default NULL,
              `num_steps_complete` int(11) default NULL,
              `drawing_num` varchar(75) collate utf8_unicode_ci default NULL,
              `in_mrp` int(2) default NULL,
              `restart` DATETIME DEFAULT NULL,
              `reend` DATETIME DEFAULT NULL
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
            /*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
            /*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
            ALTER TABLE `z_sched_xtra_{$table_date}` ADD INDEX ( `proj_id` );
            ALTER TABLE `z_sched_xtra_{$table_date}` ADD INDEX ( `task_code` );
        ";
        $sql_array = explode(';',$sqls);
        foreach($sql_array as $sql)
        {
            if(trim($sql)!='')$junk = dbCall($sql,$debug,'schedule_data_archive',$db_server);
        }
    }
}

// ------------------------------------------------------------------
function copySchedXtraTable($proj_id,$db_server='localhost',$debug=false)
{
    $table_date = date('mdY',time());
    //$sql = "truncate sched_xtra";
    $sql = "delete from sched_xtra where proj_id=$proj_id";
    $junk = dbCall($sql,$debug,'schedule_data',$db_server);

    //$sql = "insert into schedule_data.sched_xtra (select * from schedule_data_archive.`z_sched_xtra_{$table_date}`)";
    //$sql = "insert into schedule_data.sched_xtra (proj_id,task_code,evt,pc,num_steps,num_steps_complete,drawing_num,in_mrp)(select proj_id,task_code,evt,pc,num_steps,num_steps_complete,drawing_num,in_mrp from schedule_data_archive.`z_sched_xtra_{$table_date}` where proj_id=$proj_id)";
    $sql = "insert into schedule_data.sched_xtra (proj_id,task_code,evt,pc,num_steps,num_steps_complete,drawing_num,in_mrp,restart,reend)(select proj_id,task_code,evt,pc,num_steps,num_steps_complete,drawing_num,in_mrp,restart,reend from schedule_data_archive.`z_sched_xtra_{$table_date}` where proj_id=$proj_id)";
    $junk = dbCall($sql,$debug,'schedule_data',$db_server);
    return true;
}

// ------------------------------------------------------------------
function updateMRPPNTable($db_server='localhost',$debug=false)
{
    //print "\nUpdating MRP Partno Table on MTL_DATA2\n\n";
    $sql = "delete from mrp_partno where isNumeric(thrueff)=0";
    //$junk = dbCall($sql,$debug,'premier_core_mtl',$db_server);
    $junk = dbCall($sql,$debug,'mtl_data2',$db_server);
    $sql = "delete from mrp_partno where thrueff='' or fromeff=''";
    //$junk = dbCall($sql,$debug,'premier_core_mtl',$db_server);
    $junk = dbCall($sql,$debug,'mtl_data2',$db_server);
    return true;
}

function getAircraft($proj_id,$task_code,$db_server='localhost',$debug=false)
{
    $sql = "select aircraft from schedule where proj_id=$proj_id and task_code='$task_code'";
    $rs  = dbCall($sql,$debug,'schedule_data',$db_server);
    return trim($rs->fields['aircraft']);
}

// ------------------------------------------------------------------
function checkMRP($dn,$aircraft,$debug=false)
{
    //$sql = "select trim(drawing_num) from z_sched_xtra_01232009 where length(drawing_num)>10 and substr(drawing_num,4,1)='-' group by drawing_num";
    /*
    and fromeff_274 >= concat('0','$aircraft') and thrueff_274 <= concat('0','$aircraft')
                    and substr(fromeff_274,0,2) = concat('0',substr('$aircraft',0,1)) and substr(thrueff_274,0,2) = concat('0',substr('$aircraft',0,1))
    */
    //print "starting...";
    $dn=trim($dn);
    $aircraft=trim($aircraft);
    $sql = "select partno_201 as partno from FRH_MRP.PSK02374 where partno_201='$dn' and partno_201 is not null";
    $rs = dbCall_Oracle($sql,$debug,'DWPROD');
    $r = trim($rs->fields['PARTNO']);
    if($r<>'')
    {
        $r=1;
    }
    else
    {
        $r=0;
    }
    //print "done\n";
    return $r;
}

 // ------------------------------------------------------------------
function schedXtraGetPPMData($proj_id,$db_server='localhost',$debug=false,$oracle_database='A019PROD')
{
    updateMRPPNTable($db_server,$debug);
    //$pages = getProjectCount();
    //if($page=='') $page = 0;
    //while($page < $pages)
    //{
    $ev_method    = '.EV Method';
    $ev_technique = '.EV Technique';
    if($oracle_database == 'A057PROD')
    {
        $ev_method    = 'EV Method';
        $ev_technique = 'EV Technique';
    }

        //print "page $page of $pages\n\n";
        $table = "z_sched_xtra_". date('mdY',time());

        $sql = "delete from $table where proj_id=$proj_id";
        $junk = dbCall($sql,$debug,'schedule_data_archive',$db_server);

        //$in    = getIDs();
        //$in = 2146;
        //$ins = explode(',',$in);
        //foreach ($ins as $in)
        //{
        $sql = "
            SELECT
            t.proj_id,
            t.task_code,
            t.PHYS_COMPLETE_PCT AS pc,
            t.restart_date,
            t.reend_date,
                   (SELECT c.short_name AS ca
                    FROM    admuser.taskactv a,
                            admuser.actvcode c,
                            admuser.actvtype t1
                    WHERE a.actv_code_type_id=t1.actv_code_type_id
                    AND a.actv_code_id=c.ACTV_CODE_ID
                    AND t1.actv_code_type='$ev_method'
                    AND t1.actv_code_type_scope='AS_Global'
                    AND a.task_id=t.task_id
                    AND a.proj_id=t.proj_id) AS ev_method,
                 (SELECT c.short_name AS ca
                    FROM   admuser.taskactv a,
                            admuser.actvcode c,
                            admuser.actvtype t1
                    WHERE a.actv_code_type_id=t1.actv_code_type_id
                    AND a.actv_code_id=c.ACTV_CODE_ID
                    AND t1.actv_code_type='$ev_technique'
                    AND t1.actv_code_type_scope='AS_Global'
                    AND a.task_id=t.task_id
                    AND a.proj_id=t.proj_id) AS ev_technique,
                   (SELECT COUNT(p.task_id)
                    FROM admuser.taskproc p
                    WHERE p.proj_id=t.proj_id
                    AND p.task_id=t.task_id
                    GROUP BY p.proj_id,p.task_id) AS num_steps,
                   (SELECT COUNT(p.task_id)
                    FROM admuser.taskproc p
                    WHERE complete_pct>=100
                    AND p.proj_id=t.proj_id
                    AND p.task_id=t.task_id
                    GROUP BY p.proj_id,p.task_id) AS num_steps_complete,
        (SELECT v1.udf_text
         FROM  ADMUSER.UDFTYPE t1,
              admuser.udfvalue v1
         WHERE
                v1.UDF_type_ID=t1.udf_type_id
         AND t1.table_name='TASK'
         AND t1.udf_type_label='Drawing #'
         AND v1.fk_id=t.task_id
         AND v1.proj_id=t.proj_id) AS drawing_num
            FROM admuser.task t left join admuser.project p ON t.proj_id=p.proj_id
            WHERE
            t.proj_id in ($proj_id)
        ";
        //print "$sql";
        //exit();
        $rs = '';
        $rs = dbCall_Oracle($sql,$debug,$oracle_database);
        $r=1;

        //if($page>0) array_debug($rs);

        while(!$rs->EOF)
        {
            $proj_id      = $rs->fields['PROJ_ID'];
            $task_code    = $rs->fields['TASK_CODE'];
            $pc           = (int)$rs->fields['PC'];
            $evt          = $rs->fields['EV_TECHNIQUE'];
            $ns           = (int)$rs->fields['NUM_STEPS'];
            $nsc          = (int)$rs->fields['NUM_STEPS_COMPLETE'];
            $dn           = trim($rs->fields['DRAWING_NUM']);
            $aircraft     = getAircraft($proj_id,$task_code,$db_server,$debug);
            $restart_date = $rs->fields['RESTART_DATE'];
            $reend_date   = $rs->fields['REEND_DATE'];

            $sql = "select partno from mrp_partno where partno='$dn' and fromeff >= concat('0','$aircraft') and thrueff <= concat('0','$aircraft')
                    and substr(fromeff,0,2) = concat('0',substr('$aircraft',0,1)) and substr(thrueff,0,2) = concat('0',substr('$aircraft',0,1))";
            //$rs2 = dbCall($sql,$debug,'premier_core_mtl',$db_server);
            $rs2 = dbCall($sql,$debug,'mtl_data2',$db_server);

            //print "dn=$dn <br>\n";
            $in_mrp = 0;
            if($dn!='') $in_mrp = checkMRP($dn,$aircraft,$debug);
            //exit();

            $sql = "insert into $table (proj_id,task_code,evt,pc,num_steps,num_steps_complete,drawing_num,in_mrp,restart,reend) values
            ($proj_id,'$task_code','$evt',$pc,$ns,$nsc,'$dn',$in_mrp,'$restart_date','$reend_date')";
            $junk = dbCall($sql,$debug,'schedule_data_archive',$db_server);

            //print "Proj ID ($proj_id): $r\n";
            $r++;
            $rs->MoveNext();
        }
        //}

        //$page++;
    //}
    //exit();
}

// -----------------------------------------------------------------
function updateCacheFields($table_from,$table_to,$db)
{
    //convert dest. table columns into array
    $sql = "select * from $table_to limit 1";
    $rs = dbCall($sql,false,$db);
    $dest_fields = getFieldNamesFromRS($rs);

    /*
     1. comparing the config_table and dest. table(cache_table)
     2. if find new item from config_table, grab it
     3. alter dest. table add new item as a new field
     4. save config_table db_name as an array
    */
    $db_name_array = array();
    $sql = "select db_name,data_type,size from $table_from";
    $rs = dbCall($sql,false,$db);
    if($rs)
    {
        while(!$rs->EOF)
        {
            $db_name    = $rs->fields['db_name'];
            $data_type  = $rs->fields['data_type'];
            $size       = $rs->fields['size'];
            $value      = $db_name.' '.$data_type.'('.$size.')';
             if(isItemInArray($dest_fields,$db_name)==false)
             {
              $sql = "ALTER TABLE $table_to ADD COLUMN $value;";
              $junk = dbCall($sql,false,$db);
             }
            $db_name_array[] = strtolower(trim($rs->fields['db_name']));
            $rs->MoveNext();
        }
    }
    // comparing dest table to source table and drop fields not in the source table
    $result = array_diff($dest_fields,$db_name_array);
    foreach($result as $value)
    {
         if($value=='date' or $value=='filters')
         {
          //do nothing here
         }
         else
         {
             $sql = " ALTER TABLE $table_to DROP $value;";
             $junk = dbCall($sql,false,$db);
         }

     }
   }

// -----------------------------------------------------------
function updatePPMAPDataDate($cid,$debug=false,$db_server='localhost',$oracle_database='A019PROD')
{
    $sql = "SELECT last_recalc_date FROM privuser.project where proj_id=$cid";
    $rs = dbCall_Oracle($sql,$debug,$oracle_database);
    $data_date = $rs->fields['LAST_RECALC_DATE'];

    $sql = "update master_project set ppm_ap_data_date = '$data_date' where ppm_ap_id = $cid";
    $junk = dbCall($sql,$debug,'premier_core',$db_server);

    //kick off process to regenerate project master in IB

    return true;
}
// -----------------------------------------------------------------
function copyPMToMillenniumPM($db_server,$s_user_id,$debug=false)
{
    //whodunit log
    $log_datetime     = date("Y_m_d_H_i_s");
    $log_file         = "e:/logs/master_tables_to_millennium_logs/".$log_datetime."_premier_pm_2_mil_pm_log.txt";
    string2file($log_file,"Starting Premier PM Table Copy to Millennium PM by s_user_id: $s_user_id - ".date("Y-m-d H:i:s")."\n\n",'w');
    // get rows from current project_master
    $sql_values = "
                    select
                      id
                      ,program_type
                      ,program_group
                      ,project_group
                      ,premier_name
                      ,project_long_name
                      ,contract_type
                      ,project_type
                      ,dfars
                      ,dashboard
                      ,pcm_project_id
                      ,pcm_name
                      ,rate
                      ,ws_contract_name
                      ,ws_contract_id
                      ,ppm_ap_name
                      ,ppm_ap_id
                      ,ppm_bl_name
                      ,ppm_bl_id
                      ,pcma_actuals
                      ,case when otb_date='0000-00-00' then null else otb_date end as otb_date
                      ,otb_year
                      ,otb_period
                      ,visibility
                      ,ppm_apprv_proj_id
                      ,ppm_apprv_proj_name
                      ,process_priority
                      ,mtl_actuals
                      ,program_review
                      ,program_review_rate_set
                      ,pg_ws_id
                      ,struid
                      ,pcm_schema
                      ,enovia_id
                      ,case when ppm_ap_data_date='0000-00-00 00:00:00' then null else date(ppm_ap_data_date) end as ppm_ap_data_date
                      ,category
                      ,site
                      ,source
                      ,bell_contract_num
                    from
                      project_master
    ";
    $rs_values      = dbCall($sql_values,$debug,'tools_data',$db_server);

    $premier_pm_rc     = $rs_values->RecordCount();

    string2file($log_file,"Premier PM record count: $premier_pm_rc\n",'a');

    //verify rows exist and if so delete current project_master on Millennium
    if ($rs_values->RecordCount()>=1)
    {
        $conn   = dbCon_Oracle('A021PROD-FULL-ACCESS',$debug);
        $conn->StartTrans();
        $truncate_mil_project_master_reload_sql            = "TRUNCATE TABLE \"MILLENNIUM\".\"PROJECT_MASTER_RELOAD\"";
        $stmt_for_truncate_mil_project_master_reload       = $conn->Prepare($truncate_mil_project_master_reload_sql);
        $conn->_Execute($stmt_for_truncate_mil_project_master_reload);
        $conn->CompleteTrans();

        //turn rows/values into an array and insert array into project_master on Millennium
        $values_array = getArrayFromRS($rs_values);

        $conn = dbCon_Oracle('A021PROD-FULL-ACCESS',$debug);
        $conn->StartTrans();
        $sql  = "INSERT INTO \"MILLENNIUM\".\"PROJECT_MASTER_RELOAD\" (\"ID\",\"PROGRAM_TYPE\",\"PROGRAM_GROUP\",\"PROJECT_GROUP\",\"PREMIER_NAME\",\"PROJECT_LONG_NAME\",\"CONTRACT_TYPE\",\"PROJECT_TYPE\",\"DFARS\",\"DASHBOARD\",\"PCM_PROJECT_ID\",\"PCM_NAME\",\"RATE\",\"WS_CONTRACT_NAME\",\"WS_CONTRACT_ID\",\"PPM_AP_NAME\",\"PPM_AP_ID\",\"PPM_BL_NAME\",\"PPM_BL_ID\",\"PCMA_ACTUALS\",\"OTB_DATE\",\"OTB_YEAR\",\"OTB_PERIOD\",\"VISIBILITY\",\"PPM_APPRV_PROJ_ID\",\"PPM_APPRV_PROJ_NAME\",\"PROCESS_PRIORITY\",\"MTL_ACTUALS\",\"PROGRAM_REVIEW\",\"PROGRAM_REVIEW_RATE_SET\",\"PG_WS_ID\",\"STRUID\",\"PCM_SCHEMA\",\"ENOVIA_ID\",\"PPM_AP_DATA_DATE\",\"CATEGORY\",\"SITE\",\"SOURCE\",\"BELL_CONTRACT_NUM\") VALUES (:ID, :PROGRAM_TYPE, :PROGRAM_GROUP, :PROJECT_GROUP, :PREMIER_NAME, :PROJECT_LONG_NAME, :CONTRACT_TYPE, :PROJECT_TYPE, :DFARS, :DASHBOARD, :PCM_PROJECT_ID, :PCM_NAME, :RATE, :WS_CONTRACT_NAME, :WS_CONTRACT_ID, :PPM_AP_NAME, :PPM_AP_ID, :PPM_BL_NAME, :PPM_BL_ID, :PCMA_ACTUALS, :OTB_DATE, :OTB_YEAR, :OTB_PERIOD, :VISIBILITY, :PPM_APPRV_PROJ_ID, :PPM_APPRV_PROJ_NAME, :PROCESS_PRIORITY, :MTL_ACTUALS, :PROGRAM_REVIEW, :PROGRAM_REVIEW_RATE_SET, :PG_WS_ID, :STRUID, :PCM_SCHEMA, :ENOVIA_ID, :PPM_AP_DATA_DATE, :CATEGORY, :SITE, :SOURCE, :BELL_CONTRACT_NUM)";
        $stmt = $conn->Prepare($sql);

        foreach ($values_array as $row)
        {
            $conn->_Execute($stmt,$row);
        }
        $conn->CompleteTrans();

        //check record counts before truncating millennium's project master
        $sql = "SELECT ID FROM \"MILLENNIUM\".\"PROJECT_MASTER_RELOAD\"";
        $rs  = dbCall_Oracle($sql,$debug,'A021PROD-FULL-ACCESS');
        $mil_pmr_rc = $rs->RecordCount();

        string2file($log_file,"Millennium PM Reload record count: $mil_pmr_rc\n",'a');

        if($mil_pmr_rc==$premier_pm_rc)
        {
            //truncate millennium.project_master
            $conn   = dbCon_Oracle('A021PROD-FULL-ACCESS',$debug);
            $conn->StartTrans();
            $truncate_mil_project_master_sql        = "TRUNCATE TABLE \"MILLENNIUM\".\"PROJECT_MASTER\"";
            $stmt_for_truncate_mil_project_master    = $conn->Prepare($truncate_mil_project_master_sql);
            $conn->_Execute($stmt_for_truncate_mil_project_master);
            $conn->CompleteTrans();

            //insert into millennium.project_master from millennium.PROJECT_MASTER_RELOAD
            $conn   = dbCon_Oracle('A021PROD-FULL-ACCESS',$debug);
            $conn->StartTrans();
            $sql    = "INSERT INTO \"MILLENNIUM\".\"PROJECT_MASTER\" (\"ID\",\"PROGRAM_TYPE\",\"PROGRAM_GROUP\",\"PROJECT_GROUP\",\"PREMIER_NAME\",\"PROJECT_LONG_NAME\",\"CONTRACT_TYPE\",\"PROJECT_TYPE\",\"DFARS\",\"DASHBOARD\",\"PCM_PROJECT_ID\",\"PCM_NAME\",\"RATE\",\"WS_CONTRACT_NAME\",\"WS_CONTRACT_ID\",\"PPM_AP_NAME\",\"PPM_AP_ID\",\"PPM_BL_NAME\",\"PPM_BL_ID\",\"PCMA_ACTUALS\",\"OTB_DATE\",\"OTB_YEAR\",\"OTB_PERIOD\",\"VISIBILITY\",\"PPM_APPRV_PROJ_ID\",\"PPM_APPRV_PROJ_NAME\",\"PROCESS_PRIORITY\",\"MTL_ACTUALS\",\"PROGRAM_REVIEW\",\"PROGRAM_REVIEW_RATE_SET\",\"PG_WS_ID\",\"STRUID\",\"PCM_SCHEMA\",\"ENOVIA_ID\",\"PPM_AP_DATA_DATE\",\"CATEGORY\",\"SITE\",\"SOURCE\",\"BELL_CONTRACT_NUM\") SELECT \"ID\",\"PROGRAM_TYPE\",\"PROGRAM_GROUP\",\"PROJECT_GROUP\",\"PREMIER_NAME\",\"PROJECT_LONG_NAME\",\"CONTRACT_TYPE\",\"PROJECT_TYPE\",\"DFARS\",\"DASHBOARD\",\"PCM_PROJECT_ID\",\"PCM_NAME\",\"RATE\",\"WS_CONTRACT_NAME\",\"WS_CONTRACT_ID\",\"PPM_AP_NAME\",\"PPM_AP_ID\",\"PPM_BL_NAME\",\"PPM_BL_ID\",\"PCMA_ACTUALS\",\"OTB_DATE\",\"OTB_YEAR\",\"OTB_PERIOD\",\"VISIBILITY\",\"PPM_APPRV_PROJ_ID\",\"PPM_APPRV_PROJ_NAME\",\"PROCESS_PRIORITY\",\"MTL_ACTUALS\",\"PROGRAM_REVIEW\",\"PROGRAM_REVIEW_RATE_SET\",\"PG_WS_ID\",\"STRUID\",\"PCM_SCHEMA\",\"ENOVIA_ID\",\"PPM_AP_DATA_DATE\",\"CATEGORY\",\"SITE\",\"SOURCE\",\"BELL_CONTRACT_NUM\" FROM \"MILLENNIUM\".\"PROJECT_MASTER_RELOAD\"";
            $stmt   = $conn->Prepare($sql);
            $conn->_Execute($stmt);
            $conn->CompleteTrans();

            //check record counts before truncating millennium's project master
            $sql = "SELECT ID FROM \"MILLENNIUM\".\"PROJECT_MASTER\"";
            $rs  = dbCall_Oracle($sql,$debug,'A021PROD-FULL-ACCESS');
            $mil_pm_rc = $rs->RecordCount();

            string2file($log_file,"Millennium PM record count: $mil_pm_rc\n",'a');
        }
        else
        {
            string2file($log_file,"Premier to Millennium PM table copy failed due to non-matching record counts.\n",'a');
        }
    }
    string2file($log_file,"\nFinished Premier PM Table Copy to Millennium PM by s_user_id: $s_user_id - ".date("Y-m-d H:i:s")."\n\n",'a');
}
// -----------------------------------------------------------
function copyCAMToMillenniumCAM($db_server,$s_user_id,$debug=false)
{

    $mil_reload_table   = "CA_MASTER_RELOAD";
    $mil_primary_table  = "CA_MASTER";

    $fields_list = array(
                       'id'
                      ,'task_is_control_account'
                      ,'project_id'
                      ,'program_group'
                      ,'project_name'
                      ,'task_id'
                      ,'task_name'
                      ,'task_description'
                      ,'control_account'
                      ,'cam'
                      ,'ipt_leader_2'
                      ,'ipt_leader_3'
                      ,'ipt_leader_4'
                      ,'csa'
                      ,'team_code'
                      ,'level_2_ipt_title'
                      ,'level_3_ipt_title'
                      ,'level_4_ipt_title'
                      ,'ccdr_level_2'
                      ,'ccdr_level_3'
                      ,'ccdr_level_4'
                      ,'ccdr_level_5'
                      ,'ccdr_level_6'
                      ,'ccdr_level_7'
                      ,'ccdr_level_8'
                      ,'wbs_level_2'
                      ,'wbs_level_3'
                      ,'wbs_level_4'
                      ,'wbs_level_5'
                      ,'wbs_level_6'
                      ,'wbs_level_7'
                      ,'wbs_level_8'
                      ,'pm_name'
                      ,'pm_title'
                      ,'ips'
                      ,'var_threshold_rollup'
                      ,'var_ext_cum_percent'
                      ,'var_ext_cum_dollars'
                      ,'var_int_cum_percent'
                      ,'var_int_cum_dollars'
                      ,'var_ext_cum_and_or_flag'
                      ,'var_int_cum_and_or_flag'
                      ,'var_ext_cum_top'
                      ,'var_int_cum_top'
                      ,'var_ext_cum_change_flag'
                      ,'var_int_cum_change_flag'
                      ,'var_ext_cur_percent'
                      ,'var_int_cur_percent'
                      ,'var_ext_ac_percent'
                      ,'var_int_ac_percent'
                      ,'var_ext_ac_dollars'
                      ,'var_int_ac_dollars'
                      ,'var_ext_ac_and_or_flag'
                      ,'var_int_ac_and_or_flag'
                      ,'var_ext_ac_top'
                      ,'var_int_ac_top'
                      ,'var_ext_ac_change_flag'
                      ,'var_int_ac_change_flag'
                      ,'var_ext_cur_dollars'
                      ,'var_int_cur_dollars'
                      ,'var_ext_cur_and_or_flag'
                      ,'var_int_cur_and_or_flag'
                      ,'var_ext_cur_top'
                      ,'var_int_cur_top'
                      ,'var_ext_cur_change_flag'
                      ,'var_int_cur_change_flag'
                      ,'clin'
                      ,'var_int_cur_min_amt'
                      ,'var_ext_cur_min_amt'
                      ,'var_int_cum_min_amt'
                      ,'var_ext_cum_min_amt'
                      ,'var_int_ac_min_amt'
                      ,'var_ext_ac_min_amt'
                      ,'do'
                      ,'project_group'
                      ,'premier_pm_id'
                      ,'full_wbs_id'
                      ,'full_wbs_name'
                      ,'bell_contract_type'
                      ,'sales_order_num'
                      ,'pop_start'
                      ,'pop_end'
                      ,'acrn'
                      ,'site'
    );

    $premier_fields_list    = '';
    $mil_fields_list        = '';
    $mil_values_list        = '';
    foreach($fields_list as $field)
    {
        $premier_fields_list    .= "`$field`,";

        $mil_fields_list        .= "\"$field\",";

        $mil_values_list        .= ":$field,";

    }
    $premier_fields_list    = stripLastCharacter($premier_fields_list);
    $mil_fields_list        = strtoupper(stripLastCharacter($mil_fields_list));
    $mil_values_list        = strtoupper(stripLastCharacter($mil_values_list));

    //whodunit log
    $log_datetime     = date("Y_m_d_H_i_s");
    $log_file         = "e:/logs/master_tables_to_millennium_logs/".$log_datetime."_premier_ca_2_mil_ca_log.txt";
    string2file($log_file,"Starting Premier CA Master Table Copy to Millennium CA Master by s_user_id: $s_user_id - ".date("Y-m-d H:i:s")."\n\n",'w');
    // get rows from current ca_master on 212
    $sql_values = "
                    SELECT
                      $premier_fields_list
                    FROM
                      ca_master
    ";
    $rs_values      = dbCall($sql_values,$debug,'tools_data',$db_server);

    $premier_ca_rc  = $rs_values->RecordCount();

    string2file($log_file,"Premier CA record count: $premier_ca_rc\n",'a');

    //verify rows exist and if so delete current ca_master on Millennium
    if ($rs_values->RecordCount()>=1)
    {
        $conn   = dbCon_Oracle('A021PROD-FULL-ACCESS',$debug);
        $conn->StartTrans();
        $truncate_mil_ca_master_reload_sql         = "TRUNCATE TABLE \"MILLENNIUM\".\"$mil_reload_table\"";
        $stmt_for_truncate_mil_ca_master_reload    = $conn->Prepare($truncate_mil_ca_master_reload_sql);
        $conn->_Execute($stmt_for_truncate_mil_ca_master_reload);
        $conn->CompleteTrans();

        //turn rows/values into an array and insert array into ca_master on Millennium
        $values_array = getArrayFromRS($rs_values);

        $conn   = dbCon_Oracle('A021PROD-FULL-ACCESS',$debug);
        $conn->StartTrans();
        $sql    = "INSERT INTO \"MILLENNIUM\".\"$mil_reload_table\" ($mil_fields_list) values ($mil_values_list)";
        $stmt   = $conn->Prepare($sql);

        foreach ($values_array as $row)
        {
            $conn->_Execute($stmt,$row);
        }
        $conn->CompleteTrans();

        //check record counts before truncating millennium's ca master
        $sql = "SELECT ID FROM \"MILLENNIUM\".\"$mil_reload_table\"";
        $rs  = dbCall_Oracle($sql,$debug,'A021PROD-FULL-ACCESS');
        $mil_car_rc = $rs->RecordCount();

        string2file($log_file,"Millennium CA Reload record count: $mil_car_rc\n",'a');

        if($mil_car_rc==$premier_ca_rc)
        {
            //truncate millennium.ca_master
            $conn   = dbCon_Oracle('A021PROD-FULL-ACCESS',$debug);
            $conn->StartTrans();
            $truncate_mil_ca_master_sql            = "TRUNCATE TABLE \"MILLENNIUM\".\"$mil_primary_table\"";
            $stmt_for_truncate_mil_ca_master       = $conn->Prepare($truncate_mil_ca_master_sql);
            $conn->_Execute($stmt_for_truncate_mil_ca_master);
            $conn->CompleteTrans();

            //insert into millennium.ca_master from millennium.CA_MASTER_RELOAD
            $conn   = dbCon_Oracle('A021PROD-FULL-ACCESS',$debug);
            $conn->StartTrans();
            $sql    = "INSERT INTO \"MILLENNIUM\".\"$mil_primary_table\" ($mil_fields_list) SELECT $mil_fields_list FROM \"MILLENNIUM\".\"$mil_reload_table\"";
            $stmt   = $conn->Prepare($sql);
            $conn->_Execute($stmt);
            $conn->CompleteTrans();

            //check record counts before truncating millennium's project master
            $sql = "SELECT ID FROM \"MILLENNIUM\".\"$mil_primary_table\"";
            $rs  = dbCall_Oracle($sql,$debug,'A021PROD-FULL-ACCESS');
            $mil_ca_rc = $rs->RecordCount();

            string2file($log_file,"Millennium CA record count: $mil_ca_rc\n",'a');
        }
        else
        {
            string2file($log_file,"Premier to Millennium CA Master table copy failed due to non-matching record counts.\n",'a');
        }
    }
    string2file($log_file,"\nFinished Premier CA Master Table Copy to Millennium CA Master by s_user_id: $s_user_id - ".date("Y-m-d H:i:s")."\n\n",'a');
}
// ------------------------------------------------------------------
function loadIECDData($pmid,$debug=false)
{
    // get latest date for bcwp and bcws
    $sql = "SELECT
                max(date1) as the_date
            FROM
                data_cost
            WHERE
                type='BCWP'
                AND pmid=$pmid
                AND hours1>0
        ";
    $drs = dbCall($sql,$debug);
    $cp = $drs->fields['the_date'];

    // added commercial data and basisname is different for commercial. adding code to create appropriate basisname

    $sql = "SELECT premier_name,program_type FROM master_project WHERE id=$pmid limit 1";
    $ptrs = dbCall($sql,$debug);
    $program_type = $ptrs->fields['program_type'];
    $premier_name = $ptrs->fields['premier_name'];

    if ($program_type == "Commercial")   {
        $basisname = "Baseline";
    }
    else {
        $basisname = "Baseline-".left($cp,4).'-'.right($cp,2);
    }

     $z .= "<br><br><br><br><hr><b>1)&nbsp;</b>premier_name=$premier_name | cp=$cp<hr>";

    // remove current data
    $sql = "delete from data_iecd_buckets where pmid=$pmid";
    $junk = dbCall($sql,$debug);

    $sql = "delete from data_iecd where pmid=$pmid and date1='$cp'";
    $junk = dbCall($sql,$debug);

    // get end date, bl finish and tf from icp-end milestone
    //max(finish) as last_apf
    //$sql = "select left(finish,10) as finish,left(baseline_finish,10) as baseline_finish,(total_float_hr_cnt/8) as tf_ap from schedule where proj_id=$ppm_id and icp='End'";
    // Note: per Scott - remove: and icp='End'
    $sql = "SELECT LEFT(MAX(finish),10) AS finish,LEFT(MAX(baseline_finish),10) AS baseline_finish,(total_float_hr_cnt/8) AS tf_ap from data_schedule where pmid=$pmid GROUP BY pmid";
    $rs = dbCall($sql,$debug);
    $finish_ap      = $rs->fields['finish'];
    $finish_bl      = $rs->fields['baseline_finish'];
    $tf_ap          = $rs->fields['tf_ap'];

    $z .= "<hr><b>2)&nbsp;</b>premier_name=$premier_name | program_type=$program_type | ppm_id=$ppm_id | finish_ap = $finish_ap | finish_bl = $finish_bl | tf_ap=$tf_ap<hr>";
     //string2file($test_file,"\"$premier_name\",\"$program_type\",\"$ppm_id\",\"$finish_ap\",\"$finish_bl\",\"$tf_ap\"\r\n",'a');

    //$basisname = 'Baseline';

    // get buckets
    // Note: commented out the following two lines out of where clause:
    //sr.BASISNAME='$basisname' AND
    //sr.burdenname LIKE '0%' AND
    //changed the below line
    //ca.control_account=sr.controlaccount AND
    $sql = "
    insert into data_iecd_buckets (pmid,date1,bcwp,bcws) (
        SELECT
          cd.pmid,
          cd.date1,
        SUM(CASE WHEN cd.TYPE='BCWP' AND cd.date1<='$cp' THEN ((cd.hours1)) ELSE 0 END) AS bcwp,
        SUM(CASE WHEN cd.TYPE='BCWS' AND cd.date1<='$cp' THEN ((cd.hours1)) ELSE 0 END) AS bcws
        FROM
            data_cost cd,
            main mv
        WHERE
            mv.pmid=cd.pmid
            and mv.cmid=cd.cmid
            and cd.type IN ('BCWP','BCWS') AND
            cd.pmid=$pmid and
            cd.date1<='$cp'
        GROUP BY
          cd.pmid,
          cd.date1
        ORDER BY
          cd.date1
        )
    ";
    $rs = dbCall($sql,$debug);
    //exit();

    // get running totals
    $sql = "select id,bcwp,bcws from data_iecd_buckets where pmid=$pmid order by date1";
    $rs = dbCall($sql,$debug);
    $bcwp = 0;
    $bcws = 0;
    while(!$rs->EOF)
    {
        $id      = $rs->fields['id'];
        $bcwp   += $rs->fields['bcwp'];
        $bcws   += $rs->fields['bcws'];
        $bcwp    = round($bcwp,2);
        $bcws    = round($bcws,2);

        $junk = dbCall("update data_iecd_buckets set bcwp_rt=$bcwp, bcws_rt=$bcws where id=$id",$debug);
        $rs->MoveNext();
    }

    // get cum bcwp
    //Note: for AAI - comented out below line from where clause
    //sr.BASISNAME='$basisname'
    $sql = "SELECT
                round(sum(hours1)) as bcwp
            FROM
                data_cost
            WHERE
                type='BCWP' AND
                pmid=$pmid and
                date1<='$cp'
        ";
       $rs = dbCall($sql,$debug);
       $bcwp = $rs->fields['bcwp'];

    if($bcwp==0)
    {
        $sql = "select id,date1,bcws_rt from data_iecd_buckets where pmid=$pmid and bcws_rt<=0 order by date1";
    }
    else
    {
        $sql = "select id,date1,bcws_rt from data_iecd_buckets where pmid=$pmid and bcws_rt<=$bcwp order by date1";
    }

    // bcwp is the largest (latest) bcwp
    // now see where bcwp fits into the bcws schedule

    $rs = dbCall($sql,$debug);
    $i=0;
    $last_day = 0;
    while(!$rs->EOF)
    {
        $i++;
        $id     = $rs->fields['id'];
        $date1  = $rs->fields['date1'];
        $bcws   = $rs->fields['bcws_rt'];
        $days   = $i * 30;

        if($date1==$cp) $last_day = $days;
        //$z .=  "i=$i|days=$days|id=$id|date1=$date1|bcws=$bcws|last_day=$last_day<br>\n";
          //string2file($log_file,"\ni=$i|days=$days|id=$id|date1=$date1|bcws=$bcws|last_day=$last_day\n",'a');

        $rs->MoveNext();
    }
    $the_month = $date1;

    $id = $id+1;
    $bcws_lower_bound = $bcws;

    // now get the next bcws_rt record
    $sql = "select bcws_rt from data_iecd_buckets where pmid=$pmid and id=$id ";
    $rs = dbCall($sql,$debug);
    $bcws_upper_bound = $rs->fields['bcws_rt'];

    // get the total days for the cp
    $sql = "select id from data_iecd_buckets where pmid=$pmid and date1<='$cp'";
    $rs = dbCall($sql,$debug);
    $last_day = $rs->RecordCount() * 30;

    $p          = abs(round(($bcwp-$bcws_lower_bound)/($bcws_upper_bound-$bcws_lower_bound),3));
    $more_days  = round(30-(30*$p)); // number of days into the month where p overtakes s
    $total_days = $days+$more_days;
    //$spit       = round($total_days/$last_day,2);
    //$es         = $total_days;
   //$us         = $last_day-$es;

    // get date p overtakes s
    // step 1, get number of days in month
    $month = right($date1,2);
    $num_days_in_month = substr(lastDayOfMonth($month),3,2);
    $days_to_add = round($p*$num_days_in_month) + $num_days_in_month;   // note - per Yancy add a month to POS
    $converted_date = UnixDate2TimeStamp(left($date1,4) .'-'. right($date1,2) .'-'. "01");
    $date_for_pos = DateAdd('d',$days_to_add,$converted_date);

    $cd4y = left($date1,4) .'-'. right($date1,2) .'-'. "01";

    $z .=  "<hr><b>3)&nbsp;</b>date1=$date1|converted_date=$cd4y|date_for_pos=$date_for_pos|bcwp=$bcwp|bcws_lower_bound=$bcws_lower_bound|bcws_upper_bound=$bcws_upper_bound|p=$p|more_days=$more_days|total_days=$total_days|last_day=$last_day|spit=$spit\n<hr>";
    //string2file($log_file,"\ndate1=$date1|converted_date=$converted_date|date_for_pos=$date_for_pos|bcwp=$bcwp|bcws_lower_bound=$bcws_lower_bound|bcws_upper_bound=$bcws_upper_bound|p=$p|more_days=$more_days|total_days=$total_days|last_day=$last_day|spit=$spit\n",'a');

    // get the data date
    // Note: for AAI - using status_date from schedule table - i.e. not Oracle
    /*
    $sql = "SELECT  p.proj_id, p.proj_short_name, p.last_recalc_date, p.plan_start_date, p.plan_end_date, p.scd_end_date
    FROM admuser.project p where p.proj_id=$ppm_ap_id ORDER BY p.proj_short_name";
    $rs = dbCall_Oracle($sql,$debug,'A019PROD');
    $data_date = UnixDate2USDate($rs->fields['LAST_RECALC_DATE']);
    */
    $sql = "SELECT LEFT(MAX(ppm_ap_data_date),10) AS status_date FROM master_project WHERE id=$pmid";
    $rs = dbCall($sql,$debug);
    $data_date = UnixDate2USDate($rs->fields['status_date']);

    // 2010-01-07 per Yancy use bcwp date for calculating spit and tspit instead of data date
    $spit_month                      = right($the_month,2);
    $spit_year                       = left($the_month,4);
    $spit_num_days_in_month          = substr(lastDayOfMonth($spit_month),3,2);
    $spit_date                       = "$spit_year-$spit_month-$spit_num_days_in_month";

    // get RD BL
    //$sql = "select first_bls,last_blf,last_apf,datediff(last_apf,first_aps) as td_ap,datediff('".USDate2UnixDate($data_date)."',first_bls) as dur_til_dd,datediff(last_blf,first_bls) as td_bl,datediff(last_blf,'".USDate2UnixDate($data_date)."') as rd_bl,datediff(last_apf,'".USDate2UnixDate($data_date)."') as rd_ap from (select min(baseline_start) as first_bls, max(baseline_finish) as last_blf,max(finish) as last_apf,min(start) as first_aps from schedule where proj_id=$ppm_ap_id  AND baseline_start<>'0000-00-00 00:00:00' and ev_method<>'LE' and task_type not like '%Mile%' and aps_code is not null group by proj_id) subquery";
    //first_aps
    //$sql = "select first_aps,first_bls,last_blf,last_apf,datediff(last_apf,first_aps) as td_ap,datediff('".USDate2UnixDate($data_date)."',first_bls) as dur_til_dd,datediff(last_blf,first_bls) as td_bl,datediff(last_blf,'".USDate2UnixDate($data_date)."') as rd_bl,datediff(last_apf,'".USDate2UnixDate($data_date)."') as rd_ap from (select min(baseline_start) as first_bls, max(baseline_finish) as last_blf,max(finish) as last_apf,min(start) as first_aps from schedule where proj_id=$ppm_ap_id  AND baseline_start<>'0000-00-00 00:00:00' and ev_method<>'LE' and task_type not like '%Mile%' and aps_code is not null group by proj_id) subquery";
    /*
    $sql = "select first_aps,first_bls,last_blf,last_apf,datediff(last_apf,first_aps) as td_ap,datediff('".USDate2UnixDate($data_date)."',first_bls) as dur_til_dd,datediff(last_blf,first_bls) as td_bl,datediff(last_blf,'".USDate2UnixDate($data_date)."') as rd_bl,datediff(last_apf,'".USDate2UnixDate($data_date)."') as rd_ap from (select min(baseline_start) as first_bls, max(baseline_finish) as last_blf,max(finish) as last_apf,min(start) as first_aps from schedule where proj_id=$ppm_ap_id  AND baseline_start<>'0000-00-00 00:00:00' and ev_method<>'LE' and task_type not like '%Mile%' and aps_code is not null
    and work_package_type not in ('MFG')
    and target_equip_qty = 0
    and target_work_qty > 0
    group by proj_id) subquery";
    */

    // Note: for AAI - commented out in sql below:
    //and task_type not like '%Mile%'
    //and target_equip_qty = 0
    //and target_work_qty > 0
    // Note: added  and ev_method<>'LOE'
    //Note: changed
    //and work_package_type not in ('MFG')
    //to
    //and (work_package_type not in ('MFG','MTL')  or work_package_type='' or work_package_type is null)
    $sql = "select first_aps,first_bls,last_blf,last_apf,datediff(last_apf,first_aps) as td_ap,datediff('".USDate2UnixDate($data_date)."',first_bls) as dur_til_dd,datediff(last_blf,first_bls) as td_bl,
            datediff(last_blf,'".USDate2UnixDate($data_date)."') as rd_bl,datediff(last_apf,'".USDate2UnixDate($data_date)."') as rd_ap

    from
        (
            select min(baseline_start) as first_bls, max(baseline_finish) as last_blf,max(finish) as last_apf,min(start) as first_aps
            from
                data_schedule
            where
                pmid=$pmid
                AND baseline_start<>'0000-00-00 00:00:00'
                and (ev_method<>'LE' and ev_method<>'LOE')
                and (work_package_type not in ('MFG','MTL')  or work_package_type='' or work_package_type is null)
            group by
                pmid
        )
    subquery";

    $rs         = dbCall($sql,$debug);
    $last_blf   = left($rs->fields['last_blf'],10);
    //$last_blf   = $finish_bl;
    $first_aps  = left($rs->fields['first_aps'],10);
    $first_bls  = left($rs->fields['first_bls'],10);
    $rd_bl      = $rs->fields['rd_bl'];
    //$last_apf   = left($rs->fields['last_apf'],10);
    $last_apf   = $finish_ap;
    $rd_ap      = $rs->fields['rd_ap'];
    $td_bl      = $rs->fields['td_bl'];
    $dtdd       = $rs->fields['dur_til_dd'];
    $td_ap      = $rs->fields['td_ap'];

    /*
    $fbls_sql = "select first_aps,first_bls,last_blf,last_apf,datediff(last_apf,first_aps) as td_ap,datediff('".USDate2UnixDate($data_date)."',first_bls) as dur_til_dd,datediff(last_blf,first_bls) as td_bl,datediff(last_blf,'".USDate2UnixDate($data_date)."') as rd_bl,datediff(last_apf,'".USDate2UnixDate($data_date)."') as rd_ap from (select min(baseline_start) as first_bls, max(baseline_finish) as last_blf,max(finish) as last_apf,min(start) as first_aps from schedule where proj_id=$ppm_ap_id  AND baseline_start<>'0000-00-00 00:00:00' and ev_method<>'LE' and task_type not like '%Mile%' and aps_code is not null
    and work_package_type not in ('MFG')
    and target_equip_qty = 0
    and target_work_qty > 0
    group by proj_id) subquery";
    $fbls_rs    = dbCall($fbls_sql,$debug,'schedule_data',$db_server);
    $first_bls  = left($fbls_rs->fields['first_bls'],10);
    */

    $z .=  "<hr><b>4)&nbsp;</b>last_blf=$last_blf | first_aps=$first_aps | first_bls=$first_bls | rd_bl=$rd_bl | last_apf=$last_apf | rd_ap=$rd_ap | td_bl=$td_bl | dtdd=$dtdd | td_ap=$td_ap\n<hr>";
    //string2file($log_file,"\nlast_blf=$last_blf | first_aps=$first_aps | first_bls=$first_bls | rd_bl=$rd_bl | last_apf=$last_apf | rd_ap=$rd_ap | td_bl=$td_bl | dtdd=$dtdd | td_ap=$td_ap\n",'a');

    $rd_ap = datediff('d',strtotime($data_date),strtotime($finish_ap));
    $rd_bl = datediff('d',strtotime($data_date),strtotime($finish_bl));
    $tspit_date_diff = datediff('d',strtotime($spit_date),strtotime($finish_ap));

    // calcs for es and us
    $es = DateDiff('d',UnixDate2TimeStamp($first_bls),USDate2TimeStamp($date_for_pos));
    //$us = $td_bl - $es;

    // pos = P overtakes S
    $z .=  "<hr><b>5)&nbsp;</b>pos=$date_for_pos|finish_bl=$finish_bl\n<hr>";
    //string2file($log_file,"\npos=$date_for_pos|finish_bl=$finish_bl\n",'a');
    //$us = DateDiff('d',strtotime(USDate2UnixDate($date_for_pos)),strtotime($finish_bl));

     // below 4 Yancy -- tallums 1-8-10
    $bcwp_sql = "SELECT enddate FROM data_cost_ws_reporting_periods WHERE reporting_period='$cp'";
    $bcwp_rs = dbCall($bcwp_sql,$debug);
    $bcwp_date = $bcwp_rs->fields['enddate'];

    if($bcwp_date == '')    // get last day of month for $cp if there was no endate in cprdate
    {
        $cp_yr = substr($cp,0,4);
        $cp_mo = substr($cp,4,2);
        $bcwp_date = date('Y-m-d',mktime(0, 0, 0, $cp_mo + 1 ,0, $cp_yr));
    }

    $lapse              = DateDiff('d',strtotime($bcwp_date),strtotime($data_date));
    $us                 = DateDiff('d',strtotime(USDate2UnixDate($date_for_pos)),strtotime($finish_bl))-$lapse;

    $z .=  "<hr><b>6)&nbsp;</b>bcwp_date=$bcwp_date\n<hr>";
    //string2file($log_file,"\nbcwp_date=$bcwp_date\n",'a');

    //$us = $td_bl - $es;

    // GET FIRST ACTIVE PLAN DATE INSTEAD OF FIRST BLS
    $z .=  "<hr><b>7)&nbsp;</b>first_bls:$first_bls | data_date:$data_date\n<hr>";
    //string2file($log_file,"\nfirst_bls:$first_bls|data_date:$data_date\n",'a');

    //$denom = round((DateDiff('d',UnixDate2TimeStamp($first_bls),USDate2TimeStamp($data_date))),2);
    //$denom = round((DateDiff('d',UnixDate2TimeStamp($first_bls),UnixDate2TimeStamp($spit_date))),2);
    //$denom = round((DateDiff('d',UnixDate2TimeStamp($first_bls),UnixDate2TimeStamp($bcwp_date))),2);
    $denom = round((DateDiff('d',UnixDate2TimeStamp($first_aps),UnixDate2TimeStamp($bcwp_date))),2);

    $z .=  "<hr><b>8)&nbsp;</b>denom=$denom\n<hr>";
    //string2file($log_file,"\ndenom=$denom\n",'a');
    $spit       = round(($es/$denom),2);
    $z .=  "<hr><b>9)&nbsp;</b>spit=$spit\n<hr>";
    //string2file($log_file,"\nspit=$spit\n",'a');
    $tspit      = round(($us/$rd_ap),2);
    //$tspit      = round(($us/$tspit_date_diff),2);

    $z .=  "<hr><b>10)&nbsp;</b>month=$month|num_days_in_month=$num_days_in_month|days_to_add=$days_to_add|converted_date=$converted_date|date_for_pos=$date_for_pos|first_bls=$first_bls|data_date=$data_date|es=$es|us=$us|spit=$spit|tspit=$tspit<hr>";
    //string2file($log_file,"\nmonth=$month|num_days_in_month=$num_days_in_month|days_to_add=$days_to_add|converted_date=$converted_date|date_for_pos=$date_for_pos|first_bls=$first_bls|data_date=$data_date|es=$es|us=$us|spit=$spit|tspit=$tspit\n",'a');

    //$z .=  "rd_ap=$rd_ap|last_apf=$last_apf";
    $z .=  "<hr><b>11)&nbsp;</b>rd_bl=$rd_bl|last_blf=$last_blf|first_bls=$first_bls|lapf=$last_apf<hr>";
    //string2file($log_file,"\nrd_bl=$rd_bl|last_blf=$last_blf|first_bls=$first_bls|lapf=$last_apf\n",'a');
    // get cur total float
    //$sql = "select avg(total_float_hr_cnt/8) as tf from schedule where proj_id=$ppm_ap_id and icp='End' group by proj_id";
    // Note: per Scott - remove: and icp='End'
    $sql = "select avg(total_float_hr_cnt/8) as tf from data_schedule where pmid=$pmid group by pmid";
    $rs = dbCall($sql,$debug);
    $tf_cur = round($rs->fields['tf']);

    // get minus 3 months total float
    $the_date = date("mY",time());
    $y = right($the_date,4);
    $m = left($the_date,2);

    if($m>3)
    {
        $m -= 3;
    }
    else
    {
        switch("$m")
        {
            case "03":
                $m = 12;
                break;
            case "02":
                $m = 11;
                break;
            case "01":
                $m = 10;
                break;
        }
        $y--;
    }
    if(strlen($m)<2) $m = "0$m";

    // Notes: At AAI, for $tf_minus3, if schedule tables are not (yet) being archived, use $tf_cur
    $sql = "SELECT table_name FROM information_schema.TABLES WHERE table_name LIKE 'data_schedule_".$m."%".$y."%' ORDER BY table_name DESC LIMIT 1";
    $rs  = dbCall($sql,$debug,'information_schema');
    $table = $rs->fields['table_name'];
    if($table=='')
    {
        $tf_minus3 = $tf_cur;
    }
    else
    {
        //$sql = "select round(total_float_hr_cnt/8) as tf from schedule_data_archive.$table where proj_id=$ppm_ap_id and icp='End' group by proj_id";
        // Note: per Scott - remove: and icp='End'
        $sql = "select round(total_float_hr_cnt/8) as tf from schedule_data_archive.$table where pmid=$pmid group by pmid";
        $rs  = dbCall($sql,$debug);
        $tf_minus3 = round($rs->fields['tf']);
        if($tf_minus3==0 or $tf_minus3=='') $tf_minus3 = $tf_cur;
    }

    // get schedule metrics
    $sql = "
    select
       sum(case when
           (EV_Method <> 'LOE' or ev_method is null)
           and (status_code<>'Complete' or status_code is null)
        then 1 else 0 end) as discrete_open_tasks,

       sum(case when
           (status_code!='Complete' or status_code is null)
           and (cstr_type is not null and cstr_type<>'' and cstr_type<>'As Soon As Possible' and cstr_type<>'As Late As Possible'
           and cstr_type<>'Start No Earlier Than' and cstr_type<>'Finish No Earlier Than')
           and (EV_Method <> 'LOE' or ev_method is null)
       then 1 else 0 end) as constraint_problems,

       sum(case when
        (ev_method<>'LOE' or ev_method is null)
        and     (
                (
                    (num_pred is null or num_pred<=0)
                    and status_code='Future Task'
                )
                or
                (
                    (status_code<>'Complete' or status_code is null)
                    and (num_succ<=0 or num_succ is null)
                )
            )
        then 1 else 0 end) as logic_problems,

       sum(case when
            (ev_method<>'LOE' or ev_method is null)
            and (status_code<>'Complete' or status_code is null)
       then 1 else 0 end) as logic_problems_bottom,

       sum(case when
           (EV_Method <> 'LOE' or ev_method is null)
           and status_code='Complete'
           and baseline_finish is not null
        then 1 else 0 end) as tasks_completed_with_baselines,

       sum(case when
           (EV_Method <> 'LOE' or ev_method is null)
           and date(baseline_finish)<date(status_date)
       then 1 else 0 end) as tasks_baselined_to_complete

    from
        data_schedule s
    where
        pmid=$pmid
    group by
        pmid
    ";
    $rs = dbCall($sql,$debug);

    $open_tasks    = $rs->fields['discrete_open_tasks'];
    $lp            = $rs->fields['logic_problems'];
    $lpb           = $rs->fields['logic_problems_bottom'];
    //$logic         = round(($logic_problems/$logic_problems_bottom));
    $logic         = round(($lp/$lpb)*100);

    $cp_top        = $rs->fields['constraint_problems'];
    //$constraint    = round(($cp_top/$open_tasks));
    $constraint    = round(($cp_top/$open_tasks)*100);

    $tcw           = $rs->fields['tasks_completed_with_baselines'];
    $tbc           = $rs->fields['tasks_baselined_to_complete'];
    $bei           = round(($tcw/$tbc),3);

    $z .=  "<hr><b>12)&nbsp;</b>bei=$bei<hr>";
    //string2file($log_file,"\nbei=$bei\n",'a');

    // unstatused
    // Notes: At AAI, if unstatused tables are not (yet) being used, use zero
    $usql = "select table_name from tables where table_name = 'data_unstatused'";
    $urs  = dbCall($usql,$debug,'information_schema');
    $unstatused_schema = $urs->fields['schema_name'];
    if($unstatused_schema=='')
    {
        $unstatused = 0;
        $statused = 0;
        $pu = 0;
    }
    else
    {
        $table = 'data_unstatused';
        $sql = "select count(task_code) as num from $table u where status='Unstatused' and pmid=$pmid and ((select dashboard from master_project where id=u.pmid limit 1)=1)";
        $rs  = dbCall($sql,$debug);
        $unstatused = $rs->fields['num'];
        //$sql = "select count(task_code) as num from $table where status='Statused' and  proj_id=$ppm_ap_id";
        $sql = "select count(task_code) as num from $table u where status='Statused' and pmid=$pmid and ((select dashboard from master_project where id=u.pmid limit 1)=1)";
        $rs  = dbCall($sql,$debug);
        $statused = $rs->fields['num'];
        $pu = round(($unstatused / ($statused + $unstatused))*100);
    }

    // perform the tests
    // TEST 1
    $test1 = round($us/$spit);
    $test2 = round($rd_ap + ((($tf_minus3 - $tf_cur)/90)*($rd_ap/2)));
    //$test2 = round((($td_bl) + ($tf_cur-$tf_minus3))/((63*$rd_ap)/(2+$rd_ap)),2);
    $z .=  "<hr><b>13)&nbsp;</b>dtdd:$dtdd + td_bl:$td_bl - es:$es<hr>";
    //string2file($log_file,"\ndtdd:$dtdd + td_bl:$td_bl - es:$es\n",'a');
    //$test3 = $dtdd + $td_bl - $es;
    $test3 = $us;
    $test4 = 0; // MISSING SPI NO LOE
    $test5 = round($us / $bei);
    $test6 = .4*($logic) + .2*($constraint) + .4*($pu);
    $test7 = round(abs($tspit-$spit)*100);

    $first_bls_ts = UnixDate2TimeStamp(left($first_bls,10));
    $z .=  "<hr><b>14)&nbsp;</b>first_bls=$first_bls|first_bls_ts=$first_bls_ts<hr>";
    //string2file($log_file,"\nfirst_bls=$first_bls|first_bls_ts=$first_bls_ts\n",'a');
    $test1_date = USDate2UnixDate(dateadd('d',$test1,$first_bls_ts));
    $test2_date = USDate2UnixDate(dateadd('d',$test2,$first_bls_ts));
    $test3_date = USDate2UnixDate(dateadd('d',$test3,$first_bls_ts));
    $test5_date = USDate2UnixDate(dateadd('d',$test5,$first_bls_ts));

    $date_for_pos = USDate2UnixDate($date_for_pos);

    $z .= "date_for_pos:$date_for_pos<br>";

    // insert into database table
    /*
    $sql = "insert into cost_data.iecd_data (program_group,projectid,date1,bcwpf_month,bcwpf_days,bcwpf_total_days,bcwpf_last_day,spit,bcwp_cum,ap_start,ap_finish,bl_start,bl_finish) values
    ('$program_group',$projid,'$cp','$the_month',$more_days,$total_days,$last_day,$spit,$bcwp,'$first_bls','$last_apf','$first_bls','$finish_bl')
    ";
    */
        //condition variables so that inserts always happen (i.e., do not fail because a variable is empty)
    if($pmid == '') $pmid = 'NULL';
    if($cp == '')
    {
        $cp = 'NULL';
    }
    else
    {
        $cp = "'$cp'";
    }
    if($the_month == '') $the_month = 'NULL';
    if($more_days == '') $more_days = 'NULL';
    if($total_days == '') $total_days = 'NULL';
    if($last_day == '') $last_day = 'NULL';
    if($spit == '') $spit = 'NULL';
    if($bcwp == '') $bcwp = 'NULL';
    if($first_aps == '')
    {
        $first_aps = 'NULL';
    }
    else
    {
        $first_aps = "'$first_aps'";
    }
    if($last_apf == '')
    {
        $last_apf = 'NULL';
    }
    else
    {
        $last_apf = "'$last_apf'";
    }
    if($first_bls == '')
    {
        $first_bls = 'NULL';
    }
    else
    {
        $first_bls = "'$first_bls'";
    }
    if($finish_bl == '')
    {
        $finish_bl = 'NULL';
    }
    else
    {
        $finish_bl = "'$finish_bl'";
    }
    if($date_for_pos == '')
    {
        $date_for_pos = 'NULL';
    }
    else
    {
        $date_for_pos = "'$date_for_pos'";
    }
    if($bcwp_date == '')
    {
        $bcwp_date = 'NULL';
    }
    else
    {
        $bcwp_date = "'$bcwp_date'";
    }

    $sql = "insert into data_iecd (pmid,date1,bcwpf_month,bcwpf_days,bcwpf_total_days,bcwpf_last_day,spit,bcwp_cum,ap_start,ap_finish,bl_start,bl_finish,pos_date,bcwp_date) values
    ($pmid,$cp,$the_month,$more_days,$total_days,$last_day,$spit,$bcwp,$first_aps,$last_apf,$first_bls,$finish_bl,$date_for_pos,$bcwp_date)
    ";
    $junk = dbCall($sql,$debug);

    string2file($log_file,"\n$sql\n",'a');

    //condition variables so that updates always happen (i.e., do not fail because a variable is empty)
    if($data_date == '')
    {
        $data_date = 'NULL';
    }
    else
    {
        $data_date = "'".USDate2UnixDate($data_date)."'";
    }
    if($rd_bl == '') $rd_bl = 'NULL';
    if($td_bl == '') $td_bl = 'NULL';
    if($rd_ap == '') $rd_ap = 'NULL';
    if($tf_cur == '') $tf_cur = 'NULL';
    if($tf_minus3 == '') $tf_minus3 = 'NULL';
    if($es == '') $es = 'NULL';
    if($us == '') $us = 'NULL';
    if($bei == '') $bei = 'NULL';
    if($logic == '') $logic = 'NULL';
    if($constraint == '') $constraint = 'NULL';
    if($pu == '') $pu = 'NULL';
    if($test1 == '') $test1 = 'NULL';
    if($test2 == '') $test2 = 'NULL';
    if($test3 == '') $test3 = 'NULL';
    if($test4 == '') $test4 = 'NULL';
    if($test5 == '') $test5 = 'NULL';
    if($test6 == '') $test6 = 'NULL';
    if($test7 == '') $test7 = 'NULL';
    if($spit == '') $spit = 'NULL';
    if($tspit == '') $tspit = 'NULL';
    if($dtdd == '') $dtdd = 'NULL';
    if($test1_date == '')
    {
        $test1_date = 'NULL';
    }
    else
    {
        $test1_date = "'$test1_date'";
    }
    if($test2_date == '')
    {
        $test2_date = 'NULL';
    }
    else
    {
        $test2_date = "'$test2_date'";
    }
    if($test3_date == '')
    {
        $test3_date = 'NULL';
    }
    else
    {
        $test3_date = "'$test3_date'";
    }
    if($test5_date == '')
    {
        $test5_date = 'NULL';
    }
    else
    {
        $test5_date = "'$test5_date'";
    }
    if($td_ap == '') $td_ap = 'NULL';

    // store the data
    $sql = "update data_iecd set data_date=$data_date,rd_bl=$rd_bl,td_bl=$td_bl,rd_ap=$rd_ap,tf_cur=$tf_cur,
                    tf_minus3=$tf_minus3,`es`=$es,`us`=$us,`bei`=$bei,`logic`=$logic,`constraint`=$constraint,unstatused=$pu,
                    test1=$test1,test2=$test2,test3=$test3,test4=$test4,test5=$test5,test6=$test6,test7=$test7,spit=$spit,tspit=$tspit,dur_to_dd=$dtdd,
                    test1_date=$test1_date,test2_date=$test2_date,test3_date=$test3_date,test5_date=$test5_date,lf_ap=$last_apf,td_ap=$td_ap
           where pmid=$pmid and date1=$cp";
    $junk = dbCall($sql,$debug);

    string2file($log_file,"\n$sql\n\n\n\n",'a');

    //reset variables for next loop
    $pmid           = '';
    $cp             = '';
    $the_month      = '';
    $more_days      = '';
    $total_days     = '';
    $last_day       = '';
    $spit           = '';
    $bcwp           = '';
    $first_aps      = '';
    $last_apf       = '';
    $first_bls      = '';
    $finish_bl      = '';
    $date_for_pos   = '';
    $bcwp_date      = '';
    $data_date      = '';
    $rd_bl          = '';
    $td_bl          = '';
    $rd_ap          = '';
    $tf_cur         = '';
    $tf_minus3      = '';
    $es             = '';
    $us             = '';
    $bei            = '';
    $logic          = '';
    $constraint     = '';
    $pu             = '';
    $test1          = '';
    $test2          = '';
    $test3          = '';
    $test4          = '';
    $test5          = '';
    $test6          = '';
    $test7          = '';
    $spit           = '';
    $tspit          = '';
    $dtdd           = '';
    $test1_date     = '';
    $test2_date     = '';
    $test3_date     = '';
    $test5_date     = '';
    $td_ap          = '';

    //string2file('temp/iecd.txt',"\n$z\n\n\n\n",'a');

    if($debug) { print $z; }

    return true;
}
// ------------------------------------------------------------------
function loadIECDDataIB($pmid,$debug=false)
{
    // get latest date for bcwp and bcws
    $sql = "
        SELECT
            max(date1) as the_date
        FROM
            data_cost
        WHERE
            type='BCWP'
            AND pmid=$pmid
            AND hours1>0
        ";
    $drs = dbCall_IB($sql,$debug);
    $cp = $drs->fields['the_date'];

    $sql = "SELECT company,premier_name,program_type FROM master_project WHERE id=$pmid limit 1";
    $ptrs = dbCall_IB($sql,$debug);
    $company      = $ptrs->fields['company'];
    $program_type = $ptrs->fields['program_type'];
    $premier_name = $ptrs->fields['premier_name'];

    $z .= "<br><br><br><br><hr><b>1)&nbsp;</b>premier_name=$premier_name | cp=$cp<hr>";

    // remove current data
    $sql = "delete from data_iecd_buckets where pmid=$pmid";
    $junk = dbCall_IB($sql,$debug);

    $sql = "delete from data_iecd where pmid=$pmid and date1='$cp'";
    $junk = dbCall_IB($sql,$debug);

    // get end date, bl finish and tf from icp-end milestone
    //max(finish) as last_apf
    //$sql = "select left(finish,10) as finish,left(baseline_finish,10) as baseline_finish,(total_float_hr_cnt/8) as tf_ap from schedule where proj_id=$ppm_id and icp='End'";
    // Note: per Scott - remove: and icp='End'
    $sql = "
        SELECT
            LEFT(MAX(finish),10) AS finish
            ,LEFT(MAX(baseline_finish),10) AS baseline_finish
            ,(total_float_hr_cnt/8) AS tf_ap
        from
            data_schedule
        where
            pmid=$pmid
        GROUP BY
            pmid
    ";
    $rs = dbCall_IB($sql,$debug);
    $finish_ap      = $rs->fields['finish'];
    $finish_bl      = $rs->fields['baseline_finish'];
    $tf_ap          = $rs->fields['tf_ap'];

    $z .= "<hr><b>2)&nbsp;</b>premier_name=$premier_name | program_type=$program_type | ppm_id=$ppm_id | finish_ap = $finish_ap | finish_bl = $finish_bl | tf_ap=$tf_ap<hr>";
     //string2file($test_file,"\"$premier_name\",\"$program_type\",\"$ppm_id\",\"$finish_ap\",\"$finish_bl\",\"$tf_ap\"\r\n",'a');

    //$basisname = 'Baseline';

    // get buckets
    // Note: commented out the following two lines out of where clause:
    //sr.BASISNAME='$basisname' AND
    //sr.burdenname LIKE '0%' AND
    //changed the below line
    //ca.control_account=sr.controlaccount AND
    $sql = "
    insert into data_iecd_buckets (pmid,date1,bcwp,bcws) (
        SELECT
          cd.pmid,
          cd.date1,
        SUM(CASE WHEN cd.TYPE='BCWP' AND cd.date1<='$cp' THEN ((cd.hours1)) ELSE 0 END) AS bcwp,
        SUM(CASE WHEN cd.TYPE='BCWS' AND cd.date1<='$cp' THEN ((cd.hours1)) ELSE 0 END) AS bcws
        FROM
            data_cost cd,
            main mv
        WHERE
            mv.pmid=cd.pmid
            and mv.cmid=cd.cmid
            and cd.type IN ('BCWP','BCWS')
            and cd.pmid=$pmid
            and cd.date1<='$cp'
        GROUP BY
          cd.pmid,
          cd.date1
        ORDER BY
          cd.date1
        )
    ";
    $rs = dbCall_IB($sql,$debug);
    //exit();

    // get running totals
    $sql = "select id,bcwp,bcws from data_iecd_buckets where pmid=$pmid order by date1";
    $rs = dbCall_IB($sql,$debug);
    $bcwp = 0;
    $bcws = 0;
    while(!$rs->EOF)
    {
        $id      = $rs->fields['id'];
        $bcwp   += $rs->fields['bcwp'];
        $bcws   += $rs->fields['bcws'];
        $bcwp    = round($bcwp,2);
        $bcws    = round($bcws,2);

        $junk = dbCall_IB("update data_iecd_buckets set bcwp_rt=$bcwp, bcws_rt=$bcws where id=$id",$debug);
        $rs->MoveNext();
    }

    // get cum bcwp
    //Note: for AAI - comented out below line from where clause
    //sr.BASISNAME='$basisname'

    $basisname_wc = '';
    if($company=='Bell' or $company=='Mirabel') $basisname_wc = "and basisname='$basisname'";

    $sql = "SELECT
                round(sum(hours1)) as bcwp
            FROM
                data_cost
            WHERE
                type='BCWP'
                and pmid=$pmid
                and date1<='$cp'
        ";
       $rs = dbCall_IB($sql,$debug);
       $bcwp = $rs->fields['bcwp'];

    if($bcwp==0)
    {
        $sql = "select id,date1,bcws_rt from data_iecd_buckets where pmid=$pmid and bcws_rt<=0 order by date1";
    }
    else
    {
        $sql = "select id,date1,bcws_rt from data_iecd_buckets where pmid=$pmid and bcws_rt<=$bcwp order by date1";
    }

    // bcwp is the largest (latest) bcwp
    // now see where bcwp fits into the bcws schedule

    $rs = dbCall_IB($sql,$debug);
    $i=0;
    $last_day = 0;
    while(!$rs->EOF)
    {
        $i++;
        $id     = $rs->fields['id'];
        $date1  = $rs->fields['date1'];
        $bcws   = $rs->fields['bcws_rt'];
        $days   = $i * 30;

        if($date1==$cp) $last_day = $days;
        //$z .=  "i=$i|days=$days|id=$id|date1=$date1|bcws=$bcws|last_day=$last_day<br>\n";
          //string2file($log_file,"\ni=$i|days=$days|id=$id|date1=$date1|bcws=$bcws|last_day=$last_day\n",'a');

        $rs->MoveNext();
    }
    $the_month = $date1;

    $id = $id+1;
    $bcws_lower_bound = $bcws;

    // now get the next bcws_rt record
    $sql = "select bcws_rt from data_iecd_buckets where pmid=$pmid and id=$id ";
    $rs = dbCall_IB($sql,$debug);
    $bcws_upper_bound = $rs->fields['bcws_rt'];

    // get the total days for the cp
    $sql = "select id from data_iecd_buckets where pmid=$pmid and date1<='$cp'";
    $rs = dbCall_IB($sql,$debug);
    $last_day = $rs->RecordCount() * 30;

    $p          = abs(round(($bcwp-$bcws_lower_bound)/($bcws_upper_bound-$bcws_lower_bound),3));
    $more_days  = round(30-(30*$p)); // number of days into the month where p overtakes s
    $total_days = $days+$more_days;
    //$spit       = round($total_days/$last_day,2);
    //$es         = $total_days;
   //$us         = $last_day-$es;

    // get date p overtakes s
    // step 1, get number of days in month
    $month = right($date1,2);
    $num_days_in_month = substr(lastDayOfMonth($month),3,2);
    $days_to_add = round($p*$num_days_in_month) + $num_days_in_month;   // note - per Yancy add a month to POS
    $converted_date = UnixDate2TimeStamp(left($date1,4) .'-'. right($date1,2) .'-'. "01");
    $date_for_pos = DateAdd('d',$days_to_add,$converted_date);

    $cd4y = left($date1,4) .'-'. right($date1,2) .'-'. "01";

    $z .=  "<hr><b>3)&nbsp;</b>date1=$date1|converted_date=$cd4y|date_for_pos=$date_for_pos|bcwp=$bcwp|bcws_lower_bound=$bcws_lower_bound|bcws_upper_bound=$bcws_upper_bound|p=$p|more_days=$more_days|total_days=$total_days|last_day=$last_day|spit=$spit\n<hr>";
    //string2file($log_file,"\ndate1=$date1|converted_date=$converted_date|date_for_pos=$date_for_pos|bcwp=$bcwp|bcws_lower_bound=$bcws_lower_bound|bcws_upper_bound=$bcws_upper_bound|p=$p|more_days=$more_days|total_days=$total_days|last_day=$last_day|spit=$spit\n",'a');

    // get the data date
    // Note: for AAI - using status_date from schedule table - i.e. not Oracle
    if($company=='Bell' or $company=='Mirabel')
    {
        $sql = "select ppm_ap_id,ppm_bl_id,left(ppm_ap_data_date,10) as ppm_ap_data_date from master_project where id=$pmid";
        $rs = dbCall_IB($sql,$debug);
        $ppm_ap_id = $rs->fields['ppm_ap_id'];
        $ppm_bl_id = $rs->fields['ppm_bl_id'];
        $data_date = UnixDate2USDate($rs->fields['ppm_ap_data_date']);
    }
    else
    {
        $sql = "SELECT LEFT(MAX(ppm_ap_data_date),10) AS status_date FROM master_project WHERE id=$pmid";
        $rs = dbCall($sql,$debug);
        $data_date = UnixDate2USDate($rs->fields['status_date']);
    }
    // 2010-01-07 per Yancy use bcwp date for calculating spit and tspit instead of data date
    $spit_month                      = right($the_month,2);
    $spit_year                       = left($the_month,4);
    $spit_num_days_in_month          = substr(lastDayOfMonth($spit_month),3,2);
    $spit_date                       = "$spit_year-$spit_month-$spit_num_days_in_month";

    // get RD BL
    //$sql = "select first_bls,last_blf,last_apf,datediff(last_apf,first_aps) as td_ap,datediff('".USDate2UnixDate($data_date)."',first_bls) as dur_til_dd,datediff(last_blf,first_bls) as td_bl,datediff(last_blf,'".USDate2UnixDate($data_date)."') as rd_bl,datediff(last_apf,'".USDate2UnixDate($data_date)."') as rd_ap from (select min(baseline_start) as first_bls, max(baseline_finish) as last_blf,max(finish) as last_apf,min(start) as first_aps from schedule where proj_id=$ppm_ap_id  AND baseline_start<>'0000-00-00 00:00:00' and ev_method<>'LE' and task_type not like '%Mile%' and aps_code is not null group by proj_id) subquery";
    //first_aps
    //$sql = "select first_aps,first_bls,last_blf,last_apf,datediff(last_apf,first_aps) as td_ap,datediff('".USDate2UnixDate($data_date)."',first_bls) as dur_til_dd,datediff(last_blf,first_bls) as td_bl,datediff(last_blf,'".USDate2UnixDate($data_date)."') as rd_bl,datediff(last_apf,'".USDate2UnixDate($data_date)."') as rd_ap from (select min(baseline_start) as first_bls, max(baseline_finish) as last_blf,max(finish) as last_apf,min(start) as first_aps from schedule where proj_id=$ppm_ap_id  AND baseline_start<>'0000-00-00 00:00:00' and ev_method<>'LE' and task_type not like '%Mile%' and aps_code is not null group by proj_id) subquery";
    /*
    $sql = "select first_aps,first_bls,last_blf,last_apf,datediff(last_apf,first_aps) as td_ap,datediff('".USDate2UnixDate($data_date)."',first_bls) as dur_til_dd,datediff(last_blf,first_bls) as td_bl,datediff(last_blf,'".USDate2UnixDate($data_date)."') as rd_bl,datediff(last_apf,'".USDate2UnixDate($data_date)."') as rd_ap from (select min(baseline_start) as first_bls, max(baseline_finish) as last_blf,max(finish) as last_apf,min(start) as first_aps from schedule where proj_id=$ppm_ap_id  AND baseline_start<>'0000-00-00 00:00:00' and ev_method<>'LE' and task_type not like '%Mile%' and aps_code is not null
    and work_package_type not in ('MFG')
    and target_equip_qty = 0
    and target_work_qty > 0
    group by proj_id) subquery";
    */

    // Note: for AAI - commented out in sql below:
    //and task_type not like '%Mile%'
    //and target_equip_qty = 0
    //and target_work_qty > 0
    // Note: added  and ev_method<>'LOE'
    //Note: changed
    //and work_package_type not in ('MFG')
    //to
    //and (work_package_type not in ('MFG','MTL')  or work_package_type='' or work_package_type is null)

    if($company=='Bell' or $company=='Mirabel')
    {
        $sql = "select first_aps,first_bls,last_blf,last_apf,datediff(last_apf,first_aps) as td_ap,datediff('".USDate2UnixDate($data_date)."',first_bls) as dur_til_dd,datediff(last_blf,first_bls) as td_bl,
                datediff(last_blf,'".USDate2UnixDate($data_date)."') as rd_bl,datediff(last_apf,'".USDate2UnixDate($data_date)."') as rd_ap

        from
            (
                select min(baseline_start) as first_bls, max(baseline_finish) as last_blf,max(finish) as last_apf,min(start) as first_aps
                from
                    data_schedule
                where
                    pmid=$pmid
                    and baseline_start<>'0000-00-00 00:00:00'
                    and ev_method<>'LE'
                    and task_type not like '%Mile%'
                    and work_package_type not in ('MFG')
                    and target_equip_qty = 0
                    and target_work_qty > 0
                group by
                    pmid
            )
        subquery";
    }
    else
    {
        $sql = "select first_aps,first_bls,last_blf,last_apf,datediff(last_apf,first_aps) as td_ap,datediff('".USDate2UnixDate($data_date)."',first_bls) as dur_til_dd,datediff(last_blf,first_bls) as td_bl,
                datediff(last_blf,'".USDate2UnixDate($data_date)."') as rd_bl,datediff(last_apf,'".USDate2UnixDate($data_date)."') as rd_ap

        from
            (
                select min(baseline_start) as first_bls, max(baseline_finish) as last_blf,max(finish) as last_apf,min(start) as first_aps
                from
                    data_schedule
                where
                    pmid=$pmid
                    and baseline_start<>'0000-00-00 00:00:00'
                    and (ev_method<>'LE' and ev_method<>'LOE')
                    and (work_package_type not in ('MFG','MTL')  or work_package_type='' or work_package_type is null)
                group by
                    pmid
            )
        subquery";
    }

    $rs         = dbCall_IB($sql,$debug);
    $last_blf   = left($rs->fields['last_blf'],10);
    //$last_blf   = $finish_bl;
    $first_aps  = left($rs->fields['first_aps'],10);
    $first_bls  = left($rs->fields['first_bls'],10);
    $rd_bl      = $rs->fields['rd_bl'];
    //$last_apf   = left($rs->fields['last_apf'],10);
    $last_apf   = $finish_ap;
    $rd_ap      = $rs->fields['rd_ap'];
    $td_bl      = $rs->fields['td_bl'];
    $dtdd       = $rs->fields['dur_til_dd'];
    $td_ap      = $rs->fields['td_ap'];

    /*
    $fbls_sql = "select first_aps,first_bls,last_blf,last_apf,datediff(last_apf,first_aps) as td_ap,datediff('".USDate2UnixDate($data_date)."',first_bls) as dur_til_dd,datediff(last_blf,first_bls) as td_bl,datediff(last_blf,'".USDate2UnixDate($data_date)."') as rd_bl,datediff(last_apf,'".USDate2UnixDate($data_date)."') as rd_ap from (select min(baseline_start) as first_bls, max(baseline_finish) as last_blf,max(finish) as last_apf,min(start) as first_aps from schedule where proj_id=$ppm_ap_id  AND baseline_start<>'0000-00-00 00:00:00' and ev_method<>'LE' and task_type not like '%Mile%' and aps_code is not null
    and work_package_type not in ('MFG')
    and target_equip_qty = 0
    and target_work_qty > 0
    group by proj_id) subquery";
    $fbls_rs    = dbCall($fbls_sql,$debug,'schedule_data',$db_server);
    $first_bls  = left($fbls_rs->fields['first_bls'],10);
    */

    $z .=  "<hr><b>4)&nbsp;</b>last_blf=$last_blf | first_aps=$first_aps | first_bls=$first_bls | rd_bl=$rd_bl | last_apf=$last_apf | rd_ap=$rd_ap | td_bl=$td_bl | dtdd=$dtdd | td_ap=$td_ap\n<hr>";
    //string2file($log_file,"\nlast_blf=$last_blf | first_aps=$first_aps | first_bls=$first_bls | rd_bl=$rd_bl | last_apf=$last_apf | rd_ap=$rd_ap | td_bl=$td_bl | dtdd=$dtdd | td_ap=$td_ap\n",'a');

    $rd_ap = datediff('d',strtotime($data_date),strtotime($finish_ap));
    $rd_bl = datediff('d',strtotime($data_date),strtotime($finish_bl));
    $tspit_date_diff = datediff('d',strtotime($spit_date),strtotime($finish_ap));

    // calcs for es and us
    $es = DateDiff('d',UnixDate2TimeStamp($first_bls),USDate2TimeStamp($date_for_pos));
    //$us = $td_bl - $es;

    // pos = P overtakes S
    $z .=  "<hr><b>5)&nbsp;</b>pos=$date_for_pos|finish_bl=$finish_bl\n<hr>";
    //string2file($log_file,"\npos=$date_for_pos|finish_bl=$finish_bl\n",'a');
    //$us = DateDiff('d',strtotime(USDate2UnixDate($date_for_pos)),strtotime($finish_bl));

     // below 4 Yancy -- tallums 1-8-10
    //$bcwp_sql = "SELECT enddate FROM cprdate WHERE cprdate='$cp'";
    //$bcwp_rs = dbCall($bcwp_sql,$debug,'tools_data',$db_server);
    //$bcwp_date          = $bcwp_rs->fields['enddate'];

    // use last day of month for bcwp date so that we do not have
    // to look up the acct calendar
    $bcwp_y = left($cp,4);
    $bcwp_m = right($cp,2);
    $bcwp_d = lastDayOfMonth("$bcwp_m/15/$bcwp_y");
    //$bcwp_date = $bcwp_y . "-" . addZero($bcwp_m) . "-" . addZero($bcwp_d);
    $bcwp_date = USDate2UnixDate($bcwp_d);

    $lapse              = DateDiff('d',strtotime($bcwp_date),strtotime($data_date));
    $us                 = DateDiff('d',strtotime(USDate2UnixDate($date_for_pos)),strtotime($finish_bl))-$lapse;

    $z .=  "<hr><b>6)&nbsp;</b>bcwp_date=$bcwp_date\n<hr>";
    //string2file($log_file,"\nbcwp_date=$bcwp_date\n",'a');

    //$us = $td_bl - $es;

    // GET FIRST ACTIVE PLAN DATE INSTEAD OF FIRST BLS
    $z .=  "<hr><b>7)&nbsp;</b>first_bls:$first_bls | data_date:$data_date\n<hr>";
    //string2file($log_file,"\nfirst_bls:$first_bls|data_date:$data_date\n",'a');

    //$denom = round((DateDiff('d',UnixDate2TimeStamp($first_bls),USDate2TimeStamp($data_date))),2);
    //$denom = round((DateDiff('d',UnixDate2TimeStamp($first_bls),UnixDate2TimeStamp($spit_date))),2);
    //$denom = round((DateDiff('d',UnixDate2TimeStamp($first_bls),UnixDate2TimeStamp($bcwp_date))),2);
    $denom = round((DateDiff('d',UnixDate2TimeStamp($first_aps),UnixDate2TimeStamp($bcwp_date))),2);

    $z .=  "<hr><b>8)&nbsp;</b>denom=$denom\n<hr>";
    //string2file($log_file,"\ndenom=$denom\n",'a');
    $spit       = round(($es/$denom),2);
    $z .=  "<hr><b>9)&nbsp;</b>spit=$spit\n<hr>";
    //string2file($log_file,"\nspit=$spit\n",'a');
    $tspit      = round(($us/$rd_ap),2);
    //$tspit      = round(($us/$tspit_date_diff),2);

    $z .=  "<hr><b>10)&nbsp;</b>month=$month|num_days_in_month=$num_days_in_month|days_to_add=$days_to_add|converted_date=$converted_date|date_for_pos=$date_for_pos|first_bls=$first_bls|data_date=$data_date|es=$es|us=$us|spit=$spit|tspit=$tspit<hr>";
    //string2file($log_file,"\nmonth=$month|num_days_in_month=$num_days_in_month|days_to_add=$days_to_add|converted_date=$converted_date|date_for_pos=$date_for_pos|first_bls=$first_bls|data_date=$data_date|es=$es|us=$us|spit=$spit|tspit=$tspit\n",'a');

    //$z .=  "rd_ap=$rd_ap|last_apf=$last_apf";
    $z .=  "<hr><b>11)&nbsp;</b>rd_bl=$rd_bl|last_blf=$last_blf|first_bls=$first_bls|lapf=$last_apf<hr>";
    //string2file($log_file,"\nrd_bl=$rd_bl|last_blf=$last_blf|first_bls=$first_bls|lapf=$last_apf\n",'a');
    // get cur total float
    //$sql = "select avg(total_float_hr_cnt/8) as tf from schedule where proj_id=$ppm_ap_id and icp='End' group by proj_id";
    // Note: per Scott - remove: and icp='End'
    $sql = "select avg(total_float_hr_cnt/8) as tf from data_schedule where pmid=$pmid group by pmid";
    $rs = dbCall_IB($sql,$debug);
    $tf_cur = round($rs->fields['tf']);

    // get minus 3 months total float
    $the_date = date("mY",time());
    $y = right($the_date,4);
    $m = left($the_date,2);

    if($m>3)
    {
        $m -= 3;
    }
    else
    {
        switch("$m")
        {
            case "03":
                $m = 12;
                break;
            case "02":
                $m = 11;
                break;
            case "01":
                $m = 10;
                break;
        }
        $y--;
    }
    if(strlen($m)<2) $m = "0$m";

    // Notes: At AAI, for $tf_minus3, if schedule tables are not (yet) being archived, use $tf_cur
    $sql = "SELECT table_name FROM information_schema.TABLES WHERE table_schema='schedule_data_archive' and table_name = 'data_schedule_$y$m' ORDER BY table_name DESC LIMIT 1";
    $rs  = dbCall_IB($sql,$debug,'information_schema');
    $table = $rs->fields['table_name'];
    if($table=='')
    {
        $tf_minus3 = $tf_cur;
    }
    else
    {
        //$sql = "select round(total_float_hr_cnt/8) as tf from schedule_data_archive.$table where proj_id=$ppm_ap_id and icp='End' group by proj_id";
        // Note: per Scott - remove: and icp='End'
        $icp_wc = '';
        if($company=='Bell' or $company=='Mirabel') $icp_wc = " and icp='End'";
        $sql = "select round(total_float_hr_cnt/8) as tf from schedule_data_archive.$table where pmid=$pmid $ipc_wc group by pmid";
        $rs  = dbCall_IB($sql,$debug);
        $tf_minus3 = round($rs->fields['tf']);
        if($tf_minus3==0 or $tf_minus3=='') $tf_minus3 = $tf_cur;
    }

    if($company=='Bell' or $company=='Mirabel')
    {
        // get schedule metrics
        $sql = "
            select
               sum(case when
                       (sc.ev_method <> 'LE' or sc.ev_method is null)
                       and sc.task_name not like '%Cost Collection%'
                       AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
                       and (sc.work_package_type not in ('MFG','MTL')  or sc.work_package_type='' or sc.work_package_type is null)
                    and (sc.status_code<>'TK_Complete' or sc.status_code is null)
                then 1 else 0 end) as discrete_open_tasks,

               sum(case when
                       (sc.status_code!='TK_Complete' or sc.status_code is null)
                       and (sc.aps_code is null or sc.aps_code='')
                       and (sc.cstr_type is not null and sc.cstr_type<>'')
                       and sc.cstr_type not like '%ALAP'
                       and (sc.ev_method <> 'LE' or sc.ev_method is null)
                       AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
                       and (sc.work_package_type not in ('MFG','MTL')  or sc.work_package_type='' or sc.work_package_type is null)
                       and (sc.icp='' or sc.icp is null)
                       and sc.task_name not like '%Cost Collection%'
                then 1 else 0 end) as constraint_problems,

               sum(case when
                    (sc.ev_method<>'LE' or sc.ev_method is null)
                    and sc.task_name not like '%Cost Collection%'
                    and (sc.icp <> 'Ref' or sc.icp is null)
                    AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
                    and
                        (sc.work_package_type not in ('MFG','MTL')  or sc.work_package_type='' or sc.work_package_type is null)
                    and     (
                            (
                                (sc.num_pred is null or sc.num_pred<=0)
                                and sc.status_code='TK_NotStart'
                                and (sc.aps_code='' or sc.aps_code is null)
                                and (sc.icp<>'In' or sc.icp is null)
                            )
                            or
                            (
                                (sc.status_code<>'TK_Complete' or sc.status_code is null)
                                and (sc.num_succ<=0 or sc.num_succ is null)
                                and (sc.aps_code='' or sc.aps_code is null)
                                and (sc.icp not in ('Out','MM','End') or sc.icp is null)
                            )
                        )
                then 1 else 0 end) as logic_problems,

               sum(case when
                    (sc.ev_method<>'LE' or sc.ev_method is null)
                    and sc.task_name not like '%Cost Collection%'
                    and (sc.icp <> 'Ref' or sc.icp is null)
                    AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
                    and (sc.work_package_type not in ('MFG','MTL') or sc.work_package_type='' or sc.work_package_type is null)
                    and (sc.status_code<>'TK_Complete' or sc.status_code is null)
                then 1 else 0 end) as logic_problems_bottom,

               sum(case when
                       (sc.ev_method <> 'LE' or sc.ev_method is null)
                       and (sc.icp<>'Ref' or sc.icp is null)
                    and sc.status_code='TK_Complete'
                    AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
                    and (sc.work_package_type not in ('MFG','MTL') or sc.work_package_type='' or sc.work_package_type is null)
                    and sc.baseline_finish is not null
                    AND sc.baseline_finish <> '0000-00-00 00:00:00'
                then 1 else 0 end) as tasks_completed_with_baselines,

               sum(case when
                       (sc.ev_method <> 'LE' or sc.ev_method is null)
                       and (sc.icp<>'Ref' or sc.icp is null)
                       AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
                    and sc.baseline_finish<sysdate()
                    and (sc.work_package_type not in ('MFG','MTL')  or sc.work_package_type='' or sc.work_package_type is null)
                    AND sc.baseline_finish IS NOT NULL
                    AND sc.baseline_finish <> '0000-00-00 00:00:00'
               then 1 else 0 end) as tasks_baselined_to_complete
            from
                data_schedule sc
            where
                pmid=$pmid
            group by
                pmid
        ";
    }
    else
    {
        // get schedule metrics
        $sql = "
        select
            sum(case when
                   (sc.EV_Method <> 'LOE' or sc.EV_Method is null)
                and (sc.status_code<>'Complete' or sc.status_code is null)
            then 1 else 0 end) as discrete_open_tasks,

            sum(case when
                   (sc.status_code!='Complete' or sc.status_code is null)
                   and (sc.cstr_type is not null and sc.cstr_type<>'' and sc.cstr_type<>'As Soon As Possible' and sc.cstr_type<>'As Late As Possible'
                   and sc.cstr_type<>'Start No Earlier Than' and sc.cstr_type<>'Finish No Earlier Than')
                   and (sc.EV_Method <> 'LOE' or sc.EV_Method is null)
            then 1 else 0 end) as constraint_problems,

            sum(case when
                (sc.EV_Method<>'LOE' or sc.EV_Method is null)
                and     (
                        (
                            (sc.num_pred is null or sc.num_pred<=0)
                            and sc.status_code='Future Task'
                        )
                        or
                        (
                            (sc.status_code<>'Complete' or sc.status_code is null)
                            and (sc.num_succ<=0 or sc.num_succ is null)
                        )
                    )
            then 1 else 0 end) as logic_problems,

            sum(case when
                (sc.EV_Method<>'LOE' or sc.EV_Method is null)
                and (sc.status_code<>'Complete' or sc.status_code is null)
            then 1 else 0 end) as logic_problems_bottom,

            sum(case when
                   (sc.EV_Method <> 'LOE' or sc.EV_Method is null)
                and sc.status_code='Complete'
                and sc.baseline_finish is not null
            then 1 else 0 end) as tasks_completed_with_baselines,

            sum(case when
                   (sc.EV_Method <> 'LOE' or sc.EV_Method is null)
                and date(sc.baseline_finish)<date(sc.status_date)
            then 1 else 0 end) as tasks_baselined_to_complete

        from
            data_schedule sc
        where
            pmid=$pmid
        group by
            pmid
        ";
    }
    $rs = dbCall_IB($sql,$debug);

    $open_tasks    = $rs->fields['discrete_open_tasks'];
    $lp            = $rs->fields['logic_problems'];
    $lpb           = $rs->fields['logic_problems_bottom'];
    //$logic         = round(($logic_problems/$logic_problems_bottom));
    $logic         = round(($lp/$lpb)*100);

    $cp_top        = $rs->fields['constraint_problems'];
    //$constraint    = round(($cp_top/$open_tasks));
    $constraint    = round(($cp_top/$open_tasks)*100);

    $tcw           = $rs->fields['tasks_completed_with_baselines'];
    $tbc           = $rs->fields['tasks_baselined_to_complete'];
    $bei           = round(($tcw/$tbc),3);

    $z .=  "<hr><b>12)&nbsp;</b>bei=$bei<hr>";
    //string2file($log_file,"\nbei=$bei\n",'a');

    // unstatused
    // Notes: At AAI, if unstatused tables are not (yet) being used, use zero
    if($company!='Bell' and $company!='Mirabel')
    {
        //$usql = "select table_name from tables where table_name = 'data_unstatused'";
        //$urs  = dbCall($usql,$debug,'information_schema');
        //$unstatused_schema = $urs->fields['schema_name'];

        $unstatused = 0;
        $statused = 0;
        $pu = 0;
    }
    else
    {
        /*
            $table = 'data_unstatused';
            $sql = "select count(task_code) as num from $table u where status='Unstatused' and pmid=$pmid and ((select dashboard from master_project where id=u.pmid limit 1)=1)";
            $rs  = dbCall($sql,$debug);
            $unstatused = $rs->fields['num'];
            //$sql = "select count(task_code) as num from $table where status='Statused' and  proj_id=$ppm_ap_id";
            $sql = "select count(task_code) as num from $table u where status='Statused' and pmid=$pmid and ((select dashboard from master_project where id=u.pmid limit 1)=1)";
            $rs  = dbCall($sql,$debug);
            $statused = $rs->fields['num'];
            $pu = round(($unstatused / ($statused + $unstatused))*100);
        */
            // unstatused
        $table = 'unstatused';
        //$sql = "select count(task_code) as num from $table where status='Unstatused' and  proj_id=$ppm_ap_id";
        $sql = "select count(task_code) as num from $table u where status='Unstatused' and  proj_id=$ppm_ap_id and ((select dashboard from premier_core.master_project where ppm_ap_id=u.proj_id limit 1)=1)";
        $rs  = dbCall($sql,$debug,'unstatused',$db_server);
        $unstatused = $rs->fields['num'];

        //$sql = "select count(task_code) as num from $table where status='Statused' and  proj_id=$ppm_ap_id";
        $sql = "select count(task_code) as num from $table u where status='Statused' and  proj_id=$ppm_ap_id and ((select dashboard from premier_core.master_project where ppm_ap_id=u.proj_id limit 1)=1)";
        $rs  = dbCall($sql,$debug,'unstatused',$db_server);
        $statused = $rs->fields['num'];
        $pu = round(($unstatused / ($statused + $unstatused))*100);
    }

    // perform the tests
    // TEST 1
    $test1 = round($us/$spit);
    $test2 = round($rd_ap + ((($tf_minus3 - $tf_cur)/90)*($rd_ap/2)));
    //$test2 = round((($td_bl) + ($tf_cur-$tf_minus3))/((63*$rd_ap)/(2+$rd_ap)),2);
    $z .=  "<hr><b>13)&nbsp;</b>dtdd:$dtdd + td_bl:$td_bl - es:$es<hr>";
    //string2file($log_file,"\ndtdd:$dtdd + td_bl:$td_bl - es:$es\n",'a');
    //$test3 = $dtdd + $td_bl - $es;
    $test3 = $us;
    $test4 = 0; // MISSING SPI NO LOE
    $test5 = round($us / $bei);
    $test6 = .4*($logic) + .2*($constraint) + .4*($pu);
    $test7 = round(abs($tspit-$spit)*100);

    $first_bls_ts = UnixDate2TimeStamp(left($first_bls,10));
    $z .=  "<hr><b>14)&nbsp;</b>first_bls=$first_bls|first_bls_ts=$first_bls_ts<hr>";
    //string2file($log_file,"\nfirst_bls=$first_bls|first_bls_ts=$first_bls_ts\n",'a');
    $test1_date = USDate2UnixDate(dateadd('d',$test1,$first_bls_ts));
    $test2_date = USDate2UnixDate(dateadd('d',$test2,$first_bls_ts));
    $test3_date = USDate2UnixDate(dateadd('d',$test3,$first_bls_ts));
    $test5_date = USDate2UnixDate(dateadd('d',$test5,$first_bls_ts));

    $date_for_pos = USDate2UnixDate($date_for_pos);

    $z .= "date_for_pos:$date_for_pos<br>";

    // insert into database table
    /*
    $sql = "insert into cost_data.iecd_data (program_group,projectid,date1,bcwpf_month,bcwpf_days,bcwpf_total_days,bcwpf_last_day,spit,bcwp_cum,ap_start,ap_finish,bl_start,bl_finish) values
    ('$program_group',$projid,'$cp','$the_month',$more_days,$total_days,$last_day,$spit,$bcwp,'$first_bls','$last_apf','$first_bls','$finish_bl')
    ";
    */
        //condition variables so that inserts always happen (i.e., do not fail because a variable is empty)
    if($pmid == '') $pmid = 'NULL';
    if($cp == '')
    {
        $cp = 'NULL';
    }
    else
    {
        $cp = "'$cp'";
    }
    if($the_month == '') $the_month = 'NULL';
    if($more_days == '') $more_days = 'NULL';
    if($total_days == '') $total_days = 'NULL';
    if($last_day == '') $last_day = 'NULL';
    if($spit == '') $spit = 'NULL';
    if($bcwp == '') $bcwp = 'NULL';
    if($first_aps == '')
    {
        $first_aps = 'NULL';
    }
    else
    {
        $first_aps = "'$first_aps'";
    }
    if($last_apf == '')
    {
        $last_apf = 'NULL';
    }
    else
    {
        $last_apf = "'$last_apf'";
    }
    if($first_bls == '')
    {
        $first_bls = 'NULL';
    }
    else
    {
        $first_bls = "'$first_bls'";
    }
    if($finish_bl == '')
    {
        $finish_bl = 'NULL';
    }
    else
    {
        $finish_bl = "'$finish_bl'";
    }
    if($date_for_pos == '')
    {
        $date_for_pos = 'NULL';
    }
    else
    {
        $date_for_pos = "'$date_for_pos'";
    }
    if($bcwp_date == '')
    {
        $bcwp_date = 'NULL';
    }
    else
    {
        $bcwp_date = "'$bcwp_date'";
    }

    $new_id = getNextIBID('data_iecd', $debug);
    $sql = "insert into data_iecd (pmid,id,date1,bcwpf_month,bcwpf_days,bcwpf_total_days,bcwpf_last_day,spit,bcwp_cum,ap_start,ap_finish,bl_start,bl_finish,pos_date,bcwp_date) values
    ($pmid,$new_id,$cp,$the_month,$more_days,$total_days,$last_day,$spit,$bcwp,$first_aps,$last_apf,$first_bls,$finish_bl,$date_for_pos,$bcwp_date)
    ";
    $junk = dbCall_IB($sql,$debug);

    string2file($log_file,"\n$sql\n",'a');

    //condition variables so that updates always happen (i.e., do not fail because a variable is empty)
    if($data_date == '')
    {
        $data_date = 'NULL';
    }
    else
    {
        $data_date = "'".USDate2UnixDate($data_date)."'";
    }
    if($rd_bl == '') $rd_bl = 'NULL';
    if($td_bl == '') $td_bl = 'NULL';
    if($rd_ap == '') $rd_ap = 'NULL';
    if($tf_cur == '') $tf_cur = 'NULL';
    if($tf_minus3 == '') $tf_minus3 = 'NULL';
    if($es == '') $es = 'NULL';
    if($us == '') $us = 'NULL';
    if($bei == '') $bei = 'NULL';
    if($logic == '') $logic = 'NULL';
    if($constraint == '') $constraint = 'NULL';
    if($pu == '') $pu = 'NULL';
    if($test1 == '') $test1 = 'NULL';
    if($test2 == '') $test2 = 'NULL';
    if($test3 == '') $test3 = 'NULL';
    if($test4 == '') $test4 = 'NULL';
    if($test5 == '') $test5 = 'NULL';
    if($test6 == '') $test6 = 'NULL';
    if($test7 == '') $test7 = 'NULL';
    if($spit == '') $spit = 'NULL';
    if($tspit == '') $tspit = 'NULL';
    if($dtdd == '') $dtdd = 'NULL';
    if($test1_date == '')
    {
        $test1_date = 'NULL';
    }
    else
    {
        $test1_date = "'$test1_date'";
    }
    if($test2_date == '')
    {
        $test2_date = 'NULL';
    }
    else
    {
        $test2_date = "'$test2_date'";
    }
    if($test3_date == '')
    {
        $test3_date = 'NULL';
    }
    else
    {
        $test3_date = "'$test3_date'";
    }
    if($test5_date == '')
    {
        $test5_date = 'NULL';
    }
    else
    {
        $test5_date = "'$test5_date'";
    }
    if($td_ap == '') $td_ap = 'NULL';

    // store the data
    $sql = "update data_iecd set data_date=$data_date,rd_bl=$rd_bl,td_bl=$td_bl,rd_ap=$rd_ap,tf_cur=$tf_cur,
                    tf_minus3=$tf_minus3,`es`=$es,`us`=$us,`bei`=$bei,`logic`=$logic,`constraint`=$constraint,unstatused=$pu,
                    test1=$test1,test2=$test2,test3=$test3,test4=$test4,test5=$test5,test6=$test6,test7=$test7,spit=$spit,tspit=$tspit,dur_to_dd=$dtdd,
                    test1_date=$test1_date,test2_date=$test2_date,test3_date=$test3_date,test5_date=$test5_date,lf_ap=$last_apf,td_ap=$td_ap
           where pmid=$pmid and date1=$cp";
    $junk = dbCall_IB($sql,$debug);

    string2file($log_file,"\n$sql\n\n\n\n",'a');

    //reset variables for next loop
    $pmid           = '';
    $cp             = '';
    $the_month      = '';
    $more_days      = '';
    $total_days     = '';
    $last_day       = '';
    $spit           = '';
    $bcwp           = '';
    $first_aps      = '';
    $last_apf       = '';
    $first_bls      = '';
    $finish_bl      = '';
    $date_for_pos   = '';
    $bcwp_date      = '';
    $data_date      = '';
    $rd_bl          = '';
    $td_bl          = '';
    $rd_ap          = '';
    $tf_cur         = '';
    $tf_minus3      = '';
    $es             = '';
    $us             = '';
    $bei            = '';
    $logic          = '';
    $constraint     = '';
    $pu             = '';
    $test1          = '';
    $test2          = '';
    $test3          = '';
    $test4          = '';
    $test5          = '';
    $test6          = '';
    $test7          = '';
    $spit           = '';
    $tspit          = '';
    $dtdd           = '';
    $test1_date     = '';
    $test2_date     = '';
    $test3_date     = '';
    $test5_date     = '';
    $td_ap          = '';

    //string2file('temp/iecd.txt',"\n$z\n\n\n\n",'a');

    if($debug) { print $z; }

    return true;
}
// ------------------------------------------------------------------
function getProgramsAsArray($include_all_flag=true,$include_sub_program_flag=false)
{
    $company = trim($_SESSION['filters']['company']);
    if($company=='') $company = $_SESSION['s_user']['company'];
    $c_wc = createCompanyWC('company',$company,$ao='or');
    $uc_wc = " and (company='%' $c_wc)";

    $sql = "select program_group from master_project where program_group is not null $uc_wc group by program_group order by program_group";
    $rs  = dbCall($sql,false);
    $programs = array();
    $values = array(); // this variable is the value in the select
    if($rs)
        {while(!$rs->EOF)
        {
            $program = $rs->fields['program_group'];
            $programs[] = $program;
            $values[] = $program;

            if($include_sub_program_flag)
            {
                $sql = "select sub_program_group from master_project where program_group='$program'
                        and sub_program_group is not null group by sub_program_group order by sub_program_group";
                $rs2 = dbCall($sql,false);
                while(!$rs2->EOF)
                {
                    $sp = $rs2->fields['sub_program_group'];
                    $programs[] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $sp;
                    $values[] = ':'.$program.'.'.$sp;
                    $rs2->MoveNext();
                }
            }

            $rs->MoveNext();
        }
    }

    if(count($programs)<1)
    {
        $programs = array("$user_company");
        $programs = array("$user_company");
    }
    if($include_all_flag)
    {
        $programs[] = 'ALL';
        $values[] = '%';
    }
    if($include_sub_program_flag)
    {
        return array($programs,$programs);
    }
    else
    {
        return $programs;
    }
}
// -----------------------------------------------------------------
function updateWSWithCostData($rps_with_pmids,$company,$debug=false)
{
    foreach($rps_with_pmids as $reporting_period => $pmids_array)
    {
        $join_wc = '';
        $pmids_wc = '';
        $pmids_wc_cam = '';
        if(count($pmids_array)>1)
        {
            $join_wc = " , master_project mv ";
            $pmids_wc = " ws.pmid=mv.id and company='$company' and mv.rpt_period is not null and mv.rpt_period<>'' ";
            $pmids_cd_wc = " cd.pmid=mv.id and company='$company' and mv.rpt_period is not null and mv.rpt_period<>'' ";
            $pmids_wc_cam = " and company='$company' and mv.rpt_period is not null and mv.rpt_period<>'' ";
        }
        else
        {
            $pmids = implode(",",$pmids_array);
            $pmids_wc = " pmid = $pmids ";
            $pmids_cd_wc = " pmid = $pmids ";
            $pmids_wc_cam = " and ws.pmid = $pmids ";
        }

        $year = substr($reporting_period,0,4);
        $month = substr($reporting_period,-2);

        //get previous period
        $sql = "SELECT DATE_FORMAT(CONCAT($year,'-',$month,'-01') - INTERVAL 1 MONTH, '%Y%m') prev_period";
        $rs  = dbCall_IB($sql,$debug);
        $prev_period = $rs->fields['prev_period'];

        $prev_year = substr($prev_period,0,4);
        $prev_month = substr($prev_period,-2);

        //Dollars
        $sql = "
            INSERT INTO data_cost_ws
            (
                pmid,
                cmid,
                wp,
                elemtype,
                unitid,
                data_type,
                reporting_period,
                bcwscur,
                bcwpcur,
                acwpcur,
                bcwscum,
                bcwpcum,
                acwpcum,
                bac,
                lre,
                ev_method
            )
            (
                SELECT
                    pmid,
                    cmid,
                    wp,
                    'DT' AS elemtype,
                    '1' AS unitid,
                    'Dollars' AS data_type,
                    reporting_period,
                    (CASE WHEN `type`='BCWS' AND date1=reporting_period THEN SUM(direct+overhead+ga+com+oh2+fringe+diralloc)/1000 ELSE 0 END) AS bcwscur,
                    (case when type='BCWP' and reporting_period='$reporting_period' and (cd.`year`<$year or (cd.`year`=$year and cd.period<=$month)) then SUM(direct+overhead+ga+com+oh2+fringe+diralloc)/1000 else 0 end) - (case when type='BCWPPrev' and reporting_period='$reporting_period' then SUM(direct+overhead+ga+com+oh2+fringe+diralloc)/1000 else 0 end) AS bcwpcur,
                    (CASE WHEN `type`='Actuals' AND date1=reporting_period THEN SUM(direct+overhead+ga+com+oh2+fringe+diralloc)/1000 ELSE 0 END) AS acwpcur,
                    (CASE WHEN `type`='BCWS' AND date1<=reporting_period THEN SUM(direct+overhead+ga+com+oh2+fringe+diralloc)/1000 ELSE 0 END) AS bcwscum,
                    (CASE WHEN `type`='BCWP' AND date1<=reporting_period THEN SUM(direct+overhead+ga+com+oh2+fringe+diralloc)/1000 ELSE 0 END) AS bcwpcum,
                    (CASE WHEN `type`='Actuals' AND date1<=reporting_period THEN SUM(direct+overhead+ga+com+oh2+fringe+diralloc)/1000 ELSE 0 END) AS acwpcum,
                    (CASE WHEN `type`='BCWS' THEN SUM(direct+overhead+ga+com+oh2+fringe+diralloc)/1000 ELSE 0 END) AS bac,
                    (CASE WHEN `type`='ETC' THEN SUM(direct+overhead+ga+com+oh2+fringe+diralloc)/1000 ELSE 0 END) AS lre,
                    ev_method
                FROM
                    data_cost cd $join_wc
                WHERE
                    $pmids_cd_wc
                    AND reporting_period = '$reporting_period'
                GROUP BY
                    pmid
                    ,cmid
                    ,wp
                    ,`type`
                    ,date1
            )
        ";
        $junk = dbCall_IB($sql,$debug);

        //PrimeOH Dollars
        $sql = "
            INSERT INTO data_cost_ws
            (
                pmid,
                cmid,
                wp,
                elemtype,
                unitid,
                data_type,
                reporting_period,
                bcwscur,
                bcwpcur,
                acwpcur,
                bcwscum,
                bcwpcum,
                acwpcum,
                bac,
                lre,
                ev_method
            )
            (
                SELECT
                    pmid,
                    cmid,
                    wp,
                    'DT' AS elemtype,
                    '5' AS unitid,
                    'PrimeOH Dollars' AS data_type,
                    reporting_period,
                    (CASE WHEN `type`='BCWS' AND date1=reporting_period THEN SUM(direct+overhead)/1000 ELSE 0 END) AS bcwscur,
                    (case when type='BCWP' and reporting_period='$reporting_period' and (cd.`year`<$year or (cd.`year`=$year and cd.period<=$month)) then SUM(direct+overhead)/1000 else 0 end) - (case when type='BCWPPrev' and reporting_period='$reporting_period' then SUM(direct+overhead)/1000 else 0 end) AS bcwpcur,
                    (CASE WHEN `type`='Actuals' AND date1=reporting_period THEN SUM(direct+overhead)/1000 ELSE 0 END) AS acwpcur,
                    (CASE WHEN `type`='BCWS' AND date1<=reporting_period THEN SUM(direct+overhead)/1000 ELSE 0 END) AS bcwscum,
                    (CASE WHEN `type`='BCWP' AND date1<=reporting_period THEN SUM(direct+overhead)/1000 ELSE 0 END) AS bcwpcum,
                    (CASE WHEN `type`='Actuals' AND date1<=reporting_period THEN SUM(direct+overhead)/1000 ELSE 0 END) AS acwpcum,
                    (CASE WHEN `type`='BCWS' THEN SUM(direct+overhead)/1000 ELSE 0 END) AS bac,
                    (CASE WHEN `type`='ETC' THEN SUM(direct+overhead)/1000 ELSE 0 END) AS lre,
                    ev_method
                FROM
                    data_cost cd $join_wc
                WHERE
                    $pmids_cd_wc
                    AND reporting_period = '$reporting_period'
                GROUP BY
                    pmid
                    ,cmid
                    ,wp
                    ,`type`
                    ,date1
            )
        ";
        $junk = dbCall_IB($sql,$debug);

        //Hours
        $sql = "
            INSERT INTO data_cost_ws
            (
                pmid,
                cmid,
                wp,
                elemtype,
                unitid,
                data_type,
                reporting_period,
                bcwscur,
                bcwpcur,
                acwpcur,
                bcwscum,
                bcwpcum,
                acwpcum,
                bac,
                lre,
                ev_method
            )
            (
                SELECT
                    pmid,
                    cmid,
                    wp,
                    'DT' AS elemtype,
                    '2' AS unitid,
                    'Hours' AS data_type,
                    reporting_period,
                    (CASE WHEN `type`='BCWS' AND date1=reporting_period THEN SUM(hours1) ELSE 0 END) AS bcwscur,
                    (CASE WHEN TYPE='BCWP' AND reporting_period='$reporting_period' AND (cd.`year`<$year OR (cd.`year`=$year AND cd.period<=$month)) THEN SUM(hours1) ELSE 0 END) - (CASE WHEN TYPE='BCWPPrev' AND reporting_period='$reporting_period' THEN SUM(hours1) ELSE 0 END) AS bcwpcur,
                    (CASE WHEN `type`='Actuals' AND date1=reporting_period THEN SUM(hours1) ELSE 0 END) AS acwpcur,
                    (CASE WHEN `type`='BCWS' AND date1<=reporting_period THEN SUM(hours1) ELSE 0 END) AS bcwscum,
                    (CASE WHEN `type`='BCWP' AND date1<=reporting_period THEN SUM(hours1) ELSE 0 END) AS bcwpcum,
                    (CASE WHEN `type`='Actuals' AND date1<=reporting_period THEN SUM(hours1) ELSE 0 END) AS acwpcum,
                    (CASE WHEN `type`='BCWS' THEN SUM(hours1) ELSE 0 END) AS bac,
                    (CASE WHEN `type`='ETC' THEN SUM(hours1) ELSE 0 END) AS lre,
                    ev_method
                FROM
                    data_cost cd $join_wc
                WHERE
                    $pmids_cd_wc
                    AND reporting_period = '$reporting_period'
                GROUP BY
                    pmid
                    ,cmid
                    ,wp
                    ,`type`
                    ,date1
            )
        ";
        $junk = dbCall_IB($sql,$debug);

        //Heads
        $sql = "
            INSERT INTO data_cost_ws
            (
                pmid,
                cmid,
                wp,
                elemtype,
                unitid,
                data_type,
                reporting_period,
                bcwscur,
                bcwpcur,
                acwpcur,
                bcwscum,
                bcwpcum,
                acwpcum,
                bac,
                lre,
                ev_method
            )
            (
                SELECT
                    pmid,
                    cmid,
                    wp,
                    'DT' AS elemtype,
                    '3' AS unitid,
                    'Heads' AS data_type,
                    reporting_period,
                    (CASE WHEN `type`='BCWS' AND date1=reporting_period THEN SUM(heads) ELSE 0 END) AS bcwscur,
                    (case when type='BCWP' and reporting_period='$reporting_period' and (cd.`year`<$year or (cd.`year`=$year and cd.period<=$month)) then SUM(heads) else 0 end) - (CASE WHEN TYPE='BCWPPrev' AND reporting_period='$reporting_period' THEN SUM(heads) ELSE 0 END) AS bcwpcur,
                    (CASE WHEN `type`='Actuals' AND date1=reporting_period THEN SUM(heads) ELSE 0 END) AS acwpcur,
                    (CASE WHEN `type`='BCWS' AND date1<=reporting_period THEN SUM(heads) ELSE 0 END) AS bcwscum,
                    (CASE WHEN `type`='BCWP' AND date1<=reporting_period THEN SUM(heads) ELSE 0 END) AS bcwpcum,
                    (CASE WHEN `type`='Actuals' AND date1<=reporting_period THEN SUM(heads) ELSE 0 END) AS acwpcum,
                    (CASE WHEN `type`='BCWS' THEN SUM(heads) ELSE 0 END) AS bac,
                    (CASE WHEN `type`='ETC' THEN SUM(heads) ELSE 0 END) AS lre,
                    ev_method
                FROM
                    data_cost cd $join_wc
                WHERE
                    $pmids_cd_wc
                    AND reporting_period = '$reporting_period'
                GROUP BY
                    pmid
                    ,cmid
                    ,wp
                    ,`type`
                    ,date1
            )
        ";
        $junk = dbCall_IB($sql,$debug);

        //Direct Dollars
        $sql = "
            INSERT INTO data_cost_ws
            (
                pmid,
                cmid,
                wp,
                elemtype,
                unitid,
                data_type,
                reporting_period,
                bcwscur,
                bcwpcur,
                acwpcur,
                bcwscum,
                bcwpcum,
                acwpcum,
                bac,
                lre,
                ev_method
            )
            (
                SELECT
                    pmid,
                    cmid,
                    wp,
                    'DT' AS elemtype,
                    '4' AS unitid,
                    'Direct Dollars' AS data_type,
                    reporting_period,
                    (CASE WHEN `type`='BCWS' AND date1=reporting_period THEN SUM(direct)/1000 ELSE 0 END) AS bcwscur,
                    (case when type='BCWP' and reporting_period='$reporting_period' and (cd.`year`<$year or (cd.`year`=$year and cd.period<=$month)) then SUM(direct)/1000 else 0 end) - (CASE WHEN TYPE='BCWPPrev' AND reporting_period='$reporting_period' THEN SUM(direct)/1000 ELSE 0 END) AS bcwpcur,
                    (CASE WHEN `type`='Actuals' AND date1=reporting_period THEN SUM(direct)/1000 ELSE 0 END) AS acwpcur,
                    (CASE WHEN `type`='BCWS' AND date1<=reporting_period THEN SUM(direct)/1000 ELSE 0 END) AS bcwscum,
                    (CASE WHEN `type`='BCWP' AND date1<=reporting_period THEN SUM(direct)/1000 ELSE 0 END) AS bcwpcum,
                    (CASE WHEN `type`='Actuals' AND date1<=reporting_period THEN SUM(direct)/1000 ELSE 0 END) AS acwpcum,
                    (CASE WHEN `type`='BCWS' THEN SUM(direct)/1000 ELSE 0 END) AS bac,
                    (CASE WHEN `type`='ETC' THEN SUM(direct)/1000 ELSE 0 END) AS lre,
                    ev_method
                FROM
                    data_cost cd $join_wc
                WHERE
                    $pmids_cd_wc
                    AND reporting_period = '$reporting_period'
                GROUP BY
                    pmid
                    ,cmid
                    ,wp
                    ,`type`
                    ,date1
            )
        ";
        $junk = dbCall_IB($sql,$debug);

        // get ENDDATE
        $sql = "
            UPDATE
                data_cost_ws ws
            SET
                ENDDATE = (SELECT max(cd.enddate) FROM data_cost_ws_reporting_periods cd WHERE cd.reporting_period = ws.reporting_period)
                ,elemid = id
                ,struid = 1
            where
                elemid is null
        ";
        $junk = dbCall_IB($sql,$debug);

        $sql = "UPDATE data_cost_ws SET wp=wbsnum WHERE wbsnum LIKE '[%'";
        $junk = dbCall_IB($sql,$debug);

        //update data_cost_ws_project
        $sql = " insert into data_cost_ws_project (pmid,reporting_period,data_type,ev_method,bcwscum,bcwpcum,acwpcum,bcwscur,bcwpcur,acwpcur,
        lre,bac,cv,sv,cvcur,svcur,vac,bcwr,cv_percentage,sv_percentage,cv_percentage_cur,sv_percentage_cur,cpi,spi,bcwpcum_no_le_se,
        bcwscum_no_le_se,bcwscum_le_se,bcwscum_se,tcpi,overall_schedule_percentage,overall_complete_percentage,overall_spent_percentage,mgt_reserve)
            (
                SELECT
                    pmid,
                    reporting_period,
                    data_type,
                    ev_method,
                    SUM(bcwscum) AS bcwscum,
                    SUM(bcwpcum) AS bcwpcum,
                    SUM(acwpcum) AS acwpcum,
                    SUM(bcwscur) AS bcwscur,
                    SUM(bcwpcur) AS bcwpcur,
                    SUM(acwpcur) AS acwpcur,
                    SUM(lre) AS lre,
                    SUM(bac) AS bac,
                    SUM(bcwpcum - acwpcum) AS cv,
                    SUM(bcwpcum - bcwscum) AS sv,
                    SUM(bcwpcur - acwpcur) AS cvcur,
                    SUM(bcwpcur - bcwscur) AS svcur,
                    SUM(bac - lre) AS vac,
                    SUM(bac-bcwpcum) AS bcwr,
                    (((SUM(bcwpcum) - SUM(acwpcum)) / SUM(bcwpcum)) * 100) AS cv_percentage,
                    (((SUM(bcwpcum) - SUM(bcwscum)) / SUM(bcwscum)) * 100) AS sv_percentage,
                    (((SUM(bcwpcur) - SUM(acwpcur)) / SUM(bcwpcur)) * 100) AS cv_percentage_cur,
                    (((SUM(bcwpcur) - SUM(bcwscur)) / SUM(bcwscur)) * 100) AS sv_percentage_cur,
                    (SUM(bcwpcum) / SUM(acwpcum))  AS cpi,
                    (SUM(bcwpcum) / SUM(bcwscum))  AS spi,
                    SUM(CASE WHEN ((ev_method<>'LE' OR ev_method IS NULL) AND elemtype<>'SE') THEN bcwpcum ELSE 0 END) bcwpcum_no_le_se,
                    SUM(CASE WHEN ((ev_method<>'LE' OR ev_method IS NULL) AND elemtype<>'SE') THEN bcwscum ELSE 0 END) bcwscum_no_le_se,
                    SUM(CASE WHEN (ev_method='LE' AND elemtype<>'SE') THEN bcwscum ELSE 0 END) bcwscum_le_se,
                    SUM(CASE WHEN elemtype<>'SE' THEN bcwscum ELSE 0 END) bcwscum_se,
                    (((SUM(bac) - SUM(bcwpcum)) / (SUM(lre) - SUM(acwpcum))) ) AS tcpi,
                    ((SUM(bcwscum) / SUM(bac)) * 100) AS overall_schedule_percentage,
                    ((SUM(bcwpcum) / SUM(bac)) * 100) AS overall_complete_percentage,
                    ((SUM(acwpcum) / SUM(bac)) * 100) AS overall_spent_percentage,
                    SUM(CASE WHEN wp='[MR]' THEN bac ELSE 0 END) AS mgt_reserve
                FROM
                    data_cost_ws ws $join_wc
                WHERE
                    $pmids_wc
                    AND reporting_period = '$reporting_period'
                GROUP BY
                    pmid,
                    reporting_period,
                    data_type,
                    ev_method
            )
        ";
        $junk = dbCall_IB($sql,$debug);

        //update data_cost_cam
        $sql = "
            insert into data_cost_ws_cam (pmid,cmid,reporting_period,data_type,ev_method,bcwscum,bcwpcum,acwpcum,bcwscur,bcwpcur,acwpcur,
        lre,bac,cv,sv,vac,bcwr,cv_percentage,sv_percentage,cv_percentage_cur,sv_percentage_cur,cpi,spi,bcwpcum_no_le_se,
        bcwscum_no_le_se,bcwscum_le_se,bcwscum_se,tcpi,overall_schedule_percentage,overall_complete_percentage,overall_spent_percentage,mgt_reserve)
            (
                SELECT
                    ws.pmid,
                    ws.cmid,
                    ws.reporting_period,
                    ws.data_type,
                    ws.ev_method,
                    SUM(ws.bcwscum) AS bcwscum,
                    SUM(ws.bcwpcum) AS bcwpcum,
                    SUM(ws.acwpcum) AS acwpcum,
                    SUM(ws.bcwscur) AS bcwscur,
                    SUM(ws.bcwpcur) AS bcwpcur,
                    SUM(ws.acwpcur) AS acwpcur,
                    SUM(ws.lre) AS lre,
                    SUM(ws.bac) AS bac,
                    SUM(ws.bcwpcum - ws.acwpcum) AS cv,
                    SUM(ws.bcwpcum - ws.bcwscum) AS sv,
                    SUM(ws.bac - ws.lre) AS vac,
                    SUM(ws.bac-ws.bcwpcum) AS bcwr,
                    (((SUM(ws.bcwpcum) - SUM(ws.acwpcum)) / SUM(ws.bcwpcum)) * 100) AS cv_percentage,
                    (((SUM(ws.bcwpcum) - SUM(ws.bcwscum)) / SUM(ws.bcwscum)) * 100) AS sv_percentage,
                    (((SUM(ws.bcwpcur) - SUM(ws.acwpcur)) / SUM(ws.bcwpcur)) * 100) AS cv_percentage_cur,
                    (((SUM(ws.bcwpcur) - SUM(ws.bcwscur)) / SUM(ws.bcwscur)) * 100) AS sv_percentage_cur,
                    (SUM(ws.bcwpcum) / SUM(ws.acwpcum))  AS cpi,
                    (SUM(ws.bcwpcum) / SUM(ws.bcwscum))   AS spi,
                    SUM(CASE WHEN ((ev_method<>'LE' OR ev_method IS NULL) AND elemtype<>'SE') THEN bcwpcum ELSE 0 END) bcwpcum_no_le_se,
                    SUM(CASE WHEN ((ev_method<>'LE' OR ev_method IS NULL) AND elemtype<>'SE') THEN bcwscum ELSE 0 END) bcwscum_no_le_se,
                    SUM(CASE WHEN (ev_method='LE' AND elemtype<>'SE') THEN bcwscum ELSE 0 END) bcwscum_le_se,
                    SUM(CASE WHEN elemtype<>'SE' THEN bcwscum ELSE 0 END) bcwscum_se,
                    (((SUM(ws.bac) - SUM(ws.bcwpcum)) / (SUM(ws.lre) - SUM(ws.acwpcum))) ) AS tcpi,
                    ((SUM(ws.bcwscum) / SUM(ws.bac)) * 100) AS overall_schedule_percentage,
                    ((SUM(ws.bcwpcum) / SUM(ws.bac)) * 100) AS overall_complete_percentage,
                    ((SUM(ws.acwpcum) / SUM(ws.bac)) * 100) AS overall_spent_percentage,
                    SUM(CASE WHEN ws.wp='[MR]' THEN ws.bac ELSE 0 END) AS mgt_reserve
                FROM
                    data_cost_ws ws,
                    main mv
                where
                    ws.pmid=mv.pmid
                    and ws.cmid=mv.cmid
                    $pmids_wc_cam
                    AND reporting_period = '$reporting_period'
                GROUP BY
                    ws.pmid,
                    ws.cmid,
                    mv.cam,
                    ws.reporting_period,
                    ws.data_type,
                    ws.ev_method
            )
        ";
        $junk = dbCall_IB($sql,$debug);

        //update data_cost_mr
        $sql = "
            insert into data_cost_ws_mr (pmid,reporting_period,data_type,mgt_reserve,eac_mgt_reserve)
            (
                SELECT
                    ws.pmid,
                    ws.reporting_period,
                    ws.data_type,
                    SUM(ws.bac) AS mgt_reserve,
                    SUM(ws.lre) AS eac_mgt_reserve
                FROM
                    data_cost_ws ws $join_wc
                WHERE
                    $pmids_wc
                    AND reporting_period = '$reporting_period'
                    and ws.wp='[MR]'
                GROUP BY
                    ws.pmid,
                    ws.reporting_period,
                    ws.data_type
             )
        ";
        $junk = dbCall_IB($sql,$debug);
    }
    return true;
}
//-------------------------------------------------------------------
function updateCostSummaryTablesWithCostData($rps_with_pmids,$company,$debug=false)
{
    foreach($rps_with_pmids as $reporting_period => $pmids_array)
    {
        $join_wc = '';
        $pmids_wc = '';
        $pmids_wc_cam = '';
        if(count($pmids_array)>1)
        {
            $join_wc = " , master_project mv ";
            $pmids_wc = " cd.pmid=mv.id and company='$company' and mv.rpt_period is not null and mv.rpt_period<>'' ";
            $pmids_wc_cam = " and company='$company' and mv.rpt_period is not null and mv.rpt_period<>'' ";
            $pmids_wc_rp = " d.pmid=mv.id and company='$company' and mv.rpt_period is not null and mv.rpt_period<>'' ";
            $pmids_wc_rp_cam = " and company='$company' and mv.rpt_period is not null and mv.rpt_period<>'' ";
        }
        else
        {
            $pmids = implode(",",$pmids_array);
            $pmids_wc = " pmid = $pmids ";
            $pmids_wc_cam = " and cd.pmid = $pmids ";
            $pmids_wc_rp = " d.pmid = $pmids ";
            $pmids_wc_rp_cam = " and d.pmid = $pmids ";
        }

        //update data_cost_project
        $sql = "
            insert into data_cost_project (pmid,category,date1,reporting_period,ev_method,eoc,basisname,`type`,`year`,period,
            heads,hours1,direct,overhead,ga,com,oh2,fringe,diralloc)
            (
                SELECT
                    cd.pmid,
                    category,
                    date1,
                    reporting_period,
                    ev_method,
                    eoc,
                    basisname,
                    `type`,
                    `year`,
                    period,
                    SUM(heads) AS heads,
                    SUM(hours1) AS hours1,
                    SUM(direct) AS direct,
                    SUM(overhead) AS overhead,
                    SUM(ga) AS ga,
                    SUM(com) AS com,
                    SUM(oh2) AS oh2,
                    SUM(fringe) AS fringe,
                    SUM(diralloc) AS diralloc
                FROM
                    data_cost cd $join_wc
                where
                    $pmids_wc
                    AND reporting_period = '$reporting_period'
                GROUP BY
                    cd.pmid,
                    category,
                    ev_method,
                    date1,
                    reporting_period,
                    eoc,
                    basisname,
                    `type`,
                    `year`,
                    period
           )
        ";
        $junk = dbCall_IB($sql,$debug);

        //update data_cost_ca
        $sql = "
            INSERT INTO data_cost_ca
            (
              `pmid`
              , `cmid`
              , `category`
              , `date1`
              , `reporting_period`
              , `ev_method`
              , `eoc`
              , `basisname`
              , `type`
              , `year`
              , `period`
              , `heads`
              , `hours1`
              , `direct`
              , `overhead`
              , `ga`
              , `com`
              , `oh2`
              , `fringe`
              , `diralloc`
            )
            (
                SELECT
                  cd.pmid
                  , cd.cmid
                  , category
                  , date1
                  , reporting_period
                  , ev_method
                  , eoc
                  , basisname
                  , `type`
                  , `year`
                  , period
                  , SUM(heads) AS heads
                  , SUM(hours1) AS hours1
                  , SUM(direct) AS direct
                  , SUM(overhead) AS overhead
                  , SUM(ga) AS ga
                  , SUM(com) AS com
                  , SUM(oh2) AS oh2
                  , SUM(fringe) AS fringe
                  , SUM(diralloc) AS diralloc
                FROM
                  data_cost cd,
                  main mv
                where
                    cd.pmid=mv.pmid and cd.cmid=mv.cmid
                    $pmids_wc_cam
                    AND reporting_period = '$reporting_period'
                GROUP BY
                    cd.pmid
                  , cd.cmid
                  , category
                  , date1
                  , reporting_period
                  , ev_method
                  , eoc
                  , basisname
                  , `type`
                  , `year`
                  , period
           )
        ";
        $junk = dbCall_IB($sql,$debug);

        //update data_cost_rp tables - current
        $sql = "
            insert into data_cost_rp (reporting_period,`year`,period,pmid,cmid,wp,wp_desc,wp_lead,category,`type`,ev_method,eoc,eoc_desc,burdenuser1,
            burdenuser2,burdenuser3,resourceid,resource_name,eng_system,dg_id,dg,dg_desc,basisid,basisname,basisdescription,
            heads,hours1,direct,overhead,ga,com,oh2,fringe,diralloc,compressedsummaryisperformed,taskisleaf,category1,projectuser7)
            (
                SELECT
                    reporting_period,
                    LEFT(reporting_period, 4) AS rp_year,
                    RIGHT(reporting_period, 2) AS rp_period,
                    d.`pmid`,
                    d.`cmid`,
                    `wp`,
                    `wp_desc`,
                    `wp_lead`,
                    `category`,
                    CASE WHEN `type` = 'bcwpprev' THEN 'BCWPPriorPeriod' ELSE `type` END as `type`,
                    `ev_method`,
                    `eoc`,
                    `eoc_desc`,
                    `burdenuser1`,
                    `burdenuser2`,
                    `burdenuser3`,
                    `resourceid`,
                    `resource_name`,
                    `eng_system`,
                    `dg_id`,
                    `dg`,
                    `dg_desc`,
                    `basisid`,
                    `basisname`,
                    `basisdescription`,
                    ROUND(SUM(heads), 3) AS heads,
                    ROUND(SUM(hours1), 3) AS hours1,
                    ROUND(SUM(direct), 3) AS direct,
                    ROUND(SUM(overhead), 3) AS overhead,
                    ROUND(SUM(ga), 3) AS ga,
                    ROUND(SUM(com), 3) AS com,
                    ROUND(SUM(oh2), 3) AS oh2,
                    ROUND(SUM(fringe), 3) AS fringe,
                    ROUND(SUM(diralloc), 3) AS diralloc,
                    `compressedsummaryisperformed`,
                    `taskisleaf`,
                    `category1`,
                    `projectuser7`
                FROM
                    data_cost d $join_wc
                WHERE
                    (d.year = LEFT(reporting_period, 4)
                    AND d.period = RIGHT(reporting_period, 2))
                    and $pmids_wc_rp
                    AND reporting_period = '$reporting_period'
                GROUP BY
                    d.reporting_period,
                    d.pmid,
                    d.cmid,
                    wp,
                    category,
                    `type`,
                    ev_method,
                    eoc,
                    dg_id,
                    resourceid,
                    basisid,
                    burdenuser1,
                    burdenuser2,
                    burdenuser3
            )
        ";
        $junk = dbCall_IB($sql,$debug);

        //update data_cost_rp tables - prior
        $sql = "
            insert into data_cost_rp (reporting_period,`year`,period,pmid,cmid,wp,wp_desc,wp_lead,category,`type`,ev_method,eoc,eoc_desc,burdenuser1,
            burdenuser2,burdenuser3,resourceid,resource_name,eng_system,dg_id,dg,dg_desc,basisid,basisname,basisdescription,
            heads,hours1,direct,overhead,ga,com,oh2,fringe,diralloc,compressedsummaryisperformed,taskisleaf,category1,projectuser7)
            (
                SELECT
                    reporting_period,
                    LEFT(reporting_period, 4) AS rp_year,
                    RIGHT(reporting_period, 2) AS rp_period,
                    d.`pmid`,
                    d.`cmid`,
                    `wp`,
                    `wp_desc`,
                    `wp_lead`,
                    `category`,
                    CASE WHEN `type` = 'bcwpprev' THEN 'BCWPPriorPeriod' ELSE CONCAT(`type`, 'Prev') END as `type`,
                    `ev_method`,
                    `eoc`,
                    `eoc_desc`,
                    `burdenuser1`,
                    `burdenuser2`,
                    `burdenuser3`,
                    `resourceid`,
                    `resource_name`,
                    `eng_system`,
                    `dg_id`,
                    `dg`,
                    `dg_desc`,
                    `basisid`,
                    `basisname`,
                    `basisdescription`,
                    ROUND(SUM(heads), 3) AS heads,
                    ROUND(SUM(hours1), 3) AS hours1,
                    ROUND(SUM(direct), 3) AS direct,
                    ROUND(SUM(overhead), 3) AS overhead,
                    ROUND(SUM(ga), 3) AS ga,
                    ROUND(SUM(com), 3) AS com,
                    ROUND(SUM(oh2), 3) AS oh2,
                    ROUND(SUM(fringe), 3) AS fringe,
                    ROUND(SUM(diralloc), 3) AS diralloc,
                    `compressedsummaryisperformed`,
                    `taskisleaf`,
                    `category1`,
                    `projectuser7`
                FROM
                    data_cost d $join_wc
                WHERE
                    (d.year < LEFT(reporting_period, 4)
                    OR (
                        d.year = LEFT(reporting_period, 4)
                        AND d.period < RIGHT(reporting_period, 2)
                    ))
                    and $pmids_wc_rp
                    AND reporting_period = '$reporting_period'
                GROUP BY
                    d.reporting_period,
                    d.pmid,
                    d.cmid,
                    wp,
                    category,
                    `type`,
                    ev_method,
                    eoc,
                    dg_id,
                    resourceid,
                    basisid,
                    burdenuser1,
                    burdenuser2,
                    burdenuser3
            )
        ";
        $junk = dbCall_IB($sql,$debug);

        //update data_cost_rp tables - future
        $sql = "
            insert into data_cost_rp (reporting_period,`year`,period,pmid,cmid,wp,wp_desc,wp_lead,category,`type`,ev_method,eoc,eoc_desc,burdenuser1,
            burdenuser2,burdenuser3,resourceid,resource_name,eng_system,dg_id,dg,dg_desc,basisid,basisname,basisdescription,
            heads,hours1,direct,overhead,ga,com,oh2,fringe,diralloc,compressedsummaryisperformed,taskisleaf,category1,projectuser7)
            (
                SELECT
                    reporting_period,
                    LEFT(reporting_period, 4) AS rp_year,
                    RIGHT(reporting_period, 2) AS rp_period,
                    d.`pmid`,
                    d.`cmid`,
                    `wp`,
                    `wp_desc`,
                    `wp_lead`,
                    `category`,
                    CASE WHEN `type` = 'bcwpprev' THEN 'BCWPPriorPeriod' ELSE CONCAT(`type`, 'Future') END as `type`,
                    `ev_method`,
                    `eoc`,
                    `eoc_desc`,
                    `burdenuser1`,
                    `burdenuser2`,
                    `burdenuser3`,
                    `resourceid`,
                    `resource_name`,
                    `eng_system`,
                    `dg_id`,
                    `dg`,
                    `dg_desc`,
                    `basisid`,
                    `basisname`,
                    `basisdescription`,
                    ROUND(SUM(heads), 3) AS heads,
                    ROUND(SUM(hours1), 3) AS hours1,
                    ROUND(SUM(direct), 3) AS direct,
                    ROUND(SUM(overhead), 3) AS overhead,
                    ROUND(SUM(ga), 3) AS ga,
                    ROUND(SUM(com), 3) AS com,
                    ROUND(SUM(oh2), 3) AS oh2,
                    ROUND(SUM(fringe), 3) AS fringe,
                    ROUND(SUM(diralloc), 3) AS diralloc,
                    `compressedsummaryisperformed`,
                    `taskisleaf`,
                    `category1`,
                    `projectuser7`
                FROM
                    data_cost d $join_wc
                WHERE
                    (d.year > LEFT(reporting_period, 4)
                    OR (
                        d.year = LEFT(reporting_period, 4)
                        AND d.period > RIGHT(reporting_period, 2)
                    ))
                    and $pmids_wc_rp
                    AND reporting_period = '$reporting_period'
                GROUP BY
                    d.reporting_period,
                    d.pmid,
                    d.cmid,
                    wp,
                    category,
                    `type`,
                    ev_method,
                    eoc,
                    dg_id,
                    resourceid,
                    basisid,
                    burdenuser1,
                    burdenuser2,
                    burdenuser3
            )
        ";
        $junk = dbCall_IB($sql,$debug);

        //update data_cost_rp_project
        $sql = "
            insert into data_cost_rp_project (reporting_period,`year`,period,pmid,basisname,`type`,ev_method,heads,hours1,direct,overhead,ga,com,oh2,
            fringe,diralloc)
            (
                SELECT
                    reporting_period,
                    LEFT(reporting_period, 4) AS rp_year,
                    RIGHT(reporting_period, 2) AS rp_period,
                    d.pmid,
                    basisname,
                    `type`,
                    ev_method,
                    ROUND(SUM(heads), 3) AS heads,
                    ROUND(SUM(hours1), 3) AS hours1,
                    ROUND(SUM(direct), 3) AS direct,
                    ROUND(SUM(overhead), 3) AS overhead,
                    ROUND(SUM(ga), 3) AS ga,
                    ROUND(SUM(com), 3) AS com,
                    ROUND(SUM(oh2), 3) AS oh2,
                    ROUND(SUM(fringe), 3) AS fringe,
                    ROUND(SUM(diralloc), 3) AS diralloc
                FROM
                    data_cost_rp d $join_wc
                where
                    $pmids_wc_rp
                    AND reporting_period = '$reporting_period'
                GROUP BY
                    reporting_period,
                    d.pmid,
                    basisname,
                    `type`,
                    ev_method
           )
        ";
        $junk = dbCall_IB($sql,$debug);

        //update data_cost_rp_ca
        $sql = "
            insert into data_cost_rp_ca (reporting_period,`year`,period,pmid,cmid,basisname,`type`,ev_method,heads,hours1,direct,overhead,ga,com,oh2,
            fringe,diralloc)
            (
                SELECT
                    reporting_period,
                    LEFT(reporting_period, 4) AS rp_year,
                    RIGHT(reporting_period, 2) AS rp_period,
                    d.pmid,
                    d.cmid,
                    basisname,
                    `type`,
                    ev_method,
                    ROUND(SUM(heads), 3) AS heads,
                    ROUND(SUM(hours1), 3) AS hours1,
                    ROUND(SUM(direct), 3) AS direct,
                    ROUND(SUM(overhead), 3) AS overhead,
                    ROUND(SUM(ga), 3) AS ga,
                    ROUND(SUM(com), 3) AS com,
                    ROUND(SUM(oh2), 3) AS oh2,
                    ROUND(SUM(fringe), 3) AS fringe,
                    ROUND(SUM(diralloc), 3) AS diralloc
                FROM
                    data_cost_rp d,
                    main mv
                where
                    d.pmid=mv.pmid and d.cmid=mv.cmid
                    $pmids_wc_rp_cam
                    AND reporting_period = '$reporting_period'
                GROUP BY
                    reporting_period,
                    d.pmid,
                    d.cmid,
                    basisname,
                    `type`,
                    ev_method
            )
        ";
        $junk = dbCall_IB($sql,$debug);

        //update data_cost_tp
        // Dollars
        $sql = "
            INSERT INTO data_cost_tp (pmid, cmid, reporting_period, ev_method, category, eoc, eoc_desc, wp, `type`, data_type, `year`, jan, feb, mar, apr, may,
             jun, jul, aug, sep, `oct`, nov,`dec`) (
            SELECT
               cd.pmid
              , cd.cmid
              , reporting_period
              , ev_method
              , category
              , eoc
              , eoc_desc
              , wp
              , `type`
              , 'Dollars' AS data_type
              , LEFT(date1,4) AS `year`
              , SUM(CASE WHEN (RIGHT(date1,2)=1) THEN ((direct+overhead+ga+com+oh2+fringe+diralloc)) ELSE 0 END) AS jan
              , SUM(CASE WHEN (RIGHT(date1,2)=2) THEN ((direct+overhead+ga+com+oh2+fringe+diralloc)) ELSE 0 END) AS feb
              , SUM(CASE WHEN (RIGHT(date1,2)=3) THEN ((direct+overhead+ga+com+oh2+fringe+diralloc)) ELSE 0 END) AS mar
              , SUM(CASE WHEN (RIGHT(date1,2)=4) THEN ((direct+overhead+ga+com+oh2+fringe+diralloc)) ELSE 0 END) AS apr
              , SUM(CASE WHEN (RIGHT(date1,2)=5) THEN ((direct+overhead+ga+com+oh2+fringe+diralloc)) ELSE 0 END) AS may
              , SUM(CASE WHEN (RIGHT(date1,2)=6) THEN ((direct+overhead+ga+com+oh2+fringe+diralloc)) ELSE 0 END) AS jun
              , SUM(CASE WHEN (RIGHT(date1,2)=7) THEN ((direct+overhead+ga+com+oh2+fringe+diralloc)) ELSE 0 END) AS jul
              , SUM(CASE WHEN (RIGHT(date1,2)=8) THEN ((direct+overhead+ga+com+oh2+fringe+diralloc)) ELSE 0 END) AS aug
              , SUM(CASE WHEN (RIGHT(date1,2)=9) THEN ((direct+overhead+ga+com+oh2+fringe+diralloc)) ELSE 0 END) AS sep
              , SUM(CASE WHEN (RIGHT(date1,2)=10) THEN ((direct+overhead+ga+com+oh2+fringe+diralloc)) ELSE 0 END) AS `oct`
              , SUM(CASE WHEN (RIGHT(date1,2)=11) THEN ((direct+overhead+ga+com+oh2+fringe+diralloc)) ELSE 0 END) AS nov
              , SUM(CASE WHEN (RIGHT(date1,2)=12) THEN ((direct+overhead+ga+com+oh2+fringe+diralloc)) ELSE 0 END) AS `dec`
            FROM
              data_cost cd $join_wc
            WHERE
                $pmids_wc
                and `type` IN ('Actuals','BCWS','ETC')
                AND reporting_period = '$reporting_period'
            GROUP BY reporting_period
              , cd.pmid
              , cd.cmid
              , Category
              , ev_method
              , eoc
              , eoc_desc
              , wp
              , `Type`
              , LEFT(date1,4)
            )
        ";
        $junk = dbCall_IB($sql,$debug);

        // PrimeOH
        $sql = "
            INSERT INTO data_cost_tp (pmid, cmid, reporting_period, ev_method, category, eoc, eoc_desc, wp, `type`, data_type, `year`, jan, feb, mar, apr, may,
             jun, jul, aug, sep, `oct`, nov,`dec`) (
            SELECT
               cd.pmid
              , cd.cmid
              , reporting_period
              , ev_method
              , category
              , eoc
              , eoc_desc
              , wp
              , `type`
              , 'PrimeOH Dollars' AS data_type
              , LEFT(date1,4) AS `year`
              , SUM(CASE WHEN (RIGHT(date1,2)=1) THEN ((direct+overhead)) ELSE 0 END) AS jan
              , SUM(CASE WHEN (RIGHT(date1,2)=2) THEN ((direct+overhead)) ELSE 0 END) AS feb
              , SUM(CASE WHEN (RIGHT(date1,2)=3) THEN ((direct+overhead)) ELSE 0 END) AS mar
              , SUM(CASE WHEN (RIGHT(date1,2)=4) THEN ((direct+overhead)) ELSE 0 END) AS apr
              , SUM(CASE WHEN (RIGHT(date1,2)=5) THEN ((direct+overhead)) ELSE 0 END) AS may
              , SUM(CASE WHEN (RIGHT(date1,2)=6) THEN ((direct+overhead)) ELSE 0 END) AS jun
              , SUM(CASE WHEN (RIGHT(date1,2)=7) THEN ((direct+overhead)) ELSE 0 END) AS jul
              , SUM(CASE WHEN (RIGHT(date1,2)=8) THEN ((direct+overhead)) ELSE 0 END) AS aug
              , SUM(CASE WHEN (RIGHT(date1,2)=9) THEN ((direct+overhead)) ELSE 0 END) AS sep
              , SUM(CASE WHEN (RIGHT(date1,2)=10) THEN ((direct+overhead)) ELSE 0 END) AS `oct`
              , SUM(CASE WHEN (RIGHT(date1,2)=11) THEN ((direct+overhead)) ELSE 0 END) AS nov
              , SUM(CASE WHEN (RIGHT(date1,2)=12) THEN ((direct+overhead)) ELSE 0 END) AS `dec`
            FROM
              data_cost cd $join_wc
            WHERE
                $pmids_wc
                and `type` IN ('Actuals','BCWS','ETC')
                AND reporting_period = '$reporting_period'
            GROUP BY reporting_period
              , cd.pmid
              , cd.cmid
              , Category
              , ev_method
              , eoc
              , eoc_desc
              , wp
              , `Type`
              , LEFT(date1,4)
            )
        ";
        $junk = dbCall_IB($sql,$debug);

        // Hours
        $sql = "
            INSERT INTO data_cost_tp (pmid, cmid, reporting_period, ev_method, Category, eoc, eoc_desc, wp, `Type`, data_type, `Year`, Jan, Feb, Mar, Apr, May, Jun, Jul, Aug, Sep, `Oct`, Nov,`Dec`) (
            SELECT
               cd.pmid
              , cd.cmid
              , reporting_period
              , ev_method
              , Category
              , eoc
              , eoc_desc
              , wp
              , `Type`
              , 'Hours' AS data_type
              , LEFT(date1,4) AS `Year`
              , SUM(CASE WHEN (RIGHT(date1,2)=1) THEN (hours1) ELSE 0 END) AS Jan
              , SUM(CASE WHEN (RIGHT(date1,2)=2) THEN (hours1) ELSE 0 END) AS Feb
              , SUM(CASE WHEN (RIGHT(date1,2)=3) THEN (hours1) ELSE 0 END) AS Mar
              , SUM(CASE WHEN (RIGHT(date1,2)=4) THEN (hours1) ELSE 0 END) AS Apr
              , SUM(CASE WHEN (RIGHT(date1,2)=5) THEN (hours1) ELSE 0 END) AS May
              , SUM(CASE WHEN (RIGHT(date1,2)=6) THEN (hours1) ELSE 0 END) AS Jun
              , SUM(CASE WHEN (RIGHT(date1,2)=7) THEN (hours1) ELSE 0 END) AS Jul
              , SUM(CASE WHEN (RIGHT(date1,2)=8) THEN (hours1) ELSE 0 END) AS Aug
              , SUM(CASE WHEN (RIGHT(date1,2)=9) THEN (hours1) ELSE 0 END) AS Sep
              , SUM(CASE WHEN (RIGHT(date1,2)=10) THEN (hours1) ELSE 0 END) AS `Oct`
              , SUM(CASE WHEN (RIGHT(date1,2)=11) THEN (hours1) ELSE 0 END) AS Nov
              , SUM(CASE WHEN (RIGHT(date1,2)=12) THEN (hours1) ELSE 0 END) AS `Dec`
            FROM
              data_cost cd $join_wc
            WHERE
                $pmids_wc
                and `type` IN ('Actuals','BCWS','ETC')
                AND reporting_period = '$reporting_period'
            GROUP BY
                reporting_period
              , cd.pmid
              , cd.cmid
              , Category
              , ev_method
              , eoc
              , eoc_desc
              , wp
              , `Type`
              , LEFT(date1,4)
            )
        ";
        $junk = dbCall_IB($sql,$debug);

        // Heads
        $sql = "
            INSERT INTO data_cost_tp (pmid, cmid, reporting_period, ev_method, category, eoc, eoc_desc, wp, `type`, data_type, `year`, jan, feb, mar, apr, may,
             jun, jul, aug, sep, `oct`, nov,`dec`) (
            SELECT
               cd.pmid
              , cd.cmid
              , reporting_period
              , ev_method
              , category
              , eoc
              , eoc_desc
              , wp
              , `type`
              , 'Heads' AS data_type
              , LEFT(date1,4) AS `year`
              , SUM(CASE WHEN (RIGHT(date1,2)=1) THEN (heads) ELSE 0 END) AS jan
              , SUM(CASE WHEN (RIGHT(date1,2)=2) THEN (heads) ELSE 0 END) AS feb
              , SUM(CASE WHEN (RIGHT(date1,2)=3) THEN (heads) ELSE 0 END) AS mar
              , SUM(CASE WHEN (RIGHT(date1,2)=4) THEN (heads) ELSE 0 END) AS apr
              , SUM(CASE WHEN (RIGHT(date1,2)=5) THEN (heads) ELSE 0 END) AS may
              , SUM(CASE WHEN (RIGHT(date1,2)=6) THEN (heads) ELSE 0 END) AS jun
              , SUM(CASE WHEN (RIGHT(date1,2)=7) THEN (heads) ELSE 0 END) AS jul
              , SUM(CASE WHEN (RIGHT(date1,2)=8) THEN (heads) ELSE 0 END) AS aug
              , SUM(CASE WHEN (RIGHT(date1,2)=9) THEN (heads) ELSE 0 END) AS sep
              , SUM(CASE WHEN (RIGHT(date1,2)=10) THEN (heads) ELSE 0 END) AS `oct`
              , SUM(CASE WHEN (RIGHT(date1,2)=11) THEN (heads) ELSE 0 END) AS nov
              , SUM(CASE WHEN (RIGHT(date1,2)=12) THEN (heads) ELSE 0 END) AS `dec`
            FROM
              data_cost cd $join_wc
            WHERE
                $pmids_wc
                and `type` IN ('Actuals','BCWS','ETC')
                AND reporting_period = '$reporting_period'
            GROUP BY
                reporting_period
              , cd.pmid
              , cd.cmid
              , Category
              , ev_method
              , eoc
              , eoc_desc
              , wp
              , `Type`
              , LEFT(date1,4)
            )
        ";
        $junk = dbCall_IB($sql,$debug);

        // Direct Dollars
        $sql = "
            INSERT INTO data_cost_tp (pmid, cmid, reporting_period, ev_method, category, eoc, eoc_desc, wp, `type`, data_type, `year`, jan, feb, mar, apr, may,
             jun, jul, aug, sep, `oct`, nov,`dec`) (
             SELECT
               cd.pmid
              , cd.cmid
              , reporting_period
              , ev_method
              , category
              , eoc
              , eoc_desc
              , wp
              , `type`
              , 'Direct Dollars' AS data_type
              , LEFT(date1,4) AS `year`
              , SUM(CASE WHEN (RIGHT(date1,2)=1) THEN (direct) ELSE 0 END) AS jan
              , SUM(CASE WHEN (RIGHT(date1,2)=2) THEN (direct) ELSE 0 END) AS feb
              , SUM(CASE WHEN (RIGHT(date1,2)=3) THEN (direct) ELSE 0 END) AS mar
              , SUM(CASE WHEN (RIGHT(date1,2)=4) THEN (direct) ELSE 0 END) AS apr
              , SUM(CASE WHEN (RIGHT(date1,2)=5) THEN (direct) ELSE 0 END) AS may
              , SUM(CASE WHEN (RIGHT(date1,2)=6) THEN (direct) ELSE 0 END) AS jun
              , SUM(CASE WHEN (RIGHT(date1,2)=7) THEN (direct) ELSE 0 END) AS jul
              , SUM(CASE WHEN (RIGHT(date1,2)=8) THEN (direct) ELSE 0 END) AS aug
              , SUM(CASE WHEN (RIGHT(date1,2)=9) THEN (direct) ELSE 0 END) AS sep
              , SUM(CASE WHEN (RIGHT(date1,2)=10) THEN (direct) ELSE 0 END) AS `oct`
              , SUM(CASE WHEN (RIGHT(date1,2)=11) THEN (direct) ELSE 0 END) AS nov
              , SUM(CASE WHEN (RIGHT(date1,2)=12) THEN (direct) ELSE 0 END) AS `dec`
            FROM
              data_cost cd $join_wc
            WHERE
                $pmids_wc
                and `type` IN ('Actuals','BCWS','ETC')
                AND reporting_period = '$reporting_period'
            GROUP BY
                reporting_period
              , cd.pmid
              , cd.cmid
              , Category
              , ev_method
              , eoc
              , eoc_desc
              , wp
              , `Type`
              , LEFT(date1,4)
            )
        ";
        $junk = dbCall_IB($sql,$debug);

        //update data_cost_tp_project
        $sql = "
            insert into data_cost_tp_project (pmid,category,data_type,reporting_period,ev_method,eoc,`type`,`year`,eoc_desc,jan,feb,mar,apr,may,
            jun,jul,aug,sep,`oct`,nov,`dec`)
            (
                SELECT
                    cd.pmid,
                    category,
                    data_type,
                    reporting_period,
                    ev_method,
                    eoc,
                    `type`,
                    `year`,
                    MAX(eoc_desc) AS eoc_desc,
                    SUM(jan) AS jan,
                    SUM(feb) AS feb,
                    SUM(mar) AS mar,
                    SUM(apr) AS apr,
                    SUM(may) AS may,
                    SUM(jun) AS jun,
                    SUM(jul) AS jul,
                    SUM(aug) AS aug,
                    SUM(sep) AS sep,
                    SUM(`oct`) AS `oct`,
                    SUM(nov) AS nov,
                    SUM(`dec`) AS `dec`
                FROM
                    data_cost_tp cd $join_wc
                WHERE
                    $pmids_wc
                    AND reporting_period = '$reporting_period'
                GROUP BY
                    cd.pmid,
                    category,
                    ev_method,
                    data_type,
                    reporting_period,
                    eoc,
                    `type`,
                    `year`
           )
        ";
        $junk = dbCall_IB($sql,$debug);
    }
    return true;
}
// -----------------------------------------------------------------
function prepareMPMData($debug=false,$sql_only=false)
{
    $all_sqls = "
        -- update meta-data fields in data_cap to contain non-NULL data;
        -- UPDATE data_cap SET wp_manager = 'NOCAM' WHERE wp_manager='' OR wp_manager IS NULL;

        -- update meta-data fields in data_cost to contain non-NULL data;
        UPDATE data_cost SET wp='NOWP' WHERE wp IS NULL or wp='';
        UPDATE data_cost SET Category='NOCATEGORY' WHERE Category IS NULL or Category='';
        UPDATE data_cost SET eoc='NOEOC' WHERE eoc IS NULL or eoc ='';
        UPDATE data_cost SET `year`=LEFT(date1,4);
        UPDATE data_cost SET `period`=RIGHT(date1,2);

        DROP TABLE data_cost_tp;
        CREATE TABLE `data_cost_tp` (
          `pmid` int(11) DEFAULT NULL,
          `cmid` int(11) DEFAULT NULL,
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `reporting_period` varchar(6) COLLATE latin1_general_ci DEFAULT NULL,
          `ev_method` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
          `Category` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
          `eoc` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
          `eoc_desc` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
          `wp_lead` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
          `wp` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
          `wp_desc` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
          `Type` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
          `data_type` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
          `year` decimal(5,0) DEFAULT NULL,
          `Jan` decimal(17,3) DEFAULT NULL,
          `Feb` decimal(17,3) DEFAULT NULL,
          `Mar` decimal(17,3) DEFAULT NULL,
          `Apr` decimal(17,3) DEFAULT NULL,
          `May` decimal(17,3) DEFAULT NULL,
          `Jun` decimal(17,3) DEFAULT NULL,
          `Jul` decimal(17,3) DEFAULT NULL,
          `Aug` decimal(17,3) DEFAULT NULL,
          `Sep` decimal(17,3) DEFAULT NULL,
          `Oct` decimal(17,3) DEFAULT NULL,
          `Nov` decimal(17,3) DEFAULT NULL,
          `Dec` decimal(17,3) DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `reporting_period` (`reporting_period`),
          KEY `data_type` (`data_type`),
          KEY `Type` (`Type`),
          KEY `ev_method` (`ev_method`),
          KEY `wp` (`wp`),
          KEY `pmid` (`pmid`),
          KEY `cmid` (`cmid`),
          KEY `pmidcmid` (`pmid`,`cmid`)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci
        ;
        INSERT INTO data_cost_tp (pmid, cmid, reporting_period, ev_method, Category, eoc, eoc_desc, wp, `Type`, data_type, `Year`, Jan, Feb, Mar, Apr, May, Jun, Jul, Aug, Sep, `Oct`, Nov,`Dec`) (
        SELECT
           pmid
          , cmid
          , reporting_period
          , ev_method
          , Category
          , eoc
          , eoc_desc
          , wp
          , `Type`
          , 'Dollars' AS data_type
          , LEFT(date1,4) AS `Year`
          , SUM(CASE WHEN (RIGHT(date1,2)=1) THEN ((direct+overhead+ga+com+oh2+fringe+diralloc)) ELSE 0 END) AS Jan
          , SUM(CASE WHEN (RIGHT(date1,2)=2) THEN ((direct+overhead+ga+com+oh2+fringe+diralloc)) ELSE 0 END) AS Feb
          , SUM(CASE WHEN (RIGHT(date1,2)=3) THEN ((direct+overhead+ga+com+oh2+fringe+diralloc)) ELSE 0 END) AS Mar
          , SUM(CASE WHEN (RIGHT(date1,2)=4) THEN ((direct+overhead+ga+com+oh2+fringe+diralloc)) ELSE 0 END) AS Apr
          , SUM(CASE WHEN (RIGHT(date1,2)=5) THEN ((direct+overhead+ga+com+oh2+fringe+diralloc)) ELSE 0 END) AS May
          , SUM(CASE WHEN (RIGHT(date1,2)=6) THEN ((direct+overhead+ga+com+oh2+fringe+diralloc)) ELSE 0 END) AS Jun
          , SUM(CASE WHEN (RIGHT(date1,2)=7) THEN ((direct+overhead+ga+com+oh2+fringe+diralloc)) ELSE 0 END) AS Jul
          , SUM(CASE WHEN (RIGHT(date1,2)=8) THEN ((direct+overhead+ga+com+oh2+fringe+diralloc)) ELSE 0 END) AS Aug
          , SUM(CASE WHEN (RIGHT(date1,2)=9) THEN ((direct+overhead+ga+com+oh2+fringe+diralloc)) ELSE 0 END) AS Sep
          , SUM(CASE WHEN (RIGHT(date1,2)=10) THEN ((direct+overhead+ga+com+oh2+fringe+diralloc)) ELSE 0 END) AS `Oct`
          , SUM(CASE WHEN (RIGHT(date1,2)=11) THEN ((direct+overhead+ga+com+oh2+fringe+diralloc)) ELSE 0 END) AS Nov
          , SUM(CASE WHEN (RIGHT(date1,2)=12) THEN ((direct+overhead+ga+com+oh2+fringe+diralloc)) ELSE 0 END) AS `Dec`
        FROM
          data_cost
        WHERE
            `type` IN ('Actuals','BCWS','ETC')
        GROUP BY reporting_period
          , pmid
          , cmid
          , Category
          , ev_method
          , eoc
          , eoc_desc
          , wp
          , `Type`
          , LEFT(date1,4) -- `Year`
        );

        INSERT INTO data_cost_tp (pmid, cmid, reporting_period, ev_method, Category, eoc, eoc_desc, wp, `Type`, data_type, `Year`, Jan, Feb, Mar, Apr, May, Jun, Jul, Aug, Sep, `Oct`, Nov,`Dec`) (
        SELECT
           pmid
          , cmid
          , reporting_period
          , ev_method
          , Category
          , eoc
          , eoc_desc
          , wp
          , `Type`
          , 'Hours' AS data_type
          , LEFT(date1,4) AS `Year`
          , SUM(CASE WHEN (RIGHT(date1,2)=1) THEN (hours1) ELSE 0 END) AS Jan
          , SUM(CASE WHEN (RIGHT(date1,2)=2) THEN (hours1) ELSE 0 END) AS Feb
          , SUM(CASE WHEN (RIGHT(date1,2)=3) THEN (hours1) ELSE 0 END) AS Mar
          , SUM(CASE WHEN (RIGHT(date1,2)=4) THEN (hours1) ELSE 0 END) AS Apr
          , SUM(CASE WHEN (RIGHT(date1,2)=5) THEN (hours1) ELSE 0 END) AS May
          , SUM(CASE WHEN (RIGHT(date1,2)=6) THEN (hours1) ELSE 0 END) AS Jun
          , SUM(CASE WHEN (RIGHT(date1,2)=7) THEN (hours1) ELSE 0 END) AS Jul
          , SUM(CASE WHEN (RIGHT(date1,2)=8) THEN (hours1) ELSE 0 END) AS Aug
          , SUM(CASE WHEN (RIGHT(date1,2)=9) THEN (hours1) ELSE 0 END) AS Sep
          , SUM(CASE WHEN (RIGHT(date1,2)=10) THEN (hours1) ELSE 0 END) AS `Oct`
          , SUM(CASE WHEN (RIGHT(date1,2)=11) THEN (hours1) ELSE 0 END) AS Nov
          , SUM(CASE WHEN (RIGHT(date1,2)=12) THEN (hours1) ELSE 0 END) AS `Dec`
        FROM
          data_cost
        WHERE
            `type` IN ('Actuals','BCWS','ETC')
        GROUP BY reporting_period
          , pmid
          , cmid
          , Category
          , ev_method
          , eoc
          , eoc_desc
          , wp
          , `Type`
          , LEFT(date1,4) -- `Year`
        );

        INSERT INTO data_cost_tp (pmid, cmid, reporting_period, ev_method, Category, eoc, eoc_desc, wp, `Type`, data_type, `Year`, Jan, Feb, Mar, Apr, May, Jun, Jul, Aug, Sep, `Oct`, Nov,`Dec`) (
        SELECT
           pmid
          , cmid
          , reporting_period
          , ev_method
          , Category
          , eoc
          , eoc_desc
          , wp
          , `Type`
          , 'Heads' AS data_type
          , LEFT(date1,4) AS `Year`
          , SUM(CASE WHEN (RIGHT(date1,2)=1) THEN (heads) ELSE 0 END) AS Jan
          , SUM(CASE WHEN (RIGHT(date1,2)=2) THEN (heads) ELSE 0 END) AS Feb
          , SUM(CASE WHEN (RIGHT(date1,2)=3) THEN (heads) ELSE 0 END) AS Mar
          , SUM(CASE WHEN (RIGHT(date1,2)=4) THEN (heads) ELSE 0 END) AS Apr
          , SUM(CASE WHEN (RIGHT(date1,2)=5) THEN (heads) ELSE 0 END) AS May
          , SUM(CASE WHEN (RIGHT(date1,2)=6) THEN (heads) ELSE 0 END) AS Jun
          , SUM(CASE WHEN (RIGHT(date1,2)=7) THEN (heads) ELSE 0 END) AS Jul
          , SUM(CASE WHEN (RIGHT(date1,2)=8) THEN (heads) ELSE 0 END) AS Aug
          , SUM(CASE WHEN (RIGHT(date1,2)=9) THEN (heads) ELSE 0 END) AS Sep
          , SUM(CASE WHEN (RIGHT(date1,2)=10) THEN (heads) ELSE 0 END) AS `Oct`
          , SUM(CASE WHEN (RIGHT(date1,2)=11) THEN (heads) ELSE 0 END) AS Nov
          , SUM(CASE WHEN (RIGHT(date1,2)=12) THEN (heads) ELSE 0 END) AS `Dec`
        FROM
          data_cost
        WHERE
            `type` IN ('Actuals','BCWS','ETC')
        GROUP BY reporting_period
          , pmid
          , cmid
          , Category
          , ev_method
          , eoc
          , eoc_desc
          , wp
          , `Type`
          , LEFT(date1,4) -- `Year`
        );

        INSERT INTO data_cost_tp (pmid, cmid, reporting_period, ev_method, Category, eoc, eoc_desc, wp, `Type`, data_type, `Year`, Jan, Feb, Mar, Apr, May, Jun, Jul, Aug, Sep, `Oct`, Nov,`Dec`) (
        SELECT
           pmid
          , cmid
          , reporting_period
          , ev_method
          , Category
          , eoc
          , eoc_desc
          , wp
          , `Type`
          , 'Direct Dollars' AS data_type
          , LEFT(date1,4) AS `Year`
          , SUM(CASE WHEN (RIGHT(date1,2)=1) THEN (direct) ELSE 0 END) AS Jan
          , SUM(CASE WHEN (RIGHT(date1,2)=2) THEN (direct) ELSE 0 END) AS Feb
          , SUM(CASE WHEN (RIGHT(date1,2)=3) THEN (direct) ELSE 0 END) AS Mar
          , SUM(CASE WHEN (RIGHT(date1,2)=4) THEN (direct) ELSE 0 END) AS Apr
          , SUM(CASE WHEN (RIGHT(date1,2)=5) THEN (direct) ELSE 0 END) AS May
          , SUM(CASE WHEN (RIGHT(date1,2)=6) THEN (direct) ELSE 0 END) AS Jun
          , SUM(CASE WHEN (RIGHT(date1,2)=7) THEN (direct) ELSE 0 END) AS Jul
          , SUM(CASE WHEN (RIGHT(date1,2)=8) THEN (direct) ELSE 0 END) AS Aug
          , SUM(CASE WHEN (RIGHT(date1,2)=9) THEN (direct) ELSE 0 END) AS Sep
          , SUM(CASE WHEN (RIGHT(date1,2)=10) THEN (direct) ELSE 0 END) AS `Oct`
          , SUM(CASE WHEN (RIGHT(date1,2)=11) THEN (direct) ELSE 0 END) AS Nov
          , SUM(CASE WHEN (RIGHT(date1,2)=12) THEN (direct) ELSE 0 END) AS `Dec`
        FROM
          data_cost
        WHERE
            `type` IN ('Actuals','BCWS','ETC')
        GROUP BY reporting_period
          , pmid
          , cmid
          , Category
          , ev_method
          , eoc
          , eoc_desc
          , wp
          , `Type`
          , LEFT(date1,4) -- `Year`
        );

        -- create summary tables;
        -- data_cost_project;
        DROP TABLE IF EXISTS `data_cost_project`;

        CREATE TABLE data_cost_project
        (
            SELECT
                pmid,
                category,
                date1,
                reporting_period,
                ev_method,
                eoc,
                basisname,
                `type`,
                `year`,
                period,
                SUM(heads) AS heads,
                SUM(hours1) AS hours1,
                SUM(direct) AS direct,
                SUM(overhead) AS overhead,
                SUM(ga) AS ga,
                SUM(com) AS com,
                SUM(oh2) AS oh2,
                SUM(fringe) AS fringe,
                SUM(diralloc) AS diralloc
            FROM
                data_cost
            GROUP BY
                pmid,
                category,
                ev_method,
                date1,
                reporting_period,
                eoc,
                basisname,
                `type`,
                `year`,
                period
        );

        -- add indexes to data_cost_project;
        ALTER TABLE `data_cost_project` ADD `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST ;
        ALTER TABLE `data_cost_project` ADD INDEX ( `pmid` );
        ALTER TABLE `data_cost_project` ADD INDEX ( `category` );
        ALTER TABLE `data_cost_project` ADD INDEX ( `date1` );
        ALTER TABLE `data_cost_project` ADD INDEX ( `reporting_period` );
        ALTER TABLE `data_cost_project` ADD INDEX ( `ev_method` );
        ALTER TABLE `data_cost_project` ADD INDEX ( `eoc` );
        ALTER TABLE `data_cost_project` ADD INDEX ( `type` );
        ALTER TABLE `data_cost_project` ADD INDEX ( `year` );
        ALTER TABLE `data_cost_project` ADD INDEX ( `period` );
        ALTER TABLE `data_cost_project` ADD INDEX ( `basisname` );

        -- drop and recreate data_cost_ca;
        drop table IF EXISTS data_cost_ca;
        CREATE TABLE `data_cost_ca` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `pmid` int(11) DEFAULT NULL,
          `cmid` int(11) DEFAULT NULL,
          `category` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
          `date1` varchar(6) COLLATE latin1_general_ci DEFAULT NULL,
          `reporting_period` varchar(6) COLLATE latin1_general_ci DEFAULT NULL,
          `ev_method` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
          `eoc` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
          `basisname` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
          `type` varchar(25) COLLATE latin1_general_ci DEFAULT NULL,
          `year` decimal(5,0) DEFAULT NULL,
          `period` decimal(5,0) DEFAULT NULL,
          `heads` decimal(14,4) DEFAULT NULL,
          `hours1` decimal(14,4) DEFAULT NULL,
          `direct` decimal(14,4) DEFAULT NULL,
          `overhead` decimal(14,4) DEFAULT NULL,
          `ga` decimal(14,4) DEFAULT NULL,
          `com` decimal(14,4) DEFAULT NULL,
          `oh2` decimal(14,4) DEFAULT NULL,
          `fringe` decimal(14,4) DEFAULT NULL,
          `diralloc` decimal(14,4) DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `pmid` (`pmid`),
          KEY `cmid` (`cmid`),
          KEY `category` (`category`),
          KEY `date1` (`date1`),
          KEY `reporting_period` (`reporting_period`),
          KEY `ev_method` (`ev_method`),
          KEY `eoc` (`eoc`),
          KEY `type` (`type`),
          KEY `year` (`year`),
          KEY `period` (`period`),
          KEY `basisname` (`basisname`)
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

        -- repopulate data_cost_ca;
        INSERT INTO data_cost_ca (
              `pmid`
              , `cmid`
              , `category`
              , `date1`
              , `reporting_period`
              , `ev_method`
              , `eoc`
              , `basisname`
              , `type`
              , `year`
              , `period`
              , `heads`
              , `hours1`
              , `direct`
              , `overhead`
              , `ga`
              , `com`
              , `oh2`
              , `fringe`
              , `diralloc`
            )
            (SELECT
              pmid
              , cmid
              , category
              , date1
              , reporting_period
              , ev_method
              , eoc
              , basisname
              , `type`
              , `year`
              , period
              , SUM(heads) AS heads
              , SUM(hours1) AS hours1
              , SUM(direct) AS direct
              , SUM(overhead) AS overhead
              , SUM(ga) AS ga
              , SUM(com) AS com
              , SUM(oh2) AS oh2
              , SUM(fringe) AS fringe
              , SUM(diralloc) AS diralloc
            FROM
              data_cost
            GROUP BY pmid
              , cmid
              , category
              , date1
              , reporting_period
              , ev_method
              , eoc
              , basisname
              , `type`
              , `year`
              , period) ;

        -- update meta-data fields in data_cost_tp to contain non-NULL data;
        UPDATE data_cost_tp SET Category='NOCATEGORY' WHERE Category IS NULL or Category='';
        UPDATE data_cost_tp SET wp='NOWP' WHERE wp IS NULL or wp='';
        UPDATE data_cost_tp SET eoc='NOEOC' WHERE eoc IS NULL or eoc='';

        -- data_cost_tp_project;
        DROP TABLE IF EXISTS `data_cost_tp_project`;

        CREATE TABLE data_cost_tp_project
        (
            SELECT
                pmid,
                category,
                data_type,
                reporting_period,
                ev_method,
                eoc,
                `type`,
                `year`,
                MAX(eoc_desc) AS eoc_desc,
                SUM(jan) AS jan,
                SUM(feb) AS feb,
                SUM(mar) AS mar,
                SUM(apr) AS apr,
                SUM(may) AS may,
                SUM(jun) AS jun,
                SUM(jul) AS jul,
                SUM(aug) AS aug,
                SUM(sep) AS sep,
                SUM(`oct`) AS 'oct',
                SUM(nov) AS nov,
                SUM(`dec`) AS 'dec'
            FROM
                data_cost_tp
            GROUP BY
                pmid,
                category,
                ev_method,
                data_type,
                reporting_period,
                eoc,
                `type`,
                `year`
        );

        -- add indexes to data_cost_tp_project;
        ALTER TABLE `data_cost_tp_project` ADD `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST ;
        ALTER TABLE `data_cost_tp_project` ADD INDEX ( `category` );
        ALTER TABLE `data_cost_tp_project` ADD INDEX ( `data_type` );
        ALTER TABLE `data_cost_tp_project` ADD INDEX ( `reporting_period` );
        ALTER TABLE `data_cost_tp_project` ADD INDEX ( `ev_method` );
        ALTER TABLE `data_cost_tp_project` ADD INDEX ( `eoc` );
        ALTER TABLE `data_cost_tp_project` ADD INDEX ( `type` );
        ALTER TABLE `data_cost_tp_project` ADD INDEX ( `year` );
    ";

    if($sql_only==false)
    {
        $sqls = explode(';',$all_sqls);
        foreach($sqls as $sql)
        {
            $sql = trim($sql);
            if($sql!='' and substr($sql,0,2)!='--') $junk = dbCall_IB($sql,$debug);
        }

        return true;
    }
    else
    {
        return $all_sqls;
    }
}
// -----------------------------------------------------------------
function prepareWSData($debug=false,$sql_only=false)
{
    $all_sqls = "

        -- update meta-data fields in data_cost_ws to contain non-NULL data;
        UPDATE data_cost_ws SET wp=wbsnum WHERE wbsnum LIKE '[%';
        UPDATE data_cost_ws SET wp = 'NOWP' WHERE wp='' or wp is null;

        -- create summary tables;
        -- data_cost_ws_project;
        DROP TABLE IF EXISTS `data_cost_ws_project`;

        CREATE TABLE data_cost_ws_project
        (
            SELECT
                pmid,
                reporting_period,
                data_type,
                ev_method,
                SUM(bcwscum) AS bcwscum,
                SUM(bcwpcum) AS bcwpcum,
                SUM(acwpcum) AS acwpcum,
                SUM(bcwscur) AS bcwscur,
                SUM(bcwpcur) AS bcwpcur,
                SUM(acwpcur) AS acwpcur,
                SUM(lre) AS lre,
                SUM(bac) AS bac,
                SUM(bcwpcum - acwpcum) AS cv,
                SUM(bcwpcum - bcwscum) AS sv,
                SUM(bcwpcur - acwpcur) AS cvcur,
                SUM(bcwpcur - bcwscur) AS svcur,
                SUM(bac - lre) AS vac,
                SUM(bac-bcwpcum) AS bcwr,
                (((SUM(bcwpcum) - SUM(acwpcum)) / SUM(bcwpcum)) * 100) AS cv_percentage,
                (((SUM(bcwpcum) - SUM(bcwscum)) / SUM(bcwscum)) * 100) AS sv_percentage,
                (((SUM(bcwpcur) - SUM(acwpcur)) / SUM(bcwpcur)) * 100) AS cv_percentage_cur,
                (((SUM(bcwpcur) - SUM(bcwscur)) / SUM(bcwscur)) * 100) AS sv_percentage_cur,
                (SUM(bcwpcum) / SUM(acwpcum))  AS cpi,
                (SUM(bcwpcum) / SUM(bcwscum))  AS spi,
                SUM(CASE WHEN ((ev_method<>'LE' OR ev_method IS NULL) AND elemtype<>'SE') THEN bcwpcum ELSE 0 END) bcwpcum_no_le_se,
                SUM(CASE WHEN ((ev_method<>'LE' OR ev_method IS NULL) AND elemtype<>'SE') THEN bcwscum ELSE 0 END) bcwscum_no_le_se,
                SUM(CASE WHEN (ev_method='LE' AND elemtype<>'SE') THEN bcwscum ELSE 0 END) bcwscum_le_se,
                SUM(CASE WHEN elemtype<>'SE' THEN bcwscum ELSE 0 END) bcwscum_se,
                (((SUM(bac) - SUM(bcwpcum)) / (SUM(lre) - SUM(acwpcum))) ) AS tcpi,
                ((SUM(bcwscum) / SUM(bac)) * 100) AS overall_schedule_percentage,
                ((SUM(bcwpcum) / SUM(bac)) * 100) AS overall_complete_percentage,
                ((SUM(acwpcum) / SUM(bac)) * 100) AS overall_spent_percentage,
                SUM(CASE WHEN wp='[MR]' THEN bac ELSE 0 END) AS mgt_reserve
            FROM
                data_cost_ws
            GROUP BY
                pmid,
                reporting_period,
                data_type,
                ev_method
        );

        -- add indexes to data_cost_ws_project;
        ALTER TABLE `data_cost_ws_project` ADD `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
        ALTER TABLE `data_cost_ws_project` ADD INDEX ( `reporting_period` );
        ALTER TABLE `data_cost_ws_project` ADD INDEX ( `data_type` );
        ALTER TABLE `data_cost_ws_project` ADD INDEX ( `ev_method` );

        -- data_cost_ws_mr;
        DROP TABLE IF EXISTS `data_cost_ws_mr`;

        CREATE TABLE data_cost_ws_mr
        (
            SELECT
                ws.pmid,
                ws.reporting_period,
                ws.data_type,
                SUM(ws.bac) AS mgt_reserve,
                SUM(ws.lre) AS eac_mgt_reserve
            FROM
                data_cost_ws ws
            where
                ws.wp='[MR]'
            GROUP BY
                ws.pmid,
                ws.reporting_period,
                ws.data_type
        );

        -- add indexes to data_cost_ws_mr;
        ALTER TABLE `data_cost_ws_mr` ADD `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
        ALTER TABLE `data_cost_ws_mr` ADD INDEX ( `reporting_period` );
        ALTER TABLE `data_cost_ws_mr` ADD INDEX ( `data_type` );

        -- data_cost_ws_cam;
        DROP TABLE IF EXISTS `data_cost_ws_cam`;

        CREATE TABLE data_cost_ws_cam
        (
            SELECT
                ws.pmid,
                ws.cmid,
                ws.reporting_period,
                ws.data_type,
                ws.ev_method,
                SUM(ws.bcwscum) AS bcwscum,
                SUM(ws.bcwpcum) AS bcwpcum,
                SUM(ws.acwpcum) AS acwpcum,
                SUM(ws.bcwscur) AS bcwscur,
                SUM(ws.bcwpcur) AS bcwpcur,
                SUM(ws.acwpcur) AS acwpcur,
                SUM(ws.lre) AS lre,
                SUM(ws.bac) AS bac,
                SUM(ws.bcwpcum - ws.acwpcum) AS cv,
                SUM(ws.bcwpcum - ws.bcwscum) AS sv,
                SUM(ws.bac - ws.lre) AS vac,
                SUM(ws.bac-ws.bcwpcum) AS bcwr,
                (((SUM(ws.bcwpcum) - SUM(ws.acwpcum)) / SUM(ws.bcwpcum)) * 100) AS cv_percentage,
                (((SUM(ws.bcwpcum) - SUM(ws.bcwscum)) / SUM(ws.bcwscum)) * 100) AS sv_percentage,
                (((SUM(ws.bcwpcur) - SUM(ws.acwpcur)) / SUM(ws.bcwpcur)) * 100) AS cv_percentage_cur,
                (((SUM(ws.bcwpcur) - SUM(ws.bcwscur)) / SUM(ws.bcwscur)) * 100) AS sv_percentage_cur,
                (SUM(ws.bcwpcum) / SUM(ws.acwpcum))  AS cpi,
                (SUM(ws.bcwpcum) / SUM(ws.bcwscum))   AS spi,
                SUM(CASE WHEN ((ev_method<>'LE' OR ev_method IS NULL) AND elemtype<>'SE') THEN bcwpcum ELSE 0 END) bcwpcum_no_le_se,
                SUM(CASE WHEN ((ev_method<>'LE' OR ev_method IS NULL) AND elemtype<>'SE') THEN bcwscum ELSE 0 END) bcwscum_no_le_se,
                SUM(CASE WHEN (ev_method='LE' AND elemtype<>'SE') THEN bcwscum ELSE 0 END) bcwscum_le_se,
                SUM(CASE WHEN elemtype<>'SE' THEN bcwscum ELSE 0 END) bcwscum_se,
                (((SUM(ws.bac) - SUM(ws.bcwpcum)) / (SUM(ws.lre) - SUM(ws.acwpcum))) ) AS tcpi,
                ((SUM(ws.bcwscum) / SUM(ws.bac)) * 100) AS overall_schedule_percentage,
                ((SUM(ws.bcwpcum) / SUM(ws.bac)) * 100) AS overall_complete_percentage,
                ((SUM(ws.acwpcum) / SUM(ws.bac)) * 100) AS overall_spent_percentage,
                SUM(CASE WHEN ws.wp='[MR]' THEN ws.bac ELSE 0 END) AS mgt_reserve
            FROM
                data_cost_ws ws,
                main mv
            where
                ws.pmid=mv.pmid
                and ws.cmid=mv.cmid
            GROUP BY
                ws.pmid,
                ws.cmid,
                mv.cam,
                ws.reporting_period,
                ws.data_type,
                ws.ev_method
        );

        -- add indexes to data_cost_ws_cam;
        ALTER TABLE `data_cost_ws_cam` ADD `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
        ALTER TABLE `data_cost_ws_cam` ADD INDEX ( `reporting_period` );
        ALTER TABLE `data_cost_ws_cam` ADD INDEX ( `data_type` );
        ALTER TABLE `data_cost_ws_cam` ADD INDEX ( `ev_method` );
    ";

    if($sql_only==false)
    {
        $sqls = explode(';',$all_sqls);
        foreach($sqls as $sql)
        {
            $sql = trim($sql);
            if($sql!='' and substr($sql,0,2)!='--') $junk = dbCall_IB($sql,$debug);
        }

        return true;
    }
    else
    {
        return $all_sqls;
    }
}
// -----------------------------------------------------------------
function prepareScheduleData($debug=false,$sql_only=false)
{
    //make sure cmid is filled in
    $sql = "update data_schedule sc set cmid=(select id from master_ca where pmid=sc.pmid and ca='NOCA' limit 1) where cmid is null or cmid=''";
    $junk = dbCall($sql,$debug);

    $sql = "update data_schedule_relationships sc set cmid=(select id from master_ca where pmid=sc.pmid and ca='NOCA' limit 1) where cmid is null or cmid=''";
    $junk = dbCall($sql,$debug);

    //update wp to NOWP where null or blank
    $sql = "update data_schedule set wp='NOWP' where wp is null or wp='';";
    $junk = dbCall($sql,$debug);

    $all_sqls = "

    -- drop and recreate data_schedule_metrics;
    drop table if exists data_schedule_metrics;

    CREATE TABLE `data_schedule_metrics` (
      `pmid` int(11) DEFAULT NULL,
      `cmid` int(11) DEFAULT NULL,
      `wp_lead` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
      `wp` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
      `task_id` int(11) DEFAULT NULL,
      `task_code` varchar(40) COLLATE latin1_general_ci DEFAULT NULL,
      `discrete_tasks` int(11) DEFAULT NULL,
      `discrete_open_tasks` int(11) DEFAULT NULL,
      `float_problems` int(11) DEFAULT NULL,
      `tasks_with_neg_float` int(11) DEFAULT NULL,
      `dp_top` int(11) DEFAULT NULL,
      `dp_bottom` int(11) DEFAULT NULL,
      `constraint_problems` int(11) DEFAULT NULL,
      `hard_constraint_problems` int(11) DEFAULT NULL,
      `logic_problems` int(11) DEFAULT NULL,
      `logic_problems_bottom` int(11) DEFAULT NULL,
      `tasks_completed` int(11) DEFAULT NULL,
      `tasks_completed_with_baselines` int(11) DEFAULT NULL,
      `tasks_baselined_to_complete` int(11) DEFAULT NULL,
      `tasks_baselined_to_start` int(11) DEFAULT NULL,
      `tasks_started` int(11) DEFAULT NULL,
      `tasks_started_with_baselines` int(11) DEFAULT NULL,
      `lags` int(11) DEFAULT NULL,
      `leads` int(11) DEFAULT NULL,
      `relationships` int(11) DEFAULT NULL,
      `pred_count` int(11) DEFAULT NULL,
      `succ_count` int(11) DEFAULT NULL,
      `resources` int(11) DEFAULT NULL,
      `resources_count` int(11) DEFAULT NULL,
      `missed_tasks` int(11) DEFAULT NULL,
      `missed_tasks_bottom` int(11) DEFAULT NULL,
      `invalid_dates` int(11) DEFAULT NULL,
      `invalid_dates_denominator` int(11) DEFAULT NULL,
      `pp_in_window` int(11) DEFAULT NULL,
      `mra_top` int(11) DEFAULT NULL,
      `mra_bottom` int(11) DEFAULT NULL,
      `mrf_top` int(11) DEFAULT NULL,
      `mrf_bottom` int(11) DEFAULT NULL,
      `no_pred_top` int(11) DEFAULT NULL,
      `no_pred_bottom` int(11) DEFAULT NULL,
      `no_succ_top` int(11) DEFAULT NULL,
      `icp_in` int(11) DEFAULT NULL,
      `icp_out` int(11) DEFAULT NULL,
      `icp_ref` int(11) DEFAULT NULL,
      `critical_top` int(11) DEFAULT NULL,
      `critical_bottom` int(11) DEFAULT NULL,
      `scritical_top` int(11) DEFAULT NULL,
      `future_actuals` int(11) DEFAULT NULL,
      `unstatused_bottom` int(11) DEFAULT NULL,
      `delayed_tasks_top` int(11) DEFAULT NULL
    ) ENGINE=BRIGHTHOUSE DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

    INSERT INTO data_schedule_metrics (

    SELECT

    mv.pmid,
    mv.cmid,
    sc.wp_lead,
    sc.wp,
    sc.task_id,
    sc.task_code,

    SUM(CASE WHEN
           (sc.EV_Method <> 'LE' OR sc.ev_method IS NULL)
           AND sc.task_name NOT LIKE '%Cost Collection%'
           AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
           AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND (sc.status_code LIKE '%' OR sc.status_code IS NULL) THEN 1 ELSE 0 END) AS discrete_tasks,

    SUM(CASE WHEN
           (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND sc.task_name NOT LIKE '%Cost Collection%'
           AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
           AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL) THEN 1 ELSE 0 END) AS discrete_open_tasks,

    SUM(CASE WHEN
           (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
        AND (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL)
        AND (sc.total_float_hr_cnt/8)>99
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.icp <> 'Ref' OR sc.icp IS NULL)
           AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
           AND sc.task_name NOT LIKE '%Cost Collection%'
     THEN 1 ELSE 0 END) AS float_problems,

    SUM(CASE WHEN
           (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
        AND (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL)
        AND (sc.total_float_hr_cnt/8)<0
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.icp <> 'Ref' OR sc.icp IS NULL)
           AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
           AND sc.task_name NOT LIKE '%Cost Collection%'
    THEN 1 ELSE 0 END) AS tasks_with_neg_float,

    SUM(CASE WHEN
           (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND sc.task_name NOT LIKE '%Cost Collection%'
        AND (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL)
        AND sc.start<LEFT(DATE_ADD(SYSDATE(),INTERVAL 90 DAY),10)
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.aps_code IS NULL OR sc.aps_code='')
        AND (sc.remain_drtn_hr_cnt/8)>42
           AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL) THEN 1 ELSE 0 END) AS dp_top,

    SUM(CASE WHEN
        (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL)
        AND sc.start<LEFT(DATE_ADD(SYSDATE(),INTERVAL 90 DAY),10)
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.aps_code IS NULL OR sc.aps_code='')
           AND (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
           AND sc.task_name NOT LIKE '%Cost Collection%'
    THEN 1 ELSE 0 END) AS dp_bottom,

    SUM(CASE WHEN
           (sc.status_code!='TK_Complete' OR sc.status_code IS NULL)
           AND (sc.aps_code IS NULL OR sc.aps_code='')
           AND (sc.cstr_type IS NOT NULL AND sc.cstr_type<>'')
           AND sc.cstr_type NOT LIKE '%ALAP'
           AND (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
           AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
           AND (sc.icp='' OR sc.icp IS NULL)
           AND sc.task_name NOT LIKE '%Cost Collection%'
    THEN 1 ELSE 0 END) AS constraint_problems,

    SUM(CASE WHEN
           (sc.status_code!='TK_Complete' OR sc.status_code IS NULL)
           AND (sc.aps_code IS NULL OR sc.aps_code='')
           AND (sc.cstr_type IS NOT NULL AND sc.cstr_type<>'')
           AND sc.cstr_type IN ('CS_MANDSTART','CS_MANDFIN')
           AND (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
           AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
           AND (sc.icp <> 'Ref' OR sc.icp IS NULL)
           AND sc.task_name NOT LIKE '%Cost Collection%'
    THEN 1 ELSE 0 END) AS hard_constraint_problems,

    SUM(CASE WHEN
        (sc.ev_method<>'LE' OR sc.ev_method IS NULL)
        AND sc.task_name NOT LIKE '%Cost Collection%'
        AND (sc.icp <> 'Ref' OR sc.icp IS NULL)
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND
            (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND     (
                (
                    (sc.num_pred IS NULL OR sc.num_pred<=0)
                    AND sc.status_code='TK_NotStart'
                    AND (sc.aps_code='' OR sc.aps_code IS NULL)
                    AND (sc.icp<>'In' OR sc.icp IS NULL)
                )
                OR
                (
                    (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL)
                    AND (sc.num_succ<=0 OR sc.num_succ IS NULL)
                    AND (sc.aps_code='' OR sc.aps_code IS NULL)
                    AND (sc.icp NOT IN ('Out','MM','End') OR sc.icp IS NULL)
                )
            )
    THEN 1 ELSE 0 END) AS logic_problems,

    SUM(CASE WHEN
        (sc.ev_method<>'LE' OR sc.ev_method IS NULL)
        AND sc.task_name NOT LIKE '%Cost Collection%'
        AND (sc.icp <> 'Ref' OR sc.icp IS NULL)
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL') OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL) THEN 1 ELSE 0 END) AS logic_problems_bottom,

    SUM(CASE WHEN
           (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND (sc.icp<>'Ref' OR sc.icp IS NULL)
        AND sc.status_code='TK_Complete'
    THEN 1 ELSE 0 END) AS tasks_completed,

    SUM(CASE WHEN
           (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND (sc.icp<>'Ref' OR sc.icp IS NULL)
        AND sc.status_code='TK_Complete'
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL') OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND sc.baseline_finish IS NOT NULL
        AND sc.baseline_finish <> '0000-00-00 00:00:00'
    THEN 1 ELSE 0 END) AS tasks_completed_with_baselines,

    SUM(CASE WHEN
           (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND (sc.icp<>'Ref' OR sc.icp IS NULL)
           AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND sc.baseline_finish<SYSDATE()
        AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND sc.baseline_finish IS NOT NULL
        AND sc.baseline_finish <> '0000-00-00 00:00:00'
    THEN 1 ELSE 0 END) AS tasks_baselined_to_complete,

    SUM(CASE WHEN
           (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND (sc.icp<>'Ref' OR sc.icp IS NULL)
        AND sc.baseline_start<SYSDATE()
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND sc.baseline_start IS NOT NULL
        AND sc.baseline_start <> '0000-00-00 00:00:00'
    THEN 1 ELSE 0 END) AS tasks_baselined_to_start,

    SUM(CASE WHEN
           (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND (sc.icp<>'Ref' OR sc.icp IS NULL)
        AND sc.start<SYSDATE()
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL) THEN 1 ELSE 0 END) AS tasks_started,

    SUM(CASE WHEN
           (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND (sc.icp<>'Ref' OR sc.icp IS NULL)
        AND sc.start<SYSDATE()
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL') OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND sc.baseline_start IS NOT NULL
        AND sc.baseline_start <> '0000-00-00 00:00:00'
        AND sc.status_code <> 'TK_NotStart'
    THEN 1 ELSE 0 END) AS tasks_started_with_baselines,

    SUM(CASE WHEN
        (lag>0)
        AND (sc.ev_method <> 'LE' OR sc.ev_method='' OR sc.ev_method IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL') OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.status_code='TK_NotStart')
    THEN lag ELSE 0 END) AS lags,

    SUM(CASE WHEN
        (lead>0)
        AND (sc.ev_method <> 'LE' OR sc.ev_method='' OR sc.ev_method IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL') OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.status_code='TK_NotStart')
    THEN lead ELSE 0 END) AS leads,

    SUM(CASE WHEN
        (link_type>0)
        AND (sc.ev_method <> 'LE' OR sc.ev_method='' OR sc.ev_method IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL') OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND (sc.task_type IN ('TT_Task','TT_Rsrc','TT_Mile','TT_FinMile'))
        AND (sc.status_code='TK_NotStart')
    THEN link_type ELSE 0 END) AS relationships,

    SUM(CASE WHEN
        (phys_complete_pct<>100.00 AND (sc.num_pred <> '' OR sc.num_pred IS NOT NULL))
        AND (sc.ev_method <> 'LE' OR sc.ev_method='' OR sc.ev_method IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL') OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.status_code='TK_NotStart')
    THEN sc.num_pred ELSE 0 END) AS pred_count,

    SUM(CASE WHEN
        (phys_complete_pct<>100.00 AND ((sc.num_pred = '' OR sc.num_pred IS NULL) AND (sc.num_succ <> '' OR sc.num_succ IS NOT NULL)))
        AND (sc.ev_method <> 'LE' OR sc.ev_method='' OR sc.ev_method IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL') OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.status_code='TK_NotStart')
    THEN sc.num_succ ELSE 0 END) AS succ_count,

    SUM(CASE WHEN
           ((sc.ev_method <> 'LE' AND sc.task_type <> 'TT_Mile' AND sc.task_type <> 'TT_FinMile') OR sc.ev_method IS NULL)
           AND (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL)
           AND (sc.start<>sc.finish)
           AND (sc.budgeted_hours IS NULL OR sc.budgeted_hours<=0)
           AND (sc.budgeted_dollars IS NULL OR sc.budgeted_dollars<=0) THEN 1 ELSE 0 END) AS resources,

    SUM(CASE WHEN
           ((sc.ev_method <> 'LE' AND sc.task_type <> 'TT_Mile' AND sc.task_type <> 'TT_FinMile') OR sc.ev_method IS NULL)
           AND (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL)
           AND (sc.start<>sc.finish)
    THEN 1 ELSE 0 END) AS resources_count,

    SUM(CASE WHEN
        (sc.ev_method <> 'LE' OR sc.ev_method='' OR sc.ev_method IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL') OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.icp<>'Ref' OR sc.icp IS NULL)
        AND sc.baseline_finish < SYSDATE()
        AND
        (
            (LEFT(sc.baseline_finish,10)<>'0000-00-00' AND sc.baseline_finish IS NOT NULL)
            AND
            (LEFT(sc.baseline_start,10)<>'0000-00-00' AND sc.baseline_start IS NOT NULL)
        )
        AND DATEDIFF(sc.baseline_finish,finish) < 0 THEN 1 ELSE 0 END) AS missed_tasks,

    SUM(CASE WHEN
       (sc.ev_method <> 'LE' OR sc.ev_method='' OR sc.ev_method IS NULL)
       AND (sc.work_package_type NOT IN ('MFG','MTL') OR sc.work_package_type='' OR sc.work_package_type IS NULL)
       AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
       AND (sc.icp<>'Ref' OR sc.icp IS NULL)
       AND sc.baseline_finish < SYSDATE()
       AND
       (
            (LEFT(sc.baseline_finish,10)<>'0000-00-00' AND sc.baseline_finish IS NOT NULL)
            AND
            (LEFT(sc.baseline_start,10)<>'0000-00-00' AND sc.baseline_start IS NOT NULL)
        )
    THEN 1 ELSE 0 END) AS missed_tasks_bottom,

    SUM(CASE WHEN
       sc.act_start_date > SYSDATE()
    OR sc.act_end_date > SYSDATE()
    THEN 1 ELSE 0 END) AS invalid_dates,

    SUM(CASE WHEN
       sc.act_start_date > SYSDATE()
    OR sc.act_end_date > SYSDATE()
    OR (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL) THEN 1 ELSE 0 END) AS invalid_dates_denominator,

    SUM(CASE WHEN
        (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL)
        AND (sc.start<=LEFT(DATE_ADD(SYSDATE(),INTERVAL 90 DAY),10) OR sc.baseline_start<=LEFT(DATE_ADD(SYSDATE(),INTERVAL 90 DAY),10))
        AND sc.task_type NOT IN ('TT_LOE','TT_WBS')
        AND (sc.aps_code IS NULL OR sc.aps_code='')
           AND (sc.ev_method = 'PP')
           AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
           AND sc.task_name NOT LIKE '%Cost Collection%'
    THEN 1 ELSE 0 END) AS pp_in_window,

    -- metrics page below
    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND (finish >= LEFT(DATE_ADD(SYSDATE(),INTERVAL -30 DAY),10) AND finish <= SYSDATE())
        AND task_type<>'TT_LOE'
        AND (icp<>'Ref' OR icp IS NULL)
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL)
        AND (finish<=baseline_finish)
    THEN 1 ELSE 0 END) AS mra_top,

    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND (finish >= LEFT(DATE_ADD(SYSDATE(),INTERVAL -30 DAY),10) AND finish <= SYSDATE())
        AND task_type<>'TT_LOE'
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL) THEN 1 ELSE 0 END) AS mra_bottom,

    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND (finish <= LEFT(DATE_ADD(SYSDATE(),INTERVAL 90 DAY),10) AND finish >= SYSDATE())
        AND task_type<>'TT_LOE'
        AND (icp<>'Ref' OR icp IS NULL)
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL)
        AND (finish<=baseline_finish)
    THEN 1 ELSE 0 END) AS mrf_top,

    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND (finish <= LEFT(DATE_ADD(SYSDATE(),INTERVAL 90 DAY),10) AND finish >= SYSDATE())
        AND task_type<>'TT_LOE'
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL) THEN 1 ELSE 0 END) AS mrf_bottom,


    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND task_type<>'TT_LOE'
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL)
        AND (num_pred<=0 OR num_pred IS NULL)
        AND (icp<>'Ref' OR icp IS NULL)
        AND (icp<>'In' OR icp IS NULL)
        AND status_code='TK_NotStart'
        AND (aps_code='' OR aps_code IS NULL) THEN 1 ELSE 0 END) AS no_pred_top,

    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND (icp<>'Ref' OR icp IS NULL)
        AND task_type<>'TT_LOE'
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL)
        AND (status_code<>'TK_Complete' OR status_code IS NULL) THEN 1 ELSE 0 END) AS no_pred_bottom,

    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND task_type<>'TT_LOE'
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL)
        AND (num_succ<=0 OR num_succ IS NULL)
        AND (icp<>'Ref' OR icp IS NULL)
        AND (icp<>'Out' OR icp IS NULL)
        AND (icp<>'MM' OR icp IS NULL)
        AND (status_code<>'TK_Complete' OR status_code IS NULL) THEN 1 ELSE 0 END) AS no_succ_top,

    SUM(CASE WHEN
        icp='In'
        AND (status_code<>'TK_Complete' OR status_code IS NULL) THEN 1 ELSE 0 END) AS icp_in,

    SUM(CASE WHEN
        icp IN ('Out','MM','End')
        AND (status_code<>'TK_Complete' OR status_code IS NULL) THEN 1 ELSE 0 END) AS icp_out,

    SUM(CASE WHEN
        icp='Ref'
        AND (status_code<>'TK_Complete' OR status_code IS NULL) THEN 1 ELSE 0 END) AS icp_ref,

    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND task_type<>'TT_LOE'
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL)
        AND (status_code<>'TK_Complete' OR status_code IS NULL)
        AND (total_float_hr_cnt<=0 OR total_float_hr_cnt IS NULL) THEN 1 ELSE 0 END) AS critical_top,

    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND task_type<>'TT_LOE'
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL)
        AND (status_code<>'TK_Complete' OR status_code IS NULL) THEN 1 ELSE 0 END) AS critical_bottom,

    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND task_type<>'TT_LOE'
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL)
        AND (status_code<>'TK_Complete' OR status_code IS NULL)
        AND ((total_float_hr_cnt/8)<=-1 OR total_float_hr_cnt IS NULL) THEN 1 ELSE 0 END) AS scritical_top,

    SUM(CASE WHEN
        sc.act_start_date > SYSDATE()
        OR sc.act_end_date > SYSDATE()
    THEN 1 ELSE 0 END) AS future_actuals,

    SUM(CASE WHEN
        (
            work_package_type NOT IN ('MFG','MTL')
            AND (status_code='TK_Active')
            AND (remain_work_qty<=0 OR remain_work_qty IS NULL)
        )
        OR
        (
            START > SYSDATE()+1
            AND (status_code='TK_NotStart')
            AND (aps_code='' OR aps_code IS NULL)
            AND (remain_work_qty<=0 OR remain_work_qty IS NULL)
            AND task_code NOT LIKE '%LBB%'
            AND task_type<>'TT_LOE'
            AND work_package_type NOT IN ('MFG','MTL')
        )
    THEN 1 ELSE 0 END) AS unstatused_bottom,

    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND task_type<>'TT_LOE'
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL)
        AND status_code='TK_NotStart'
        AND START>baseline_start
        AND finish<=baseline_finish
    THEN 1 ELSE 0 END) AS delayed_tasks_top

    FROM
      data_schedule sc,
      main mv

    WHERE sc.pmid = mv.pmid
      AND sc.cmid = mv.cmid
    GROUP BY
        sc.pmid,
        sc.cmid,
        sc.wp_lead,
        sc.wp,
        sc.task_id,
        sc.task_code
    );

    -- drop and recreate data_schedule_metrics;
    drop table if exists data_schedule_metrics_wp;
    CREATE TABLE `data_schedule_metrics_wp` (
      `pmid` int(11) DEFAULT NULL,
      `cmid` int(11) DEFAULT NULL,
      `wp_lead` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
      `wp` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
      `discrete_tasks` int(11) DEFAULT NULL,
      `discrete_open_tasks` int(11) DEFAULT NULL,
      `float_problems` int(11) DEFAULT NULL,
      `tasks_with_neg_float` int(11) DEFAULT NULL,
      `dp_top` int(11) DEFAULT NULL,
      `dp_bottom` int(11) DEFAULT NULL,
      `constraint_problems` int(11) DEFAULT NULL,
      `hard_constraint_problems` int(11) DEFAULT NULL,
      `logic_problems` int(11) DEFAULT NULL,
      `logic_problems_bottom` int(11) DEFAULT NULL,
      `tasks_completed` int(11) DEFAULT NULL,
      `tasks_completed_with_baselines` int(11) DEFAULT NULL,
      `tasks_baselined_to_complete` int(11) DEFAULT NULL,
      `tasks_baselined_to_start` int(11) DEFAULT NULL,
      `tasks_started` int(11) DEFAULT NULL,
      `tasks_started_with_baselines` int(11) DEFAULT NULL,
      `lags` int(11) DEFAULT NULL,
      `leads` int(11) DEFAULT NULL,
      `relationships` int(11) DEFAULT NULL,
      `pred_count` int(11) DEFAULT NULL,
      `succ_count` int(11) DEFAULT NULL,
      `resources` int(11) DEFAULT NULL,
      `resources_count` int(11) DEFAULT NULL,
      `missed_tasks` int(11) DEFAULT NULL,
      `missed_tasks_bottom` int(11) DEFAULT NULL,
      `invalid_dates` int(11) DEFAULT NULL,
      `invalid_dates_denominator` int(11) DEFAULT NULL,
      `pp_in_window` int(11) DEFAULT NULL,
      `mra_top` int(11) DEFAULT NULL,
      `mra_bottom` int(11) DEFAULT NULL,
      `mrf_top` int(11) DEFAULT NULL,
      `mrf_bottom` int(11) DEFAULT NULL,
      `no_pred_top` int(11) DEFAULT NULL,
      `no_pred_bottom` int(11) DEFAULT NULL,
      `no_succ_top` int(11) DEFAULT NULL,
      `icp_in` int(11) DEFAULT NULL,
      `icp_out` int(11) DEFAULT NULL,
      `icp_ref` int(11) DEFAULT NULL,
      `critical_top` int(11) DEFAULT NULL,
      `critical_bottom` int(11) DEFAULT NULL,
      `scritical_top` int(11) DEFAULT NULL,
      `future_actuals` int(11) DEFAULT NULL,
      `unstatused_bottom` int(11) DEFAULT NULL,
      `delayed_tasks_top` int(11) DEFAULT NULL
    ) ENGINE=BRIGHTHOUSE DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

INSERT INTO data_schedule_metrics_wp (

    SELECT

    mv.pmid,
    mv.cmid,
    sc.wp_lead,
    sc.wp,

    SUM(CASE WHEN
           (sc.EV_Method <> 'LE' OR sc.ev_method IS NULL)
           AND sc.task_name NOT LIKE '%Cost Collection%'
           AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
           AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND (sc.status_code LIKE '%' OR sc.status_code IS NULL) THEN 1 ELSE 0 END) AS discrete_tasks,

    SUM(CASE WHEN
           (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND sc.task_name NOT LIKE '%Cost Collection%'
           AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
           AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL) THEN 1 ELSE 0 END) AS discrete_open_tasks,

    SUM(CASE WHEN
           (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
        AND (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL)
        AND (sc.total_float_hr_cnt/8)>99
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.icp <> 'Ref' OR sc.icp IS NULL)
           AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
           AND sc.task_name NOT LIKE '%Cost Collection%'
     THEN 1 ELSE 0 END) AS float_problems,

    SUM(CASE WHEN
           (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
        AND (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL)
        AND (sc.total_float_hr_cnt/8)<0
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.icp <> 'Ref' OR sc.icp IS NULL)
           AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
           AND sc.task_name NOT LIKE '%Cost Collection%'
    THEN 1 ELSE 0 END) AS tasks_with_neg_float,

    SUM(CASE WHEN
           (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND sc.task_name NOT LIKE '%Cost Collection%'
        AND (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL)
        AND sc.start<LEFT(DATE_ADD(SYSDATE(),INTERVAL 90 DAY),10)
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.aps_code IS NULL OR sc.aps_code='')
        AND (sc.remain_drtn_hr_cnt/8)>42
           AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL) THEN 1 ELSE 0 END) AS dp_top,

    SUM(CASE WHEN
        (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL)
        AND sc.start<LEFT(DATE_ADD(SYSDATE(),INTERVAL 90 DAY),10)
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.aps_code IS NULL OR sc.aps_code='')
           AND (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
           AND sc.task_name NOT LIKE '%Cost Collection%'
    THEN 1 ELSE 0 END) AS dp_bottom,

    SUM(CASE WHEN
           (sc.status_code!='TK_Complete' OR sc.status_code IS NULL)
           AND (sc.aps_code IS NULL OR sc.aps_code='')
           AND (sc.cstr_type IS NOT NULL AND sc.cstr_type<>'')
           AND sc.cstr_type NOT LIKE '%ALAP'
           AND (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
           AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
           AND (sc.icp='' OR sc.icp IS NULL)
           AND sc.task_name NOT LIKE '%Cost Collection%'
    THEN 1 ELSE 0 END) AS constraint_problems,

    SUM(CASE WHEN
           (sc.status_code!='TK_Complete' OR sc.status_code IS NULL)
           AND (sc.aps_code IS NULL OR sc.aps_code='')
           AND (sc.cstr_type IS NOT NULL AND sc.cstr_type<>'')
           AND sc.cstr_type IN ('CS_MANDSTART','CS_MANDFIN')
           AND (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
           AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
           AND (sc.icp <> 'Ref' OR sc.icp IS NULL)
           AND sc.task_name NOT LIKE '%Cost Collection%'
    THEN 1 ELSE 0 END) AS hard_constraint_problems,

    SUM(CASE WHEN
        (sc.ev_method<>'LE' OR sc.ev_method IS NULL)
        AND sc.task_name NOT LIKE '%Cost Collection%'
        AND (sc.icp <> 'Ref' OR sc.icp IS NULL)
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND
            (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND     (
                (
                    (sc.num_pred IS NULL OR sc.num_pred<=0)
                    AND sc.status_code='TK_NotStart'
                    AND (sc.aps_code='' OR sc.aps_code IS NULL)
                    AND (sc.icp<>'In' OR sc.icp IS NULL)
                )
                OR
                (
                    (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL)
                    AND (sc.num_succ<=0 OR sc.num_succ IS NULL)
                    AND (sc.aps_code='' OR sc.aps_code IS NULL)
                    AND (sc.icp NOT IN ('Out','MM','End') OR sc.icp IS NULL)
                )
            )
    THEN 1 ELSE 0 END) AS logic_problems,

    SUM(CASE WHEN
        (sc.ev_method<>'LE' OR sc.ev_method IS NULL)
        AND sc.task_name NOT LIKE '%Cost Collection%'
        AND (sc.icp <> 'Ref' OR sc.icp IS NULL)
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL') OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL) THEN 1 ELSE 0 END) AS logic_problems_bottom,

    SUM(CASE WHEN
           (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND (sc.icp<>'Ref' OR sc.icp IS NULL)
        AND sc.status_code='TK_Complete'
    THEN 1 ELSE 0 END) AS tasks_completed,

    SUM(CASE WHEN
           (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND (sc.icp<>'Ref' OR sc.icp IS NULL)
        AND sc.status_code='TK_Complete'
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL') OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND sc.baseline_finish IS NOT NULL
        AND sc.baseline_finish <> '0000-00-00 00:00:00'
    THEN 1 ELSE 0 END) AS tasks_completed_with_baselines,

    SUM(CASE WHEN
           (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND (sc.icp<>'Ref' OR sc.icp IS NULL)
           AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND sc.baseline_finish<SYSDATE()
        AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND sc.baseline_finish IS NOT NULL
        AND sc.baseline_finish <> '0000-00-00 00:00:00'
    THEN 1 ELSE 0 END) AS tasks_baselined_to_complete,

    SUM(CASE WHEN
           (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND (sc.icp<>'Ref' OR sc.icp IS NULL)
        AND sc.baseline_start<SYSDATE()
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND sc.baseline_start IS NOT NULL
        AND sc.baseline_start <> '0000-00-00 00:00:00'
    THEN 1 ELSE 0 END) AS tasks_baselined_to_start,

    SUM(CASE WHEN
           (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND (sc.icp<>'Ref' OR sc.icp IS NULL)
        AND sc.start<SYSDATE()
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL) THEN 1 ELSE 0 END) AS tasks_started,

    SUM(CASE WHEN
           (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND (sc.icp<>'Ref' OR sc.icp IS NULL)
        AND sc.start<SYSDATE()
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL') OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND sc.baseline_start IS NOT NULL
        AND sc.baseline_start <> '0000-00-00 00:00:00'
        AND sc.status_code <> 'TK_NotStart'
    THEN 1 ELSE 0 END) AS tasks_started_with_baselines,

    SUM(CASE WHEN
        (lag>0)
        AND (sc.ev_method <> 'LE' OR sc.ev_method='' OR sc.ev_method IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL') OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.status_code='TK_NotStart')
    THEN lag ELSE 0 END) AS lags,

    SUM(CASE WHEN
        (lead>0)
        AND (sc.ev_method <> 'LE' OR sc.ev_method='' OR sc.ev_method IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL') OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.status_code='TK_NotStart')
    THEN lead ELSE 0 END) AS leads,

    SUM(CASE WHEN
        (link_type>0)
        AND (sc.ev_method <> 'LE' OR sc.ev_method='' OR sc.ev_method IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL') OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND (sc.task_type IN ('TT_Task','TT_Rsrc','TT_Mile','TT_FinMile'))
        AND (sc.status_code='TK_NotStart')
    THEN link_type ELSE 0 END) AS relationships,

    SUM(CASE WHEN
        (phys_complete_pct<>100.00 AND (sc.num_pred <> '' OR sc.num_pred IS NOT NULL))
        AND (sc.ev_method <> 'LE' OR sc.ev_method='' OR sc.ev_method IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL') OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.status_code='TK_NotStart')
    THEN sc.num_pred ELSE 0 END) AS pred_count,

    SUM(CASE WHEN
        (phys_complete_pct<>100.00 AND ((sc.num_pred = '' OR sc.num_pred IS NULL) AND (sc.num_succ <> '' OR sc.num_succ IS NOT NULL)))
        AND (sc.ev_method <> 'LE' OR sc.ev_method='' OR sc.ev_method IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL') OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.status_code='TK_NotStart')
    THEN sc.num_succ ELSE 0 END) AS succ_count,

    SUM(CASE WHEN
           ((sc.ev_method <> 'LE' AND sc.task_type <> 'TT_Mile' AND sc.task_type <> 'TT_FinMile') OR sc.ev_method IS NULL)
           AND (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL)
           AND (sc.start<>sc.finish)
           AND (sc.budgeted_hours IS NULL OR sc.budgeted_hours<=0)
           AND (sc.budgeted_dollars IS NULL OR sc.budgeted_dollars<=0) THEN 1 ELSE 0 END) AS resources,

    SUM(CASE WHEN
           ((sc.ev_method <> 'LE' AND sc.task_type <> 'TT_Mile' AND sc.task_type <> 'TT_FinMile') OR sc.ev_method IS NULL)
           AND (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL)
           AND (sc.start<>sc.finish)
    THEN 1 ELSE 0 END) AS resources_count,

    SUM(CASE WHEN
        (sc.ev_method <> 'LE' OR sc.ev_method='' OR sc.ev_method IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL') OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.icp<>'Ref' OR sc.icp IS NULL)
        AND sc.baseline_finish < SYSDATE()
        AND
        (
            (LEFT(sc.baseline_finish,10)<>'0000-00-00' AND sc.baseline_finish IS NOT NULL)
            AND
            (LEFT(sc.baseline_start,10)<>'0000-00-00' AND sc.baseline_start IS NOT NULL)
        )
        AND DATEDIFF(sc.baseline_finish,finish) < 0 THEN 1 ELSE 0 END) AS missed_tasks,

    SUM(CASE WHEN
       (sc.ev_method <> 'LE' OR sc.ev_method='' OR sc.ev_method IS NULL)
       AND (sc.work_package_type NOT IN ('MFG','MTL') OR sc.work_package_type='' OR sc.work_package_type IS NULL)
       AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
       AND (sc.icp<>'Ref' OR sc.icp IS NULL)
       AND sc.baseline_finish < SYSDATE()
       AND
       (
            (LEFT(sc.baseline_finish,10)<>'0000-00-00' AND sc.baseline_finish IS NOT NULL)
            AND
            (LEFT(sc.baseline_start,10)<>'0000-00-00' AND sc.baseline_start IS NOT NULL)
        )
    THEN 1 ELSE 0 END) AS missed_tasks_bottom,

    SUM(CASE WHEN
       sc.act_start_date > SYSDATE()
    OR sc.act_end_date > SYSDATE()
    THEN 1 ELSE 0 END) AS invalid_dates,

    SUM(CASE WHEN
       sc.act_start_date > SYSDATE()
    OR sc.act_end_date > SYSDATE()
    OR (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL) THEN 1 ELSE 0 END) AS invalid_dates_denominator,

    SUM(CASE WHEN
        (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL)
        AND (sc.start<=LEFT(DATE_ADD(SYSDATE(),INTERVAL 90 DAY),10) OR sc.baseline_start<=LEFT(DATE_ADD(SYSDATE(),INTERVAL 90 DAY),10))
        AND sc.task_type NOT IN ('TT_LOE','TT_WBS')
        AND (sc.aps_code IS NULL OR sc.aps_code='')
           AND (sc.ev_method = 'PP')
           AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
           AND sc.task_name NOT LIKE '%Cost Collection%'
    THEN 1 ELSE 0 END) AS pp_in_window,

    -- metrics page below
    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND (finish >= LEFT(DATE_ADD(SYSDATE(),INTERVAL -30 DAY),10) AND finish <= SYSDATE())
        AND task_type<>'TT_LOE'
        AND (icp<>'Ref' OR icp IS NULL)
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL)
        AND (finish<=baseline_finish)
    THEN 1 ELSE 0 END) AS mra_top,

    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND (finish >= LEFT(DATE_ADD(SYSDATE(),INTERVAL -30 DAY),10) AND finish <= SYSDATE())
        AND task_type<>'TT_LOE'
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL) THEN 1 ELSE 0 END) AS mra_bottom,

    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND (finish <= LEFT(DATE_ADD(SYSDATE(),INTERVAL 90 DAY),10) AND finish >= SYSDATE())
        AND task_type<>'TT_LOE'
        AND (icp<>'Ref' OR icp IS NULL)
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL)
        AND (finish<=baseline_finish)
    THEN 1 ELSE 0 END) AS mrf_top,

    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND (finish <= LEFT(DATE_ADD(SYSDATE(),INTERVAL 90 DAY),10) AND finish >= SYSDATE())
        AND task_type<>'TT_LOE'
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL) THEN 1 ELSE 0 END) AS mrf_bottom,


    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND task_type<>'TT_LOE'
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL)
        AND (num_pred<=0 OR num_pred IS NULL)
        AND (icp<>'Ref' OR icp IS NULL)
        AND (icp<>'In' OR icp IS NULL)
        AND status_code='TK_NotStart'
        AND (aps_code='' OR aps_code IS NULL) THEN 1 ELSE 0 END) AS no_pred_top,

    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND (icp<>'Ref' OR icp IS NULL)
        AND task_type<>'TT_LOE'
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL)
        AND (status_code<>'TK_Complete' OR status_code IS NULL) THEN 1 ELSE 0 END) AS no_pred_bottom,

    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND task_type<>'TT_LOE'
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL)
        AND (num_succ<=0 OR num_succ IS NULL)
        AND (icp<>'Ref' OR icp IS NULL)
        AND (icp<>'Out' OR icp IS NULL)
        AND (icp<>'MM' OR icp IS NULL)
        AND (status_code<>'TK_Complete' OR status_code IS NULL) THEN 1 ELSE 0 END) AS no_succ_top,

    SUM(CASE WHEN
        icp='In'
        AND (status_code<>'TK_Complete' OR status_code IS NULL) THEN 1 ELSE 0 END) AS icp_in,

    SUM(CASE WHEN
        icp IN ('Out','MM','End')
        AND (status_code<>'TK_Complete' OR status_code IS NULL) THEN 1 ELSE 0 END) AS icp_out,

    SUM(CASE WHEN
        icp='Ref'
        AND (status_code<>'TK_Complete' OR status_code IS NULL) THEN 1 ELSE 0 END) AS icp_ref,

    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND task_type<>'TT_LOE'
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL)
        AND (status_code<>'TK_Complete' OR status_code IS NULL)
        AND (total_float_hr_cnt<=0 OR total_float_hr_cnt IS NULL) THEN 1 ELSE 0 END) AS critical_top,

    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND task_type<>'TT_LOE'
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL)
        AND (status_code<>'TK_Complete' OR status_code IS NULL) THEN 1 ELSE 0 END) AS critical_bottom,

    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND task_type<>'TT_LOE'
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL)
        AND (status_code<>'TK_Complete' OR status_code IS NULL)
        AND ((total_float_hr_cnt/8)<=-1 OR total_float_hr_cnt IS NULL) THEN 1 ELSE 0 END) AS scritical_top,

    SUM(CASE WHEN
        sc.act_start_date > SYSDATE()
        OR sc.act_end_date > SYSDATE()
    THEN 1 ELSE 0 END) AS future_actuals,

    SUM(CASE WHEN
        (
            work_package_type NOT IN ('MFG','MTL')
            AND (status_code='TK_Active')
            AND (remain_work_qty<=0 OR remain_work_qty IS NULL)
        )
        OR
        (
            START > SYSDATE()+1
            AND (status_code='TK_NotStart')
            AND (aps_code='' OR aps_code IS NULL)
            AND (remain_work_qty<=0 OR remain_work_qty IS NULL)
            AND task_code NOT LIKE '%LBB%'
            AND task_type<>'TT_LOE'
            AND work_package_type NOT IN ('MFG','MTL')
        )
    THEN 1 ELSE 0 END) AS unstatused_bottom,

    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND task_type<>'TT_LOE'
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL)
        AND status_code='TK_NotStart'
        AND START>baseline_start
        AND finish<=baseline_finish
    THEN 1 ELSE 0 END) AS delayed_tasks_top

    FROM
      data_schedule sc,
      main mv

    WHERE sc.pmid = mv.pmid
      AND sc.cmid = mv.cmid
    GROUP BY
        sc.pmid,
        sc.cmid,
        sc.wp_lead,
        sc.wp
    );

    drop table if exists data_schedule_metrics_ca;
    CREATE TABLE `data_schedule_metrics_ca` (
      `pmid` int(11) DEFAULT NULL,
      `cmid` int(11) DEFAULT NULL,
      `discrete_tasks` int(11) DEFAULT NULL,
      `discrete_open_tasks` int(11) DEFAULT NULL,
      `float_problems` int(11) DEFAULT NULL,
      `tasks_with_neg_float` int(11) DEFAULT NULL,
      `dp_top` int(11) DEFAULT NULL,
      `dp_bottom` int(11) DEFAULT NULL,
      `constraint_problems` int(11) DEFAULT NULL,
      `hard_constraint_problems` int(11) DEFAULT NULL,
      `logic_problems` int(11) DEFAULT NULL,
      `logic_problems_bottom` int(11) DEFAULT NULL,
      `tasks_completed` int(11) DEFAULT NULL,
      `tasks_completed_with_baselines` int(11) DEFAULT NULL,
      `tasks_baselined_to_complete` int(11) DEFAULT NULL,
      `tasks_baselined_to_start` int(11) DEFAULT NULL,
      `tasks_started` int(11) DEFAULT NULL,
      `tasks_started_with_baselines` int(11) DEFAULT NULL,
      `lags` int(11) DEFAULT NULL,
      `leads` int(11) DEFAULT NULL,
      `relationships` int(11) DEFAULT NULL,
      `pred_count` int(11) DEFAULT NULL,
      `succ_count` int(11) DEFAULT NULL,
      `resources` int(11) DEFAULT NULL,
      `resources_count` int(11) DEFAULT NULL,
      `missed_tasks` int(11) DEFAULT NULL,
      `missed_tasks_bottom` int(11) DEFAULT NULL,
      `invalid_dates` int(11) DEFAULT NULL,
      `invalid_dates_denominator` int(11) DEFAULT NULL,
      `pp_in_window` int(11) DEFAULT NULL,
      `mra_top` int(11) DEFAULT NULL,
      `mra_bottom` int(11) DEFAULT NULL,
      `mrf_top` int(11) DEFAULT NULL,
      `mrf_bottom` int(11) DEFAULT NULL,
      `no_pred_top` int(11) DEFAULT NULL,
      `no_pred_bottom` int(11) DEFAULT NULL,
      `no_succ_top` int(11) DEFAULT NULL,
      `icp_in` int(11) DEFAULT NULL,
      `icp_out` int(11) DEFAULT NULL,
      `icp_ref` int(11) DEFAULT NULL,
      `critical_top` int(11) DEFAULT NULL,
      `critical_bottom` int(11) DEFAULT NULL,
      `scritical_top` int(11) DEFAULT NULL,
      `future_actuals` int(11) DEFAULT NULL,
      `unstatused_bottom` int(11) DEFAULT NULL,
      `delayed_tasks_top` int(11) DEFAULT NULL
    ) ENGINE=BRIGHTHOUSE DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

    INSERT INTO data_schedule_metrics_ca (

    SELECT

    mv.pmid,
    mv.cmid,

    SUM(CASE WHEN
           (sc.EV_Method <> 'LE' OR sc.ev_method IS NULL)
           AND sc.task_name NOT LIKE '%Cost Collection%'
           AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
           AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND (sc.status_code LIKE '%' OR sc.status_code IS NULL) THEN 1 ELSE 0 END) AS discrete_tasks,

    SUM(CASE WHEN
           (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND sc.task_name NOT LIKE '%Cost Collection%'
           AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
           AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL) THEN 1 ELSE 0 END) AS discrete_open_tasks,

    SUM(CASE WHEN
           (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
        AND (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL)
        AND (sc.total_float_hr_cnt/8)>99
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.icp <> 'Ref' OR sc.icp IS NULL)
           AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
           AND sc.task_name NOT LIKE '%Cost Collection%'
     THEN 1 ELSE 0 END) AS float_problems,

    SUM(CASE WHEN
           (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
        AND (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL)
        AND (sc.total_float_hr_cnt/8)<0
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.icp <> 'Ref' OR sc.icp IS NULL)
           AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
           AND sc.task_name NOT LIKE '%Cost Collection%'
    THEN 1 ELSE 0 END) AS tasks_with_neg_float,

    SUM(CASE WHEN
           (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND sc.task_name NOT LIKE '%Cost Collection%'
        AND (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL)
        AND sc.start<LEFT(DATE_ADD(SYSDATE(),INTERVAL 90 DAY),10)
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.aps_code IS NULL OR sc.aps_code='')
        AND (sc.remain_drtn_hr_cnt/8)>42
           AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL) THEN 1 ELSE 0 END) AS dp_top,

    SUM(CASE WHEN
        (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL)
        AND sc.start<LEFT(DATE_ADD(SYSDATE(),INTERVAL 90 DAY),10)
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.aps_code IS NULL OR sc.aps_code='')
           AND (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
           AND sc.task_name NOT LIKE '%Cost Collection%'
    THEN 1 ELSE 0 END) AS dp_bottom,

    SUM(CASE WHEN
           (sc.status_code!='TK_Complete' OR sc.status_code IS NULL)
           AND (sc.aps_code IS NULL OR sc.aps_code='')
           AND (sc.cstr_type IS NOT NULL AND sc.cstr_type<>'')
           AND sc.cstr_type NOT LIKE '%ALAP'
           AND (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
           AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
           AND (sc.icp='' OR sc.icp IS NULL)
           AND sc.task_name NOT LIKE '%Cost Collection%'
    THEN 1 ELSE 0 END) AS constraint_problems,

    SUM(CASE WHEN
           (sc.status_code!='TK_Complete' OR sc.status_code IS NULL)
           AND (sc.aps_code IS NULL OR sc.aps_code='')
           AND (sc.cstr_type IS NOT NULL AND sc.cstr_type<>'')
           AND sc.cstr_type IN ('CS_MANDSTART','CS_MANDFIN')
           AND (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
           AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
           AND (sc.icp <> 'Ref' OR sc.icp IS NULL)
           AND sc.task_name NOT LIKE '%Cost Collection%'
    THEN 1 ELSE 0 END) AS hard_constraint_problems,

    SUM(CASE WHEN
        (sc.ev_method<>'LE' OR sc.ev_method IS NULL)
        AND sc.task_name NOT LIKE '%Cost Collection%'
        AND (sc.icp <> 'Ref' OR sc.icp IS NULL)
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND
            (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND     (
                (
                    (sc.num_pred IS NULL OR sc.num_pred<=0)
                    AND sc.status_code='TK_NotStart'
                    AND (sc.aps_code='' OR sc.aps_code IS NULL)
                    AND (sc.icp<>'In' OR sc.icp IS NULL)
                )
                OR
                (
                    (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL)
                    AND (sc.num_succ<=0 OR sc.num_succ IS NULL)
                    AND (sc.aps_code='' OR sc.aps_code IS NULL)
                    AND (sc.icp NOT IN ('Out','MM','End') OR sc.icp IS NULL)
                )
            )
    THEN 1 ELSE 0 END) AS logic_problems,

    SUM(CASE WHEN
        (sc.ev_method<>'LE' OR sc.ev_method IS NULL)
        AND sc.task_name NOT LIKE '%Cost Collection%'
        AND (sc.icp <> 'Ref' OR sc.icp IS NULL)
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL') OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL) THEN 1 ELSE 0 END) AS logic_problems_bottom,

    SUM(CASE WHEN
           (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND (sc.icp<>'Ref' OR sc.icp IS NULL)
        AND sc.status_code='TK_Complete'
    THEN 1 ELSE 0 END) AS tasks_completed,

    SUM(CASE WHEN
           (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND (sc.icp<>'Ref' OR sc.icp IS NULL)
        AND sc.status_code='TK_Complete'
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL') OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND sc.baseline_finish IS NOT NULL
        AND sc.baseline_finish <> '0000-00-00 00:00:00'
    THEN 1 ELSE 0 END) AS tasks_completed_with_baselines,

    SUM(CASE WHEN
           (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND (sc.icp<>'Ref' OR sc.icp IS NULL)
           AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND sc.baseline_finish<SYSDATE()
        AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND sc.baseline_finish IS NOT NULL
        AND sc.baseline_finish <> '0000-00-00 00:00:00'
    THEN 1 ELSE 0 END) AS tasks_baselined_to_complete,

    SUM(CASE WHEN
           (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND (sc.icp<>'Ref' OR sc.icp IS NULL)
        AND sc.baseline_start<SYSDATE()
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND sc.baseline_start IS NOT NULL
        AND sc.baseline_start <> '0000-00-00 00:00:00'
    THEN 1 ELSE 0 END) AS tasks_baselined_to_start,

    SUM(CASE WHEN
           (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND (sc.icp<>'Ref' OR sc.icp IS NULL)
        AND sc.start<SYSDATE()
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL) THEN 1 ELSE 0 END) AS tasks_started,

    SUM(CASE WHEN
           (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND (sc.icp<>'Ref' OR sc.icp IS NULL)
        AND sc.start<SYSDATE()
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL') OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND sc.baseline_start IS NOT NULL
        AND sc.baseline_start <> '0000-00-00 00:00:00'
        AND sc.status_code <> 'TK_NotStart'
    THEN 1 ELSE 0 END) AS tasks_started_with_baselines,

    SUM(CASE WHEN
        (lag>0)
        AND (sc.ev_method <> 'LE' OR sc.ev_method='' OR sc.ev_method IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL') OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.status_code='TK_NotStart')
    THEN lag ELSE 0 END) AS lags,

    SUM(CASE WHEN
        (lead>0)
        AND (sc.ev_method <> 'LE' OR sc.ev_method='' OR sc.ev_method IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL') OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.status_code='TK_NotStart')
    THEN lead ELSE 0 END) AS leads,

    SUM(CASE WHEN
        (link_type>0)
        AND (sc.ev_method <> 'LE' OR sc.ev_method='' OR sc.ev_method IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL') OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND (sc.task_type IN ('TT_Task','TT_Rsrc','TT_Mile','TT_FinMile'))
        AND (sc.status_code='TK_NotStart')
    THEN link_type ELSE 0 END) AS relationships,

    SUM(CASE WHEN
        (phys_complete_pct<>100.00 AND (sc.num_pred <> '' OR sc.num_pred IS NOT NULL))
        AND (sc.ev_method <> 'LE' OR sc.ev_method='' OR sc.ev_method IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL') OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.status_code='TK_NotStart')
    THEN sc.num_pred ELSE 0 END) AS pred_count,

    SUM(CASE WHEN
        (phys_complete_pct<>100.00 AND ((sc.num_pred = '' OR sc.num_pred IS NULL) AND (sc.num_succ <> '' OR sc.num_succ IS NOT NULL)))
        AND (sc.ev_method <> 'LE' OR sc.ev_method='' OR sc.ev_method IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL') OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.status_code='TK_NotStart')
    THEN sc.num_succ ELSE 0 END) AS succ_count,

    SUM(CASE WHEN
           ((sc.ev_method <> 'LE' AND sc.task_type <> 'TT_Mile' AND sc.task_type <> 'TT_FinMile') OR sc.ev_method IS NULL)
           AND (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL)
           AND (sc.start<>sc.finish)
           AND (sc.budgeted_hours IS NULL OR sc.budgeted_hours<=0)
           AND (sc.budgeted_dollars IS NULL OR sc.budgeted_dollars<=0) THEN 1 ELSE 0 END) AS resources,

    SUM(CASE WHEN
           ((sc.ev_method <> 'LE' AND sc.task_type <> 'TT_Mile' AND sc.task_type <> 'TT_FinMile') OR sc.ev_method IS NULL)
           AND (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL)
           AND (sc.start<>sc.finish)
    THEN 1 ELSE 0 END) AS resources_count,

    SUM(CASE WHEN
        (sc.ev_method <> 'LE' OR sc.ev_method='' OR sc.ev_method IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL') OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.icp<>'Ref' OR sc.icp IS NULL)
        AND sc.baseline_finish < SYSDATE()
        AND
        (
            (LEFT(sc.baseline_finish,10)<>'0000-00-00' AND sc.baseline_finish IS NOT NULL)
            AND
            (LEFT(sc.baseline_start,10)<>'0000-00-00' AND sc.baseline_start IS NOT NULL)
        )
        AND DATEDIFF(sc.baseline_finish,finish) < 0 THEN 1 ELSE 0 END) AS missed_tasks,

    SUM(CASE WHEN
       (sc.ev_method <> 'LE' OR sc.ev_method='' OR sc.ev_method IS NULL)
       AND (sc.work_package_type NOT IN ('MFG','MTL') OR sc.work_package_type='' OR sc.work_package_type IS NULL)
       AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
       AND (sc.icp<>'Ref' OR sc.icp IS NULL)
       AND sc.baseline_finish < SYSDATE()
       AND
       (
            (LEFT(sc.baseline_finish,10)<>'0000-00-00' AND sc.baseline_finish IS NOT NULL)
            AND
            (LEFT(sc.baseline_start,10)<>'0000-00-00' AND sc.baseline_start IS NOT NULL)
        )
    THEN 1 ELSE 0 END) AS missed_tasks_bottom,

    SUM(CASE WHEN
       sc.act_start_date > SYSDATE()
    OR sc.act_end_date > SYSDATE()
    THEN 1 ELSE 0 END) AS invalid_dates,

    SUM(CASE WHEN
       sc.act_start_date > SYSDATE()
    OR sc.act_end_date > SYSDATE()
    OR (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL) THEN 1 ELSE 0 END) AS invalid_dates_denominator,

    SUM(CASE WHEN
        (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL)
        AND (sc.start<=LEFT(DATE_ADD(SYSDATE(),INTERVAL 90 DAY),10) OR sc.baseline_start<=LEFT(DATE_ADD(SYSDATE(),INTERVAL 90 DAY),10))
        AND sc.task_type NOT IN ('TT_LOE','TT_WBS')
        AND (sc.aps_code IS NULL OR sc.aps_code='')
           AND (sc.ev_method = 'PP')
           AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
           AND sc.task_name NOT LIKE '%Cost Collection%'
    THEN 1 ELSE 0 END) AS pp_in_window,

    -- metrics page below
    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND (finish >= LEFT(DATE_ADD(SYSDATE(),INTERVAL -30 DAY),10) AND finish <= SYSDATE())
        AND task_type<>'TT_LOE'
        AND (icp<>'Ref' OR icp IS NULL)
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL)
        AND (finish<=baseline_finish)
    THEN 1 ELSE 0 END) AS mra_top,

    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND (finish >= LEFT(DATE_ADD(SYSDATE(),INTERVAL -30 DAY),10) AND finish <= SYSDATE())
        AND task_type<>'TT_LOE'
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL) THEN 1 ELSE 0 END) AS mra_bottom,

    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND (finish <= LEFT(DATE_ADD(SYSDATE(),INTERVAL 90 DAY),10) AND finish >= SYSDATE())
        AND task_type<>'TT_LOE'
        AND (icp<>'Ref' OR icp IS NULL)
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL)
        AND (finish<=baseline_finish)
    THEN 1 ELSE 0 END) AS mrf_top,

    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND (finish <= LEFT(DATE_ADD(SYSDATE(),INTERVAL 90 DAY),10) AND finish >= SYSDATE())
        AND task_type<>'TT_LOE'
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL) THEN 1 ELSE 0 END) AS mrf_bottom,


    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND task_type<>'TT_LOE'
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL)
        AND (num_pred<=0 OR num_pred IS NULL)
        AND (icp<>'Ref' OR icp IS NULL)
        AND (icp<>'In' OR icp IS NULL)
        AND status_code='TK_NotStart'
        AND (aps_code='' OR aps_code IS NULL) THEN 1 ELSE 0 END) AS no_pred_top,

    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND (icp<>'Ref' OR icp IS NULL)
        AND task_type<>'TT_LOE'
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL)
        AND (status_code<>'TK_Complete' OR status_code IS NULL) THEN 1 ELSE 0 END) AS no_pred_bottom,

    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND task_type<>'TT_LOE'
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL)
        AND (num_succ<=0 OR num_succ IS NULL)
        AND (icp<>'Ref' OR icp IS NULL)
        AND (icp<>'Out' OR icp IS NULL)
        AND (icp<>'MM' OR icp IS NULL)
        AND (status_code<>'TK_Complete' OR status_code IS NULL) THEN 1 ELSE 0 END) AS no_succ_top,

    SUM(CASE WHEN
        icp='In'
        AND (status_code<>'TK_Complete' OR status_code IS NULL) THEN 1 ELSE 0 END) AS icp_in,

    SUM(CASE WHEN
        icp IN ('Out','MM','End')
        AND (status_code<>'TK_Complete' OR status_code IS NULL) THEN 1 ELSE 0 END) AS icp_out,

    SUM(CASE WHEN
        icp='Ref'
        AND (status_code<>'TK_Complete' OR status_code IS NULL) THEN 1 ELSE 0 END) AS icp_ref,

    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND task_type<>'TT_LOE'
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL)
        AND (status_code<>'TK_Complete' OR status_code IS NULL)
        AND (total_float_hr_cnt<=0 OR total_float_hr_cnt IS NULL) THEN 1 ELSE 0 END) AS critical_top,

    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND task_type<>'TT_LOE'
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL)
        AND (status_code<>'TK_Complete' OR status_code IS NULL) THEN 1 ELSE 0 END) AS critical_bottom,

    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND task_type<>'TT_LOE'
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL)
        AND (status_code<>'TK_Complete' OR status_code IS NULL)
        AND ((total_float_hr_cnt/8)<=-1 OR total_float_hr_cnt IS NULL) THEN 1 ELSE 0 END) AS scritical_top,

    SUM(CASE WHEN
        sc.act_start_date > SYSDATE()
        OR sc.act_end_date > SYSDATE()
    THEN 1 ELSE 0 END) AS future_actuals,

    SUM(CASE WHEN
        (
            work_package_type NOT IN ('MFG','MTL')
            AND (status_code='TK_Active')
            AND (remain_work_qty<=0 OR remain_work_qty IS NULL)
        )
        OR
        (
            START > SYSDATE()+1
            AND (status_code='TK_NotStart')
            AND (aps_code='' OR aps_code IS NULL)
            AND (remain_work_qty<=0 OR remain_work_qty IS NULL)
            AND task_code NOT LIKE '%LBB%'
            AND task_type<>'TT_LOE'
            AND work_package_type NOT IN ('MFG','MTL')
        )
    THEN 1 ELSE 0 END) AS unstatused_bottom,

    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND task_type<>'TT_LOE'
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL)
        AND status_code='TK_NotStart'
        AND START>baseline_start
        AND finish<=baseline_finish
    THEN 1 ELSE 0 END) AS delayed_tasks_top

    FROM
      data_schedule sc,
      main mv

    WHERE sc.pmid = mv.pmid
      AND sc.cmid = mv.cmid
    GROUP BY
        sc.pmid,
        sc.cmid
    );

    drop table if exists data_schedule_metrics_project;
    CREATE TABLE `data_schedule_metrics_project` (
      `pmid` int(11) DEFAULT NULL,
      `discrete_tasks` int(11) DEFAULT NULL,
      `discrete_open_tasks` int(11) DEFAULT NULL,
      `float_problems` int(11) DEFAULT NULL,
      `tasks_with_neg_float` int(11) DEFAULT NULL,
      `dp_top` int(11) DEFAULT NULL,
      `dp_bottom` int(11) DEFAULT NULL,
      `constraint_problems` int(11) DEFAULT NULL,
      `hard_constraint_problems` int(11) DEFAULT NULL,
      `logic_problems` int(11) DEFAULT NULL,
      `logic_problems_bottom` int(11) DEFAULT NULL,
      `tasks_completed` int(11) DEFAULT NULL,
      `tasks_completed_with_baselines` int(11) DEFAULT NULL,
      `tasks_baselined_to_complete` int(11) DEFAULT NULL,
      `tasks_baselined_to_start` int(11) DEFAULT NULL,
      `tasks_started` int(11) DEFAULT NULL,
      `tasks_started_with_baselines` int(11) DEFAULT NULL,
      `lags` int(11) DEFAULT NULL,
      `leads` int(11) DEFAULT NULL,
      `relationships` int(11) DEFAULT NULL,
      `pred_count` int(11) DEFAULT NULL,
      `succ_count` int(11) DEFAULT NULL,
      `resources` int(11) DEFAULT NULL,
      `resources_count` int(11) DEFAULT NULL,
      `missed_tasks` int(11) DEFAULT NULL,
      `missed_tasks_bottom` int(11) DEFAULT NULL,
      `invalid_dates` int(11) DEFAULT NULL,
      `invalid_dates_denominator` int(11) DEFAULT NULL,
      `pp_in_window` int(11) DEFAULT NULL,
      `mra_top` int(11) DEFAULT NULL,
      `mra_bottom` int(11) DEFAULT NULL,
      `mrf_top` int(11) DEFAULT NULL,
      `mrf_bottom` int(11) DEFAULT NULL,
      `no_pred_top` int(11) DEFAULT NULL,
      `no_pred_bottom` int(11) DEFAULT NULL,
      `no_succ_top` int(11) DEFAULT NULL,
      `icp_in` int(11) DEFAULT NULL,
      `icp_out` int(11) DEFAULT NULL,
      `icp_ref` int(11) DEFAULT NULL,
      `critical_top` int(11) DEFAULT NULL,
      `critical_bottom` int(11) DEFAULT NULL,
      `scritical_top` int(11) DEFAULT NULL,
      `future_actuals` int(11) DEFAULT NULL,
      `unstatused_bottom` int(11) DEFAULT NULL,
      `delayed_tasks_top` int(11) DEFAULT NULL
    ) ENGINE=BRIGHTHOUSE DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

    INSERT INTO data_schedule_metrics_project (

    SELECT

    mv.pmid,

    SUM(CASE WHEN
           (sc.EV_Method <> 'LE' OR sc.ev_method IS NULL)
           AND sc.task_name NOT LIKE '%Cost Collection%'
           AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
           AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND (sc.status_code LIKE '%' OR sc.status_code IS NULL) THEN 1 ELSE 0 END) AS discrete_tasks,

    SUM(CASE WHEN
           (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND sc.task_name NOT LIKE '%Cost Collection%'
           AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
           AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL) THEN 1 ELSE 0 END) AS discrete_open_tasks,

    SUM(CASE WHEN
           (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
        AND (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL)
        AND (sc.total_float_hr_cnt/8)>99
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.icp <> 'Ref' OR sc.icp IS NULL)
           AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
           AND sc.task_name NOT LIKE '%Cost Collection%'
     THEN 1 ELSE 0 END) AS float_problems,

    SUM(CASE WHEN
           (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
        AND (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL)
        AND (sc.total_float_hr_cnt/8)<0
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.icp <> 'Ref' OR sc.icp IS NULL)
           AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
           AND sc.task_name NOT LIKE '%Cost Collection%'
    THEN 1 ELSE 0 END) AS tasks_with_neg_float,

    SUM(CASE WHEN
           (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND sc.task_name NOT LIKE '%Cost Collection%'
        AND (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL)
        AND sc.start<LEFT(DATE_ADD(SYSDATE(),INTERVAL 90 DAY),10)
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.aps_code IS NULL OR sc.aps_code='')
        AND (sc.remain_drtn_hr_cnt/8)>42
           AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL) THEN 1 ELSE 0 END) AS dp_top,

    SUM(CASE WHEN
        (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL)
        AND sc.start<LEFT(DATE_ADD(SYSDATE(),INTERVAL 90 DAY),10)
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.aps_code IS NULL OR sc.aps_code='')
           AND (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
           AND sc.task_name NOT LIKE '%Cost Collection%'
    THEN 1 ELSE 0 END) AS dp_bottom,

    SUM(CASE WHEN
           (sc.status_code!='TK_Complete' OR sc.status_code IS NULL)
           AND (sc.aps_code IS NULL OR sc.aps_code='')
           AND (sc.cstr_type IS NOT NULL AND sc.cstr_type<>'')
           AND sc.cstr_type NOT LIKE '%ALAP'
           AND (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
           AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
           AND (sc.icp='' OR sc.icp IS NULL)
           AND sc.task_name NOT LIKE '%Cost Collection%'
    THEN 1 ELSE 0 END) AS constraint_problems,

    SUM(CASE WHEN
           (sc.status_code!='TK_Complete' OR sc.status_code IS NULL)
           AND (sc.aps_code IS NULL OR sc.aps_code='')
           AND (sc.cstr_type IS NOT NULL AND sc.cstr_type<>'')
           AND sc.cstr_type IN ('CS_MANDSTART','CS_MANDFIN')
           AND (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
           AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
           AND (sc.icp <> 'Ref' OR sc.icp IS NULL)
           AND sc.task_name NOT LIKE '%Cost Collection%'
    THEN 1 ELSE 0 END) AS hard_constraint_problems,

    SUM(CASE WHEN
        (sc.ev_method<>'LE' OR sc.ev_method IS NULL)
        AND sc.task_name NOT LIKE '%Cost Collection%'
        AND (sc.icp <> 'Ref' OR sc.icp IS NULL)
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND
            (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND     (
                (
                    (sc.num_pred IS NULL OR sc.num_pred<=0)
                    AND sc.status_code='TK_NotStart'
                    AND (sc.aps_code='' OR sc.aps_code IS NULL)
                    AND (sc.icp<>'In' OR sc.icp IS NULL)
                )
                OR
                (
                    (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL)
                    AND (sc.num_succ<=0 OR sc.num_succ IS NULL)
                    AND (sc.aps_code='' OR sc.aps_code IS NULL)
                    AND (sc.icp NOT IN ('Out','MM','End') OR sc.icp IS NULL)
                )
            )
    THEN 1 ELSE 0 END) AS logic_problems,

    SUM(CASE WHEN
        (sc.ev_method<>'LE' OR sc.ev_method IS NULL)
        AND sc.task_name NOT LIKE '%Cost Collection%'
        AND (sc.icp <> 'Ref' OR sc.icp IS NULL)
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL') OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL) THEN 1 ELSE 0 END) AS logic_problems_bottom,

    SUM(CASE WHEN
           (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND (sc.icp<>'Ref' OR sc.icp IS NULL)
        AND sc.status_code='TK_Complete'
    THEN 1 ELSE 0 END) AS tasks_completed,

    SUM(CASE WHEN
           (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND (sc.icp<>'Ref' OR sc.icp IS NULL)
        AND sc.status_code='TK_Complete'
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL') OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND sc.baseline_finish IS NOT NULL
        AND sc.baseline_finish <> '0000-00-00 00:00:00'
    THEN 1 ELSE 0 END) AS tasks_completed_with_baselines,

    SUM(CASE WHEN
           (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND (sc.icp<>'Ref' OR sc.icp IS NULL)
           AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND sc.baseline_finish<SYSDATE()
        AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND sc.baseline_finish IS NOT NULL
        AND sc.baseline_finish <> '0000-00-00 00:00:00'
    THEN 1 ELSE 0 END) AS tasks_baselined_to_complete,

    SUM(CASE WHEN
           (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND (sc.icp<>'Ref' OR sc.icp IS NULL)
        AND sc.baseline_start<SYSDATE()
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND sc.baseline_start IS NOT NULL
        AND sc.baseline_start <> '0000-00-00 00:00:00'
    THEN 1 ELSE 0 END) AS tasks_baselined_to_start,

    SUM(CASE WHEN
           (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND (sc.icp<>'Ref' OR sc.icp IS NULL)
        AND sc.start<SYSDATE()
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL) THEN 1 ELSE 0 END) AS tasks_started,

    SUM(CASE WHEN
           (sc.ev_method <> 'LE' OR sc.ev_method IS NULL)
           AND (sc.icp<>'Ref' OR sc.icp IS NULL)
        AND sc.start<SYSDATE()
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL') OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND sc.baseline_start IS NOT NULL
        AND sc.baseline_start <> '0000-00-00 00:00:00'
        AND sc.status_code <> 'TK_NotStart'
    THEN 1 ELSE 0 END) AS tasks_started_with_baselines,

    SUM(CASE WHEN
        (lag>0)
        AND (sc.ev_method <> 'LE' OR sc.ev_method='' OR sc.ev_method IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL') OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.status_code='TK_NotStart')
    THEN lag ELSE 0 END) AS lags,

    SUM(CASE WHEN
        (lead>0)
        AND (sc.ev_method <> 'LE' OR sc.ev_method='' OR sc.ev_method IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL') OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.status_code='TK_NotStart')
    THEN lead ELSE 0 END) AS leads,

    SUM(CASE WHEN
        (link_type>0)
        AND (sc.ev_method <> 'LE' OR sc.ev_method='' OR sc.ev_method IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL') OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND (sc.task_type IN ('TT_Task','TT_Rsrc','TT_Mile','TT_FinMile'))
        AND (sc.status_code='TK_NotStart')
    THEN link_type ELSE 0 END) AS relationships,

    SUM(CASE WHEN
        (phys_complete_pct<>100.00 AND (sc.num_pred <> '' OR sc.num_pred IS NOT NULL))
        AND (sc.ev_method <> 'LE' OR sc.ev_method='' OR sc.ev_method IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL') OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.status_code='TK_NotStart')
    THEN sc.num_pred ELSE 0 END) AS pred_count,

    SUM(CASE WHEN
        (phys_complete_pct<>100.00 AND ((sc.num_pred = '' OR sc.num_pred IS NULL) AND (sc.num_succ <> '' OR sc.num_succ IS NOT NULL)))
        AND (sc.ev_method <> 'LE' OR sc.ev_method='' OR sc.ev_method IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL') OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.status_code='TK_NotStart')
    THEN sc.num_succ ELSE 0 END) AS succ_count,

    SUM(CASE WHEN
           ((sc.ev_method <> 'LE' AND sc.task_type <> 'TT_Mile' AND sc.task_type <> 'TT_FinMile') OR sc.ev_method IS NULL)
           AND (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL)
           AND (sc.start<>sc.finish)
           AND (sc.budgeted_hours IS NULL OR sc.budgeted_hours<=0)
           AND (sc.budgeted_dollars IS NULL OR sc.budgeted_dollars<=0) THEN 1 ELSE 0 END) AS resources,

    SUM(CASE WHEN
           ((sc.ev_method <> 'LE' AND sc.task_type <> 'TT_Mile' AND sc.task_type <> 'TT_FinMile') OR sc.ev_method IS NULL)
           AND (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL)
           AND (sc.start<>sc.finish)
    THEN 1 ELSE 0 END) AS resources_count,

    SUM(CASE WHEN
        (sc.ev_method <> 'LE' OR sc.ev_method='' OR sc.ev_method IS NULL)
        AND (sc.work_package_type NOT IN ('MFG','MTL') OR sc.work_package_type='' OR sc.work_package_type IS NULL)
        AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
        AND (sc.icp<>'Ref' OR sc.icp IS NULL)
        AND sc.baseline_finish < SYSDATE()
        AND
        (
            (LEFT(sc.baseline_finish,10)<>'0000-00-00' AND sc.baseline_finish IS NOT NULL)
            AND
            (LEFT(sc.baseline_start,10)<>'0000-00-00' AND sc.baseline_start IS NOT NULL)
        )
        AND DATEDIFF(sc.baseline_finish,finish) < 0 THEN 1 ELSE 0 END) AS missed_tasks,

    SUM(CASE WHEN
       (sc.ev_method <> 'LE' OR sc.ev_method='' OR sc.ev_method IS NULL)
       AND (sc.work_package_type NOT IN ('MFG','MTL') OR sc.work_package_type='' OR sc.work_package_type IS NULL)
       AND (sc.task_type NOT IN ('TT_LOE','TT_WBS') OR sc.task_type='' OR sc.task_type IS NULL)
       AND (sc.icp<>'Ref' OR sc.icp IS NULL)
       AND sc.baseline_finish < SYSDATE()
       AND
       (
            (LEFT(sc.baseline_finish,10)<>'0000-00-00' AND sc.baseline_finish IS NOT NULL)
            AND
            (LEFT(sc.baseline_start,10)<>'0000-00-00' AND sc.baseline_start IS NOT NULL)
        )
    THEN 1 ELSE 0 END) AS missed_tasks_bottom,

    SUM(CASE WHEN
       sc.act_start_date > SYSDATE()
    OR sc.act_end_date > SYSDATE()
    THEN 1 ELSE 0 END) AS invalid_dates,

    SUM(CASE WHEN
       sc.act_start_date > SYSDATE()
    OR sc.act_end_date > SYSDATE()
    OR (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL) THEN 1 ELSE 0 END) AS invalid_dates_denominator,

    SUM(CASE WHEN
        (sc.status_code<>'TK_Complete' OR sc.status_code IS NULL)
        AND (sc.start<=LEFT(DATE_ADD(SYSDATE(),INTERVAL 90 DAY),10) OR sc.baseline_start<=LEFT(DATE_ADD(SYSDATE(),INTERVAL 90 DAY),10))
        AND sc.task_type NOT IN ('TT_LOE','TT_WBS')
        AND (sc.aps_code IS NULL OR sc.aps_code='')
           AND (sc.ev_method = 'PP')
           AND (sc.work_package_type NOT IN ('MFG','MTL')  OR sc.work_package_type='' OR sc.work_package_type IS NULL)
           AND sc.task_name NOT LIKE '%Cost Collection%'
    THEN 1 ELSE 0 END) AS pp_in_window,

    -- metrics page below
    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND (finish >= LEFT(DATE_ADD(SYSDATE(),INTERVAL -30 DAY),10) AND finish <= SYSDATE())
        AND task_type<>'TT_LOE'
        AND (icp<>'Ref' OR icp IS NULL)
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL)
        AND (finish<=baseline_finish)
    THEN 1 ELSE 0 END) AS mra_top,

    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND (finish >= LEFT(DATE_ADD(SYSDATE(),INTERVAL -30 DAY),10) AND finish <= SYSDATE())
        AND task_type<>'TT_LOE'
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL) THEN 1 ELSE 0 END) AS mra_bottom,

    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND (finish <= LEFT(DATE_ADD(SYSDATE(),INTERVAL 90 DAY),10) AND finish >= SYSDATE())
        AND task_type<>'TT_LOE'
        AND (icp<>'Ref' OR icp IS NULL)
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL)
        AND (finish<=baseline_finish)
    THEN 1 ELSE 0 END) AS mrf_top,

    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND (finish <= LEFT(DATE_ADD(SYSDATE(),INTERVAL 90 DAY),10) AND finish >= SYSDATE())
        AND task_type<>'TT_LOE'
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL) THEN 1 ELSE 0 END) AS mrf_bottom,


    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND task_type<>'TT_LOE'
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL)
        AND (num_pred<=0 OR num_pred IS NULL)
        AND (icp<>'Ref' OR icp IS NULL)
        AND (icp<>'In' OR icp IS NULL)
        AND status_code='TK_NotStart'
        AND (aps_code='' OR aps_code IS NULL) THEN 1 ELSE 0 END) AS no_pred_top,

    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND (icp<>'Ref' OR icp IS NULL)
        AND task_type<>'TT_LOE'
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL)
        AND (status_code<>'TK_Complete' OR status_code IS NULL) THEN 1 ELSE 0 END) AS no_pred_bottom,

    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND task_type<>'TT_LOE'
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL)
        AND (num_succ<=0 OR num_succ IS NULL)
        AND (icp<>'Ref' OR icp IS NULL)
        AND (icp<>'Out' OR icp IS NULL)
        AND (icp<>'MM' OR icp IS NULL)
        AND (status_code<>'TK_Complete' OR status_code IS NULL) THEN 1 ELSE 0 END) AS no_succ_top,

    SUM(CASE WHEN
        icp='In'
        AND (status_code<>'TK_Complete' OR status_code IS NULL) THEN 1 ELSE 0 END) AS icp_in,

    SUM(CASE WHEN
        icp IN ('Out','MM','End')
        AND (status_code<>'TK_Complete' OR status_code IS NULL) THEN 1 ELSE 0 END) AS icp_out,

    SUM(CASE WHEN
        icp='Ref'
        AND (status_code<>'TK_Complete' OR status_code IS NULL) THEN 1 ELSE 0 END) AS icp_ref,

    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND task_type<>'TT_LOE'
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL)
        AND (status_code<>'TK_Complete' OR status_code IS NULL)
        AND (total_float_hr_cnt<=0 OR total_float_hr_cnt IS NULL) THEN 1 ELSE 0 END) AS critical_top,

    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND task_type<>'TT_LOE'
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL)
        AND (status_code<>'TK_Complete' OR status_code IS NULL) THEN 1 ELSE 0 END) AS critical_bottom,

    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND task_type<>'TT_LOE'
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL)
        AND (status_code<>'TK_Complete' OR status_code IS NULL)
        AND ((total_float_hr_cnt/8)<=-1 OR total_float_hr_cnt IS NULL) THEN 1 ELSE 0 END) AS scritical_top,

    SUM(CASE WHEN
        sc.act_start_date > SYSDATE()
        OR sc.act_end_date > SYSDATE()
    THEN 1 ELSE 0 END) AS future_actuals,

    SUM(CASE WHEN
        (
            work_package_type NOT IN ('MFG','MTL')
            AND (status_code='TK_Active')
            AND (remain_work_qty<=0 OR remain_work_qty IS NULL)
        )
        OR
        (
            START > SYSDATE()+1
            AND (status_code='TK_NotStart')
            AND (aps_code='' OR aps_code IS NULL)
            AND (remain_work_qty<=0 OR remain_work_qty IS NULL)
            AND task_code NOT LIKE '%LBB%'
            AND task_type<>'TT_LOE'
            AND work_package_type NOT IN ('MFG','MTL')
        )
    THEN 1 ELSE 0 END) AS unstatused_bottom,

    SUM(CASE WHEN
        (EV_Method <> 'LE' OR ev_method IS NULL)
        AND task_type<>'TT_LOE'
        AND (work_package_type NOT IN ('MFG','MTL') OR work_package_type='' OR work_package_type IS NULL)
        AND status_code='TK_NotStart'
        AND START>baseline_start
        AND finish<=baseline_finish
    THEN 1 ELSE 0 END) AS delayed_tasks_top

    FROM
      data_schedule sc,
      main mv

    WHERE sc.pmid = mv.pmid
      AND sc.cmid = mv.cmid
    GROUP BY sc.pmid
    );
    ";

    if($sql_only==false)
    {
        $sqls = explode(';',$all_sqls);
        foreach($sqls as $sql)
        {
            $sql = trim($sql);
            if($sql!='' and substr($sql,0,2)!='--') $junk = dbCall_IB($sql,$debug);
        }

        return true;
    }
    else
    {
        return $all_sqls;
    }
}
// -----------------------------------------------------------------
function getPMIDs($field,$value)
{
    //get pmids
    $pmid_sql = "select id from master_project where $field like '%$value%' group by $field";
    $pmid_rs = dbCall_IB($pmid_sql,$debug);
    while(!$pmid_rs->EOF)
    {
        $pmid = $pmid_rs->fields['id'];
        $pmids .= "$pmid,";
        $pmid_rs->MoveNext();
    }
    $pmids = stripLastCharacter($pmids);

    return $pmids;
}// -----------------------------------------------------------------
function getCMIDs($field,$value)
{
    //get cmids
    $cmid_sql = "select cmid from main where $field like '%$value%' group by $field";
    $cmid_rs = dbCall_IB($cmid_sql,$debug);
    while(!$cmid_rs->EOF)
    {
        $cmid = $cmid_rs->fields['cmid'];
        $cmids .= "$cmid,";
        $cmid_rs->MoveNext();
    }
    $cmids = stripLastCharacter($cmids);

    return $cmids;
}
// -----------------------------------------------------------------
function cmidNA($pmid)
{
    global $debug;
    // check to see if this pmid already has a "NA" Control Account
    $sql        = "SELECT cmid FROM master_ca WHERE pmid = '$pmid' AND ca = 'NOCA'";
    $rs         = dbCall($sql,$debug);
    $na_cmid    = $rs->fields['cmid'];

    // if it does not, then create one in master_ca
    if($na_cmid=='')
    {
        // field `ca` has a unique index constraint therefore adding pmid to 'NA' makes it unique
        $na_sql     = "INSERT INTO master_ca (pmid,ca,ca_desc,cam) VALUES ('$pmid','NOCA','NOCA','NOCAM')";
        $junk       = dbCall($na_sql,$debug);

        // get newly inserted cmid
        $ca_sql     = "SELECT cmid FROM master_ca WHERE pmid = '$pmid' AND ca = 'NOCA'";
        $ca_rs      = dbCall($ca_sql,$debug);
        $na_cmid    = $ca_rs->fields['cmid'];
    }
    return $na_cmid;
}
// -----------------------------------------------------------------
function updateMasterCAfromMPM($mpm_project,$company,$source='dw')
{
    //insert/update master_ca from tsc_mpm_dw_wbs

    // $source 'dw' is datawarehouse, 'csv' is for import of csv files

    $debug = true;

    // TODO first, using kettle job, update table `main` so it is current when checking for existing control accounts
    exec("c:/pdi/kitchen.bat /file:\"c:/pdi/jobs/tsc_master_tables/repopulate_master_pm_and_ca_tables_from_pc.kjb\" /level:Nothing & ");
    //$main = "main";
    $main = "main_view";

    $wbs_table_for_ca = "tsc_temp_wbs_structure";

    $cost_account_wc = "AND TRIM(e) = 'Cost Account'";
    $ca_from_wbs_structure_pull = "`wbs_id`";
    //$ca_from_wbs_structure_pull = "`parent`";
    if($source=='csv')
    {
        $cost_account_wc = "AND TRIM(e) = 'C'";

        if($company=='AAI')
        {
            $ca_from_wbs_structure_pull = "`parent`";
            $cost_account_wc = "";
        }
        if($company=='TMLS')
        {
            $ca_from_wbs_structure_pull = "`xref-2`";
        }
    }

    // set 'NOCA' control account
    $sql = "SELECT id FROM `master_project` WHERE premier_name LIKE '$mpm_project%' AND company = '$company'";
    $rs = dbCall($sql,$debug,'premier_core');
    $pmid    = $rs->fields['id'];
    cmidNA($pmid);

    // first update existing control accounts
    $get_ca = "
        SELECT
        mv.pmid,
        mv.cmid,
        wb.description AS ca_desc,
        wb.manager AS cam
        FROM (SELECT project,$ca_from_wbs_structure_pull,description,manager FROM $wbs_table_for_ca WHERE ( $ca_from_wbs_structure_pull NOT LIKE '%*%' AND TRIM($ca_from_wbs_structure_pull) <> '' $cost_account_wc) GROUP BY $ca_from_wbs_structure_pull) wb
        LEFT JOIN $main mv
        ON wb.$ca_from_wbs_structure_pull = mv.ca
        AND mv.pmid = $pmid
        AND mv.company = '$company'
        WHERE mv.ca IS NOT NULL
        AND mv.pmid = $pmid
        AND mv.company = '$company'
    ";
    $grs = dbCall($get_ca,$debug,'premier_core');
    while(!$grs->EOF)
    {
        $ca_pmid    = $grs->fields['pmid'];
        $ca_cmid    = $grs->fields['cmid'];
        $ca_ca_desc = $grs->fields['ca_desc'];
        $ca_cam     = $grs->fields['cam'];

        $update_ca_sql = "UPDATE master_ca SET ca_desc = '$ca_ca_desc',cam = '$ca_cam' WHERE pmid = $ca_pmid and cmid = $ca_cmid";
        $junk = dbCall($update_ca_sql,$debug,'premier_core');

        $grs->MoveNext();
    }

    // TODO kettle job to update main
    system("c:/pdi/kitchen.bat /file:\"c:/pdi/jobs/tsc_master_tables/repopulate_master_pm_and_ca_tables_from_pc.kjb\" /level:Nothing & ");

    // then insert new control accounts
    $insert_new_ca = "
        INSERT INTO master_ca
        (pmid,ca,ca_desc,cam)
        (SELECT
        (SELECT id FROM master_project mp WHERE mp.premier_name LIKE CONCAT(wb.project,'%')) AS pmid,
        wb.$ca_from_wbs_structure_pull AS ca,
        wb.description AS ca_desc,
        wb.manager AS cam
        FROM (SELECT project,$ca_from_wbs_structure_pull,description,manager FROM $wbs_table_for_ca WHERE ( $ca_from_wbs_structure_pull NOT LIKE '%*%' AND TRIM($ca_from_wbs_structure_pull) <> '') $cost_account_wc AND project = '$mpm_project' GROUP BY $ca_from_wbs_structure_pull) wb
        LEFT JOIN $main mv
        ON wb.$ca_from_wbs_structure_pull = mv.ca
        AND mv.pmid = $pmid
        AND mv.company = '$company'
        WHERE mv.ca IS NULL
        )
    ";
    $junk = dbCall($insert_new_ca,$debug,'premier_core');

    // TODO using kettle job, update table `main` because of the additions to master_ca above
    exec("c:/pdi/kitchen.bat /file:\"c:/pdi/jobs/tsc_master_tables/repopulate_master_pm_and_ca_tables_from_pc.kjb\" /level:Nothing & ");
}
// -----------------------------------------------------------------
function getPMIDFromPremierName($name,$debug=false)
{
    $sql = "select id from master_project where premier_name='$name' limit 1";
    $rs = dbCall($sql,$debug);
    return $rs->fields['id'];
}
// -----------------------------------------------------------------
function getNextIBID($table,$debug=false,$field='id')
{
    $sql = "select max($field) as max_id from $table";
    $rs = dbCall_IB($sql, $debug);

    $id = $rs->fields['max_id'];
    if ($id== '') $id = 0;
    $id++;
    //die("id=".$id);
    return $id;
}
// -----------------------------------------------------------------
function updatePremierCAMasterThresholds($project_group='',$debug=false,$server='')
{
    //if project_group is blank, update all thresholds in ca_master
    $pg_wc = '';
    if($project_group!='') $pg_wc = " and project_group='$project_group' ";

    //blank out the var_threshold_rollup fields on the ca_master where the CA is a NOCA
    $sql = "
        UPDATE
            ca_master
        SET
            var_threshold_rollup = NULL
        WHERE
            control_account='NOCA'
            $pg_wc
    ";
    $junk = dbCall($sql,$debug,'tools_data',$server);

    //get max thresholds for each project group and apply them to the ca_master table
    $sql = "
        SELECT
              project_group
            , MAX(var_int_cur_percent) as var_int_cur_percent
            , MAX(var_int_cur_dollars) as var_int_cur_dollars
            , MAX(var_int_cur_and_or_flag) as var_int_cur_and_or_flag
            , MAX(var_int_cur_top) as var_int_cur_top
            , MAX(var_int_cur_change_flag) as var_int_cur_change_flag
            , MAX(var_int_cur_min_amt) as var_int_cur_min_amt

            , MAX(var_int_cum_percent) as var_int_cum_percent
            , MAX(var_int_cum_dollars) as var_int_cum_dollars
            , MAX(var_int_cum_and_or_flag) as var_int_cum_and_or_flag
            , MAX(var_int_cum_top) as var_int_cum_top
            , MAX(var_int_cum_change_flag) as var_int_cum_change_flag
            , MAX(var_int_cum_min_amt) as var_int_cum_min_amt

            , MAX(var_int_ac_percent) as var_int_ac_percent
            , MAX(var_int_ac_dollars) as var_int_ac_dollars
            , MAX(var_int_ac_and_or_flag) as var_int_ac_and_or_flag
            , MAX(var_int_ac_top) as var_int_ac_top
            , MAX(var_int_ac_change_flag) as var_int_ac_change_flag
            , MAX(var_int_ac_min_amt) as var_int_ac_min_amt

            , MAX(var_ext_cur_percent) as var_ext_cur_percent
            , MAX(var_ext_cur_dollars) as var_ext_cur_dollars
            , MAX(var_ext_cur_and_or_flag) as var_ext_cur_and_or_flag
            , MAX(var_ext_cur_top) as var_ext_cur_top
            , MAX(var_ext_cur_change_flag) as var_ext_cur_change_flag
            , MAX(var_ext_cur_min_amt) as var_ext_cur_min_amt

            , MAX(var_ext_cum_percent) as var_ext_cum_percent
            , MAX(var_ext_cum_dollars) as var_ext_cum_dollars
            , MAX(var_ext_cum_and_or_flag) as var_ext_cum_and_or_flag
            , MAX(var_ext_cum_top) as var_ext_cum_top
            , MAX(var_ext_cum_change_flag) as var_ext_cum_change_flag
            , MAX(var_ext_cum_min_amt) as var_ext_cum_min_amt

            , MAX(var_ext_ac_percent) as var_ext_ac_percent
            , MAX(var_ext_ac_dollars) as var_ext_ac_dollars
            , MAX(var_ext_ac_and_or_flag) as var_ext_ac_and_or_flag
            , MAX(var_ext_ac_top) as var_ext_ac_top
            , MAX(var_ext_ac_change_flag) as var_ext_ac_change_flag
            , MAX(var_ext_ac_min_amt) as var_ext_ac_min_amt
       FROM
           ca_master
       WHERE
           control_account<>'NOCA'
           $pg_wc
       GROUP BY
           project_group
    ";
    $rs = dbCall($sql,$debug,'tools_data',$server);

    while(!$rs->EOF)
    {
        $project_group             = $rs->fields['project_group'];
        $var_int_cur_percent       = $rs->fields['var_int_cur_percent'];
        $var_int_cur_dollars       = $rs->fields['var_int_cur_dollars'];
        $var_int_cur_and_or_flag   = $rs->fields['var_int_cur_and_or_flag'];
        $var_int_cur_top           = $rs->fields['var_int_cur_top'];
        $var_int_cur_change_flag   = $rs->fields['var_int_cur_change_flag'];
        $var_int_cur_min_amt       = $rs->fields['var_int_cur_min_amt'];

        $var_int_cum_percent       = $rs->fields['var_int_cum_percent'];
        $var_int_cum_dollars       = $rs->fields['var_int_cum_dollars'];
        $var_int_cum_and_or_flag   = $rs->fields['var_int_cum_and_or_flag'];
        $var_int_cum_top           = $rs->fields['var_int_cum_top'];
        $var_int_cum_change_flag   = $rs->fields['var_int_cum_change_flag'];
        $var_int_cum_min_amt       = $rs->fields['var_int_cum_min_amt'];

        $var_int_ac_percent        = $rs->fields['var_int_ac_percent'];
        $var_int_ac_dollars        = $rs->fields['var_int_ac_dollars'];
        $var_int_ac_and_or_flag    = $rs->fields['var_int_ac_and_or_flag'];
        $var_int_ac_top            = $rs->fields['var_int_ac_top'];
        $var_int_ac_change_flag    = $rs->fields['var_int_ac_change_flag'];
        $var_int_ac_min_amt        = $rs->fields['var_int_ac_min_amt'];

        $var_ext_cur_percent       = $rs->fields['var_ext_cur_percent'];
        $var_ext_cur_dollars       = $rs->fields['var_ext_cur_dollars'];
        $var_ext_cur_and_or_flag   = $rs->fields['var_ext_cur_and_or_flag'];
        $var_ext_cur_top           = $rs->fields['var_ext_cur_top'];
        $var_ext_cur_change_flag   = $rs->fields['var_ext_cur_change_flag'];
        $var_ext_cur_min_amt       = $rs->fields['var_ext_cur_min_amt'];

        $var_ext_cum_percent       = $rs->fields['var_ext_cum_percent'];
        $var_ext_cum_dollars       = $rs->fields['var_ext_cum_dollars'];
        $var_ext_cum_and_or_flag   = $rs->fields['var_ext_cum_and_or_flag'];
        $var_ext_cum_top           = $rs->fields['var_ext_cum_top'];
        $var_ext_cum_change_flag   = $rs->fields['var_ext_cum_change_flag'];
        $var_ext_cum_min_amt       = $rs->fields['var_ext_cum_min_amt'];

        $var_ext_ac_percent        = $rs->fields['var_ext_ac_percent'];
        $var_ext_ac_dollars        = $rs->fields['var_ext_ac_dollars'];
        $var_ext_ac_and_or_flag    = $rs->fields['var_ext_ac_and_or_flag'];
        $var_ext_ac_top            = $rs->fields['var_ext_ac_top'];
        $var_ext_ac_change_flag    = $rs->fields['var_ext_ac_change_flag'];
        $var_ext_ac_min_amt        = $rs->fields['var_ext_ac_min_amt'];

        //change blanks to nulls
        //percent
        if($var_int_cur_percent=='') $var_int_cur_percent = 'NULL';
        if($var_ext_cur_percent=='') $var_ext_cur_percent = 'NULL';

        if($var_int_cum_percent=='') $var_int_cum_percent = 'NULL';
        if($var_ext_cum_percent=='') $var_ext_cum_percent = 'NULL';

        if($var_int_ac_percent=='') $var_int_ac_percent = 'NULL';
        if($var_ext_ac_percent=='') $var_ext_ac_percent = 'NULL';

        //dollars
        if($var_int_cur_dollars=='') $var_int_cur_dollars = 'NULL';
        if($var_ext_cur_dollars=='') $var_ext_cur_dollars = 'NULL';

        if($var_int_cum_dollars=='') $var_int_cum_dollars = 'NULL';
        if($var_ext_cum_dollars=='') $var_ext_cum_dollars = 'NULL';

        if($var_int_ac_dollars=='') $var_int_ac_dollars = 'NULL';
        if($var_ext_ac_dollars=='') $var_ext_ac_dollars = 'NULL';

        //tops
        if($var_int_cur_top=='') $var_int_cur_top = 'NULL';
        if($var_ext_cur_top=='') $var_ext_cur_top = 'NULL';

        if($var_int_cum_top=='') $var_int_cum_top = 'NULL';
        if($var_ext_cum_top=='') $var_ext_cum_top = 'NULL';

        if($var_int_ac_top=='') $var_int_ac_top = 'NULL';
        if($var_ext_ac_top=='') $var_ext_ac_top = 'NULL';

        //change flags
        if($var_int_cur_change_flag=='') $var_int_cur_change_flag = 'NULL';
        if($var_ext_cur_change_flag=='') $var_ext_cur_change_flag = 'NULL';

        if($var_int_cum_change_flag=='') $var_int_cum_change_flag = 'NULL';
        if($var_ext_cum_change_flag=='') $var_ext_cum_change_flag = 'NULL';

        if($var_int_ac_change_flag=='') $var_int_ac_change_flag = 'NULL';
        if($var_ext_ac_change_flag=='') $var_ext_ac_change_flag = 'NULL';

        //min amts
        if($var_int_cur_min_amt=='') $var_int_cur_min_amt = 'NULL';
        if($var_ext_cur_min_amt=='') $var_ext_cur_min_amt = 'NULL';

        if($var_int_cum_min_amt=='') $var_int_cum_min_amt = 'NULL';
        if($var_ext_cum_min_amt=='') $var_ext_cum_min_amt = 'NULL';

        if($var_int_ac_min_amt=='') $var_int_ac_min_amt = 'NULL';
        if($var_ext_ac_min_amt=='') $var_ext_ac_min_amt = 'NULL';


        $update_sql = "
             UPDATE
                 ca_master
             SET
                  var_int_cur_percent = $var_int_cur_percent
                , var_int_cur_dollars = $var_int_cur_dollars
                , var_int_cur_and_or_flag = '$var_int_cur_and_or_flag'
                , var_int_cur_top = $var_int_cur_top
                , var_int_cur_change_flag = $var_int_cur_change_flag
                , var_int_cur_min_amt = $var_int_cur_min_amt

                , var_int_cum_percent = $var_int_cum_percent
                , var_int_cum_dollars = $var_int_cum_dollars
                , var_int_cum_and_or_flag = '$var_int_cum_and_or_flag'
                , var_int_cum_top = $var_int_cum_top
                , var_int_cum_change_flag = $var_int_cum_change_flag
                , var_int_cum_min_amt = $var_int_cum_min_amt

                , var_int_ac_percent = $var_int_ac_percent
                , var_int_ac_dollars = $var_int_ac_dollars
                , var_int_ac_and_or_flag = '$var_int_ac_and_or_flag'
                , var_int_ac_top = $var_int_ac_top
                , var_int_ac_change_flag = $var_int_ac_change_flag
                , var_int_ac_min_amt = $var_int_ac_min_amt

                , var_ext_cur_percent = $var_ext_cur_percent
                , var_ext_cur_dollars = $var_ext_cur_dollars
                , var_ext_cur_and_or_flag = '$var_ext_cur_and_or_flag'
                , var_ext_cur_top = $var_ext_cur_top
                , var_ext_cur_change_flag = $var_ext_cur_change_flag
                , var_ext_cur_min_amt = $var_ext_cur_min_amt

                , var_ext_cum_percent = $var_ext_cum_percent
                , var_ext_cum_dollars = $var_ext_cum_dollars
                , var_ext_cum_and_or_flag = '$var_ext_cum_and_or_flag'
                , var_ext_cum_top = $var_ext_cum_top
                , var_ext_cum_change_flag = $var_ext_cum_change_flag
                , var_ext_cum_min_amt = $var_ext_cum_min_amt

                , var_ext_ac_percent = $var_ext_ac_percent
                , var_ext_ac_dollars = $var_ext_ac_dollars
                , var_ext_ac_and_or_flag = '$var_ext_ac_and_or_flag'
                , var_ext_ac_top = $var_ext_ac_top
                , var_ext_ac_change_flag = $var_ext_ac_change_flag
                , var_ext_ac_min_amt = $var_ext_ac_min_amt
           WHERE
               project_group = '$project_group'
        ";
        $junk = dbCall($update_sql,$debug,'tools_data',$server);

        $rs->MoveNext();
    }

    return true;
}
// -----------------------------------------------------------------
function updatePremierMasterCAThresholds($project_group='',$debug=false)
{
    //if project_group is blank, update all thresholds in ca_master
    $pg_wc = '';
    if($project_group!='') $pg_wc = " and pmid in (select id from master_project where project_group='$project_group') ";

    //blank out the var_threshold_rollup fields on the ca_master where the CA is a NOCA
    $sql = "
        UPDATE
            master_ca_thresholds
        SET
            var_threshold_rollup = NULL
        WHERE
            cmid in (select cmid from master_ca where ca='NOCA')
            $pg_wc
    ";
    $junk = dbCall($sql,$debug,'premier_core');

    //get max thresholds for each project group and apply them to the ca_master table
    $sql = "
        SELECT
              project_group
            , MAX(var_int_cur_percent) as var_int_cur_percent
            , MAX(var_int_cur_dollars) as var_int_cur_dollars
            , MAX(var_int_cur_and_or_flag) as var_int_cur_and_or_flag
            , MAX(var_int_cur_top) as var_int_cur_top
            , MAX(var_int_cur_change_flag) as var_int_cur_change_flag
            , MAX(var_int_cur_min_amt) as var_int_cur_min_amt

            , MAX(var_int_cum_percent) as var_int_cum_percent
            , MAX(var_int_cum_dollars) as var_int_cum_dollars
            , MAX(var_int_cum_and_or_flag) as var_int_cum_and_or_flag
            , MAX(var_int_cum_top) as var_int_cum_top
            , MAX(var_int_cum_change_flag) as var_int_cum_change_flag
            , MAX(var_int_cum_min_amt) as var_int_cum_min_amt

            , MAX(var_int_ac_percent) as var_int_ac_percent
            , MAX(var_int_ac_dollars) as var_int_ac_dollars
            , MAX(var_int_ac_and_or_flag) as var_int_ac_and_or_flag
            , MAX(var_int_ac_top) as var_int_ac_top
            , MAX(var_int_ac_change_flag) as var_int_ac_change_flag
            , MAX(var_int_ac_min_amt) as var_int_ac_min_amt

            , MAX(var_ext_cur_percent) as var_ext_cur_percent
            , MAX(var_ext_cur_dollars) as var_ext_cur_dollars
            , MAX(var_ext_cur_and_or_flag) as var_ext_cur_and_or_flag
            , MAX(var_ext_cur_top) as var_ext_cur_top
            , MAX(var_ext_cur_change_flag) as var_ext_cur_change_flag
            , MAX(var_ext_cur_min_amt) as var_ext_cur_min_amt

            , MAX(var_ext_cum_percent) as var_ext_cum_percent
            , MAX(var_ext_cum_dollars) as var_ext_cum_dollars
            , MAX(var_ext_cum_and_or_flag) as var_ext_cum_and_or_flag
            , MAX(var_ext_cum_top) as var_ext_cum_top
            , MAX(var_ext_cum_change_flag) as var_ext_cum_change_flag
            , MAX(var_ext_cum_min_amt) as var_ext_cum_min_amt

            , MAX(var_ext_ac_percent) as var_ext_ac_percent
            , MAX(var_ext_ac_dollars) as var_ext_ac_dollars
            , MAX(var_ext_ac_and_or_flag) as var_ext_ac_and_or_flag
            , MAX(var_ext_ac_top) as var_ext_ac_top
            , MAX(var_ext_ac_change_flag) as var_ext_ac_change_flag
            , MAX(var_ext_ac_min_amt) as var_ext_ac_min_amt
       FROM
           main
       WHERE
           ca<>'NOCA'
           $pg_wc
       GROUP BY
           project_group
    ";
    $rs = dbCall($sql,$debug,'premier_core');

    while(!$rs->EOF)
    {
        $project_group             = $rs->fields['project_group'];

        $var_int_cur_percent       = $rs->fields['var_int_cur_percent'];
        $var_int_cur_dollars       = $rs->fields['var_int_cur_dollars'];
        $var_int_cur_and_or_flag   = $rs->fields['var_int_cur_and_or_flag'];
        $var_int_cur_top           = $rs->fields['var_int_cur_top'];
        $var_int_cur_change_flag   = $rs->fields['var_int_cur_change_flag'];
        $var_int_cur_min_amt       = $rs->fields['var_int_cur_min_amt'];

        $var_int_cum_percent       = $rs->fields['var_int_cum_percent'];
        $var_int_cum_dollars       = $rs->fields['var_int_cum_dollars'];
        $var_int_cum_and_or_flag   = $rs->fields['var_int_cum_and_or_flag'];
        $var_int_cum_top           = $rs->fields['var_int_cum_top'];
        $var_int_cum_change_flag   = $rs->fields['var_int_cum_change_flag'];
        $var_int_cum_min_amt       = $rs->fields['var_int_cum_min_amt'];

        $var_int_ac_percent        = $rs->fields['var_int_ac_percent'];
        $var_int_ac_dollars        = $rs->fields['var_int_ac_dollars'];
        $var_int_ac_and_or_flag    = $rs->fields['var_int_ac_and_or_flag'];
        $var_int_ac_top            = $rs->fields['var_int_ac_top'];
        $var_int_ac_change_flag    = $rs->fields['var_int_ac_change_flag'];
        $var_int_ac_min_amt        = $rs->fields['var_int_ac_min_amt'];

        $var_ext_cur_percent       = $rs->fields['var_ext_cur_percent'];
        $var_ext_cur_dollars       = $rs->fields['var_ext_cur_dollars'];
        $var_ext_cur_and_or_flag   = $rs->fields['var_ext_cur_and_or_flag'];
        $var_ext_cur_top           = $rs->fields['var_ext_cur_top'];
        $var_ext_cur_change_flag   = $rs->fields['var_ext_cur_change_flag'];
        $var_ext_cur_min_amt       = $rs->fields['var_ext_cur_min_amt'];

        $var_ext_cum_percent       = $rs->fields['var_ext_cum_percent'];
        $var_ext_cum_dollars       = $rs->fields['var_ext_cum_dollars'];
        $var_ext_cum_and_or_flag   = $rs->fields['var_ext_cum_and_or_flag'];
        $var_ext_cum_top           = $rs->fields['var_ext_cum_top'];
        $var_ext_cum_change_flag   = $rs->fields['var_ext_cum_change_flag'];
        $var_ext_cum_min_amt       = $rs->fields['var_ext_cum_min_amt'];

        $var_ext_ac_percent        = $rs->fields['var_ext_ac_percent'];
        $var_ext_ac_dollars        = $rs->fields['var_ext_ac_dollars'];
        $var_ext_ac_and_or_flag    = $rs->fields['var_ext_ac_and_or_flag'];
        $var_ext_ac_top            = $rs->fields['var_ext_ac_top'];
        $var_ext_ac_change_flag    = $rs->fields['var_ext_ac_change_flag'];
        $var_ext_ac_min_amt        = $rs->fields['var_ext_ac_min_amt'];

        //change blanks to nulls
        //percent
        if($var_int_cur_percent=='') $var_int_cur_percent = 'NULL';
        if($var_ext_cur_percent=='') $var_ext_cur_percent = 'NULL';

        if($var_int_cum_percent=='') $var_int_cum_percent = 'NULL';
        if($var_ext_cum_percent=='') $var_ext_cum_percent = 'NULL';

        if($var_int_ac_percent=='') $var_int_ac_percent = 'NULL';
        if($var_ext_ac_percent=='') $var_ext_ac_percent = 'NULL';

        //dollars
        if($var_int_cur_dollars=='') $var_int_cur_dollars = 'NULL';
        if($var_ext_cur_dollars=='') $var_ext_cur_dollars = 'NULL';

        if($var_int_cum_dollars=='') $var_int_cum_dollars = 'NULL';
        if($var_ext_cum_dollars=='') $var_ext_cum_dollars = 'NULL';

        if($var_int_ac_dollars=='') $var_int_ac_dollars = 'NULL';
        if($var_ext_ac_dollars=='') $var_ext_ac_dollars = 'NULL';

        //tops
        if($var_int_cur_top=='') $var_int_cur_top = 'NULL';
        if($var_ext_cur_top=='') $var_ext_cur_top = 'NULL';

        if($var_int_cum_top=='') $var_int_cum_top = 'NULL';
        if($var_ext_cum_top=='') $var_ext_cum_top = 'NULL';

        if($var_int_ac_top=='') $var_int_ac_top = 'NULL';
        if($var_ext_ac_top=='') $var_ext_ac_top = 'NULL';

        //change flags
        if($var_int_cur_change_flag=='') $var_int_cur_change_flag = 'NULL';
        if($var_ext_cur_change_flag=='') $var_ext_cur_change_flag = 'NULL';

        if($var_int_cum_change_flag=='') $var_int_cum_change_flag = 'NULL';
        if($var_ext_cum_change_flag=='') $var_ext_cum_change_flag = 'NULL';

        if($var_int_ac_change_flag=='') $var_int_ac_change_flag = 'NULL';
        if($var_ext_ac_change_flag=='') $var_ext_ac_change_flag = 'NULL';

        //min amts
        if($var_int_cur_min_amt=='') $var_int_cur_min_amt = 'NULL';
        if($var_ext_cur_min_amt=='') $var_ext_cur_min_amt = 'NULL';

        if($var_int_cum_min_amt=='') $var_int_cum_min_amt = 'NULL';
        if($var_ext_cum_min_amt=='') $var_ext_cum_min_amt = 'NULL';

        if($var_int_ac_min_amt=='') $var_int_ac_min_amt = 'NULL';
        if($var_ext_ac_min_amt=='') $var_ext_ac_min_amt = 'NULL';


        $update_sql = "
             UPDATE
                 master_ca_thresholds
             SET
                  var_int_cur_percent = $var_int_cur_percent
                , var_int_cur_dollars = $var_int_cur_dollars
                , var_int_cur_and_or_flag = '$var_int_cur_and_or_flag'
                , var_int_cur_top = $var_int_cur_top
                , var_int_cur_change_flag = $var_int_cur_change_flag
                , var_int_cur_min_amt = $var_int_cur_min_amt

                , var_int_cum_percent = $var_int_cum_percent
                , var_int_cum_dollars = $var_int_cum_dollars
                , var_int_cum_and_or_flag = '$var_int_cum_and_or_flag'
                , var_int_cum_top = $var_int_cum_top
                , var_int_cum_change_flag = $var_int_cum_change_flag
                , var_int_cum_min_amt = $var_int_cum_min_amt

                , var_int_ac_percent = $var_int_ac_percent
                , var_int_ac_dollars = $var_int_ac_dollars
                , var_int_ac_and_or_flag = '$var_int_ac_and_or_flag'
                , var_int_ac_top = $var_int_ac_top
                , var_int_ac_change_flag = $var_int_ac_change_flag
                , var_int_ac_min_amt = $var_int_ac_min_amt

                , var_ext_cur_percent = $var_ext_cur_percent
                , var_ext_cur_dollars = $var_ext_cur_dollars
                , var_ext_cur_and_or_flag = '$var_ext_cur_and_or_flag'
                , var_ext_cur_top = $var_ext_cur_top
                , var_ext_cur_change_flag = $var_ext_cur_change_flag
                , var_ext_cur_min_amt = $var_ext_cur_min_amt

                , var_ext_cum_percent = $var_ext_cum_percent
                , var_ext_cum_dollars = $var_ext_cum_dollars
                , var_ext_cum_and_or_flag = '$var_ext_cum_and_or_flag'
                , var_ext_cum_top = $var_ext_cum_top
                , var_ext_cum_change_flag = $var_ext_cum_change_flag
                , var_ext_cum_min_amt = $var_ext_cum_min_amt

                , var_ext_ac_percent = $var_ext_ac_percent
                , var_ext_ac_dollars = $var_ext_ac_dollars
                , var_ext_ac_and_or_flag = '$var_ext_ac_and_or_flag'
                , var_ext_ac_top = $var_ext_ac_top
                , var_ext_ac_change_flag = $var_ext_ac_change_flag
                , var_ext_ac_min_amt = $var_ext_ac_min_amt
           WHERE
               pmid in (select id from master_project where project_group='$project_group')
        ";
        $junk = dbCall($update_sql,$debug,'premier_core');

        $rs->MoveNext();
    }
    return true;
}
// -----------------------------------------------------------------
function updatePremierCAMasterTaskIDs($project_group='',$debug=false,$server='')
{
    //get projectids from ca master for project_group
    //get taskuser2 and task ids from pcm task table based on project id
    //update ca master task id with pcm task table task id if values are different

    //if project_group is blank, update all thresholds in ca_master
    $pg_wc = '';
    if($project_group!='') $pg_wc = " and project_group='$project_group' ";

    $sql = "
        select
              project_id
            , (select pcm_schema from project_master where pcm_project_id=c.project_id limit 1) as pcm_schema
        from
            ca_master c
        where
            project_id>0
            and project_id is not null
            and (SELECT pcm_schema FROM project_master WHERE pcm_project_id=c.project_id LIMIT 1) is not null
            $pg_wc
        group by
            project_id
    ";
    $rs = dbCall($sql,$debug,'tools_data',$server);

    while(!$rs->EOF)
    {
        $project_id = $rs->fields['project_id'];
        $pcm_schema = strtoupper($rs->fields['pcm_schema']);

        //get taskuser2 and taskids from pcm task table
        $sql = "select taskuser2 as CA, taskid as TASK_ID from $pcm_schema.TASK where projectid=$project_id and TASKISCONTROLACCOUNT=1";
        $rs2 = dbCall_Oracle($sql,$debug,'A021PROD');

        //update task_id on ca master
        if($rs2)
        {
            while(!$rs2->EOF)
            {
                $ca = $rs2->fields['CA'];
                $task_id = $rs2->fields['TASK_ID'];

                //only update if task_id is not null
                if($task_id!='')
                {
                    $sql_update = "
                        update
                            ca_master
                        set
                            task_id=$task_id
                        where
                            project_id=$project_id
                            and control_account='$ca'
                    ";
                    $junk = dbCall($sql_update,$debug,'tools_data',$server);
                }
                $rs2->MoveNext();
            }
        }
        $rs->MoveNext();
    }
    return true;
}
//--------------------------------------------
function getCMIDforNOCA($pmid)
{
    $cmid_sql = "
                SELECT
                    cmid
                FROM
                    master_ca
                WHERE
                    pmid = '$pmid' AND ca = 'noca'
                LIMIT 1 ";

    $cmid_rs = dbCall($cmid_sql,$debug);
    $cmid = $cmid_rs->fields['cmid'];
    return $cmid;
}
//--------------------------------------------
function checkMath($num1,$operator,$num2,$debug=false)
{
    $sql = "select ($num1$operator$num2) as num";
    $rs  = dbCall($sql,$debug);

    return $rs->fields['num'];
}
//--------------------------------------------
function getCostFormulaByDataTypebySite($data_type='',$debug=false)
{
    $company   = $_SESSION['filters']['company'];
    if($data_type=='') $data_type = $_SESSION['filters']['data_type'];

    if($company!='Bell' and $company!='Mirabel') $company='TSC';
    $sql = "
           select
               config_value3 as `formula`
           from
               admin_site_config
           where
               company in ('%','$company')
               and config_value='$data_type'
               and config_name='data type'
    ";
    $rs = dbCall($sql,$debug);

    return $rs->fields['formula'];
}
//--------------------------------------------
function getDataTypesAsArraybySite($debug=false)
{
    $company = $_SESSION['filters']['company'];
    if($company!='Bell' and $company!='Mirabel') $company='TSC';

    $sql = "
           select
               config_value as `data_type`
           from
               admin_site_config
           where
               company in ('%','$company')
               and config_name='data type'
    ";
    $rs = dbCall($sql,$debug);

    $data_types = array();
    while(!$rs->EOF)
    {
        $data_types[] = $rs->fields['data_type'];

        $rs->MoveNext();
    }

    return $data_types;
}
//--------------------------------------------
function updateWBSActivityNamePathinSchedule($pmid='',$debug=false)
{
    /*
    //this code is commented out until we determine why it never finishes.

    $ppm_ap_wc = '';
    if($pmid!='')
    {
        $sql = "select ppm_ap_id from master_project where id=$pmid limit 1";
        $rs = dbCall_Premier($sql,$debug,'premier_core');
        $ppm_ap_id = $rs->fields['ppm_ap_id'];

        $ppm_ap_wc = " where proj_id=$ppm_ap_id ";
    }

    $sql = "select wbs_id from schedule $ppm_ap_wc group by wbs_id order by wbs_id";
    $rs = dbCall_Premier($sql,$debug,'schedule_data');

    $i=1;
    while(!$rs->EOF)
    {
        $wbs_id = $rs->fields['wbs_id'];

        // get the full_wbs_path and full_wbs_name
        $sql = "select full_wbs_id,full_wbs_name
            from (
            select length(full_wbs_id) as the_length, full_wbs_id
        ,length(full_wbs_name) as the_length2, full_wbs_name
            from (
            select rtrim(reverse(sys_connect_by_path(reverse(wbs_name),'.')),'.') as full_wbs_name,rtrim(reverse(sys_connect_by_path(reverse(wbs_short_name),'.')),'.') as full_wbs_id
              from admuser.projwbs
              start with wbs_id = '$wbs_id'
              connect by prior parent_wbs_id = wbs_id
                    and NOT ( (proj_node_flag = 'Y') and (prior proj_node_flag = 'Y') )
            )
            order by length(full_wbs_id) desc
            ) where rownum=1
        ";
        $rs2 = dbCall_Oracle($sql,$debug,'A019PROD');
        $path = left($rs2->fields['FULL_WBS_ID'],250);
        $name = left($rs2->fields['FULL_WBS_NAME'],250);
        $rs2 = '';

        $sql = "update schedule set
                    wbs_activity_path='$path',
                    wbs_activity_name='$name'
                where wbs_id='$wbs_id'";
        $junk = dbCall_Premier($sql,$debug,'schedule_data');
        $junk = '';

        //print "($i) wbs_id=$wbs_id - Done<br>\n";

        $i++;
        $rs->MoveNext();
    }
*/
}
// -------------------------------------------
function logPageTimes($args,$debug)
{
    //this function logs basic page info
    //and will be used to gather actual data
    //to answer subjective Premier response-type questions

    /*
        current available field list:
        1. user_id
        2. page
        3. start_time
        4. stop_time
        5. wc
        6. wc_type

        //example use of function
        $page_start_time    = date('Y-m-d H:i:s');
        $page_stop_time     = date('Y-m-d H:i:s');

        $args = array(
                 'user_id'      =>  "$s_user_id"
                ,'page'         =>  "$self"
                ,'start_time'   =>  "$page_start_time"
                ,'stop_time'    =>  "$page_stop_time"
                ,'wc'           =>  "$page_wc"
                ,'wc_type'      =>  "cost"
        );
        logPageTimes($args,$debug);
    */

    //flush everything to user before starting the work
    ob_end_flush();
    ob_flush();
    flush();
    ob_start();

    //build insert and values statements
    foreach($args as $field_name=>$value)
    {
        $field_list .= "$field_name,";

        //escape apostrophes in wc
        if($field_name=='wc') $value = addslashes($value);

        //handle blank values
        $value_to_insert = "'$value'";
        if(trim($value)=='') $value_to_insert = "NULL";

        $values .= "$value_to_insert,";
    }

    $additional_field = '';
    $additional_value = '';
    if($args['start_time']!='' and $args['stop_time']!='')
    {
        $start = $args['start_time'];
        $stop  = $args['stop_time'];

        $additional_field = ',secs';
        $additional_value = ",(SELECT CAST(TIME_TO_SEC(TIMEDIFF('$stop','$start')) AS DECIMAL(15,4)) AS secs)";
    }

    //remove trailing comma
    $field_list = substr($field_list,0,-1);
    $values     = substr($values,0,-1);

    //insert values to db
    $sql    = "insert into admin_page_diags ($field_list$additional_field) values ($values$additional_value) ";
    $junk   = dbCall($sql,$debug);

    return true;
}
//--------------------------------------------
function determineDataTypeFormula($data_type, $company)
{

    if($data_type == '') $data_type = 'dollars';
    $sql = "select config_value, config_value3 as formula from admin_site_config  where config_value = '$data_type'";
    $rs = dbCall($sql, $debug);

    $name    = $rs->fields['config_value'];
    $formula = $rs->fields['formula'];

    $name    = strtolower($name);
    $formula = strtolower($formula);
    $select = "sum($formula) as '$name'";

    return $select;
}
//--------------------------------------------
function requestMasterTablesReload($user_id,$pmid=0,$cmid=0,$ib_server='ib')
{
    //pmid and cmid currently can only accept 0 - which means all pmids and all cmids
    $sql = "
        insert into tools_data.master_table_loader
        (pmid,cmid,load_status,user_id,start_time)
        values
        ($pmid,$cmid,1,'$user_id',NOW())
    ";
    $junk = dbCall($sql,$debug,'tools_data',$ib_server);

    return true;
}
//--------------------------------------------
//--------------------------------------------
?>