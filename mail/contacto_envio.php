<?php

/**
 * Recibe el formulario de contacto.html y lo envía por mail.
 * Reemplaza a ftp-original/contacto_envio.php: mismos campos, mismo
 * destinatario y mismo servidor SMTP, pero con PHPMailer moderno,
 * sin copia oculta (BCC) a terceros, y con sanitización + reCAPTCHA.
 */

declare(strict_types=1);

require __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    medex_fail(405, 'Método no permitido.');
}

$config = medex_load_config();

medex_verify_recaptcha($config['recaptcha']['secret_key'], $_POST['g-recaptcha-response'] ?? null);

medex_require_fields($_POST, ['nombre', 'telefono', 'mail', 'mensaje']);
medex_require_valid_email($_POST['mail']);

$nombre       = medex_sanitize($_POST['nombre']);
$telefono     = medex_sanitize($_POST['telefono']);
$mailRecibido = medex_sanitize($_POST['mail']);
$mensaje      = medex_sanitize($_POST['mensaje']);

require __DIR__ . '/bootstrap.php';

$cuerpo = '<table align="center" style="border-color: #666;" cellpadding="10">
<tbody>
<tr>
<td colspan="2" style="text-align: center">
<img src="https://medicallexpress.com.ar/img/logo-medex.png" style="width: 100px;" alt="Medicall Express">
</td>
</tr>
<tr style="background: #eee">
<td><strong>Nombre:</strong></td>
<td>' . $nombre . '</td>
</tr>
<tr>
<td><strong>Teléfono:</strong></td>
<td>' . $telefono . '</td>
</tr>
<tr>
<td><strong>Correo:</strong></td>
<td>' . $mailRecibido . '</td>
</tr>
<tr>
<td><strong>Mensaje:</strong></td>
<td>' . $mensaje . '</td>
</tr>
</tbody>
</table>';

try {
    $mail = medex_build_mailer($config['smtp']);

    // From = la cuenta SMTP autenticada (necesario para pasar SPF/DMARC:
    // medicallexpress.com.ar no puede enviar "en nombre de" gmail.com, etc.).
    // ReplyTo = el email que completó el usuario, para que se pueda responder directo.
    $mail->setFrom($config['smtp']['username'], $nombre);
    $mail->addAddress($config['destinatario']['email'], $config['destinatario']['nombre']);
    $mail->addReplyTo($mailRecibido, $nombre);

    $mail->Subject = 'Formulario de contacto';
    $mail->Body = $cuerpo;
    $mail->AltBody = '';

    $mail->send();
} catch (\PHPMailer\PHPMailer\Exception $e) {
    medex_fail(500, 'No pudimos enviar tu mensaje. Por favor, intentá nuevamente más tarde.');
}

echo '¡Gracias por comunicarte con nosotros! Te responderemos a la brevedad.';
