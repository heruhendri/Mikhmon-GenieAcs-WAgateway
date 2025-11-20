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

  $getbridge = $API->comm("/interface/bridge/print");
  $getremoteaddress = $API->comm("/ip/pool/print");

  if (isset($_POST['name'])) {
    $name = (preg_replace('/\s+/', '-', $_POST['name']));
    $localaddress = ($_POST['localaddress']);
    $remoteaddress = ($_POST['remoteaddress']);
    $bridge = ($_POST['bridge']);
    $ratelimit = ($_POST['retelimit']);
    $onlyone = ($_POST['onlyone']);
    $bridgeportpriority = ($_POST['bridgeportpriority']);
    $bridgepathcost = ($_POST['bridgepathcost']);
    $bridgehorizon = ($_POST['bridgehorizon']);
    $incomingfilter = ($_POST['incomingfilter']);
    $outgoingfilter = ($_POST['outgoingfilter']);
    $addresslist = ($_POST['addresslist']);
    $interfacelist = ($_POST['interfacelist']);
    $dnsserver = ($_POST['dnsserver']);
    $winsserver = ($_POST['winsserver']);
    $changetcp = ($_POST['changetcp']);
    $useupnp = ($_POST['useupnp']);
    // Script expiry variables
    $useexpiryscript = ($_POST['useexpiryscript']);
    $expiryprofile = ($_POST['expiryprofile']);
    $expiryinterval = ($_POST['expiryinterval']);

    if ($bridge != '' || $bridge != NULL) {
      $API->comm("/ppp/profile/add", array(
        /*"add-mac-cookie" => "yes",*/
        "name" => "$name",
        "local-address" => "$localaddress",
        "remote-address" => "$remoteaddress",
        "bridge" => "$bridge",
        "rate-limit" => "$ratelimit",
        "only-one" => "$onlyone",
        "incoming-filter" => "$incomingfilter",
        "outgoing-filter" => "$outgoingfilter",
        "address-list" => "$addresslist",
        "dns-server" => "$dnsserver",
        "wins-server" => "$winsserver",
        "change-tcp-mss" => "$changetcp",
        "use-upnp" => "$useupnp",
      ));
    } else {
      $API->comm("/ppp/profile/add", array(
        /*"add-mac-cookie" => "yes",*/
        "name" => "$name",
        "local-address" => "$localaddress",
        "remote-address" => "$remoteaddress",
        // "bridge" => "$bridge",
        "rate-limit" => "$ratelimit",
        "only-one" => "$onlyone",
        "incoming-filter" => "$incomingfilter",
        "outgoing-filter" => "$outgoingfilter",
        "address-list" => "$addresslist",
        "dns-server" => "$dnsserver",
        "wins-server" => "$winsserver",
        "change-tcp-mss" => "$changetcp",
        "use-upnp" => "$useupnp",
      ));
    }

    // Add expiry script scheduler if enabled
    if ($useexpiryscript == "yes" && $expiryprofile != "" && $expiryinterval != "") {
      // Create the expiry script
      $expiryscript = ':local pengguna $"user"; :local date [/system clock get date]; :local time [/system clock get time]; :log info "User PPPoE $pengguna login pada $time tanggal $date"; { :if ([/system scheduler find name="exp-$pengguna"]="") do={ /system scheduler add name="exp-$pengguna" interval='.$expiryinterval.' on-event="/ppp secret set profile='.$expiryprofile.' [find name=\\\$pengguna]; /ppp active remove [find name=\\\$pengguna]; :log warning \\"User \\\$pengguna expired dan dipindah ke profile '.$expiryprofile.'\\"; /system scheduler remove [find name=\\"exp-\\\$pengguna\\"]"; :log info "Scheduler auto expiry dibuat untuk user $pengguna ('.$expiryinterval.')"; } }';
      
      // Add the script to the profile as a login script
      $API->comm("/ppp/profile/set", array(
        ".id" => "$name",
        "on-up" => "$expiryscript",
      ));
    }

    echo "<script>window.location='./?ppp=profiles&session=" . $session . "'</script>";
  }
}
?>
<div class="row">
  <div class="col-12">
    <div class="card box-bordered">
      <div class="card-header">
        <h3><i class="fa fa-plus"></i>Add PPP Profiles <small id="loader" style="display: none;"><i><i class='fa fa-circle-o-notch fa-spin'></i> Processing... </i></small></h3>
      </div>
      <div class="card-body">
        <form autocomplete="off" method="post" action="">
          <div>
            <a class="btn bg-warning" href="./?ppp=profiles&session=<?= $session; ?>"> <i class="fa fa-close btn-mrg"></i> <?= $_close ?></a>
            <button type="submit" name="save" class="btn bg-primary btn-mrg"><i class="fa fa-save btn-mrg"></i> <?= $_save ?></button>
          </div>
          <table class="table">
            <tr>
              <td class="align-middle"><?= $_name ?></td>
              <td><input class="form-control" type="text" onchange="remSpace();" autocomplete="off" name="name" value="" required="1" autofocus></td>
            </tr>
            <tr>
              <td class="align-middle">Local Address</td>
              <td><input class="form-control" type="text" size="4" required="1" autocomplete="off" name="localaddress"></td>
            </tr>
            <tr>
              <td class="align-middle">Remote Address</td>
               <td>
                  <select class="form-control " name="remoteaddress" required="1">
                    <option value="">==Pilih==</option>
                    <?php $TotalRemote = count($getremoteaddress);
                    for ($i = 0; $i < $TotalRemote; $i++) {
                      echo "<option value='" . $getremoteaddress[$i]['name'] . "'>" . $getremoteaddress[$i]['name'] . "</option>";
                    }
                    ?>
                  </select>
                </td>
            </tr>
            <?php if (count($getbridge) != 0) { ?>
              <tr>
                <td class="align-middle">Bridge</td>
                <td>
                  <select class="form-control " name="bridge">
                    <option value="">==Pilih==</option>
                    <?php $Totalbridge = count($getbridge);
                    for ($i = 0; $i < $Totalbridge; $i++) {
                      echo "<option value='" . $getbridge[$i]['name'] . "'>" . $getbridge[$i]['name'] . "</option>";
                    }
                    ?>
                  </select>
                </td>
              </tr>
            <?php } ?>
            <tr>
              <td class="align-middle">Incoming Filter</td>
              <td>
                <select class="form-control" id="incomingfilter" name="incomingfilter">
                  <option value="">== Pilih ==</option>
                  <option value="input">input</option>
                  <option value="forward">forward</option>
                  <option value="output">output</option>
                </select>
              </td>
            </tr>
            <tr>
              <td class="align-middle">Outgoing Filter</td>
              <td>
                <select class="form-control" id="outgoingfilter" name="outgoingfilter">
                  <option value="">== Pilih ==</option>
                  <option value="input">input</option>
                  <option value="forward">forward</option>
                  <option value="output">output</option>
                </select>
              </td>
            </tr>
            <tr>
              <td class="align-middle">Address List</td>
              <td><input class="form-control" type="text" size="4" autocomplete="off" name="addresslist"></td>
            </tr>
            <tr>
              <td class="align-middle">DNS Server</td>
              <td><input class="form-control" type="text" size="4" autocomplete="off" name="dnsserver"></td>
            </tr>
            <tr>
              <td class="align-middle">WINS Server</td>
              <td><input class="form-control" type="text" size="4" autocomplete="off" name="winsserver"></td>
            </tr>
            <tr>
              <td class="align-middle">Change TCP MSS</td>
              <td>
                <select class="form-control" id="changetcp" required="1" name="changetcp">
                  <option value="default">default</option>
                  <option value="no">no</option>
                  <option value="yes">yes</option>
                </select>
              </td>
            </tr>
            <tr>
              <td class="align-middle">Use UPnP</td>
              <td>
                <select class="form-control" id="useupnp" required="1" name="useupnp">
                  <option value="default">default</option>
                  <option value="no">no</option>
                  <option value="yes">yes</option>
                </select>
              </td>
            </tr>
            <tr>
              <td class="align-middle">Rate Limit</td>
              <td><input class="form-control" type="text" size="4" autocomplete="off" required="1" name="retelimit" placeholder="example: rx/tx"></td>
            </tr>
            <tr>
              <td class="align-middle">Only One</td>
              <td>
                <select class="form-control" id="onlyone" required="1" name="onlyone">
                  <option value="default">default</option>
                  <option value="no">no</option>
                  <option value="yes">yes</option>
                </select>
              </td>
            </tr>
            <!-- Expiry Script Options -->
            <tr>
              <td class="align-middle">Gunakan Script Expiry</td>
              <td>
                <select class="form-control" id="useexpiryscript" name="useexpiryscript">
                  <option value="no">Tidak</option>
                  <option value="yes">Ya</option>
                </select>
              </td>
            </tr>
            <tr id="expiryOptions" style="display: none;">
              <td class="align-middle">Profile Setelah Expiry</td>
              <td>
                <select class="form-control" name="expiryprofile">
                  <option value="">== Pilih Profile ==</option>
                  <?php 
                  $getallprofiles = $API->comm("/ppp/profile/print");
                  $TotalProfiles = count($getallprofiles);
                  for ($i = 0; $i < $TotalProfiles; $i++) {
                    echo "<option value='" . $getallprofiles[$i]['name'] . "'>" . $getallprofiles[$i]['name'] . "</option>";
                  }
                  ?>
                </select>
              </td>
            </tr>
            <tr id="expiryInterval" style="display: none;">
              <td class="align-middle">Interval Expiry</td>
              <td><input class="form-control" type="text" size="4" autocomplete="off" name="expiryinterval" placeholder="Contoh: 30d (30 hari)"></td>
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
    //alert("<?php if ($currency == in_array($currency, $cekindo['indo'])) {
                echo "Nama Profile tidak boleh berisi spasi";
              } else {
                echo "Profile name can't containing white space!";
              } ?>");
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