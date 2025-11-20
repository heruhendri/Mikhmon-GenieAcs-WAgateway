<?php
/*
 *  Copyright (C) 2018 Laksamadi Guko.
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
session_start();
// hide all error
error_reporting(0);
if (!isset($_SESSION["mikhmon"])) {
    header("Location:../admin.php?id=login");
} else {

    $getprofile = $API->comm("/ppp/profile/print");

    if (substr($secretbyname, 0, 1) == "*") {
        $secretbyname = $secretbyname;
    } elseif (substr($secretbyname, 0, 1) != "") {
        $getsecret = $API->comm("/ppp/secret/print", array(
            "?name" => "$secretbyname",
        ));
        $secretbyname = $getsecret[0]['.id'];
        if ($secretbyname == "") {
            echo "<b>Secrets not found</b>";
        }
    } else {
        $secretbyname = substr($secretbyname, 7);
    }


    $getsecret = $API->comm("/ppp/secret/print", array(
        "?.id" => "$secretbyname"
    ));
    $secretdetail = $getsecret[0];
    $sid = $secretdetail['.id'];
    $sname = $secretdetail['name'];
    $password = $secretdetail['password'];
    $service = $secretdetail['service'];
    $callerid = $secretdetail['caller-id'];
    $profile = $secretdetail['profile'];
    $onup = $secretdetail['on-up'];

    // Check if expiry script is already set
    $useexpiryscript = "no";
    $expiryprofile = "";
    $expiryinterval = "";
    if (strpos($onup, 'exp-$pengguna') !== false) {
        $useexpiryscript = "yes";
        // Extract profile and interval from existing script if possible
        // This is a simplified extraction - in practice you might want more robust parsing
    }

    $getsch = $API->comm("/system/scheduler/print", array(
        "?name" => "$schedulerbyname"
    ));
    $sechdulerdetail = $getsch[0];
    $schid = $sechdulerdetail['.id'];
    $schname = $sechdulerdetail['name'];
    $schinterval = $sechdulerdetail['interval'];
    $schon = $sechdulerdetail['on-event'];
    
    if (isset($_POST['name'])) {
        $name = (preg_replace('/\s+/', '-', $_POST['name']));
        $password = ($_POST['password']);
        $service = ($_POST['service']);
        $callerid = ($_POST['callerid']);
        $profile = ($_POST['profile']);
        $interval = ($_POST['interval']);
        // Script expiry variables
        $useexpiryscript = ($_POST['useexpiryscript']);
        $expiryprofile = ($_POST['expiryprofile']);
        $expiryinterval = ($_POST['expiryinterval']);
        
        // Prepare on-up script
        $onupscript = "";
        if ($useexpiryscript == "yes" && $expiryprofile != "" && $expiryinterval != "") {
            // Create the expiry script
            $onupscript = ':local pengguna $"user"; :local date [/system clock get date]; :local time [/system clock get time]; :log info "User PPPoE $pengguna login pada $time tanggal $date"; { :if ([/system scheduler find name="exp-$pengguna"]="") do={ /system scheduler add name="exp-$pengguna" interval='.$expiryinterval.' on-event="/ppp secret set profile='.$expiryprofile.' [find name=\\\$pengguna]; /ppp active remove [find name=\\\$pengguna]; :log warning \\"User \\\$pengguna expired dan dipindah ke profile '.$expiryprofile.'\\"; /system scheduler remove [find name=\\"exp-\\\$pengguna\\"]"; :log info "Scheduler auto expiry dibuat untuk user $pengguna ('.$expiryinterval.')"; } }';
        }

        $API->comm("/ppp/secret/set", array(
            /*"add-mac-cookie" => "yes",*/
            ".id" => "$sid",
            "name" => "$name",
            "password" => "$password",
            "service" => "$service",
            "caller-id" => "$callerid",
            "profile" => "$profile",
            "on-up" => "$onupscript",
        ));

        $API->comm("/system/scheduler/set", array(
            /*"add-mac-cookie" => "yes",*/
            ".id" => "$schid",
            "interval" => "$interval",
            // "on-event" => "$on",
        ));
        echo "<script>window.location='./?ppp=secrets&session=" . $session . "'</script>";
    }
}
?>
<div class="row">
    <div class="col-8">
        <div class="card">
            <div class="card-header">
                <h3><i class="fa fa-edit"></i> Edit PPP Secret </h3>
            </div>
            <div class="card-body">
                <form autocomplete="off" method="post" action="">
                    <div>
                        <a class="btn bg-warning" href="./?ppp=secrets&session=<?= $session; ?>"> <i
                                class="fa fa-close"></i> <?= $_close ?></a>
                        <button type="submit" name="save" class="btn bg-primary"><i class="fa fa-save"></i>
                            <?= $_save ?></button>
                    </div>
                    <table class="table">
                        <tr>
                            <td class="align-middle"><?= $_name ?></td>
                            <td><input class="form-control" type="text" onchange="remSpace();" autocomplete="off"
                                    name="name" value="<?= $sname; ?>" required="1" autofocus></td>
                        </tr>
                        <tr>
                            <td class="align-middle">Password</td>
                            <td><input class="form-control" type="text" size="4" autocomplete="off" name="password"
                                    value="<?= $password; ?>"></td>
                        </tr>
                        <tr>
                            <td class="align-middle">Service</td>
                            <td>
                                <?php if ($service == 'any') { ?>
                                <select class="form-control" name="service" required="1">
                                    <option value="any" selected>any</option>
                                    <option value="async">async</option>
                                    <option value="l2tp">l2tp</option>
                                    <option value="ovpn">ovpn</option>
                                    <option value="pppoe">pppoe</option>
                                    <option value="pptp">pptp</option>
                                    <option value="sstp">sstp</option>
                                </select>
                                <?php } elseif ($service == 'async') { ?>
                                <select class="form-control" name="service" required="1">
                                    <option value="any">any</option>
                                    <option value="async" selected>async</option>
                                    <option value="l2tp">l2tp</option>
                                    <option value="ovpn">ovpn</option>
                                    <option value="pppoe">pppoe</option>
                                    <option value="pptp">pptp</option>
                                    <option value="sstp">sstp</option>
                                </select>
                                <?php } elseif ($service == '12tp') { ?>
                                <select class="form-control" name="service" required="1">
                                    <option value="any">any</option>
                                    <option value="async">async</option>
                                    <option value="l2tp" selected>l2tp</option>
                                    <option value="ovpn">ovpn</option>
                                    <option value="pppoe">pppoe</option>
                                    <option value="pptp">pptp</option>
                                    <option value="sstp">sstp</option>
                                </select>
                                <?php } elseif ($service == 'ovpn') { ?>
                                <select class="form-control" name="service" required="1">
                                    <option value="any">any</option>
                                    <option value="async">async</option>
                                    <option value="l2tp">l2tp</option>
                                    <option value="ovpn" selected>ovpn</option>
                                    <option value="pppoe">pppoe</option>
                                    <option value="pptp">pptp</option>
                                    <option value="sstp">sstp</option>
                                </select>
                                <?php } elseif ($service == 'pppoe') { ?>
                                <select class="form-control" name="service" required="1">
                                    <option value="any">any</option>
                                    <option value="async">async</option>
                                    <option value="l2tp">l2tp</option>
                                    <option value="ovpn">ovpn</option>
                                    <option value="pppoe" selected>pppoe</option>
                                    <option value="pptp">pptp</option>
                                    <option value="sstp">sstp</option>
                                </select>
                                <?php } elseif ($service == 'pptp') { ?>
                                <select class="form-control" name="service" required="1">
                                    <option value="any">any</option>
                                    <option value="async">async</option>
                                    <option value="l2tp">l2tp</option>
                                    <option value="ovpn">ovpn</option>
                                    <option value="pppoe">pppoe</option>
                                    <option value="pptp" selected>pptp</option>
                                    <option value="sstp">sstp</option>
                                </select>
                                <?php } elseif ($service == 'sstp') { ?>
                                <select class="form-control" name="service" required="1">
                                    <option value="any">any</option>
                                    <option value="async">async</option>
                                    <option value="l2tp">l2tp</option>
                                    <option value="ovpn">ovpn</option>
                                    <option value="pppoe">pppoe</option>
                                    <option value="pptp">pptp</option>
                                    <option value="sstp" selected>sstp</option>
                                </select>
                                <?php } else { ?>
                                <select class="form-control" name="service" required="1">
                                    <option value="any">any</option>
                                    <option value="async">async</option>
                                    <option value="l2tp">l2tp</option>
                                    <option value="ovpn">ovpn</option>
                                    <option value="pppoe">pppoe</option>
                                    <option value="pptp">pptp</option>
                                    <option value="sstp">sstp</option>
                                </select>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="align-middle">Caller ID</td>
                            <td><input class="form-control" type="text" size="4" autocomplete="off" name="callerid"
                                    value="<?= $callerid; ?>"></td>
                        </tr>
                        <tr>
                            <td class="align-middle">Profile</td>
                            <td>
                                <select class="form-control" name="profile" required="1">
                                    <option value="<?php echo $profile; ?>"><?php echo $profile; ?></option>
                                    <?php $TotalReg = count($getprofile);
                                    for ($i = 0; $i < $TotalReg; $i++) {
                                        echo "<option value='" . $getprofile[$i]['name'] . "' >" . $getprofile[$i]['name'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <input type="hidden" name="schid" value="<?php echo $schid; ?>">
                            <td class="align-middle">Interval</td>
                            <td><input class="form-control" value="<?php echo $schinterval; ?>" type="text" size="4"
                                    autocomplete="off" name="interval"></td>
                        </tr>
                        <!-- Expiry Script Options -->
                        <tr>
                            <td class="align-middle">Gunakan Script Expiry</td>
                            <td>
                                <select class="form-control" id="useexpiryscript" name="useexpiryscript">
                                    <option value="no" <?php if ($useexpiryscript == "no") echo "selected"; ?>>Tidak</option>
                                    <option value="yes" <?php if ($useexpiryscript == "yes") echo "selected"; ?>>Ya</option>
                                </select>
                            </td>
                        </tr>
                        <tr id="expiryOptions" style="<?php if ($useexpiryscript == "no") echo "display: none;"; ?>">
                            <td class="align-middle">Profile Setelah Expiry</td>
                            <td>
                                <select class="form-control" name="expiryprofile">
                                    <option value="">== Pilih Profile ==</option>
                                    <?php 
                                    $getallprofiles = $API->comm("/ppp/profile/print");
                                    $TotalProfiles = count($getallprofiles);
                                    for ($i = 0; $i < $TotalProfiles; $i++) {
                                        $selected = ($getallprofiles[$i]['name'] == $expiryprofile) ? "selected" : "";
                                        echo "<option value='" . $getallprofiles[$i]['name'] . "' " . $selected . ">" . $getallprofiles[$i]['name'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr id="expiryInterval" style="<?php if ($useexpiryscript == "no") echo "display: none;"; ?>">
                            <td class="align-middle">Interval Expiry</td>
                            <td><input class="form-control" type="text" size="4" autocomplete="off" name="expiryinterval" value="<?= $expiryinterval; ?>" placeholder="Contoh: 30d (30 hari)"></td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
function remSpace() {
    var upName = document.getElementsByName("name")[0];
    var newUpName = upName.value.replace(/\s/g, "-");
    upName.value = newUpName;
    upName.focus();
}

// Show/hide expiry options based on selection
document.getElementById("useexpiryscript").addEventListener("change", function() {
    if (this.value === "yes") {
        document.getElementById("expiryOptions").style.display = "table-row";
        document.getElementById("expiryInterval").style.display = "table-row";
    } else {
        document.getElementById("expiryOptions").style.display = "none";
        document.getElementById("expiryInterval").style.display = "none";
    }
});
</script>