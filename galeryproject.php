<?php
$imagesDir = 'images/';
if (!is_dir($imagesDir)) {
    die('Папка images/ не найдена.');
}
$albums = array_filter(glob($imagesDir . '*'), 'is_dir');
$albums = array_map(function($path) {
    return basename($path);
}, $albums);

$darkThemeFlag = false; // Темная тема
$hideAlbumButtons = false; // Скрыть кнопки альбомов

$selectedAlbum = isset($_GET['album']) ? $_GET['album'] : null;
$images = [];

$videoExtensions = ['mp4', 'webm', 'ogg'];

if ($selectedAlbum && in_array($selectedAlbum, $albums)) {
    $albumPath = $imagesDir . $selectedAlbum . '/';
    $imagesFiles = array_filter(scandir($albumPath), function($file) {
        return preg_match('/\.(jpg|jpeg|png|gif|mp4|webm|ogg)$/i', $file);
    });
    $thumbsDir = $albumPath . 'thumbs/';
    foreach ($imagesFiles as $img) {
        $fullPath = $albumPath . $img;
        $ext = strtolower(pathinfo($img, PATHINFO_EXTENSION));
        $thumbSrc = $thumbsDir . $img;
        if (file_exists($thumbSrc)) {
            $thumbUrl = $thumbSrc;
        } else {
            $thumbUrl = $fullPath;
        }
        $type = in_array($ext, ['mp4','webm','ogg']) ? 'video' : 'image';
        $images[] = [
            'full' => $fullPath,
            'thumb' => $thumbUrl,
            'type' => $type
        ];
    }
} else {
    // Для общего просмотра — случайные 29 изображений
    $allImages = [];
    foreach ($albums as $album) {
        $albumPath = $imagesDir . $album . '/';
        $imagesFiles = array_filter(scandir($albumPath), function($file) {
            return preg_match('/\.(jpg|jpeg|png|gif|mp4|webm|ogg)$/i', $file);
        });
        $thumbsDir = $albumPath . 'thumbs/';
        foreach ($imagesFiles as $img) {
            $fullPath = $albumPath . $img;
            $ext = strtolower(pathinfo($img, PATHINFO_EXTENSION));
            $thumbSrc = $thumbsDir . $img;
            if (file_exists($thumbSrc)) {
                $thumbUrl = $thumbSrc;
            } else {
                $thumbUrl = $fullPath;
            }
            $type = in_array($ext, ['mp4','webm','ogg']) ? 'video' : 'image';
            $allImages[] = [
                'full' => $fullPath,
                'thumb' => $thumbUrl,
                'type' => $type
            ];
        }
    }
    shuffle($allImages);
    $images = array_slice($allImages, 0, 29);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Галерея с альбомами</title>
<link rel="stylesheet" href="style.css" />
</head>
<body>
<div class="album-list" <?php if ($hideAlbumButtons) echo 'style="display:none;"'; ?>>
<?php
if (!$hideAlbumButtons) {
    foreach ($albums as $album) {
        echo "<a class='album' href='?album=" . urlencode($album) . "'>$album</a>";
    }
}
?>
</div>
<div class="gallery-container">
  <h2>
    <?php if ($selectedAlbum): ?>
      <?php echo htmlspecialchars($selectedAlbum); ?>
    <?php else: ?>
      Случайные изображения
    <?php endif; ?>
  </h2>
  <div class="thumbnails" id="thumbnailsContainer"></div>
</div>
<div id="modal">
  <button id="closeBtn">×</button>
  <button id="prevBtn">←</button>
  <div id="modalContent"></div>
  <button id="nextBtn">→</button>
</div>
<script>
  const darkThemeFlag = <?php echo json_encode($darkThemeFlag); ?>;
  const hideAlbumButtons = <?php echo json_encode($hideAlbumButtons); ?>;
  const images = <?php echo json_encode($images); ?>;
</script>
<script src="script.js"></script>
</body>
</html>
