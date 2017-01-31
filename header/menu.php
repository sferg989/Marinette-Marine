<?php
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 1/30/2017
 * Time: 4:14 PM
 */
include("../inc/inc.php");
function getMenuCategories($us_citizen, $role){
    $data = array();
    $sql = "select category, name, url, permissions, us_citizen 
                from fmm_evms.menu 
                where us_citizen = '$us_citizen' 
                and permissions like '%$role%'
                group by category
                ";

    $rs = dbCall($sql);
    while (!$rs->EOF)
    {
        $category    = $rs->fields["category"];
        $data[]     = $category;
        $rs->MoveNext();
    }
    return $data;
}
function getPagesByCategory($category,$us_citizen, $role){
    $data = array();
    $sql = "select name, url 
                from fmm_evms.menu 
                where us_citizen = '$us_citizen' 
                and permissions like '%$role%'
                and category = '$category'
            ";

    $rs = dbCall($sql);
    while (!$rs->EOF)
    {
        $name = $rs->fields["name"];
        $url  = $rs->fields["url"];

        $data[$url] = $name;
        $rs->MoveNext();
    }
    return $data;
}
if($control =="build_menu"){
    $user = $_SESSION["user_name"];
    $role = $_SESSION["role"];
    $hullS= $_SESSION["hulls"];
    $us_citizen = $_SESSION["us_citizen"];
    $category_array = getMenuCategories($us_citizen, $role);
    foreach ($category_array as $category){
        $data.="<li class=\"dropdown\" ><a class=\"dropdown-toggle\" data-toggle=\"dropdown\" href=\"#\">$category<span class=\"caret\"></span></a>
                    <ul class=\"dropdown-menu\">
                    ";

        $menu_data = getPagesByCategory($category,$us_citizen, $role);
        foreach ($menu_data as $url=>$page_name){
            $data.="<li><a href=\"#\" onclick=\"loadIframe('$url')\">$page_name</a></li>";
        }
        $data.= "
            </ul>
            </li>
        ";
    }
    die($data);
}
