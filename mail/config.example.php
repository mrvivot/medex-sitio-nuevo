<?php

/**
 * Plantilla de configuración de mail/reCAPTCHA para el sitio MEDEX.
 *
 * Copiá este archivo a "config.php" (mismo directorio) y completá los
 * valores reales. mail/config.php está listado en .gitignore y NUNCA debe
 * subirse al repositorio ni compartirse fuera del equipo.
 */

return [

    // Mismo servidor/cuenta SMTP que usaba el sitio anterior.
    'smtp' => [
        'host'     => 'mail.medicallexpress.com.ar',
        'username' => 'web@medicallexpress.com.ar',
        'password' => 'REEMPLAZAR_CON_LA_CONTRASEÑA_REAL',

        // El sitio anterior no fijaba puerto ni cifrado (dejaba los valores
        // por default de PHPMailer). Si el hosting nuevo requiere TLS/SSL
        // explícito, descomentá y completá estas dos líneas; si no, dejalas
        // en null para mantener el mismo comportamiento que el sitio actual.
        'port'     => null, // ej: 587 (TLS) o 465 (SSL)
        'secure'   => null, // ej: 'tls' o 'ssl'
    ],

    // Casilla que recibe todos los formularios (sin copia oculta a terceros).
    'destinatario' => [
        'email' => 'info@medicallexpress.com.ar',
        'nombre' => 'MEDEX',
    ],

    // Google reCAPTCHA v2 ("No soy un robot"). Generar ambas claves en
    // https://www.google.com/recaptcha/admin para el dominio real antes de
    // salir a producción — las de abajo son las claves de PRUEBA públicas de
    // Google (siempre validan OK, no ofrecen protección real).
    'recaptcha' => [
        'site_key'   => '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI',
        'secret_key' => '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe',
    ],

];
