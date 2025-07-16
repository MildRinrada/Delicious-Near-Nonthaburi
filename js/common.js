function loadComponent(elementId, componentPath) {
    fetch(componentPath)
        .then(response => response.text())
        .then(html => {
            document.getElementById(elementId).innerHTML = html;
        })
        .catch(error => {
            console.error('เกิดข้อผิดพลาดในการโหลด Component:', error);
        });
}

document.addEventListener('DOMContentLoaded', function() {
    const smoothScrollLinks = document.querySelectorAll('a.smooth-scroll');

    smoothScrollLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            event.preventDefault(); 

            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);

            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start' 
                });
            }
        });
    });
});

function sendEmailViaEmailJS(recipientEmail, templateType, data) {
    let templateId;
    let serviceId;
    let templateParams = {
        to_email: recipientEmail,
        reply_to_email: recipientEmail
    };

    if (templateType === 'verification') {
        templateId = 'template_ejr3erd';
        serviceId = 'service_10mwfcr';
        templateParams.verification_code = data.verificationCode;
        templateParams.Name = data.userName || 'ผู้ใช้งาน';
    } else if (templateType === 'reset_password') {
        templateId = 'template_n7q2j4e';
        serviceId = 'service_gszdfxo';
        templateParams.Name = data.userName || 'ผู้ใช้งาน';
        templateParams.reset_link = data.resetLink;
    } else {
        console.error("Unknown template type:", templateType);
        return Promise.reject("Unknown template type");
    }

    if (!serviceId) {
        console.error("Service ID not defined for template type:", templateType);
        return Promise.reject("Service ID not defined");
    }

    return emailjs.send(serviceId, templateId, templateParams)
        .then(function(response) {
            console.log('EmailJS SUCCESS!', response.status, response.text);
            return true;
        }, function(error) {
            console.error('EmailJS FAILED...', error);
            throw error;
        });
}