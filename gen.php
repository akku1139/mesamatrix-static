<?php
$sourceBaseDir = __DIR__ . '/core/public';
$distBaseDir   = __DIR__ . '/dist';

$it = new RecursiveDirectoryIterator($sourceBaseDir, RecursiveDirectoryIterator::SKIP_DOTS);
$files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::LEAF_ONLY);

foreach ($files as $file) {
    if ($file->getExtension() !== 'php') continue;

    $sourcePath = $file->getRealPath();

    $relativePath = str_replace($sourceBaseDir, '', $sourcePath);

    $targetHtmlPath = $distBaseDir . $relativePath . '.html';	// dist/.../file.php.html
    $targetPhpPath  = $distBaseDir . $relativePath;		// dist/.../file.php

    $targetDir = dirname($targetHtmlPath);
    if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

    echo "Rendering: {$relativePath} ... ";

    ob_start();
    try {
        include $sourcePath;
        $output = ob_get_contents();
    } catch (Throwable $e) {
        echo "Error! " . $e->getMessage() . "\n";
        ob_end_clean();
        continue;
    }
    ob_end_clean();

    file_put_contents($targetHtmlPath, $output);

    if (file_exists($targetPhpPath)) {
        unlink($targetPhpPath);
        echo "Saved & Deleted .php\n";
    } else {
        echo "Saved\n";
    }
}

echo "\nBuild Finished.\n";
