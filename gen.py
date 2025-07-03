import os
import subprocess
from PIL import Image
from tqdm import tqdm

images_dir = 'images/'  # Путь к папке с альбомами

def generate_image_thumbnail(image_path, thumb_path, thumb_width=200):
    if not os.path.exists(image_path):
        return False
    os.makedirs(os.path.dirname(thumb_path), exist_ok=True)
    try:
        with Image.open(image_path) as img:
            width, height = img.size
            ratio = width / height
            thumb_height = int(thumb_width / ratio)
            # Используем актуальный способ ресайза
            img = img.resize((thumb_width, thumb_height), Image.Resampling.LANCZOS)
            img.save(thumb_path)
        return True
    except Exception as e:
        print(f"Ошибка при создании картинки миниатюры: {e}")
        return False

def generate_video_thumbnail(video_path, thumb_path, thumb_width=200):
    temp_dir = os.path.join(os.path.dirname(thumb_path), 'temp_frames')
    os.makedirs(temp_dir, exist_ok=True)
    frame_pattern = os.path.join(temp_dir, 'frame_%03d.jpg')
    try:
        # Обратите внимание на экранирование запятой в фильтре
        result = subprocess.run([
            'ffmpeg', '-i', video_path,
            '-vf', 'select=not(mod(n\,30))',  # экранирование
            '-frames:v', '5',
            frame_pattern
        ], check=True, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
        # дальнейшая обработка кадров...
        frames = sorted(os.listdir(temp_dir))
        if not frames:
            print(f"Кадры для видео {video_path} не извлечены.")
            return False

        images_list = []
        for frame_file in frames:
            path = os.path.join(temp_dir, frame_file)
            with Image.open(path) as img:
                width, height = img.size
                ratio = width / height
                thumb_h = int(thumb_width / ratio)
                img_resized = img.resize((thumb_width, thumb_h), Image.Resampling.LANCZOS)
                images_list.append(img_resized)

        if images_list:
            images_list[0].save(thumb_path, save_all=True, append_images=images_list[1:], duration=300, loop=0)
            print(f"Миниатюра видео сохранена: {thumb_path}")
            # Удаление временных кадров
            for f in frames:
                os.remove(os.path.join(temp_dir, f))
            os.rmdir(temp_dir)
            return True
        else:
            print(f"Нет кадров для видео {video_path}")
            return False
    except subprocess.CalledProcessError as e:
        print(f"ffmpeg вызвал ошибку: {e}")
        print(f"stderr: {e.stderr.decode('utf-8')}")
    except Exception as e:
        print(f"Ошибка при создании видео миниатюры: {e}")

    # Очистка при ошибке
    if os.path.exists(temp_dir):
        for f in os.listdir(temp_dir):
            os.remove(os.path.join(temp_dir, f))
        os.rmdir(temp_dir)
    return False

# Обрабатываем все файлы
all_files = []
for album_name in os.listdir(images_dir):
    album_path = os.path.join(images_dir, album_name)
    if os.path.isdir(album_path):
        for filename in os.listdir(album_path):
            ext = filename.lower().split('.')[-1]
            if ext in ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'avi', 'mov', 'webm']:
                full_path = os.path.join(album_path, filename)
                thumb_dir = os.path.join(album_path, 'thumbs')
                os.makedirs(thumb_dir, exist_ok=True)
                if ext in ['mp4', 'avi', 'mov', 'webm']:
                    thumb_filename = filename + '.gif'
                else:
                    thumb_filename = filename
                thumb_path = os.path.join(thumb_dir, thumb_filename)
                all_files.append((full_path, thumb_path, ext))

total = len(all_files)
with tqdm(total=total, desc='Обработка медиа') as pbar:
    for full_path, thumb_path, ext in all_files:
        # Удаляем миниатюру, если оригинал исчез
        if os.path.exists(thumb_path) and not os.path.exists(full_path):
            os.remove(thumb_path)
        else:
            if ext in ['mp4', 'avi', 'mov', 'webm']:
                generate_video_thumbnail(full_path, thumb_path)
            else:
                generate_image_thumbnail(full_path, thumb_path)
        pbar.update(1)

print("Генерация миниатюр завершена.")
