<?php

function readDatabase() {
    $json = file_get_contents("db.json");
    return json_decode($json, true);
}

function writeDatabase($data) {
    file_put_contents("db.json", json_encode($data, JSON_PRETTY_PRINT));
}
