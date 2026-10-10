<?php

/**
 * Helpers compartidos por los endpoints de envío de mail del sitio MEDEX.
 */

/**
 * Corta la ejecución con un código HTTP y un mensaje de texto plano.
 * El JS del sitio (presupuestos.js / contacto.js) sólo mira response.ok,
 * así que el status code es lo que realmente importa acá.
 */
function medex_fail($status, $message)
{
    http_response_code($status);
    header('Content-Type: text/plain; charset=utf-8');
    echo $message;
    exit;
}

/**
 * Carga mail/config.php (no versionado). Si todavía no existe —por ejemplo,
 * recién clonado el repo—, corta con un 500 explicando qué falta.
 */
function medex_load_config()
{
    $path = __DIR__ . '/config.php';

    if (!is_file($path)) {
        medex_fail(500, 'Falta configurar mail/config.php (copiá config.example.php y completá los datos reales).');
    }

    $config = require $path;

    if (!is_array($config)) {
        medex_fail(500, 'mail/config.php no tiene el formato esperado.');
    }

    return $config;
}

/**
 * Sanitiza un valor de $_POST para insertarlo de forma segura en el cuerpo
 * HTML del mail (el sitio original no hacía esto).
 */
function medex_sanitize($value)
{
    return htmlspecialchars(trim((string) $value), ENT_QUOTES, 'UTF-8');
}

/**
 * Corta con 400 si falta alguno de los campos requeridos.
 * $fields: array crudo (ej. $_POST). $required: nombres de campos obligatorios.
 */
function medex_require_fields($fields, $required)
{
    foreach ($required as $key) {
        if (!isset($fields[$key]) || trim((string) $fields[$key]) === '') {
            medex_fail(400, 'Por favor, completá todos los campos obligatorios.');
        }
    }
}

/**
 * Valida el formato de un email recibido por POST. Corta con 400 si es inválido.
 */
function medex_require_valid_email($email)
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        medex_fail(400, 'El correo tiene un formato incorrecto.');
    }
}

/**
 * Verifica el token de Google reCAPTCHA v2 contra la API de Google.
 * Corta con 400 si falta el token o si Google lo rechaza.
 */
function medex_verify_recaptcha($secretKey, $token)
{
    if ($token === null || trim((string) $token) === '') {
        medex_fail(400, 'Por favor, validá el reCAPTCHA antes de enviar.');
    }

    $params = [
        'secret'   => $secretKey,
        'response' => $token,
        'remoteip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
    ];

    $result = null;

    if (function_exists('curl_init')) {
        $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = $response ? json_decode($response, true) : null;
    }

    if ($result === null && ini_get('allow_url_fopen')) {
        $url = 'https://www.google.com/recaptcha/api/siteverify?' . http_build_query($params);
        $response = @file_get_contents($url);
        $result = $response ? json_decode($response, true) : null;
    }

    if (!is_array($result) || empty($result['success'])) {
        medex_fail(400, 'No pudimos validar el reCAPTCHA. Por favor, intentá nuevamente.');
    }
}

/**
 * Arma una instancia de PHPMailer configurada con los datos SMTP del sitio.
 */
function medex_build_mailer($smtpConfig)
{
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

    $mail->CharSet = 'UTF-8';
    $mail->isHTML(true);
    $mail->isSMTP();
    $mail->Host = $smtpConfig['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $smtpConfig['username'];
    $mail->Password = $smtpConfig['password'];

    if (!empty($smtpConfig['port'])) {
        $mail->Port = (int) $smtpConfig['port'];
    }

    if (!empty($smtpConfig['secure'])) {
        $mail->SMTPSecure = $smtpConfig['secure'];
    }

    return $mail;
}
