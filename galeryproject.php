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
if ($selectedAlbum && in_array($selectedAlbum, $albums)) {
    $albumPath = $imagesDir . $selectedAlbum . '/';
    $imagesFiles = array_filter(scandir($albumPath), function($file) {
        return preg_match('/\.(jpg|jpeg|png|gif)$/i', $file);
    });
    $thumbsDir = $albumPath . 'thumbs/';
    foreach ($imagesFiles as $img) {
        $fullPath = $albumPath . $img;
        $thumbSrc = $thumbsDir . $img;
        if (file_exists($thumbSrc)) {
            $images[] = [
                'full' => $fullPath,
                'thumb' => $thumbSrc
            ];
        } else {
            $images[] = [
                'full' => $fullPath,
                'thumb' => $fullPath
            ];
        }
    }
} else {
    $allImages = [];
    foreach ($albums as $album) {
        $albumPath = $imagesDir . $album . '/';
        $imagesFiles = array_filter(scandir($albumPath), function($file) {
            return preg_match('/\.(jpg|jpeg|png|gif)$/i', $file);
        });
        $thumbsDir = $albumPath . 'thumbs/';
        foreach ($imagesFiles as $img) {
            $fullPath = $albumPath . $img;
            $thumbSrc = $thumbsDir . $img;
            if (file_exists($thumbSrc)) {
                $allImages[] = ['full' => $fullPath, 'thumb' => $thumbSrc];
            } else {
                $allImages[] = ['full' => $fullPath, 'thumb' => $fullPath];
            }
        }
    }
    shuffle($allImages);
    $images = array_slice($allImages, 0, 29); // сколько выводить случайных миниатюр
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
<div class="album-list">
<?php
if (!$hideAlbumButtons) {
    foreach ($albums as $album) {
        echo "<a class='album' href='?album=" . urlencode($album) . "'>$album</a>";
    }
}
?>
</div>
<div class="gallery-container">
<!--  <h2> 
    <?php if ($selectedAlbum): ?>
      <?php echo htmlspecialchars($selectedAlbum); ?>
    <?php else: ?>
      Случайные изображения
    <?php endif; ?>
  </h2> -->
  <div class="thumbnails" id="thumbnailsContainer"></div>
</div>
<div id="modal">
  <button id="closeBtn">×</button>
  <button id="prevBtn">←</button>
  <img id="modalImage" src="" alt="Фото" />
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