<?php
$container->set('config_bd', function () {
    return (object)[
        "host" => "db", //Este es otro db
        "db" => "taller",
        "usr" => "root",
        "password" => "12345",
        "charset" => "utf8mb4" //Para caracteres especiales
    ];
});
