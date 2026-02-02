<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$API_KEY = "Iyo/xl8aYY31fuSJ8MCfIw==U1dNnsRi9XXe3obV";

$city = isset($_GET['city']) ? trim($_GET['city']) : '';

if (empty($city)) {
    http_response_code(400);
    echo json_encode(['error' => 'City parameter is required']);
    exit;
}

try {
    $geocodingUrl = "https://api.api-ninjas.com/v1/geocoding?city=" . urlencode($city) . "&country=GB";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $geocodingUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-Api-Key: ' . $API_KEY,
        'Content-Type: application/json'
    ]);
    
    $geocodingResponse = curl_exec($ch);
    $geocodingHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($geocodingResponse === false || !empty($curlError)) {
        http_response_code(502);
        echo json_encode(['error' => 'Failed to fetch city coordinates: ' . $curlError]);
        exit;
    }
    
    if ($geocodingHttpCode !== 200) {
        http_response_code(404);
        echo json_encode(['error' => 'City not found. Please select a different city.']);
        exit;
    }
    
    $geocodingData = json_decode($geocodingResponse, true);
    
    if (!is_array($geocodingData) || empty($geocodingData)) {
        http_response_code(404);
        echo json_encode(['error' => 'No coordinates found for the selected city']);
        exit;
    }
    
    $latitude = $geocodingData[0]['latitude'] ?? null;
    $longitude = $geocodingData[0]['longitude'] ?? null;
    
    if (!$latitude || !$longitude) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid coordinate data']);
        exit;
    }
    
    $chargerUrl = "https://api.api-ninjas.com/v1/evcharger?lat=" . $latitude . "&lon=" . $longitude;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $chargerUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-Api-Key: ' . $API_KEY,
        'Content-Type: application/json'
    ]);
    
    $chargerResponse = curl_exec($ch);
    $chargerHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($chargerResponse === false || !empty($curlError)) {
        http_response_code(502);
        echo json_encode(['error' => 'Failed to fetch charger locations: ' . $curlError]);
        exit;
    }
    
    if ($chargerHttpCode !== 200) {
        http_response_code($chargerHttpCode);
        echo json_encode(['error' => 'API returned error code: ' . $chargerHttpCode]);
        exit;
    }
    
    $chargerData = json_decode($chargerResponse, true);
    
    if (!is_array($chargerData)) {
        http_response_code(500);
        echo json_encode(['error' => 'Invalid API response']);
        exit;
    }
    
    echo json_encode($chargerData);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
