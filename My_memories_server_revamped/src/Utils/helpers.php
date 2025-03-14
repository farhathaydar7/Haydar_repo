<?php
namespace MyApp\Utils;

function response($data): void {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}