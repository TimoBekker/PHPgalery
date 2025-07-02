// Получение элементов
let currentIndex = 0;
const container = document.getElementById('thumbnailsContainer');
const modal = document.getElementById('modal');
const modalImage = document.getElementById('modalImage');
const closeBtn = document.getElementById('closeBtn');
const prevBtn = document.getElementById('prevBtn');
const nextBtn = document.getElementById('nextBtn');

// Функция для применения темы
function applyTheme() {
  if (darkThemeFlag) {
    document.body.classList.add('dark');
  } else {
    document.body.classList.remove('dark');
  }
}
applyTheme();

// Обработка отображения кнопок альбомов
if (hideAlbumButtons) {
  document.querySelector('.album-list').style.display = 'none';
}

// Создание миниатюры
function createThumbnail(src, callback) {
  const img = new Image();
  img.crossOrigin = 'Anonymous';
  img.onload = () => {
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    const thumbW = 200;
    const thumbH = 133;
    canvas.width = thumbW;
    canvas.height = thumbH;
    ctx.drawImage(img, 0, 0, thumbW, thumbH);
    callback(canvas.toDataURL());
  };
  img.onerror = () => {
    callback(src);
  };
  img.src = src;
}

function renderThumbnails() {
  container.innerHTML = '';
  images.forEach((imgObj, index) => {
    createThumbnail(imgObj.thumb, (thumbDataUrl) => {
      const imgEl = document.createElement('img');
      imgEl.className = 'thumbnail';
      imgEl.src = thumbDataUrl;
      imgEl.setAttribute('data-index', index);
      imgEl.onclick = () => {
        currentIndex = index;
        showImage(currentIndex);
      };
      container.appendChild(imgEl);
    });
  });
}

function showImage(index) {
  modal.classList.add('show');
  modalImage.src = images[index].full;
}

// Обработчики
closeBtn.onclick = () => { modal.classList.remove('show'); };
prevBtn.onclick = () => {
  currentIndex = (currentIndex - 1 + images.length) % images.length;
  showImage(currentIndex);
};
nextBtn.onclick = () => {
  currentIndex = (currentIndex + 1) % images.length;
  showImage(currentIndex);
};
window.onclick = (e) => { if (e.target === modal) modal.classList.remove('show'); };
document.addEventListener('keydown', (e) => {
  if (modal.classList.contains('show') && images.length > 0) {
    if (e.key === 'ArrowLeft') prevBtn.onclick();
    if (e.key === 'ArrowRight') nextBtn.onclick();
    if (e.key === 'Escape') modal.classList.remove('show');
  }
});

// Инициализация
window.onload = () => {
  renderThumbnails();
  applyTheme();
};