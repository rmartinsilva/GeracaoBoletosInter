/**
 * Arquivo JavaScript Principal do Projeto
 */

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {

    // Fechar alertas automaticamente
    autoCloseAlerts();

    // Adicionar animações aos cards do dashboard
    animateDashboardCards();

    // Inicializar tooltips se houver
    initTooltips();

    // Menu mobile toggle
    initMobileMenu();
});

/**
 * Auto-fechar alertas após 5 segundos
 */
function autoCloseAlerts() {
    const alerts = document.querySelectorAll('.alert[data-auto-close="true"]');

    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s ease-out';

            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });
}

/**
 * Animar cards do dashboard quando aparecem na tela
 */
function animateDashboardCards() {
    const cards = document.querySelectorAll('.dashboard-card');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fadeIn');
            }
        });
    }, {
        threshold: 0.1
    });

    cards.forEach(card => {
        observer.observe(card);
    });
}

/**
 * Inicializar tooltips (se houver)
 */
function initTooltips() {
    const tooltips = document.querySelectorAll('[data-tooltip]');

    tooltips.forEach(element => {
        element.addEventListener('mouseenter', function() {
            const tooltipText = this.getAttribute('data-tooltip');
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip absolute bg-gray-800 text-white px-3 py-2 rounded text-sm z-50';
            tooltip.textContent = tooltipText;
            tooltip.id = 'tooltip-' + Math.random().toString(36).substr(2, 9);

            document.body.appendChild(tooltip);

            const rect = this.getBoundingClientRect();
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 10) + 'px';
            tooltip.style.left = (rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2)) + 'px';

            this.addEventListener('mouseleave', function() {
                tooltip.remove();
            }, { once: true });
        });
    });
}

/**
 * Menu mobile toggle
 */
function initMobileMenu() {
    const menuToggle = document.getElementById('mobile-menu-toggle');
    const sidebar = document.querySelector('.sidebar-menu');
    const overlay = document.getElementById('mobile-overlay');

    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('mobile-open');
            if (overlay) {
                overlay.classList.toggle('hidden');
            }
        });

        if (overlay) {
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('mobile-open');
                overlay.classList.add('hidden');
            });
        }
    }
}

/**
 * Confirmar ação (para deletar, etc)
 */
function confirmAction(message = 'Tem certeza que deseja continuar?') {
    return confirm(message);
}

/**
 * Mostrar loading
 */
function showLoading() {
    const loading = document.createElement('div');
    loading.id = 'global-loading';
    loading.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    loading.innerHTML = '<div class="spinner"></div>';
    document.body.appendChild(loading);
}

/**
 * Esconder loading
 */
function hideLoading() {
    const loading = document.getElementById('global-loading');
    if (loading) {
        loading.remove();
    }
}

/**
 * Copiar para clipboard
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('Copiado para área de transferência!', 'success');
    }).catch(err => {
        console.error('Erro ao copiar:', err);
    });
}

/**
 * Mostrar toast notification
 */
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 px-6 py-4 rounded-lg shadow-lg z-50 animate-fadeIn`;

    const colors = {
        'success': 'bg-green-500 text-white',
        'error': 'bg-red-500 text-white',
        'warning': 'bg-yellow-500 text-white',
        'info': 'bg-blue-500 text-white'
    };

    toast.classList.add(...colors[type].split(' '));
    toast.textContent = message;

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

/**
 * Formatar moeda brasileira
 */
function formatCurrency(value) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value);
}

/**
 * Formatar data brasileira
 */
function formatDate(date) {
    return new Intl.DateTimeFormat('pt-BR').format(new Date(date));
}

/**
 * Validar CPF
 */
function validateCPF(cpf) {
    cpf = cpf.replace(/[^\d]/g, '');

    if (cpf.length !== 11) return false;

    if (/^(\d)\1+$/.test(cpf)) return false;

    let sum = 0;
    for (let i = 0; i < 9; i++) {
        sum += parseInt(cpf.charAt(i)) * (10 - i);
    }
    let digit = 11 - (sum % 11);
    if (digit >= 10) digit = 0;
    if (digit !== parseInt(cpf.charAt(9))) return false;

    sum = 0;
    for (let i = 0; i < 10; i++) {
        sum += parseInt(cpf.charAt(i)) * (11 - i);
    }
    digit = 11 - (sum % 11);
    if (digit >= 10) digit = 0;
    if (digit !== parseInt(cpf.charAt(10))) return false;

    return true;
}

/**
 * Máscara de CPF
 */
function maskCPF(input) {
    let value = input.value.replace(/\D/g, '');
    value = value.replace(/(\d{3})(\d)/, '$1.$2');
    value = value.replace(/(\d{3})(\d)/, '$1.$2');
    value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    input.value = value;
}

/**
 * Máscara de telefone
 */
function maskPhone(input) {
    let value = input.value.replace(/\D/g, '');
    value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
    value = value.replace(/(\d)(\d{4})$/, '$1-$2');
    input.value = value;
}

/**
 * Máscara de CEP
 */
function maskCEP(input) {
    let value = input.value.replace(/\D/g, '');
    value = value.replace(/^(\d{5})(\d)/, '$1-$2');
    input.value = value;
}
