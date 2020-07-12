<?php

filter_var($_GET['plugin'], FILTER_SANITIZE_STRING);
filter_var($_GET['page'], FILTER_SANITIZE_STRING);
filter_var($_GET['file'], FILTER_SANITIZE_STRING);
filter_var($_GET['nopage'], FILTER_SANITIZE_NUMBER_INT);

define('PAGE', filter_var($_GET['page'], FILTER_SANITIZE_STRING));

if (!empty($_GET['plugin']) && isset($_GET['plugin'])) {
    define('PLUGIN', $_GET['plugin']);
    $pluginConfigFile = $settings['configDirectory'] . "/plugin." . PLUGIN;
    if (file_exists($pluginConfigFile)) {
        $pluginSettings = parse_ini_file($pluginConfigFile);
    }
}


$isPage = $_GET['nopage'] !== 1;

if (!$isPage) {
    $skipJSsettings = 1;
} else {
    // isPAge.. check for all vars and show errors
    define('PAGE', $_GET['page']);
    if (!empty($_GET['file']) && isset($_GET['file'])) {
        define('FILE', $_GET['file']);
    }
}

require_once("config.php");

// user requesting file
if (!$isPage && defined('FILE')) {
    $file = $pluginDirectory . "/" . PLUGIN . "/" . FILE;

    if (file_exists($file)) {
        $path_parts = pathinfo($file);
        $file_extension = $path_parts['extension'];

        switch ($file_extension) {
            case "gif":
                $ctype = "image/gif;";
                break;
            case "png":
                $ctype = "image/png;";
                break;
            case "jpeg":
            case "jpg":
                $ctype = "image/jpg;";
                break;
            case "js":
                $ctype = "text/javascript;";
                break;
            case "jsonp":
                $ctype = "application/jsonp;";
                break;
            case "json":
                $ctype = "application/json;";
                break;
            case "svg":
                $ctype = "image/svg+xml;";
                break;
            case "css":
                $ctype = "text/css;";
                break;
            default:
                $ctype = "text/plain;";
                break;
        }

        header('Content-type: ' . $ctype);
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }
    header("HTTP/1.0 404 Not Found");
    exit;
}

require_once("common.php");

$pluginSettings = [];
?>
    <!DOCTYPE html>
    <html>
    <head>
        <?php include 'common/menuHead.inc'; ?>
        <title><?= $pageTitle ?></title>
        <script type="text/javascript">
            var pluginSettings = new Array();
            <?php
            foreach ($pluginSettings as $key => $value) {
                echo "pluginSettings[{$key}] = \"{$value}\";" . PHP_EOL;
            }
            ?>
        </script>
        <?= buildCss($pluginDirectory) ?>
    </head>
    <body>
    <div id="bodyWrapper">
        <?php include_once 'menu.inc'; ?>
        <br/>
        <?php
        include_once($pluginDirectory . "/" . PLUGIN . "/" . PAGE);

        if (file_exists($pluginDirectory . "/" . PLUGIN . "/plugin.php")) {
            include_once($pluginDirectory . "/" . PLUGIN . "/plugin.php");
        }

        include_once 'common/footer.inc';
        ?>
    </div>
    <?= buildJs($pluginDirectory) ?>
    </body>
    </html>

<?php
function buildJs($pluginDirectory = null): string
{
    $jsDir = $pluginDirectory . "/" . PLUGIN . "/js/";
    $scripts = '';
    foreach (glob($jsDir . "/*.js") as $filename) {
        $scripts .= '<script type="text/javascript" src="plugin.php?plugin=' . PLUGIN . '&file=js/' . $filename . '&nopage=1></script>' . PHP_EOL;
    }
    return $scripts;
}

function buildCss($pluginDirectory = null): string
{
    $cssDir = $pluginDirectory . "/" . PLUGIN . "/css/";
    $styles = '';
    foreach (glob($cssDir . "/*.css") as $filename) {
        $styles .= '<link rel="stylesheet" type="text/css" href="/plugin.php?plugin=' . PLUGIN . '&file=css/' . $filename . '&nopage=1>' . PHP_EOL;
    }
    return $styles;
}