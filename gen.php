<?php
$sourceBaseDir = __DIR__ . '/core/public';
$distBaseDir   = __DIR__ . '/dist';
$originalCwd   = getcwd();

if (!is_dir($distBaseDir)) mkdir($distBaseDir, 0755, true);

$it = new RecursiveDirectoryIterator($sourceBaseDir, RecursiveDirectoryIterator::SKIP_DOTS);
$files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::LEAVES_ONLY);

foreach ($files as $file) {
    if ($file->getExtension() !== 'php') continue;

    $sourcePath = $file->getRealPath();
    $sourceDir  = dirname($sourcePath);
    
    $relativePath = str_replace($sourceBaseDir, '', $sourcePath);
    $targetHtmlPath = $distBaseDir . $relativePath . '.html';
    $targetPhpPath  = $distBaseDir . $relativePath;

    if (!is_dir(dirname($targetHtmlPath))) mkdir(dirname($targetHtmlPath), 0755, true);

    echo "Rendering: {$relativePath} ... ";

    chdir($sourceDir);

    ob_start();
    try {
        include basename($sourcePath); 
        $output = ob_get_contents();
    } catch (Throwable $e) {
        echo "Error! " . $e->getMessage() . "\n";
        ob_end_clean();
        chdir($originalCwd);
        continue;
    }
    ob_end_clean();

    chdir($originalCwd);

    file_put_contents($targetHtmlPath, $output);

    if (file_exists($targetPhpPath)) {
        unlink($targetPhpPath);
        echo "Saved & Deleted .php\n";
    } else {
        echo "Saved\n";
    }
}

echo "\nBuild Finished.\n";
