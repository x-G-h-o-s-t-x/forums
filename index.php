<?php
header('Content-type: text/html; charset=UTF-8');
echo '<!doctype html>';
echo '<html lang="en">';

echo '<head>';
    echo '<!--[if lt IE 9]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->';
    echo '<meta charset="utf-8"/>';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>';
    echo '<title>', 'Index', '</title>';
    echo '<link rel="stylesheet" type="text/css" href="css/dark.css" media="screen"/>';
    echo '<link rel="shortcut icon" type="image/ico" href="images/celtic_cross.ico"/>';
echo '</head>';
    echo '<body>';

    echo '<header>';
        echo '<div class="logo">';
            echo '<img src="images/codemafia.png" alt=""/><br/>';
        echo '</div>';
        echo '<div class="padder"></div>';
    echo '</header>';

    echo '<nav>';
        echo '<ul>';
            echo '<li><a class="active" href="index.php">Forums</a></li>';
        echo '</ul>';
    echo '</nav>';

    echo '</body>';
echo '</html>';
?>