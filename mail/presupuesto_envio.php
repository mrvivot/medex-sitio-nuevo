<?php

/**
 * Recibe el formulario de presupuestos.html y lo envía por mail.
 * Reemplaza a ftp-original/presupuesto_envio.php: mismos campos, mismo
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

medex_require_fields($_POST, ['nombre', 'nombre_empresa', 'mail', 'telefono', 'rubro']);
medex_require_valid_email($_POST['mail']);

$nombre         = medex_sanitize($_POST['nombre']);
$nombreEmpresa  = medex_sanitize($_POST['nombre_empresa']);
$mailRecibido   = medex_sanitize($_POST['mail']);
$telefono       = medex_sanitize($_POST['telefono']);
$rubro          = medex_sanitize($_POST['rubro']);
$direccion      = medex_sanitize($_POST['direccion'] ?? '');
$horario        = medex_sanitize($_POST['horario'] ?? '');
$empleados      = medex_sanitize($_POST['empleados'] ?? '');
$comentarios    = medex_sanitize($_POST['comentarios'] ?? '');

require __DIR__ . '/bootstrap.php';

$cuerpo = '<table align="center" style="border-color: #666;" cellpadding="10">
<tbody>
<tr>
<td colspan="2" style="text-align: center">
<img src="https://medicallexpress.com.ar/img/logo-medex.png" style="width: 100px;" alt="Medicall Express">
</td>
</tr>
<tr>
<td><strong>Nombre:</strong></td>
<td>' . $nombre . '</td>
</tr>
<tr>
<td><strong>Nombre de la institución:</strong></td>
<td>' . $nombreEmpresa . '</td>
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
<td><strong>Rubro:</strong></td>
<td>' . $rubro . '</td>
</tr>
<tr>
<td><strong>Dirección:</strong></td>
<td>' . $direccion . '</td>
</tr>
<tr>
<td><strong>Cantidad de personas a cubrir:</strong></td>
<td>' . $empleados . '</td>
</tr>
<tr>
<td><strong>Horario de atención:</strong></td>
<td>' . $horario . '</td>
</tr>
<tr>
<td><strong>Comentarios:</strong></td>
<td>' . $comentarios . '</td>
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

    $mail->Subject = 'Formulario de PRESUPUESTO';
    $mail->Body = $cuerpo;
    $mail->AltBody = '';

    $mail->send();
} catch (\PHPMailer\PHPMailer\Exception $e) {
    medex_fail(500, 'No pudimos enviar tu mensaje. Por favor, intentá nuevamente más tarde.');
}

echo '¡Gracias por comunicarte con nosotros! Te responderemos a la brevedad.';
