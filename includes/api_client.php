<?php
// includes/api_client.php

/**
 * Extrait le code de statut HTTP depuis les en-tetes retournes par PHP.
 *
 * @param array<int, string>|null $headers Liste brute des en-tetes HTTP.
 * @return int Code HTTP detecte, ou 0 si non disponible.
 */
function extraireCodeHttp($headers) {
    if (!is_array($headers) || empty($headers)) {
        return 0;
    }

    foreach ($headers as $header) {
        if (preg_match('/HTTP\/\S+\s+(\d{3})/', $header, $matches)) {
            return (int) $matches[1];
        }
    }

    return 0;
}

/**
 * Execute une requete GET sur une API REST et retourne le JSON decode.
 *
 * @param string $url URL complete de la ressource a appeler.
 * @return array<mixed>|null Donnees decodees, ou null en cas d'erreur reseau/HTTP/JSON.
 */
function appelerAPI($url) {
    $options = [
        'http' => [
            'method' => 'GET',
            'header' => "Accept: application/json\r\n",
            'timeout' => 5,
            'ignore_errors' => true
        ]
    ];

    $contexte = stream_context_create($options);
    $json_data = @file_get_contents($url, false, $contexte);
    $codeHttp = extraireCodeHttp($http_response_header ?? []);

    if ($json_data === false || $codeHttp < 200 || $codeHttp >= 300) {
        return null;
    }

    $donnees = json_decode($json_data, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return null;
    }

    return $donnees;
}

/**
 * Envoie des donnees JSON en POST et retourne la reponse decodee.
 *
 * @param string $url URL complete de l'endpoint cible.
 * @param array<string, mixed> $donnees Payload a serialiser en JSON.
 * @return array<mixed>|null Reponse decodee, tableau vide si 2xx sans JSON, null en cas d'echec HTTP/reseau.
 */
function envoyerDonneesAPI($url, $donnees) {
    $payload = json_encode($donnees);
    if ($payload === false) {
        return null;
    }

    $options = [
        'http' => [
            'header' => "Content-type: application/json\r\nAccept: application/json\r\n",
            'method' => 'POST',
            'content' => $payload,
            'timeout' => 5,
            'ignore_errors' => true
        ]
    ];

    $contexte = stream_context_create($options);
    $resultat = @file_get_contents($url, false, $contexte);
    $codeHttp = extraireCodeHttp($http_response_header ?? []);

    if ($resultat === false || $codeHttp < 200 || $codeHttp >= 300) {
        return null;
    }

    // Certains services repondent 201/204 sans payload JSON utile.
    if (trim($resultat) === '') {
        return [];
    }

    $reponse = json_decode($resultat, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [];
    }

    return $reponse;
}
?>
