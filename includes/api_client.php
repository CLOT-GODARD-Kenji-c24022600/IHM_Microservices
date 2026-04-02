<?php
// includes/api_client.php

function appelerAPI($url) {
    // Désactive les avertissements si l'API est injoignable
    $json_data = @file_get_contents($url);

    if ($json_data === FALSE) {
        return null;
    }

    return json_decode($json_data, true);
}
?>
