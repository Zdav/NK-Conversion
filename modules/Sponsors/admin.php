<?php
/**
 * @version     1.8
 * @link http://www.nuked-klan.org Clan Clan Management System for Gamers
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright 2001-2015 Nuked-Klan (Registred Trademark)
 */
defined('INDEX_CHECK') or die ('You can\'t run this file alone.');

global $user, $language;
translate('modules/Sponsors/lang/' . $language . '.lang.php');
include('modules/Admin/design.php');
admintop();

$visiteur = ($user) ? $user[1] : 0;

$ModName = basename(dirname(__FILE__));
$level_admin = admin_mod($ModName);
if ($visiteur >= $level_admin && $level_admin > -1){
    function add_sponsors(){
        global $nuked, $language;

        echo "<div class=\"content-box\">\n" //<!-- Start Content Box -->
                . "<div class=\"content-box-header\"><h3>" . _ADMINSPONSORS . " - " . _ADDSPONSORS . "</h3>\n"
                . "<div style=\"text-align:right;\"><a href=\"help/" . $language . "/Sponsors.php\" rel=\"modal\">\n"
                . "<img style=\"border: 0;\" src=\"help/help.gif\" alt=\"\" title=\"" . _HELP . "\" /></a>\n"
                . "</div></div>\n"
                . "<div class=\"tab-content\" id=\"tab2\">\n";

                nkAdminMenu(2);

                echo "<form method=\"post\" action=\"index.php?file=Sponsors&amp;page=admin&amp;op=add\" onsubmit=\"backslash('sponsors_texte');\" enctype=\"multipart/form-data\">\n"
                . "<table style=\"margin-left: auto;margin-right: auto;text-align: left;\" border=\"0\" cellspacing=\"0\" cellpadding=\"2\">\n"
                . "<tr><td><b>" . _NAME . " :</b> <input type=\"text\" name=\"name\" size=\"40\" /></td></tr>\n";

        echo "</select></td></tr><tr><td><b>" . _COUNTRY . " :</b> <select name=\"country\"><option value=\"\">* " . _NOCOUNTRY . "</option>\n";

        if ($language == 'french') $pays = 'France.gif';

        $rep = Array();
        $handle = opendir('images/flags');
        while (false !== ($f = readdir($handle))){
            if ($f != '..' && $f != '.' && $f != 'index.html' && $f != 'Thumbs.db')
                $rep[] = $f;
        }

        closedir($handle);
        sort ($rep);
        reset ($rep);

        while (list ($key, $filename) = each ($rep)) {
            if ($filename == $pays)
                $checked = 'selected="selected"';
            else
                $checked = '';

            list ($country, $ext) = explode ('.', $filename);
            echo '<option value="' . $filename . '" ' . $checked . '>' . $country . '</option>'."\n";
        }

        echo "</select></td></tr>\n";

        echo "<tr><td><b>" . _LOGO . " :</b> <input type=\"text\" name=\"urlImage\" size=\"42\" /></td></tr>\n"
            . "<tr><td><b>" . _UPLOADIMAGE . " :</b> <input type=\"file\" name=\"upImage\" /></td></tr>\n"
            . "<tr><td><b>" . _DESCR . " : </b><br /><textarea class=\"editor\" id=\"sponsors_texte\" name=\"description\" rows=\"10\" cols=\"65\"></textarea></td></tr>\n"
            . "<tr><td><b>" . _URL . " :</b>  <input type=\"text\" name=\"url\" size=\"55\" value=\"http://\" /></td></tr>\n"
            . "</table>\n"
            . "<div style=\"text-align: center;\"><br /><input class=\"button\" type=\"submit\" value=\"" . _ADD . "\" /><a class=\"buttonLink\" href=\"index.php?file=Sponsors&amp;page=admin\">" . _BACK . "</a></div></form><br /></div></div>";
    }


    function add ($name, $description, $country, $url, $urlImage, $upImage){
        global $nuked, $user;

        $date = time();
        $description = secu_html(nkHtmlEntityDecode($description));
        $description = mysql_real_escape_string(stripslashes($description));
        $name = mysql_real_escape_string(stripslashes($name));

        if ($url != "" && !preg_match("`http://`i", $url)){
            $url = "http://" . $url;
        }

        //Upload du fichier
        $filename = $_FILES['upImage']['name'];
        if ($filename != "") {
            $ext = pathinfo($filename, PATHINFO_EXTENSION);

            if ($ext == "jpg" || $ext == "jpeg" || $ext == "JPG" || $ext == "JPEG" || $ext == "gif" || $ext == "GIF" || $ext == "png" || $ext == "PNG") {
                $url_image = "upload/Sponsors/" . $filename;
                move_uploaded_file($_FILES['upImage']['tmp_name'], $url_image) 
                or die (printNotification(_UPLOADFILEFAILED, 'index.php?file=Sponsors&page=admin&op=add_sponsors', $type = 'error', $back = false, $redirect = true));
                @chmod ($url_image, 0644);
            }
            else {
                printNotification(_NOIMAGEFILE, 'index.php?file=Sponsors&page=admin&op=add_sponsors', $type = 'error', $back = false, $redirect = true);
                adminfoot();
                footer();
                die;
            }
        }
        else {
            $url_image = $urlImage;
        }

        $sql = mysql_query("INSERT INTO " . SPONSORS_TABLE . " ( `id` , `date` , `name` , `logo` , `description` , `url` , `country`, `count` ) VALUES ( '' , '" . $date . "' , '" . $name . "' , '" . $url_image . "' , '" . $description . "' , '" . $url . "' , '" . $country . "' , '' )");
        // Action
        $texteaction = _ACTIONADDSPONSORS . ': ' . $name;
        $acdate = time();
        $sqlaction = mysql_query("INSERT INTO ". $nuked['prefix'] ."_action  (`date`, `pseudo`, `action`)  VALUES ('".$acdate."', '".$user[0]."', '".$texteaction."')");
        //Fin action
        printNotification(_SPONSORSADD, 'index.php?file=Sponsors&page=admin', $type = 'success', $back = false, $redirect = true);
        $sql = mysql_query("SELECT id FROM " . SPONSORS_TABLE . " WHERE name = '" . $name . "' AND date='".$date."'");
        list($sponsors_id) = mysql_fetch_array($sql);
        echo "<script>\n"
            ."setTimeout('screen()','3000');\n"
            ."function screen() { \n"
            ."screenon('index.php?file=Sponsors&op=description&sponsors_id=".$sponsors_id."', 'index.php?file=Sponsors&page=admin');\n"
            ."}\n"
            ."</script>\n";
    }

    function del($sponsors_id){
        global $nuked, $user;

        $sql = mysql_query("SELECT name FROM " . SPONSORS_TABLE . " WHERE id = '" . $sponsors_id . "'");
        list($name) = mysql_fetch_array($sql);
        $name = mysql_real_escape_string(stripslashes($name));
        $sql = mysql_query("DELETE FROM " . SPONSORS_TABLE . " WHERE id = '" . $sponsors_id . "'");
        $del_vote = mysql_query("DELETE FROM " . VOTE_TABLE . " WHERE vid = '" . $sponsors_id . "' AND module = 'Sponsors'");

        // Action
        $texteaction = _ACTIONDELSPONSORS . ': ' . $name;
        $acdate = time();
        $sqlaction = mysql_query("INSERT INTO ". $nuked['prefix'] ."_action  (`date`, `pseudo`, `action`)  VALUES ('".$acdate."', '".$user[0]."', '".$texteaction."')");
        //Fin action
        printNotification(_SPONSORSDEL, 'index.php?file=Sponsors&page=admin', $type = 'success', $back = false, $redirect = true);
    }

    function edit_sponsors($sponsors_id){
        global $nuked, $language;

        $sql = mysql_query("SELECT name, logo, description, country, url, count FROM " . SPONSORS_TABLE . " WHERE id = '" . $sponsors_id . "'");
        list($name, $logo, $description, $pays, $url, $count) = mysql_fetch_array($sql);

        if ($pays == '') $checked1 = 'selected="selected"';

        echo "<div class=\"content-box\">\n" //<!-- Start Content Box -->
                . "<div class=\"content-box-header\"><h3>" . _ADMINSPONSORS . " - " . _EDITTHISSPONSORS . "</h3>\n"
                . "<div style=\"text-align:right;\"><a href=\"help/" . $language . "/Links.php\" rel=\"modal\">\n"
                . "<img style=\"border: 0;\" src=\"help/help.gif\" alt=\"\" title=\"" . _HELP . "\" /></a>\n"
                . "</div></div>\n"
                . "<div class=\"tab-content\" id=\"tab2\">\n";

                nkAdminMenu(2);

                echo "<form method=\"post\" action=\"index.php?file=Sponsors&amp;page=admin&amp;op=modif_sponsors\" onsubmit=\"backslash('sponsors_texte');\" enctype=\"multipart/form-data\">\n"
                . "<table style=\"margin-left: auto;margin-right: auto;text-align: left;\" border=\"0\" cellspacing=\"0\" cellpadding=\"2\">\n"
                . "<tr><td><b>" . _TITLE . " :</b> <input type=\"text\" name=\"name\" size=\"40\" value=\"" . $name . "\" /></td></tr>\n";

        echo "</select></td></tr><tr><td><b>" . _COUNTRY . " :</b> <select name=\"country\"><option value=\"\" " . $checked1 . ">* " . _NOCOUNTRY . "</option>\n";

        $rep = Array();
        $handle = opendir('images/flags');
        while (false !== ($f = readdir($handle))){
            if ($f != '..' && $f != '.' && $f != 'index.html' && $f != 'Thumbs.db'){
                $rep[] = $f;
            }
        }
        closedir($handle);
        sort($rep);
        reset($rep);

        while (list ($key, $filename) = each ($rep)) {
            if ($filename == $pays)
                $checked = 'selected="selected"';
            else
                $checked = '';

            list ($country, $ext) = explode ('.', $filename);
            echo '<option value="' . $filename . '" ' . $checked . '>' . $country . '</option>',"\n";
        }

        echo "</select></td></tr>\n";

        $description = editPhpCkeditor($description);

        echo "<tr><td><b>" . _IMAGE . " :</b> <input type=\"text\" name=\"urlImage\" value=\"" . $logo . "\" size=\"42\" />\n";

            if ($logo != ""){
                echo "<img src=\"" . $logo . "\" title=\"" . printSecuTags($name) . "\" style=\"margin-left:20px; max-width:300px; height:auto; vertical-align:middle;\" />\n";
            }

            echo "</td></tr>\n"
            . "<tr><td><b>" . _UPLOADIMAGE . " :</b> <input type=\"file\" name=\"upImage\" /></td></tr>\n"
            . "<tr><td><b>" . _DESCR . " : </b><br /><textarea class=\"editor\" id=\"sponsors_texte\" name=\"description\" rows=\"10\" cols=\"65\">" . $description . "</textarea></td></tr>\n"
            . "<tr><td><b>" . _URL . " :</b>  <input type=\"text\" name=\"url\" size=\"55\" value=\"" . $url . "\" /></td></tr>\n"
            . "<tr><td><b>" . _VISIT . "</b> : <input type=\"text\" name=\"count\" size=\"7\" value=\"" . $count . "\" /></td></tr>\n"
            . "<tr><td>&nbsp;<input type=\"hidden\" name=\"sponsors_id\" value=\"" . $sponsors_id . "\" /></td></tr></table>\n"
            . "<div style=\"text-align: center;\"><br /><input class=\"button\" type=\"submit\" value=\"" . _EDIT . "\" /><a class=\"buttonLink\" href=\"index.php?file=Sponsors&amp;page=admin\">" . _BACK . "</a></div></form><br /></div>";

    }

    function modif_sponsors($sponsors_id, $name, $description, $country, $count, $url, $urlImage, $upImage){
        global $nuked, $user;

        $description = secu_html(nkHtmlEntityDecode($description));
        $description = mysql_real_escape_string(stripslashes($description));
        $name = mysql_real_escape_string(stripslashes($name));

        if ($url != "" && !preg_match("`http://`i", $url)){
            $url = "http://" . $url;
        }

        //Upload du fichier
        $filename = $_FILES['upImage']['name'];
        if ($filename != "") {
            $ext = pathinfo($filename, PATHINFO_EXTENSION);

            if ($ext == "jpg" || $ext == "jpeg" || $ext == "JPG" || $ext == "JPEG" || $ext == "gif" || $ext == "GIF" || $ext == "png" || $ext == "PNG") {
                $url_image = "upload/Sponsors/" . $filename;
                move_uploaded_file($_FILES['upImage']['tmp_name'], $url_image) 
                or die (printNotification(_UPLOADFILEFAILED, 'index.php?file=Sponsors&page=admin&op=add_sponsors', $type = 'error', $back = false, $redirect = true));
                @chmod ($url_image, 0644);
            }
            else {
                printNotification(_NOIMAGEFILE, 'index.php?file=Sponsors&page=admin&op=add_sponsors', $type = 'error', $back = false, $redirect = true);
                adminfoot();
                footer();
                die;
            }
        }
        else {
            $url_image = $urlImage;
        }

        $sql = mysql_query("UPDATE " . SPONSORS_TABLE . " SET name = '" . $name . "', description = '" . $description . "', logo = '" . $url_image . "', country = '" . $country . "', count = '" . $count. "', url = '" . $url . "' WHERE id = '" . $sponsors_id . "'");
        // Action
        $texteaction = _ACTIONEDITSPONSORS. ': ' . $name;
        $acdate = time();
        $sqlaction = mysql_query("INSERT INTO ". $nuked['prefix'] ."_action  (`date`, `pseudo`, `action`)  VALUES ('".$acdate."', '".$user[0]."', '".$texteaction."')");
        //Fin action
        printNotification(_SPONSORSEDIT, 'index.php?file=Sponsors&page=admin', $type = 'success', $back = false, $redirect = true);
        echo "<script>\n"
                ."setTimeout('screen()','3000');\n"
                ."function screen() { \n"
                ."screenon('index.php?file=Sponsors&op=description&sponsors_id=".$sponsors_id."', 'index.php?file=Sponsors&page=admin');\n"
                ."}\n"
                ."</script>\n";
    }

    function main(){
        global $nuked, $language;

        $nb_sponsors = 30;

        $sql3 = mysql_query("SELECT id FROM " . SPONSORS_TABLE . "");
        $nb_lk = mysql_num_rows($sql3);

        if (!$_REQUEST['p']) $_REQUEST['p'] = 1;
        $start = $_REQUEST['p'] * $nb_sponsors - $nb_sponsors;

        echo "<script type=\"text/javascript\">\n"
                ."<!--\n"
                ."\n"
                . "function del_sponsors(name, id)\n"
                . "{\n"
                . "if (confirm('" . _DELETESPONSORS . " '+name+' ?'))\n"
                . "{document.location.href = 'index.php?file=Sponsors&page=admin&op=del&sponsors_id='+id;}\n"
                . "}\n"
                . "\n"
                . "// -->\n"
                . "</script>\n";

       echo "<div class=\"content-box\">\n" //<!-- Start Content Box -->
                . "<div class=\"content-box-header\"><h3>" . _ADMINSPONSORS . "</h3>\n"
                . "<div style=\"text-align:right;\"><a href=\"help/" . $language . "/Sponsors.php\" rel=\"modal\">\n"
                . "<img style=\"border: 0;\" src=\"help/help.gif\" alt=\"\" title=\"" . _HELP . "\" /></a>\n"
                . "</div></div>\n"
                . "<div class=\"tab-content\" id=\"tab2\">\n";

                nkAdminMenu(1);

        if ($_REQUEST['orderby'] == 'date')
            $order_by = 'id DESC';
        else if ($_REQUEST['orderby'] == 'name')
            $order_by = 'name';
        else
            $order_by = 'id DESC';

        echo "<table width=\"100%\" cellpadding=\"2\" cellspacing=\"0\" border=\"0\">\n"
                . "<tr><td align=\"right\">" . _ORDERBY . " : ";

        if ($_REQUEST['orderby'] == 'date' || !$_REQUEST['orderby'])
            echo '<b>' . _DATE . '</b> | ';
        else
            echo "<a href=\"index.php?file=Sponsors&amp;page=admin&amp;orderby=date\">" . _DATE . "</a> | ";
        if ($_REQUEST['orderby'] == "name")
            echo "<b>" . _NAME . "</b> | ";
        else
            echo"<a href=\"index.php?file=Sponsors&amp;page=admin&amp;orderby=name\">" . _NAME . "</a>";

        echo "&nbsp;</td></tr></table>\n";

        if ($nb_lk > $nb_sponsors){
            echo "<div>";
            $url_page = "index.php?file=Sponsors&amp;page=admin&amp;orderby=" . $_REQUEST['orderby'];
            number($nb_lk, $nb_sponsors, $url_page);
            echo "</div>\n";
        }

        echo "<table width=\"100%\" border=\"0\" cellspacing=\"1\" cellpadding=\"2\">\n"
                . "<tr>\n"
                . "<td style=\"width: 25%;\" align=\"center\"><b>" . _NAME . "</b></td>\n"
                . "<td style=\"width: 20%;\" align=\"center\"><b>" . _DATE . "</b></td>\n"
                . "<td style=\"width: 15%;\" align=\"center\"><b>" . _EDIT . "</b></td>\n"
                . "<td style=\"width: 15%;\" align=\"center\"><b>" . _DEL . "</b></td></tr>\n";

        $sql = mysql_query("SELECT id, name, url, date FROM " . SPONSORS_TABLE . " ORDER BY " . $order_by . " LIMIT " . $start . ", " . $nb_sponsors."");
        while (list($sponsors_id, $name, $url, $date) = mysql_fetch_array($sql)){
            $date = nkDate($date);

            if (strlen($name) > 25)
                $title = "<a href=\"" . $url . "\" title=\"" . $url . "\" onclick=\"window.open(this.href); return false;\">" . printSecuTags(substr($name, 0, 25)) . "</a>...";
            else
                $title = "<a href=\"" . $url . "\" title=\"" . $url . "\" onclick=\"window.open(this.href); return false;\">" . printSecuTags($name) . "</a>";

            echo "<tr>\n"
                    . "<td style=\"width: 25%;\">" . $title . "</td>\n"
                    . "<td style=\"width: 20%;\" align=\"center\">" . $date . "</td>\n"
                    . "<td style=\"width: 15%;\" align=\"center\"><a href=\"index.php?file=Sponsors&amp;page=admin&amp;op=edit_sponsors&amp;sponsors_id=" . $sponsors_id . "\"><img style=\"border: 0;\" src=\"images/edit.gif\" alt=\"\" title=\"" . _EDIT . "\" /></a></td>\n"
                    . "<td style=\"width: 15%;\" align=\"center\"><a href=\"javascript:del_sponsors('" . mysql_real_escape_string(stripslashes($name)) . "', '" . $sponsors_id . "');\"><img style=\"border: 0;\" src=\"images/del.gif\" alt=\"\" title=\"" . _DEL . "\" /></a></td></tr>\n";
        }

        if ($nb_lk == 0)
            echo "<tr><td colspan=\"5\" align=\"center\">" . _NOSPONSORSINDB . "</td></tr>\n";

        echo "</table>\n";

        if ($nb_lk > $nb_sponsors){
            echo "<div>";
            $url_page = "index.php?file=Sponsors&amp;page=admin&amp;orderby=" . $_REQUEST['orderby'];
            number($nb_lk, $nb_sponsors, $url_page);
            echo "</div>\n";
        }

        echo "<br /><div style=\"text-align: center;\"><a class=\"buttonLink\" href=\"index.php?file=Admin\">" . _BACK . "</a></div><br /></div></div>";
    }

    function main_pref(){
        global $nuked, $language;

        echo "<div class=\"content-box\">\n" //<!-- Start Content Box -->
                . "<div class=\"content-box-header\"><h3>" . _PREFS . "</h3>\n"
                . "<div style=\"text-align:right;\"><a href=\"help/" . $language . "/Links.php\" rel=\"modal\">\n"
                . "<img style=\"border: 0;\" src=\"help/help.gif\" alt=\"\" title=\"" . _HELP . "\" /></a>\n"
                . "</div></div>\n"
                . "<div class=\"tab-content\" id=\"tab2\">\n";

                nkAdminMenu(5);

                echo "<form method=\"post\" action=\"index.php?file=Sponsors&amp;page=admin&amp;op=change_pref\">\n"
                . "<table style=\"margin-left: auto;margin-right: auto;text-align: left;\" border=\"0\" cellspacing=\"0\" cellpadding=\"3\">\n"
                . "<tr><td align=\"center\" colspan=\"2\"><big>" . _PREFS . "</big></td></tr>\n"
                . "<tr><td>" . _NUMBERLINK . " :</td><td><input type=\"text\" name=\"max_sponsors\" size=\"2\" value=\"" . $nuked['max_liens'] . "\" /></td></tr></table>\n"
                . "<div style=\"text-align: center;\"><br /><input class=\"button\" type=\"submit\" value=\"" . _SEND . "\" /><a class=\"buttonLink\" href=\"index.php?file=Sponsors&amp;page=admin\">" . _BACK . "</a></div>\n"
                . "</form><br /></div></div>\n";
    }

    function change_pref($max_liens){
        global $nuked, $user;

        $upd = mysql_query("UPDATE " . CONFIG_TABLE . " SET value = '" . $max_liens . "' WHERE name = 'max_sponsors'");
        // Action
        $texteaction = _ACTIONCONFLINK;
        $acdate = time();
        $sqlaction = mysql_query("INSERT INTO ". $nuked['prefix'] ."_action  (`date`, `pseudo`, `action`)  VALUES ('".$acdate."', '".$user[0]."', '".$texteaction."')");
        //Fin action
        echo "<div class=\"notification success png_bg\">\n"
                . "<div>\n"
                . "" . _PREFUPDATED . "\n"
                . "</div>\n"
                . "</div>\n";

        redirect("index.php?file=Sponsors&page=admin", 2);
    }

        function nkAdminMenu($tab = 1)
    {
        global $language, $user, $nuked;

        $class = ' class="nkClassActive" ';
?>
        <div class= "nkAdminMenu">
            <ul class="shortcut-buttons-set" id="1">
                <li <?php echo ($tab == 1 ? $class : ''); ?>>
                    <a class="shortcut-button" href="index.php?file=Sponsors&amp;page=admin">
                        <img src="modules/Admin/images/icons/speedometer.png" alt="icon" />
                        <span><?php echo _NAVSPONSORS; ?></span>
                    </a>
                </li>
                <li <?php echo ($tab == 2 ? $class : ''); ?>>
                    <a class="shortcut-button" href="index.php?file=Sponsors&amp;page=admin&amp;op=add_sponsors">
                        <img src="modules/Admin/images/icons/euro_coin.png" alt="icon" />
                        <span><?php echo _ADDSPONSORS; ?></span>
                    </a>
                </li>
                <li <?php echo ($tab == 3 ? $class : ''); ?>>
                    <a class="shortcut-button" href="index.php?file=Sponsors&amp;page=admin&amp;op=main_pref">
                        <img src="modules/Admin/images/icons/process.png" alt="icon" />
                        <span><?php echo _PREFS; ?></span>
                    </a>
                </li>
            </ul>
        </div>
        <div class="clear"></div>
<?php
    }

    switch ($_REQUEST['op']){
        case "edit_sponsors":
            edit_sponsors($_REQUEST['sponsors_id']);
            break;
        case "add_sponsors":
            add_sponsors();
            break;
        case "del":
            del($_REQUEST['sponsors_id']);
            break;
        case "add":
            add($_REQUEST['name'], $_REQUEST['description'], $_REQUEST['country'], $_REQUEST['url'], $_REQUEST['urlImage'], $_REQUEST['upImage']);
            break;
        case "modif_sponsors":
            modif_sponsors($_REQUEST['sponsors_id'], $_REQUEST['name'], $_REQUEST['description'], $_REQUEST['country'], $_REQUEST['count'], $_REQUEST['url'], $_REQUEST['urlImage'], $_REQUEST['upImage']);
            break;
        case "main":
            main();
            break;
         case "main_pref":
            main_pref();
            break;
        case "change_pref":
            change_pref($_REQUEST['max_liens']);
            break;
        default:
            main();
            break;
    }
}
else if ($level_admin == -1){
    printNotification(_MODULEOFF, 'javascript:history.back()', $type = 'error', $back = true, $redirect = false);
}
else if ($visiteur > 1){
    printNotification(_NOENTRANCE, 'javascript:history.back()', $type = 'error', $back = true, $redirect = false);
}
else{
    printNotification(_ZONEADMIN, 'javascript:history.back()', $type = 'error', $back = true, $redirect = false);
}

adminfoot();
?>
