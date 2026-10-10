<?php

/**
 * Carga PHPMailer (versión moderna, namespaced PHPMailer\PHPMailer\PHPMailer).
 *
 * Si el hosting soporta Composer y se corrió `composer install` en la raíz
 * del sitio, se usa esa copia (vendor/autoload.php). Si no existe —hosting
 * sin acceso a shell/Composer—, se cae a la copia vendorizada a mano en
 * mail/PHPMailer/, que trae exactamente los mismos 3 archivos fuente.
 */

$composerAutoload = __DIR__ . '/../vendor/autoload.php';

if (is_file($composerAutoload)) {
    require_once $composerAutoload;
} else {
    require_once __DIR__ . '/PHPMailer/src/Exception.php';
    require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
    require_once __DIR__ . '/PHPMailer/src/SMTP.php';
}
