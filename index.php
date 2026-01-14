<?php

switch($_SERVER['HTTP_HOST']) {
    case 'localhost:8080':
        // LOCAL
        header("Location: frontend/modules/login/view.html");
        break;
    case 'drawline.pt':
        // SERVER Develop
        header("Location: frontend/modules/login/view.html");
        break;
    case 'inov360.pt':
            // SERVER Prod
        header("Location: frontend/modules/login/view.html");
        break;
}
