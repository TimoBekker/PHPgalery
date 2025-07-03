// Изначально
let currentIndex = 0;
const container = document.getElementById('thumbnailsContainer');
const modalImage = document.getElementById('modalImage');
const modalVideo = document.getElementById('modalVideo');
const closeBtn = document.getElementById('closeBtn');
const prevBtn = document.getElementById('prevBtn');
const nextBtn = document.getElementById('nextBtn');

function showImage(index) {
    const item = images[index];
    if (item.type === 'image') {
        modalImage.src = item.full;
        modalImage.style.display = 'block';
        modalVideo.style.display = 'none';
        modalVideo.pause();
        modalVideo.src = '';
    } else if (item.type === 'video') {
        modalImage.style.display = 'none';
        modalVideo.src = item.full;
        modalVideo.style.display = 'block';
        modalVideo.play();
    }
    modal.classList.add('show');
}

// Обработка кликов по миниатюрам
document.querySelectorAll('#thumbnailsContainer a').forEach((link, index) => {
    link.onclick = function(e) {
        e.preventDefault();
        currentIndex = index;
        showImage(currentIndex);
    };
});

// Закрытие
closeBtn.onclick = () => {
    modal.classList.remove('show');
    modalVideo.pause();
    modalVideo.src = '';
};

// Навигация
prevBtn.onclick = () => {
    currentIndex = (currentIndex - 1 + images.length) % images.length;
    showImage(currentIndex);
};
nextBtn.onclick = () => {
    currentIndex = (currentIndex + 1) % images.length;
    showImage(currentIndex);
};

// Клавиши
document.addEventListener('keydown', (e) => {
    if (modal.classList.contains('show') && images.length > 0) {
        if (e.key === 'ArrowLeft') prevBtn.onclick();
        if (e.key === 'ArrowRight') nextBtn.onclick();
        if (e.key === 'Escape') {
            modal.classList.remove('show');
            modalVideo.pause();
            modalVideo.src = '';
        }
    }
});

function renderThumbnails() {
    container.innerHTML = '';
    images.forEach((imgObj, index) => {
        const thumb = new Image();
        thumb.crossOrigin = 'Anonymous';
        thumb.onload = () => {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const thumbW = 200;
            const thumbH = 133;
            canvas.width = thumbW;
            canvas.height = thumbH;
            ctx.drawImage(thumb, 0, 0, thumbW, thumbH);
            const thumbDataUrl = canvas.toDataURL();
            const a = document.createElement('a');
            a.href = '#';
            a.setAttribute('data-index', index);
            a.onclick = (e) => {
                e.preventDefault();
                currentIndex = index;
                showImage(currentIndex);
            };
            const img = document.createElement('img');
            img.src = imgObj.thumb;
            img.className = 'thumbnail';
            a.appendChild(img);
            container.appendChild(a);
        };
        thumb.src = imgObj.thumb;
    });
}

window.onload = () => {
    renderThumbnails();
};
