<?php
/**
 * DokuWiki Plugin custombuttons (Admin Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author Constantinos Xanthopoulos <conx@xanthopoulos.info>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();
if(!defined('DOKU_BASE')) define('DOKU_BASE',getBaseURL());
if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once DOKU_PLUGIN.'admin.php';

class admin_plugin_custombuttons extends DokuWiki_Admin_Plugin {

    function forAdminOnly() {
        return true;
    }

    function loadCBData() {
        $json = new JSON(JSON_LOOSE_TYPE);
        $file = @file_get_contents(DOKU_PLUGIN."custombuttons/config.json");
        if(!$file) return false;
        return $json->decode($file);
    }

    function saveCBData($conf) {
        $json = new JSON();
        $json = $json->encode($conf);
        file_put_contents(DOKU_PLUGIN."custombuttons/config.json", $json);
    }

    function reloadBar() {
        touch(DOKU_CONF."local.php");
    }

    function handle() {

        if (isset($_REQUEST['add'])) {
            if (!checkSecurityToken()) return;
            $conf = $this->loadCBData();
            if(!$conf) $conf = array();
            $type = 0;
            if ($_REQUEST["pretag"] != "" && $_REQUEST["posttag"] != "") $type = 1;
            array_push($conf, array(
                "label" => $_REQUEST["label"],
                "code" => $_REQUEST["code"],
                "type" => $type,
                "pretag" => $_REQUEST["pretag"],
                "posttag" => $_REQUEST["posttag"],
                "icon" => $_REQUEST["icon"],
            ));
            $this->saveCBData($conf);
            $this->reloadBar();
        } elseif (isset($_REQUEST['delete'])) {
            if (!checkSecurityToken()) return;
            $conf = $this->loadCBData();
            unset($conf[$_REQUEST["delete"]]);
            $this->saveCBData($conf);
            $this->reloadBar();
        }
    }

    function html() {
        global $ID;
        $conf = $this->loadCBData();


        //dbg($conf);
        ptln('<h3>Buttons List</h3>');
        ptln('<form action="'.wl($ID).'" method="post">');
        ptln('  <input type="hidden" name="do"   value="admin" />');
        ptln('  <input type="hidden" name="page" value="'.$this->getPluginName().'" />');
        formSecurityToken();
        ptln('  <table class="inline">');
        ptln('    <tr><th>Label</th><th>Code</th><th>Delete?</th></tr>');
        if ($conf) {
            foreach ($conf as $key => $button) {
                if (!$button["type"])
                    ptln('    <tr><td>'.$button["label"].'</td><td>'.htmlspecialchars($button["code"]).'</td><td><center><input type="radio" name="delete" value="'.$key.'"/></center></td></tr>');     # FIXME Del image
                else
                    ptln('    <tr><td>'.$button["label"].'</td><td>'.htmlspecialchars($button["pretag"]).htmlspecialchars($button["code"]).htmlspecialchars($button["posttag"]).'</td><td><center><input type="radio" name="delete" value="'.$key.'"/></center></td></tr>');        # FIXME Del image
            }
        }
        ptln('  </table>');
        ptln('<input type="submit" class="button" value="Delete Selected"/>');
        ptln('</form>');

        ptln('<h3>Add Button</h3>');
        ptln('<form action="'.wl($ID).'" method="post">');
        ptln('  <input type="hidden" name="do"   value="admin" />');
        ptln('  <input type="hidden" name="add"   value="1" />');
        ptln('  <input type="hidden" name="page" value="'.$this->getPluginName().'" />');
        formSecurityToken();
        ptln('  <table>');
        ptln('    <tr><th>Icon:</th><td>');
        ptln('<select name="icon">');
        ptln('<option value="">text only</option>');
        $files = glob(dirname(__FILE__).'/ico/*.png');
        foreach($files as $file){
            $file = hsc(basename($file));
            ptln('<option value="'.$file.'" style="padding-left: 18px; background: #fff url('.DOKU_BASE.'lib/plugins/custombuttons/ico/'.$file.') left center no-repeat">'.$file.'</option>');
        }
        ptln('</select>');
        ptln('    </td></tr>');
        ptln('    <tr><th>Label:</th><td><input type="text" name="label" /></td></tr>');
        ptln('    <tr><th>Pre tag:</th><td><input type="text" name="pretag" /><b> *</b></td></tr>');
        ptln('    <tr><th>Post tag:</th><td><input type="text" name="posttag" /><b> *</b></td></tr>');
        ptln('    <tr><th>Code:</th><td><input type="text" name="code" /></td></tr>');
        ptln('  </table>');
        ptln('  <input type="submit" class="button" value="Add" />');
        ptln('</form>');
        ptln('<br><br><div><b>*</b> If you dont want to add a shortcut button with pre and post code leave those fields empty.</div>');
    }
}
