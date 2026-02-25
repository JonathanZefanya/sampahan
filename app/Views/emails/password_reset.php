<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Reset Password – <?= esc($appName) ?></title>
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
              style="background:linear-gradient(135deg,#1a2e40,#d97706);padding:32px 40px;">
            <span style="color:#ffffff;font-size:24px;font-weight:700;letter-spacing:1px;">
              <?= esc($appName) ?>
            </span>
          </td>
        </tr>

        <!-- Body -->
        <tr>
          <td style="padding:40px 40px 24px;">
            <h2 style="color:#1a2e40;margin:0 0 20px;">Reset Password, <?= esc($name) ?></h2>
            <p style="color:#4a5568;line-height:1.7;margin:0 0 20px;">
              Kami menerima permintaan untuk mereset password akun Anda di <strong><?= esc($appName) ?></strong>.
              Klik tombol di bawah untuk membuat password baru.
            </p>

            <div style="text-align:center;margin:32px 0;">
              <a href="<?= esc($link) ?>"
                 style="display:inline-block;background:#d97706;color:#ffffff;text-decoration:none;
                        padding:14px 40px;border-radius:8px;font-size:16px;font-weight:600;">
                🔐 Reset Password Saya
              </a>
            </div>

            <p style="color:#718096;font-size:13px;line-height:1.6;margin:0 0 12px;">
              Jika tombol di atas tidak berfungsi, salin dan tempel tautan berikut ke browser Anda:
            </p>
            <p style="word-break:break-all;font-size:12px;color:#a0aec0;margin:0 0 20px;">
              <?= esc($link) ?>
            </p>

            <div style="background:#fff3cd;border-left:4px solid #d97706;border-radius:4px;padding:12px 16px;margin:8px 0 20px;">
              <p style="color:#7c4a00;font-size:13px;margin:0;">
                ⚠ Tautan ini berlaku selama <strong>1 jam</strong>.
                Jika Anda tidak meminta reset password, abaikan email ini — akun Anda tetap aman.
              </p>
            </div>
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
