<?php
/*
 * WhatsApp Gateway API Examples
 */

session_start();
// hide all error
error_reporting(0);

if (!isset($_SESSION["mikhmon"])) {
    header("Location:../admin.php?id=login");
} else {
    include('../include/config.php');
    include('../include/readcfg.php');
    include('../include/headhtml.php');
    include('../include/menu.php');
}
?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3><i class="fa fa-code"></i> WhatsApp Gateway API Examples</h3>
            </div>
            <div class="card-body">
                <p>Configure your WhatsApp gateway with the following webhook URL:</p>
                <div class="form-group">
                    <input type="text" class="form-control" readonly value="<?= htmlspecialchars($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/webhook.php'); ?>">
                </div>
                
                <h4>FONNTE</h4>
                <p>Set your webhook URL in the FONNTE dashboard to receive messages.</p>
                <pre>POST https://api.fonnte.com/send-message
Authorization: TOKEN
Content-Type: application/x-www-form-urlencoded

target=6281234567890&message=Hello</pre>
                
                <h4>MPWA</h4>
                <p>Set your webhook URL in the MPWA dashboard to receive messages.</p>
                <pre>POST https://mpwa.id/api/send-message
Authorization: Bearer TOKEN
Content-Type: application/json

{
  "number": "6281234567890",
  "message": "Hello"
}</pre>
                
                <h4>WABLAS</h4>
                <p>Set your webhook URL in the WABLAS dashboard to receive messages.</p>
                <pre>POST https://wablas.com/api/send-message
Authorization: TOKEN
Content-Type: application/json

{
  "phone": "6281234567890",
  "message": "Hello"
}</pre>
                
                <h4>Perintah yang Tersedia</h4>
                <ul>
                    <li><code>help</code> - Tampilkan pesan bantuan</li>
                    <li><code>ping</code> - Uji gateway WhatsApp</li>
                    <li><code>voucher &lt;profile&gt;</code> - Buat voucher dengan profil tertentu</li>
                    <li><code>member &lt;username&gt; &lt;password&gt; &lt;profile&gt;</code> - Buat member dengan username/password</li>
                    <li><code>generate &lt;jumlah&gt; &lt;profile&gt;</code> - Generate otomatis voucher</li>
                    <li><code>hotspot list</code> - Daftar pengguna hotspot</li>
                    <li><code>hotspot remove &lt;username&gt;</code> - Hapus pengguna hotspot</li>
                    <li><code>pppoe list</code> - Daftar pengguna PPPoE</li>
                    <li><code>pppoe add &lt;username&gt; &lt;password&gt; &lt;profile&gt;</code> - Tambah pengguna PPPoE</li>
                    <li><code>pppoe remove &lt;username&gt;</code> - Hapus pengguna PPPoE</li>
                </ul>
                
                <h4>Contoh Penggunaan</h4>
                <p><strong>Membuat voucher dengan profil "1day":</strong></p>
                <pre>voucher 1day</pre>
                
                <p><strong>Membuat member dengan username/password:</strong></p>
                <pre>member john doe123 1day</pre>
                
                <p><strong>Generate 5 voucher dengan profil "1day":</strong></p>
                <pre>generate 5 1day</pre>
                
                <h4>Konfigurasi</h4>
                <p>Anda dapat mengkonfigurasi pengaturan default untuk pembuatan pengguna hotspot dan PPPoE di halaman <a href="./?whatsapp=settings&session=<?= $session; ?>">Pengaturan WhatsApp</a>:</p>
                <ul>
                    <li>Profil default untuk pengguna hotspot dan PPPoE</li>
                    <li>Pengaturan pembuatan pengguna (tipe karakter, panjang, prefix)</li>
                    <li>Batas waktu dan data default</li>
                    <li>Mode pengguna (voucher atau username/password)</li>
                    <li>Tipe layanan PPPoE dan interval kedaluwarsa</li>
                </ul>
            </div>
        </div>
    </div>
</div>