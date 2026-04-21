<?php
$sourceBaseDir = __DIR__ . '/core/public';
$distBaseDir   = __DIR__ . '/dist';
$originalCwd   = getcwd();

$originalIncludePath = get_include_path();

if (!is_dir($distBaseDir)) mkdir($distBaseDir, 0755, true);

$it = new RecursiveDirectoryIterator($sourceBaseDir, RecursiveDirectoryIterator::SKIP_DOTS);
$files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::LEAVES_ONLY);

foreach ($files as $file) {
    if ($file->getExtension() !== 'php') continue;

    $sourcePath = $file->getRealPath();
    $sourceDir  = dirname($sourcePath);
    
    $relativePath = str_replace($sourceBaseDir, '', $sourcePath);
    $targetHtmlPath = $distBaseDir . $relativePath . '.html';

    if (!is_dir(dirname($targetHtmlPath))) mkdir(dirname($targetHtmlPath), 0755, true);

    echo "Rendering: {$relativePath} ... ";

    set_include_path($sourceDir . PATH_SEPARATOR . $originalIncludePath);
    
    chdir($sourceDir);

    ob_start();
    try {
        include basename($sourcePath); 
        $output = ob_get_clean();
        
        file_put_contents($targetHtmlPath, $output);
        echo "Saved\n";
    } catch (Throwable $e) {
        $output = ob_get_clean();
        echo "Error! " . $e->getMessage() . "\n";
    }

    chdir($originalCwd);
    set_include_path($originalIncludePath);
}

echo "\nBuild Finished.\n";
