document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('.restaurant-detail .grid img[data-full-src]');
    let lightboxOverlay = null;

    images.forEach(image => {
        image.addEventListener('click', function() {
            const fullSrc = this.getAttribute('data-full-src');
            if (!fullSrc) return;

            if (lightboxOverlay) {
                closeLightbox();
            }

            // สร้าง overlay สำหรับแสดงภาพขนาดเต็ม
            lightboxOverlay = document.createElement('div');
            lightboxOverlay.className = 'modal';
            lightboxOverlay.style.display = 'flex';
            lightboxOverlay.style.cursor = 'zoom-out';

            // สร้าง img element สำหรับรูปภาพขนาดเต็ม
            const imgElement = document.createElement('img');
            imgElement.src = fullSrc;
            imgElement.alt = this.alt || 'Restaurant image';
            imgElement.className = 'modal-content';

            // สร้างปุ่มปิด lightbox
            const closeButton = document.createElement('span');
            closeButton.innerHTML = '&times;';
            closeButton.className = 'close';
            closeButton.style.textShadow = '0 0 5px rgba(0,0,0,0.8)';

            // เพิ่มปุ่มและรูปภาพลงใน overlay และเพิ่มเข้า body
            lightboxOverlay.appendChild(closeButton);
            lightboxOverlay.appendChild(imgElement);
            document.body.appendChild(lightboxOverlay);

            // event ปิด lightbox เมื่อคลิกปุ่ม หรือคลิกพื้นที่ว่างรอบภาพ
            closeButton.addEventListener('click', closeLightbox);
            lightboxOverlay.addEventListener('click', e => {
                if (e.target === lightboxOverlay) closeLightbox();
            });

            // ป้องกันการเลื่อนหน้าเมื่อ lightbox เปิด
            document.body.style.overflow = 'hidden';
        });
    });

    function closeLightbox() {
        if (lightboxOverlay) {
            lightboxOverlay.remove();
            lightboxOverlay = null;
            document.body.style.overflow = '';
        }
    }
});

document.addEventListener('DOMContentLoaded', () => {
  const stars = document.querySelectorAll('#star-rating .star');
  const ratingInput = document.getElementById('rating');

  // แสดงดาวตามค่าที่เลือกไว้
  function setStars(rating) {
    stars.forEach(star => {
      const starValue = parseInt(star.dataset.value);
      if (starValue <= rating) star.classList.add('selected');
      else star.classList.remove('selected');
    });
  }

  if (ratingInput.value) setStars(parseInt(ratingInput.value));

  // event จับเมาส์ hover เพื่อไฮไลต์ดาว
  stars.forEach(star => {
    star.addEventListener('mouseover', () => {
      const hoverValue = parseInt(star.dataset.value);
      stars.forEach(s => {
        if (parseInt(s.dataset.value) <= hoverValue) s.classList.add('hover');
        else s.classList.remove('hover');
      });
    });

    star.addEventListener('mouseout', () => {
      stars.forEach(s => s.classList.remove('hover'));
    });

    // event คลิกเลือกคะแนนและเก็บค่าใน input hidden
    star.addEventListener('click', () => {
      const selectedValue = parseInt(star.dataset.value);
      ratingInput.value = selectedValue;
      setStars(selectedValue);
    });
  });
});
