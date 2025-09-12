<?php

switch($_SERVER['HTTP_HOST']) {
    case 'localhost:8080':
        // LOCAL
        header("Location: /page/page_login.php");
        break;
    case 'drawline.pt':
        // SERVER Develop
        header("Location: page/page_login.php");
        break;
    case 'inov360.pt':
            // SERVER Prod
        header("Location: page/page_login.php");
        break;
}
