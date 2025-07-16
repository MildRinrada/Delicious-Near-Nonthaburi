document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('.restaurant-info .grid img');
    let lightboxOverlay = null;
    let lightboxContent = null;

    images.forEach(image => {
        image.addEventListener('click', function() {
            const fullSrc = this.getAttribute('data-full-src');
            if (!fullSrc) return;

            // ตรวจสอบว่ามี lightbox เปิดอยู่แล้วหรือไม่ เพื่อป้องกันการเปิดซ้อน
            if (lightboxOverlay) {
                closeLightbox();
            }

            // 1. สร้าง Overlay
            lightboxOverlay = document.createElement('div');
            lightboxOverlay.className = 'fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 p-4';
            lightboxOverlay.style.cursor = 'zoom-out'; // เปลี่ยน cursor เป็น zoom-out เมื่ออยู่บน overlay

            // 2. สร้าง Content Container
            lightboxContent = document.createElement('div');
            // สำคัญ: เอา 'overflow-hidden' ออกจาก lightboxContent เพื่อให้รูปภาพจัดการ overflow ของตัวเอง
            // เพิ่ม 'p-2' หรือ 'p-4' ที่นี่ ถ้าอยากให้มี padding ระหว่างรูปกับขอบป๊อปอัพ
            lightboxContent.className = 'relative flex-shrink-0 bg-white rounded-lg shadow-xl'; // ปรับตรงนี้

            // 3. สร้าง Image Element
            const imgElement = document.createElement('img');
            imgElement.src = fullSrc;
            imgElement.alt = this.alt;
            // object-contain คือการย่อรูปให้พอดีกับกรอบโดยไม่ถูกตัด
            // block w-auto h-auto จะบอกให้รูปภาพใช้ขนาดธรรมชาติของมัน
            // max-w-[80vw] max-h-[80vh] คือการจำกัดขนาดสูงสุดของ "รูปภาพ" โดยตรง
            imgElement.className = 'block object-contain max-w-[80vw] max-h-[80vh] mx-auto my-auto rounded-lg'; // **ปรับตรงนี้**

            // 4. สร้าง Close Button
            const closeButton = document.createElement('button');
            closeButton.innerHTML = '&times;'; // สัญลักษณ์ X
            closeButton.className = 'absolute top-2 right-2 text-white text-4xl font-bold p-2 leading-none hover:text-gray-300 focus:outline-none';
            closeButton.style.textShadow = '0 0 5px rgba(0,0,0,0.8)'; // เพิ่มเงาเพื่อให้เห็นชัดเจน

            // 5. ประกอบร่างและเพิ่มเข้า DOM
            lightboxContent.appendChild(imgElement);
            lightboxContent.appendChild(closeButton);
            lightboxOverlay.appendChild(lightboxContent);
            document.body.appendChild(lightboxOverlay);

            // 6. เพิ่ม Event Listeners สำหรับปิด
            closeButton.addEventListener('click', closeLightbox);
            lightboxOverlay.addEventListener('click', function(e) {
                // ปิดเมื่อคลิกที่ Overlay เท่านั้น ไม่ใช่ที่รูปภาพภายใน
                if (e.target === lightboxOverlay) {
                    closeLightbox();
                }
            });

            // ป้องกันการเลื่อนหน้าเมื่อ Lightbox เปิด
            document.body.style.overflow = 'hidden';
        });
    });

    function closeLightbox() {
        if (lightboxOverlay) {
            lightboxOverlay.remove();
            lightboxOverlay = null;
            lightboxContent = null;
            document.body.style.overflow = ''; // คืนค่าการเลื่อนหน้า
        }
    }
});

        // JavaScript for image modal
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');
            const closeBtn = document.getElementsByClassName('close')[0];
            const images = document.querySelectorAll('.restaurant-detail .grid img');

            images.forEach(img => {
                img.addEventListener('click', function() {
                    modal.style.display = 'flex';
                    modalImage.src = this.getAttribute('data-full-src');
                });
            });

            closeBtn.addEventListener('click', () => {
                modal.style.display = 'none';
            });

            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });