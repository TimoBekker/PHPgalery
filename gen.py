import os
from PIL import Image
from tqdm import tqdm

images_dir = 'images/'

def generate_thumbnail(image_path, thumb_path, thumb_width=200):
    if not os.path.exists(image_path):
        return False
    os.makedirs(os.path.dirname(thumb_path), exist_ok=True)
    try:
        with Image.open(image_path) as img:
            width, height = img.size
            ratio = width / height
            thumb_height = int(thumb_width / ratio)
            img = img.resize((thumb_width, thumb_height), Image.Resampling.LANCZOS)
            img.save(thumb_path)
        return True
    except Exception:
        return False

# Собираем список всех изображений для обработки
all_images = []
for album_name in os.listdir(images_dir):
    album_path = os.path.join(images_dir, album_name)
    if os.path.isdir(album_path):
        for filename in os.listdir(album_path):
            if filename.lower().endswith(('.jpg', '.jpeg', '.png', '.gif')):
                image_path = os.path.join(album_path, filename)
                thumb_dir = os.path.join(album_path, 'thumbs')
                thumb_path = os.path.join(thumb_dir, filename)
                all_images.append((image_path, thumb_path, album_path))

total_files = len(all_images)

with tqdm(total=total_files, desc='Обработка изображений') as pbar:
    for image_path, thumb_path, album_path in all_images:
        # Проверка существования оригинала и миниатюры
        if os.path.exists(thumb_path):
            # Если оригинал исчез — удаляем миниатюру
            if not os.path.exists(image_path):
                os.remove(thumb_path)
        else:
            # Миниатюра отсутствует — создаем
            generate_thumbnail(image_path, thumb_path)
        pbar.update(1)

print("Генерация миниатюр завершена.")
