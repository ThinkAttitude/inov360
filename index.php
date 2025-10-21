<?php

switch($_SERVER['HTTP_HOST']) {
    case 'localhost:8080':
        // LOCAL
        header("Location: page/login.html");
        break;
    case 'drawline.pt':
        // SERVER Develop
        header("Location: page/login.html");
        break;
    case 'inov360.pt':
            // SERVER Prod
        header("Location: page/login.html");
        break;
}
