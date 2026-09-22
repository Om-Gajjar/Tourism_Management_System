<?php
/**
 * PHP Built-in Server Router
 * Serves files from public/ while blocking sensitive internal directories
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$docRoot = __DIR__ . '/public';

// Block direct access to sensitive directories
$blocked = ['src', 'database', 'docs', 'tests', 'logs', 'vendor', '.git', '.env'];
foreach ($blocked as $dir) {
    if (strpos($uri, "/$dir/") === 0 || $uri === "/$dir" || strpos($uri, "/$dir.") === 0) {
        http_response_code(403);
        echo "403 Forbidden";
        exit;
    }
}

// Target file path inside public/
$file = $docRoot . $uri;

// If URI points to a directory (e.g. "/" or "/admin/"), look for index.php inside it
if (is_dir($file)) {
    $file = rtrim($file, '/') . '/index.php';
}

if (is_file($file)) {
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    
    // Static file MIME types
    $contentTypes = [
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
        'ico'  => 'image/x-icon',
        'json' => 'application/json',
        'xml'  => 'application/xml',
        'txt'  => 'text/plain',
        'html' => 'text/html',
        'htm'  => 'text/html',
    ];

    if ($ext === 'php') {
        // Change working directory to the script's directory for proper relative includes
        chdir(dirname($file));
        $_SERVER['SCRIPT_FILENAME'] = $file;
        $_SERVER['SCRIPT_NAME'] = $uri;
        header("Content-Type: text/html; charset=utf-8");
        require $file;
    } else {
        $contentType = $contentTypes[$ext] ?? 'application/octet-stream';
        header("Content-Type: $contentType");
        readfile($file);
    }
} else {
    http_response_code(404);
    echo "404 Not Found";
}
