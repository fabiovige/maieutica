/**
 * Sistema de Toast Global
 * Exibe notificações temporárias na tela
 */

// Cria o container de toasts se não existir
function createToastContainer() {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    return container;
}

// Ícones para cada tipo de toast
const toastIcons = {
    success: 'bi bi-check-circle-fill',
    error: 'bi bi-exclamation-triangle-fill',
    warning: 'bi bi-exclamation-circle-fill',
    info: 'bi bi-info-circle-fill'
};

/**
 * Exibe um toast
 * @param {string} message - Mensagem a ser exibida
 * @param {string} type - Tipo do toast (success, error, warning, info)
 * @param {number} duration - Duração em milissegundos (padrão: 5000)
 */
function showToast(message, type = 'info', duration = 5000) {
    const container = createToastContainer();
    
    // Cria o elemento do toast
    const toast = document.createElement('div');
    toast.className = `toast-item ${type}`;
    
    // Cria o conteúdo do toast
    toast.innerHTML = `
        <i class="toast-icon ${toastIcons[type]}"></i>
        <div class="toast-message">${message}</div>
        <button type="button" class="toast-close" aria-label="Fechar">
            <i class="bi bi-x"></i>
        </button>
    `;
    
    // Adiciona o toast ao container
    container.appendChild(toast);
    
    // Anima a entrada
    setTimeout(() => {
        toast.classList.add('show');
    }, 100);
    
    // Função para remover o toast
    function removeToast() {
        toast.classList.remove('show');
        toast.classList.add('hide');
        setTimeout(() => {
            if (toast.parentNode) {
                container.removeChild(toast);
            }
        }, 300);
    }
    
    // Event listener para o botão de fechar
    const closeBtn = toast.querySelector('.toast-close');
    closeBtn.addEventListener('click', removeToast);
    
    // Remove automaticamente após a duração especificada
    if (duration > 0) {
        setTimeout(removeToast, duration);
    }
    
    return toast;
}

// Funções de conveniência
window.showToast = showToast;

window.showSuccess = function(message, duration = 3000) {
    return showToast(message, 'success', duration);
};

window.showError = function(message, duration = 5000) {
    return showToast(message, 'error', duration);
};

window.showWarning = function(message, duration = 4000) {
    return showToast(message, 'warning', duration);
};

window.showInfo = function(message, duration = 4000) {
    return showToast(message, 'info', duration);
};