import os
from PIL import Image
from tqdm import tqdm
import subprocess

images_dir = 'images/'

# Функция для генерации миниатюры изображения
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

# Функция для генерации миниатюры видео
def generate_video_thumbnail(video_path, thumb_path, time_offset='00:00:01'):
    os.makedirs(os.path.dirname(thumb_path), exist_ok=True)
    command = [
        'ffmpeg',
        '-ss', time_offset,
        '-i', video_path,
        '-frames:v', '1',
        '-q:v', '2',
        thumb_path
    ]
    try:
        subprocess.run(command, check=True, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
        return True
    except subprocess.CalledProcessError:
        return False

# Расширение видео файлов
video_extensions = ['mp4', 'webm', 'avi']

# Собираем список изображений и видео
image_tasks = []
video_tasks = []

for album_name in os.listdir(images_dir):
    album_path = os.path.join(images_dir, album_name)
    if os.path.isdir(album_path):
        # Обработка изображений
        for filename in os.listdir(album_path):
            ext = filename.lower().split('.')[-1]
            if ext in ['jpg', 'jpeg', 'png', 'gif']:
                image_path = os.path.join(album_path, filename)
                thumb_dir = os.path.join(album_path, 'thumbs')
                thumb_path = os.path.join(thumb_dir, filename)
                image_tasks.append((image_path, thumb_path))
            # Обработка видео
            elif ext in video_extensions:
                video_path = os.path.join(album_path, filename)
                thumb_dir = os.path.join(album_path, 'thumbs')
                thumb_name = os.path.splitext(filename)[0] + '_thumb.jpg'
                thumb_path = os.path.join(thumb_dir, thumb_name)
                video_tasks.append((video_path, thumb_path))

# Обработка изображений
with tqdm(total=len(image_tasks), desc='Обработка изображений') as pbar:
    for image_path, thumb_path in image_tasks:
        if os.path.exists(thumb_path):
            # Если миниатюра есть, проверяем, существует ли оригинал
            if not os.path.exists(image_path):
                os.remove(thumb_path)  # Удаляем миниатюру, если оригинал исчез
        else:
            # Миниатюра отсутствует, создаем
            generate_thumbnail(image_path, thumb_path)
        pbar.update(1)

# Обработка видео
with tqdm(total=len(video_tasks), desc='Обработка видео') as pbar:
    for video_path, thumb_path in video_tasks:
        if os.path.exists(thumb_path):
            # Если миниатюра есть, проверяем, существует ли видео
            if not os.path.exists(video_path):
                os.remove(thumb_path)  # Удаляем миниатюру, если видео исчезло
        else:
            # Миниатюра отсутствует, создаем
            success = generate_video_thumbnail(video_path, thumb_path)
            if not success:
                print(f"Не удалось создать миниатюру для {video_path}")
        pbar.update(1)

print("Обработка изображений и видео завершена.")
