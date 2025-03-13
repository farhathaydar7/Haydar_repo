<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__.'/../config.php';

try {
    $owner_id = isset($_GET['owner_id']) ? intval($_GET['owner_id']) : 1;
    $selected_tag = isset($_GET['tag']) ? intval($_GET['tag']) : null;
    $search_query = isset($_GET['search']) ? trim($_GET['search']) : null;

    // Fetch tags
    $tag_stmt = $db->prepare("
        SELECT t.tag_id, t.tag_name, COUNT(m.image_id) AS count 
        FROM tags t
        LEFT JOIN memory m ON t.tag_id = m.tag_id AND m.owner_id = :owner_id
        WHERE t.tag_owner = :owner_id AND t.tag_name != ''
        GROUP BY t.tag_id, t.tag_name
    ");
    $tag_stmt->bindParam(':owner_id', $owner_id, PDO::PARAM_INT);
    $tag_stmt->execute();
    $tags = $tag_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch images with dynamic query
    $image_sql = "SELECT * FROM memory WHERE owner_id = :owner_id";
    $params = [':owner_id' => $owner_id];

    if ($selected_tag) {
        $image_sql .= " AND tag_id = :tag_id";
        $params[':tag_id'] = $selected_tag;
    }
    if ($search_query) {
        $image_sql .= " AND (title LIKE :search OR description LIKE :search)";
        $params[':search'] = "%$search_query%";
    }

    $image_stmt = $db->prepare($image_sql);
    foreach ($params as $key => $value) {
        $image_stmt->bindValue($key, $value);
    }

    $image_stmt->execute();
    $images = $image_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convert image URLs to base64
    foreach ($images as &$image) {
        $relativePath = $image['image_url'];
        $filePath = realpath(__DIR__ . '/../' . $relativePath);

        if (!$filePath || !file_exists($filePath)) {
            error_log("Missing image file: {$relativePath} (Resolved path: " . __DIR__ . '/../' . $relativePath . ")");
            $image['image_data'] = null;
            $image['mime_type'] = null;
            continue;
        }

        // Get actual MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $filePath);
        finfo_close($finfo);

        // Validate image type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'image/svg+xml'];
        if (!in_array($mime_type, $allowed_types)) {
            error_log("Invalid image type: $mime_type for file: {$image['image_url']}");
            $image['image_data'] = null;
            $image['mime_type'] = null;
            continue;
        }

        // Read and encode image
        $image_data = file_get_contents($filePath);
        $base64 = base64_encode($image_data);

        // Ensure proper base64 padding
        if (strlen($base64) % 4 !== 0) {
            $base64 .= str_repeat('=', 4 - (strlen($base64) % 4));
        }

        $image['image_data'] = $base64;
        $image['mime_type'] = $mime_type;
    }

    echo json_encode([
        'tags' => $tags,
        'images' => $images,
        'selected_tag' => $selected_tag,
        'search_query' => $search_query
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
