<?php
// Integrated 404 handler with include fallbacks
http_response_code(404);

// Common header/footer candidate paths (checked in order)
$headerCandidates = [
    'header.php',
    'includes/header.php',
    'inc/header.php',
    'partials/header.php',
    'templates/header.php',
    'views/header.php'
];
$footerCandidates = [
    'footer.php',
    'includes/footer.php',
    'inc/footer.php',
    'partials/footer.php',
    'templates/footer.php',
    'views/footer.php'
];

$includedHeader = false;
foreach ($headerCandidates as $h) {
    if (file_exists($h)) {
        include_once $h;
        $includedHeader = true;
        break;
    }
}

// If no site header found, emit a minimal header so page looks OK
if (! $includedHeader) {
    ?><!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>404 - Page Not Found</title>
        <style>
            body{font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background:#f7fafc; color:#333;}
            .container{max-width:900px;margin:80px auto;padding:24px;background:#fff;border-radius:8px;box-shadow:0 8px 30px rgba(0,0,0,0.06)}
            .code{font-weight:700;color:#e53e3e;font-size:72px}
            a.button{display:inline-block;margin:8px 6px;padding:10px 18px;border-radius:6px;text-decoration:none}
            .btn-primary{background:#2b6cb0;color:#fff}
            .btn-secondary{background:#edf2f7;color:#2d3748}
        </style>
    </head>
    <body>
    <div class="container">
    <?php
}

// Main 404 content (customer-facing)
$requested = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
?>
    <div style="padding:18px; text-align:center;">
        <div class="code">404</div>
        <h1 style="margin:8px 0;">Page Not Found</h1>
        <p style="color:#555;">Sorry — we couldn't find the page you requested on <?php echo htmlspecialchars($host, ENT_QUOTES, 'UTF-8'); ?>.</p>
        <?php if ($requested): ?>
            <p style="color:#777;font-size:0.95rem;">Requested URL: <strong><?php echo htmlspecialchars($requested, ENT_QUOTES, 'UTF-8'); ?></strong></p>
        <?php endif; ?>
        <div style="margin-top:14px;">
            <a class="button btn-primary" href="/">Home</a>
            <a class="button btn-secondary" href="javascript:history.back()">Go Back</a>
        </div>
    </div>
<?php

// Try to log the 404 to logs/404.log if writable (best-effort)
$logDir = __DIR__ . DIRECTORY_SEPARATOR . 'logs';
$logFile = $logDir . DIRECTORY_SEPARATOR . '404.log';
$logEntry = sprintf("[%s] %s %s\n", date('Y-m-d H:i:s'), $_SERVER['REMOTE_ADDR'] ?? '-', $requested);

if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}
@file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

// Include footer if available
foreach ($footerCandidates as $f) {
    if (file_exists($f)) {
        include_once $f;
        $includedFooter = true;
        break;
    }
}

// If header/footer were not included, close the minimal document emitted earlier
if (! isset($includedHeader) || ! $includedHeader) {
    // close container and body/html
    echo "</div></body></html>";
}
