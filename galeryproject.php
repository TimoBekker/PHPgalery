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
        return preg_match('/\.(jpg|jpeg|png|gif|mp4|webm|avi)$/i', $file);
    });
    $thumbsDir = $albumPath . 'thumbs/';
    foreach ($imagesFiles as $file) {
        $fullPath = $albumPath . $file;
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (in_array($extension, ['mp4', 'webm', 'avi'])) {
            // ВИДЕО
            $thumbFile = preg_replace('/\.(mp4|webm|avi)$/i', '_thumb.jpg', $file);
            $thumbSrc = $thumbsDir . $thumbFile;
            $images[] = [
                'full' => $fullPath,
                'thumb' => (file_exists($thumbSrc)) ? $thumbSrc : $fullPath,
                'type' => 'video'
            ];
        } else {
            // ИЗОБРАЖЕНИЕ
            $thumbSrc = $thumbsDir . $file;
            if (file_exists($thumbSrc)) {
                $images[] = [
                    'full' => $fullPath,
                    'thumb' => $thumbSrc,
                    'type' => 'image'
                ];
            } else {
                $images[] = [
                    'full' => $fullPath,
                    'thumb' => $fullPath,
                    'type' => 'image'
                ];
            }
        }
    }
} else {
    $allImages = [];
    foreach ($albums as $album) {
        $albumPath = $imagesDir . $album . '/';
        $imagesFiles = array_filter(scandir($albumPath), function($file) {
            return preg_match('/\.(jpg|jpeg|png|gif|mp4|webm|avi)$/i', $file);
        });
        $thumbsDir = $albumPath . 'thumbs/';
        foreach ($imagesFiles as $file) {
            $fullPath = $albumPath . $file;
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($extension, ['mp4', 'webm', 'avi'])) {
                $thumbFile = preg_replace('/\.(mp4|webm|avi)$/i', '_thumb.jpg', $file);
                $thumbSrc = $thumbsDir . $thumbFile;
                $allImages[] = [
                    'full' => $fullPath,
                    'thumb' => (file_exists($thumbSrc)) ? $thumbSrc : $fullPath,
                    'type' => 'video'
                ];
            } else {
                $thumbSrc = $thumbsDir . $file;
                if (file_exists($thumbSrc)) {
                    $allImages[] = ['full' => $fullPath, 'thumb' => $thumbSrc, 'type' => 'image'];
                } else {
                    $allImages[] = ['full' => $fullPath, 'thumb' => $fullPath, 'type' => 'image'];
                }
            }
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
<div class="album-list">
<?php
if (!$hideAlbumButtons) {
    foreach ($albums as $album) {
        echo "<a class='album' href='?album=" . urlencode($album) . "'>$album</a> ";
    }
}
?>
</div>
<div class="gallery-container">
  <div class="thumbnails" id="thumbnailsContainer"></div>
</div>
<div id="modal">
  <button id="closeBtn">×</button>
  <button id="prevBtn">←</button>
  <div id="modalContent">
    <img id="modalImage" src="" alt="Фото" style="display:none;" />
    <video id="modalVideo" controls style="display:none; max-width:80%; max-height:80vh;">
      <source src="" type="video/mp4" />
      Ваш браузер не поддерживает видео.
    </video>
  </div>
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
