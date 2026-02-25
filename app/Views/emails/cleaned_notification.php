<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Area Bersih! – <?= esc($appName) ?></title>
</head>
<body style="margin:0;padding:0;font-family:Arial,sans-serif;background:#f4f7fa;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f7fa;padding:40px 0;">
  <tr>
    <td align="center">
      <table width="560" cellpadding="0" cellspacing="0"
             style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08);">

        <!-- Header -->
        <tr>
          <td align="center"
              style="background:linear-gradient(135deg,#198754,#0d6efd);padding:32px 40px;">
            <div style="font-size:48px;margin-bottom:8px;">🧹✅</div>
            <span style="color:#ffffff;font-size:24px;font-weight:700;letter-spacing:1px;">
              <?= esc($appName) ?>
            </span>
          </td>
        </tr>

        <!-- Body -->
        <tr>
          <td style="padding:40px 40px 24px;">
            <h2 style="color:#198754;margin:0 0 16px;">Laporan Anda Sudah Ditangani! 🎊</h2>
            <p style="color:#4a5568;line-height:1.7;margin:0 0 20px;">
              Halo <strong><?= esc($name) ?></strong>,<br>
              Kabar baik! Laporan sampah yang Anda kirimkan dengan kode
              <code style="background:#e8f5e9;color:#1b5e20;padding:2px 8px;border-radius:4px;">
                #<?= esc($reportId) ?></code>
              telah <strong>selesai dibersihkan</strong> oleh tim Dinas kebersihan.
            </p>

            <!-- Status Timeline (email-safe) -->
            <table width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0;">
              <tr>
                <td width="32" valign="top" style="padding-top:2px;">
                  <div style="width:28px;height:28px;border-radius:50%;background:#198754;
                              color:#fff;text-align:center;line-height:28px;font-size:14px;">✓</div>
                </td>
                <td style="padding-left:12px;padding-bottom:16px;">
                  <strong style="color:#1a2e40;">Laporan Diterima</strong><br>
                  <span style="color:#718096;font-size:13px;">Tim kami menerima laporan Anda.</span>
                </td>
              </tr>
              <tr>
                <td width="32" valign="top" style="padding-top:2px;">
                  <div style="width:28px;height:28px;border-radius:50%;background:#198754;
                              color:#fff;text-align:center;line-height:28px;font-size:14px;">✓</div>
                </td>
                <td style="padding-left:12px;padding-bottom:16px;">
                  <strong style="color:#1a2e40;">Petugas Dikirim</strong><br>
                  <span style="color:#718096;font-size:13px;">Petugas kebersihan dikirim ke lokasi.</span>
                </td>
              </tr>
              <tr>
                <td width="32" valign="top" style="padding-top:2px;">
                  <div style="width:28px;height:28px;border-radius:50%;background:#198754;
                              color:#fff;text-align:center;line-height:28px;font-size:14px;">🧹</div>
                </td>
                <td style="padding-left:12px;">
                  <strong style="color:#198754;font-size:15px;">Area Berhasil Dibersihkan</strong><br>
                  <span style="color:#718096;font-size:13px;">Sampah telah diangkut. Terima kasih atas laporan Anda!</span>
                </td>
              </tr>
            </table>

            <p style="color:#4a5568;line-height:1.7;margin:0 0 24px;">
              Partisipasi Anda sangat berarti bagi kebersihan kota. Terus laporkan jika menemukan
              sampah di sekitar Anda!
            </p>

            <div style="text-align:center;margin:8px 0 24px;">
              <a href="<?= esc($link) ?>"
                 style="display:inline-block;background:#198754;color:#ffffff;text-decoration:none;
                        padding:14px 40px;border-radius:8px;font-size:15px;font-weight:600;">
                👁 Lihat Detail Laporan
              </a>
            </div>
          </td>
        </tr>

        <!-- Appreciation banner -->
        <tr>
          <td style="background:#e8f5e9;padding:20px 40px;text-align:center;border-top:1px solid #c8e6c9;">
            <p style="color:#1b5e20;font-size:14px;margin:0;">
              💚 Terima kasih sudah menjadi Pahlawan Lingkungan!
            </p>
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style="background:#f4f7fa;padding:20px 40px;text-align:center;">
            <p style="color:#a0aec0;font-size:12px;margin:0;">
              © <?= date('Y') ?> <?= esc($appName) ?>. Semua hak dilindungi.<br>
              Email ini dikirim secara otomatis, mohon tidak membalas.
            </p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>
</body>
</html>
