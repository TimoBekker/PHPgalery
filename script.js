// Получение элементов
let currentIndex = 0;
const container = document.getElementById('thumbnailsContainer');
const modal = document.getElementById('modal');
const closeBtn = document.getElementById('closeBtn');
const prevBtn = document.getElementById('prevBtn');
const nextBtn = document.getElementById('nextBtn');
const modalContent = document.getElementById('modalContent');

function applyTheme() {
  if (darkThemeFlag) {
    document.body.classList.add('dark');
  } else {
    document.body.classList.remove('dark');
  }
}
applyTheme();

if (hideAlbumButtons) {
  document.querySelector('.album-list').style.display = 'none';
}

// Создаем миниатюру
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
        showMedia(currentIndex);
      };
      container.appendChild(imgEl);
    });
  });
}

function showMedia(index) {
  modal.classList.add('show');
  modalContent.innerHTML = ''; // очистка прошлого
  const media = images[index];

  if (media.type === 'video') {
    const video = document.createElement('video');
    video.src = media.full;
    video.controls = true;
    video.style.maxWidth = '80%';
    video.style.maxHeight = '80%';
    modalContent.appendChild(video);
  } else {
    const img = document.createElement('img');
    img.src = media.full;
    img.style.maxWidth = '80%';
    img.style.maxHeight = '80%'
    modalContent.appendChild(img);
  }
}

// Обработчики
closeBtn.onclick = () => { modal.classList.remove('show'); };
prevBtn.onclick = () => {
  currentIndex = (currentIndex - 1 + images.length) % images.length;
  showMedia(currentIndex);
};
nextBtn.onclick = () => {
  currentIndex = (currentIndex + 1) % images.length;
  showMedia(currentIndex);
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
