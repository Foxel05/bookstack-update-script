<?php
// ============================================================
//  BookStack Update Script
//  For shared hosting without SSH (e.g. all-inkl.com)
// ============================================================
//  ⚠  SECURITY: Delete this file immediately after use!
// ============================================================

$projectPath = "/www/htdocs/youruser/yourdomain.de"; // Absolute path to your BookStack installation
$phpBin      = "/usr/bin/php83";                     // Path to your PHP binary  →  <?php echo PHP_BINARY; ?>
$composerBin = "$phpBin $projectPath/composer.phar"; // composer.phar must be in the BookStack root

// ============================================================
//  Steps — critical ones will stop the script on failure
// ============================================================

$commands = [
    "git reset"        => ["cd $projectPath && git reset --hard HEAD 2>&1",              true],
    "git pull"         => ["cd $projectPath && git pull origin release 2>&1",            true],
    "composer install" => ["cd $projectPath && $composerBin install --no-dev 2>&1",      true],
    "migrate"          => ["cd $projectPath && $phpBin artisan migrate --force 2>&1",    true],
    "cache:clear"      => ["cd $projectPath && $phpBin artisan cache:clear 2>&1",        false],
    "config:clear"     => ["cd $projectPath && $phpBin artisan config:clear 2>&1",       false],
    "view:clear"       => ["cd $projectPath && $phpBin artisan view:clear 2>&1",         false],
];

// ============================================================
//  Output
// ============================================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BookStack Update</title>
    <style>
        body { font-family: monospace; background: #1e1e1e; color: #d4d4d4; padding: 2rem; }
        h1   { color: #569cd6; }
        h3   { color: #9cdcfe; margin-bottom: 0.2rem; }
        pre  { background: #252526; border-left: 3px solid #444; padding: 0.8rem 1rem; margin-top: 0.2rem; white-space: pre-wrap; word-break: break-word; }
        .ok      { color: #4ec9b0; }
        .fail    { color: #f44747; }
        .warning { color: #ce9178; font-weight: bold; }
        .done    { color: #4ec9b0; font-size: 1.2rem; font-weight: bold; margin-top: 1rem; }
    </style>
</head>
<body>
<h1>📦 BookStack Update</h1>
<p class="warning">⚠ Remember to delete this file after the update is complete!</p>

<?php

$failed = false;

foreach ($commands as $label => [$cmd, $critical]) {
    echo "<h3>▶ $label</h3><pre>";
    exec($cmd, $output, $returnCode);
    $outputText = htmlspecialchars(implode("\n", $output));
    echo $outputText ?: "(no output)";
    $statusClass = $returnCode === 0 ? "ok" : "fail";
    echo "\n\n<span class=\"$statusClass\">Return code: $returnCode</span>";
    echo "</pre>";
    $output = [];

    if ($returnCode !== 0 && $critical) {
        echo "<p class='warning'>⚠ Critical step \"$label\" failed — stopping here. Check the output above.</p>";
        $failed = true;
        break;
    }
}

if (!$failed) {
    echo "<p class='done'>✓ Update complete.</p>";
}

?>
</body>
</html>
