<?php
    include("basefunc.inc.php");

    /* variables from the environment (GET/POST) */
    if (array_key_exists("sort", $_REQUEST)) {
      $sort = $_REQUEST["sort"];
    }

    $showall = "false";
    if (array_key_exists("showall", $_REQUEST)) {
      $showall = $_REQUEST["showall"];
    }
     
    session_start();
    
    if (!array_key_exists("userid", $_SESSION)) {
        header("Location: login.php"); exit();
    }
    if ( $_SESSION["userstatus"] < 8 ) {
	header("Location: ".getenv("HTTP_REFERER"));
    }
    
    header("Pragma: no-cache");
    $db = ConnectMysql();
     
    HtmlHead("users", "", $_SESSION["userstatus"], $_SESSION["userid"]);

    if (!isset($sort)) $sort = "id";
    if (($sort == "id") || ($sort=="logons") || ($sort=="laston")) {
	$sort="$sort DESC";
    }
    $where = "where (country.id = country) ";
    $result = mysql_query("SELECT people2.id as id,firstname,surname,email,city,country,status,name,attending,children,arrival,departure,logons,laston FROM people2, country $where ORDER by $sort",$db);
    $users = 0; 
    $link = "";
    if (!$result) {
	printf("<br />%s<br />",mysql_error($db));
    } else {
        if ($showall == "false") {
            $link = "<a href=\"?showall=true&sort=$sort\">(Show All)</a>";
            while ($row = mysql_fetch_array($result)) {
                if ($row["logons"] > 0) {
                    $users++;
                }
            }
            mysql_data_seek($result, 0);
        } else {
            $users = mysql_numrows($result);
        }
	print "<br />$users users $link<br />";
    }	
    echo "Page loaded: ".date("H:i:s d-m-y",time())."<br />";
    ?>
    <table class='reginfo' width='100%'>
      <tr>
	<th><a href="?sort=id&showall=<?echo $showall ?>">id</a></th>
	<th><a href="?sort=firstname&showall=<?echo $showall ?>">name</a>,<br />
	    <a href="?sort=surname&showall=<?echo $showall ?>">surname</a></th>
<?
     if ( $_SESSION["userstatus"] >= 8 ) {
?>
	<th><a href="?sort=email&showall=<?echo $showall ?>">email</a></th>
<?
     }
?>
	<th><a href="?sort=city&showall=<?echo $showall ?>">city</a>,<br />
	    <a href="?sort=country&showall=<?echo $showall ?>">country</a></th>
	<th><a href="?sort=attending&showall=<?echo $showall ?>">Attending?<a/></th>
        <th>children</th>
        <th><a href="?sort=status&showall=<?echo $showall ?>">st</a></th>
	<th>dates</th>
        <th><a href="?sort=logons&showall=<?echo $showall ?>">hits</a></th>
	<th><a href="?sort=laston&showall=<?echo $showall ?>">last</a></th>
      </tr>
    <?php
    while ($row=mysql_fetch_array($result)){
        extract($row);
        if ($showall == "true" || $logons > 0) {
	    print  "      <tr>\n";
	    echo "        <td>$id</td>";
            echo "<td><a href='userview.php?user=$id'>".
                   "$firstname $surname</a></td>";
            if ( $_SESSION["userstatus"] >= 8 ) {
              echo "<td>".str_replace('@', '@<wbr/>', $email)."</td>";
            }
            echo "<td>$city, $name</td>";
            printf("<td>%s</td>", $attending ? "Yes" : "No");
            echo "<td>$children</td>";
	    echo "<td>$status</td>";
            printf("<td>%s - %s</td>", $date[$arrival],
                                       $date[$departure]);
            echo "<td>$logons</td>";
            printf("<td>%s</td>\n", date("Y-m-d H:i:s",$laston));
	    print  "      </tr>\n";
        }
    }
    printf("    </table>");

    HtmlTail();
?>
