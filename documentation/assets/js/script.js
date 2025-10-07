/* ============================================
   MAIÊUTICA - DOCUMENTAÇÃO PARA CLÍNICAS
   JavaScript para interatividade
   ============================================ */

document.addEventListener('DOMContentLoaded', function() {

    // ============================================
    // MENU SIDEBAR - Marcar link ativo
    // ============================================
    const currentPage = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.sidebar-nav a');

    navLinks.forEach(link => {
        const linkPage = link.getAttribute('href');
        if (linkPage === currentPage || (currentPage === '' && linkPage === 'index.html')) {
            link.classList.add('active');
        }
    });

    // ============================================
    // BUSCA EM TEMPO REAL
    // ============================================
    const searchInput = document.getElementById('searchInput');

    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const menuItems = document.querySelectorAll('.sidebar-nav li');

            menuItems.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    // ============================================
    // BOTÃO VOLTAR AO TOPO
    // ============================================
    const backToTopBtn = document.querySelector('.back-to-top');

    if (backToTopBtn) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                backToTopBtn.classList.add('show');
            } else {
                backToTopBtn.classList.remove('show');
            }
        });

        backToTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    // ============================================
    // NAVEGAÇÃO POR TECLADO (setas)
    // ============================================
    document.addEventListener('keydown', function(e) {
        const prevBtn = document.querySelector('.nav-btn.prev');
        const nextBtn = document.querySelector('.nav-btn.next');

        // Seta esquerda: página anterior
        if (e.key === 'ArrowLeft' && prevBtn) {
            window.location.href = prevBtn.getAttribute('href');
        }

        // Seta direita: próxima página
        if (e.key === 'ArrowRight' && nextBtn) {
            window.location.href = nextBtn.getAttribute('href');
        }
    });

    // ============================================
    // ACCORDION (para seções expansíveis)
    // ============================================
    const accordionHeaders = document.querySelectorAll('.accordion-header');

    accordionHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const content = this.nextElementSibling;
            const isOpen = content.style.display === 'block';

            // Fecha todos os accordions
            document.querySelectorAll('.accordion-content').forEach(item => {
                item.style.display = 'none';
            });

            // Abre o clicado (se estava fechado)
            if (!isOpen) {
                content.style.display = 'block';
            }
        });
    });

    // ============================================
    // HIGHLIGHT DE CÓDIGO (opcional)
    // ============================================
    const codeBlocks = document.querySelectorAll('pre code');
    codeBlocks.forEach(block => {
        block.classList.add('language-plaintext');
    });

    // ============================================
    // LIGHTBOX PARA SCREENSHOTS (simples)
    // ============================================
    const screenshots = document.querySelectorAll('.screenshot img');

    screenshots.forEach(img => {
        img.addEventListener('click', function() {
            const lightbox = document.createElement('div');
            lightbox.className = 'lightbox-overlay';
            lightbox.innerHTML = `
                <div class="lightbox-content">
                    <span class="lightbox-close">&times;</span>
                    <img src="${this.src}" alt="${this.alt}">
                </div>
            `;

            document.body.appendChild(lightbox);

            // Adiciona estilos inline (melhor seria em CSS separado)
            lightbox.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.9);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 9999;
                animation: fadeIn 0.3s ease;
            `;

            const lightboxContent = lightbox.querySelector('.lightbox-content');
            lightboxContent.style.cssText = `
                position: relative;
                max-width: 90%;
                max-height: 90%;
            `;

            const lightboxImg = lightbox.querySelector('img');
            lightboxImg.style.cssText = `
                max-width: 100%;
                max-height: 90vh;
                border-radius: 8px;
            `;

            const closeBtn = lightbox.querySelector('.lightbox-close');
            closeBtn.style.cssText = `
                position: absolute;
                top: -40px;
                right: 0;
                color: white;
                font-size: 40px;
                cursor: pointer;
                font-weight: 300;
            `;

            // Fechar lightbox
            closeBtn.addEventListener('click', () => lightbox.remove());
            lightbox.addEventListener('click', (e) => {
                if (e.target === lightbox) {
                    lightbox.remove();
                }
            });
        });
    });

    // ============================================
    // TOOLTIP SIMPLES (para badges e ícones)
    // ============================================
    const tooltipElements = document.querySelectorAll('[data-tooltip]');

    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', function() {
            const tooltipText = this.getAttribute('data-tooltip');
            const tooltip = document.createElement('div');
            tooltip.className = 'custom-tooltip';
            tooltip.textContent = tooltipText;
            tooltip.style.cssText = `
                position: absolute;
                background: #333;
                color: white;
                padding: 8px 12px;
                border-radius: 4px;
                font-size: 13px;
                white-space: nowrap;
                z-index: 9999;
                pointer-events: none;
                animation: fadeIn 0.2s ease;
            `;

            document.body.appendChild(tooltip);

            const rect = this.getBoundingClientRect();
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
            tooltip.style.left = (rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2)) + 'px';

            this._tooltip = tooltip;
        });

        element.addEventListener('mouseleave', function() {
            if (this._tooltip) {
                this._tooltip.remove();
                delete this._tooltip;
            }
        });
    });

    // ============================================
    // PROGRESSO DE LEITURA (barra no topo)
    // ============================================
    const progressBar = document.createElement('div');
    progressBar.className = 'reading-progress';
    progressBar.style.cssText = `
        position: fixed;
        top: 70px;
        left: 0;
        width: 0%;
        height: 3px;
        background: linear-gradient(90deg, #50c878, #4a90e2);
        z-index: 1001;
        transition: width 0.1s ease;
    `;
    document.body.appendChild(progressBar);

    window.addEventListener('scroll', function() {
        const winScroll = document.documentElement.scrollTop || document.body.scrollTop;
        const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        const scrolled = (winScroll / height) * 100;
        progressBar.style.width = scrolled + '%';
    });

    // ============================================
    // PRINT: Esconder sidebar e header
    // ============================================
    window.addEventListener('beforeprint', function() {
        document.querySelector('.doc-sidebar').style.display = 'none';
        document.querySelector('.doc-header').style.display = 'none';
        document.querySelector('.doc-content').style.marginLeft = '0';
    });

    window.addEventListener('afterprint', function() {
        document.querySelector('.doc-sidebar').style.display = 'block';
        document.querySelector('.doc-header').style.display = 'flex';
        document.querySelector('.doc-content').style.marginLeft = '280px';
    });

    // ============================================
    // COPIAR CÓDIGO AO CLICAR
    // ============================================
    const codeElements = document.querySelectorAll('code');
    codeElements.forEach(code => {
        code.style.cursor = 'pointer';
        code.title = 'Clique para copiar';

        code.addEventListener('click', function() {
            const text = this.textContent;
            navigator.clipboard.writeText(text).then(() => {
                // Feedback visual
                const original = this.style.background;
                this.style.background = '#50c878';
                this.style.color = 'white';

                setTimeout(() => {
                    this.style.background = original;
                    this.style.color = '#e74c3c';
                }, 500);
            });
        });
    });

    // ============================================
    // ANIMAÇÃO DE ENTRADA (fade in nos elementos)
    // ============================================
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animation = 'fadeInUp 0.6s ease forwards';
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observa cards, steps, alerts
    document.querySelectorAll('.doc-card, .step, .alert').forEach(el => {
        el.style.opacity = '0';
        observer.observe(el);
    });

    // Adiciona keyframes via CSS inline (alternativa ao CSS externo)
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    `;
    document.head.appendChild(style);

    console.log('📚 Documentação Maiêutica carregada com sucesso!');
});
