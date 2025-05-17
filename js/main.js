let imcChartInstance = null;
let actChartInstance = null;

function renderCharts() {
    if (imcChartInstance) imcChartInstance.destroy();
    if (actChartInstance) actChartInstance.destroy();
    const labels = window.imcLabels || [];
    const imcData = window.imcData || [];
    const activiteData = window.activiteData || [];
    const caloriesData = window.caloriesData || [];
    const imcCtx = document.getElementById('imcChart');
    if (imcCtx) {
        imcChartInstance = new Chart(imcCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'IMC',
                    data: imcData,
                    borderColor: 'blue',
                    backgroundColor: 'rgba(0,0,255,0.1)',
                    fill: true
                }]
            },
            options: {scales: {y: {beginAtZero: false}}}
        });
    }
    const actCtx = document.getElementById('actChart');
    if (actCtx) {
        actChartInstance = new Chart(actCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Activité physique (min)',
                        data: activiteData,
                        backgroundColor: 'rgba(0,200,0,0.5)'
                    },
                    {
                        label: 'Calories',
                        data: caloriesData,
                        backgroundColor: 'rgba(255,140,0,0.5)'
                    }
                ]
            },
            options: {scales: {y: {beginAtZero: true}}}
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Menu mobile toggle
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function() {
            const nav = document.querySelector('nav');
            nav.classList.toggle('active');
        });
    }

    // Fade-out messages
    const messages = document.querySelectorAll('.message');
    if (messages.length > 0) {
        setTimeout(function() {
            messages.forEach(function(message) {
                message.classList.add('fade-out');
                setTimeout(function() {
                    message.style.display = 'none';
                }, 500);
            });
        }, 3000);
    }

    // Tabs handling
    const tabBtns = document.querySelectorAll('.tab-btn-profil');
    const tabContents = {
        'infos': document.getElementById('tab-infos'),
        'favoris': document.getElementById('tab-favoris'),
        'suivi': document.getElementById('tab-suivi')
    };
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            tabBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            Object.values(tabContents).forEach(div => div.classList.remove('active'));
            tabContents[this.dataset.tab].classList.add('active');
            if (this.dataset.tab === 'suivi') {
                setTimeout(renderCharts, 100);
            }
        });
    });
    if (document.querySelector('.tab-btn-profil.active') && document.querySelector('.tab-btn-profil.active').dataset.tab === 'suivi') {
        renderCharts();
    }

    // FAQ toggle
    const faqItems = document.querySelectorAll('.faq-item h3');
    if (faqItems.length > 0) {
        faqItems.forEach(function(item) {
            item.addEventListener('click', function() {
                this.parentElement.classList.toggle('active');
            });
        });
    }

    // Form validation
    const forms = document.querySelectorAll('form');
    if (forms.length > 0) {
        forms.forEach(function(form) {
            form.addEventListener('submit', function(event) {
                let isValid = true;
                const requiredFields = form.querySelectorAll('[required]');
                requiredFields.forEach(function(field) {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.classList.add('error');
                    } else {
                        field.classList.remove('error');
                    }
                });

                const emailFields = form.querySelectorAll('input[type="email"]');
                emailFields.forEach(function(field) {
                    if (field.value.trim() && !isValidEmail(field.value.trim())) {
                        isValid = false;
                        field.classList.add('error');
                    }
                });

                if (!isValid) {
                    event.preventDefault();
                    const formMessage = document.createElement('div');
                    formMessage.className = 'message error';
                    formMessage.textContent = 'Veuillez corriger les erreurs dans le formulaire.';
                    form.insertBefore(formMessage, form.firstChild);
                    setTimeout(function() {
                        formMessage.remove();
                    }, 3000);
                }
            });
        });
    }

    function isValidEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }

    // Smooth scroll for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]:not([href="#"])');
    if (anchorLinks.length > 0) {
        anchorLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });
    }

    // Scroll-to-top button
    const scrollTopBtn = document.querySelector('.scroll-top');
    if (scrollTopBtn) {
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                scrollTopBtn.classList.add('visible');
            } else {
                scrollTopBtn.classList.remove('visible');
            }
        });

        scrollTopBtn.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
});
