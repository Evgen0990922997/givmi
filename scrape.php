<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $url = filter_var($_POST["url"], FILTER_SANITIZE_URL);

    if (filter_var($url, FILTER_VALIDATE_URL)) {
        $html = @file_get_contents($url);
        if ($html === FALSE) {
            die("Error: Unable to retrieve content from the URL.");
        }

        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $images = $dom->getElementsByTagName("img");

        $imageUrls = [];
        foreach ($images as $image) {
            $src = $image->getAttribute("src");
            if (!filter_var($src, FILTER_VALIDATE_URL)) {
                $src = $url . '/' . ltrim($src, '/');
            }
            $imageUrls[] = $src;
        }

        $totalSize = 0;
        $imageData = [];
        foreach ($imageUrls as $imageUrl) {
            $imageContent = @file_get_contents($imageUrl);
            if ($imageContent !== FALSE) {
                $imageFileSize = strlen($imageContent);
                $totalSize += $imageFileSize;
                $imageData[] = [
                    'url' => $imageUrl,
                    'size' => round($imageFileSize / 1024 / 1024, 2), // Размер в Мб
                ];
            }
        }

        $totalSizeMb = round($totalSize / 1024 / 1024, 2);
    } else {
        die("Error: Invalid URL.");
    }
} else {
    die("Error: Invalid request method.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Image Results</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        h1 {
            color: #343a40;
            margin-bottom: 20px;
        }
        .image-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin: 20px 0;
            width: 100%;
            max-width: 1200px;
        }
        .image-grid div {
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s;
        }
        .image-grid div:hover {
            transform: scale(1.05);
        }
        .image-grid img {
            width: 100%;
            height: auto;
            border-radius: 5px;
        }
        .image-grid p {
            margin: 10px 0 0;
            font-size: 14px;
            color: #495057;
        }
        p.summary {
            font-size: 18px;
            text-align: center;
            color: #343a40;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1>Image Results</h1>
    <div class="image-grid">
        <?php if (!empty($imageData)): ?>
            <?php foreach ($imageData as $image): ?>
                <div>
                    <img src="<?php echo htmlspecialchars($image['url']); ?>" alt="Image">
                    <p>Size: <?php echo htmlspecialchars($image['size']); ?> MB</p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>На странице не обнаружено изображений.</p>
        <?php endif; ?>
    </div>
    <p class="summary">На странице обнаружено <?php echo count($imageData); ?> изображений, общим размером <?php echo $totalSizeMb; ?> Мб.</p>
</body>
</html>
