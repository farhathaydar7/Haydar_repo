<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__.'/../config.php';

// Supported MIME types
$mimeTypes = [
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'gif'  => 'image/gif',
    'webp' => 'image/webp',
    'bmp'  => 'image/bmp',
    'svg'  => 'image/svg+xml'
];

try {
    $owner_id = $_GET['owner_id'] ?? 1; // Replace with actual auth logic
    $selected_tag = $_GET['tag'] ?? null;
    $search_query = $_GET['search'] ?? null;

    // Fetch tags
    $tag_stmt = $db->prepare("
        SELECT t.tag_id, t.tag_name, COUNT(m.image_id) AS count 
        FROM tags t
        LEFT JOIN memory m ON t.tag_id = m.tag_id
        WHERE t.tag_owner = :owner_id
        GROUP BY t.tag_id
    ");
    $tag_stmt->bindParam(':owner_id', $owner_id);
    $tag_stmt->execute();
    $tags = $tag_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch images
    $image_sql = "SELECT * FROM memory WHERE owner_id = :owner_id";
    if ($selected_tag) $image_sql .= " AND tag_id = :tag_id";
    if ($search_query) $image_sql .= " AND (title LIKE :search OR description LIKE :search)";
    
    $image_stmt = $db->prepare($image_sql);
    $image_stmt->bindParam(':owner_id', $owner_id);
    if ($selected_tag) $image_stmt->bindParam(':tag_id', $selected_tag);
    if ($search_query) $image_stmt->bindValue(':search', "%$search_query%");
    
    $image_stmt->execute();
    $images = $image_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convert image URLs to base64
    // Inside the image conversion loop
    foreach ($images as &$image) {
        $filePath = realpath(__DIR__.'/../'.$image['image_url']);
        
        if (!$filePath) {
            error_log("File not found: " . __DIR__.'/../'.$image['image_url']);
            $image['image_data'] = null;
            continue;
        }

        if (!file_exists($filePath)) {
            error_log("File does not exist: $filePath");
            $image['image_data'] = null;
            continue;
        }

        $fileContent = file_get_contents($filePath);
        if ($fileContent === false) {
            error_log("Failed to read file: $filePath");
            $image['image_data'] = null;
            continue;
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $image['image_data'] = base64_encode($fileContent);
        $image['mime_type'] = $mimeTypes[$extension] ?? 'application/octet-stream';
    }
    unset($image['image_url']); // Remove original URL from response

    $response = [
        'tags' => $tags,
        'images' => $images,
        'selected_tag' => $selected_tag,
        'search_query' => $search_query
    ];

    header('Content-Length: ' . strlen(json_encode($response)));
    echo json_encode($response);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch(Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}