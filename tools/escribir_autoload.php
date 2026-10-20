<?php

declare(strict_types=1);

$directorio = dirname(__DIR__) . '/vendor';
$destino = $directorio . '/autoload.php';

if (!is_dir($directorio) && !mkdir($directorio, 0755, true) && !is_dir($directorio)) {
    fwrite(STDERR, "No se pudo crear la carpeta vendor.\n");
    exit(1);
}

$codigo = <<<'PHP'
<?php

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    $base = dirname(__DIR__) . '/app/';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $file = $base . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) {
        require $file;
    }
});
PHP;

if (file_put_contents($destino, $codigo . PHP_EOL) === false) {
    fwrite(STDERR, "No se pudo escribir vendor/autoload.php\n");
    exit(1);
}

echo "Autoload creado en vendor/autoload.php\n";
