/**
 * Módulo de Busca de Endereço por CEP
 * Sistema seguro e robusto para preenchimento automático de endereços
 * 
 * @author Backend Senior Expert
 * @version 2.1.0
 */

console.log('🚀 Address CEP Manager v2.1 carregado!');

class AddressCepManager {
    constructor(containerId = null) {
        this.apiUrl = 'https://viacep.com.br/ws';
        this.timeout = 10000; // 10 segundos
        this.debounceDelay = 800;
        this.maxRetries = 2;
        this.cache = new Map();
        this.containerId = containerId;
        
        // Support multiple instances with container-specific selectors
        const prefix = containerId ? `#${containerId} ` : '';
        this.selectors = {
            cep: `${prefix}#cep`,
            logradouro: `${prefix}#logradouro`,
            bairro: `${prefix}#bairro`,
            cidade: `${prefix}#cidade`,
            estado: `${prefix}#estado`,
            numero: `${prefix}#numero`,
            complemento: `${prefix}#complemento`
        };
        
        this.debounceTimer = null;
        this.currentRequest = null;
        
        this.init();
    }

    init() {
        document.addEventListener('DOMContentLoaded', () => {
            this.bindEvents();
            this.initializeFields();
        });
    }

    bindEvents() {
        const cepInput = document.querySelector(this.selectors.cep);
        if (!cepInput) return;

        // Máscara de CEP
        cepInput.addEventListener('input', (e) => {
            this.applyCepMask(e.target);
            this.handleCepInput(e.target.value);
        });

        // Enter key
        cepInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.searchAddressByCep(e.target.value);
            }
        });

        // Blur event
        cepInput.addEventListener('blur', (e) => {
            this.searchAddressByCep(e.target.value);
        });
    }

    applyCepMask(input) {
        let cep = input.value.replace(/\D/g, '');
        
        if (cep.length <= 8) {
            if (cep.length > 5) {
                input.value = `${cep.substring(0, 5)}-${cep.substring(5)}`;
            } else {
                input.value = cep;
            }
        }
    }

    handleCepInput(cepValue) {
        const cleanCep = this.sanitizeCep(cepValue);
        
        // Clear existing timer
        if (this.debounceTimer) {
            clearTimeout(this.debounceTimer);
        }

        if (cleanCep.length === 8) {
            this.debounceTimer = setTimeout(() => {
                this.searchAddressByCep(cepValue);
            }, this.debounceDelay);
        } else if (cleanCep.length < 8) {
            this.clearAddressFields();
            this.enableAddressFields();
        }
    }

    sanitizeCep(cep) {
        return String(cep || '').replace(/\D/g, '');
    }

    validateCep(cep) {
        const cleanCep = this.sanitizeCep(cep);
        
        // Basic validation
        if (cleanCep.length !== 8 || !/^\d{8}$/.test(cleanCep)) {
            return false;
        }
        
        // Check for known invalid patterns
        const invalidPatterns = [
            /^00000000$/, // All zeros
            /^11111111$/, // All ones
            /^12345678$/, // Sequential
            /^87654321$/  // Reverse sequential
        ];
        
        return !invalidPatterns.some(pattern => pattern.test(cleanCep));
    }

    async searchAddressByCep(cep) {
        try {
            const cleanCep = this.sanitizeCep(cep);
            
            if (!this.validateCep(cleanCep)) {
                this.clearAddressFields();
                this.enableAddressFields();
                return;
            }

            // Cancel previous request
            if (this.currentRequest) {
                this.currentRequest.abort();
            }

            // Check cache first
            if (this.cache.has(cleanCep)) {
                const cachedData = this.cache.get(cleanCep);
                this.fillAddressFields(cachedData);
                return;
            }

            this.setLoadingState(true);
            this.disableAddressFields();

            const addressData = await this.fetchAddressData(cleanCep);
            
            if (addressData) {
                this.fillAddressFields(addressData);
                this.cache.set(cleanCep, addressData);
                this.focusNextField();
            } else {
                this.handleCepNotFound();
            }

        } catch (error) {
            this.handleSearchError(error);
        } finally {
            this.setLoadingState(false);
            this.currentRequest = null;
        }
    }

    async fetchAddressData(cep, retryCount = 0) {
        const controller = new AbortController();
        this.currentRequest = controller;

        const timeoutId = setTimeout(() => {
            controller.abort();
        }, this.timeout);

        try {
            const response = await fetch(`${this.apiUrl}/${cep}/json/`, {
                signal: controller.signal,
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            clearTimeout(timeoutId);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            // ViaCEP returns { erro: true } for invalid CEPs
            if (data.erro === true || data.erro === "true") {
                return null;
            }

            return this.sanitizeAddressData(data);

        } catch (error) {
            clearTimeout(timeoutId);
            
            if (error.name === 'AbortError') {
                throw new Error('Requisição cancelada');
            }
            
            // Retry logic
            if (retryCount < this.maxRetries && error.name === 'TypeError') {
                return this.fetchAddressData(cep, retryCount + 1);
            }
            
            throw error;
        }
    }

    sanitizeAddressData(data) {
        return {
            logradouro: this.sanitizeString(data.logradouro),
            bairro: this.sanitizeString(data.bairro),
            cidade: this.sanitizeString(data.localidade),
            estado: this.sanitizeString(data.uf)
        };
    }

    sanitizeString(value) {
        if (!value || typeof value !== 'string') return '';
        
        // Create a temporary div element to escape HTML entities
        const div = document.createElement('div');
        div.textContent = value.trim().substring(0, 255);
        return div.innerHTML; // Returns properly escaped string
    }

    fillAddressFields(addressData) {
        const fields = [
            { selector: this.selectors.logradouro, value: addressData.logradouro },
            { selector: this.selectors.bairro, value: addressData.bairro },
            { selector: this.selectors.cidade, value: addressData.cidade },
            { selector: this.selectors.estado, value: addressData.estado }
        ];

        fields.forEach(({ selector, value }) => {
            const field = document.querySelector(selector);
            if (field) {
                field.value = value || '';
                field.classList.add('auto-filled');
            }
        });

        // Clear manual fields
        this.clearManualFields();
        this.disableAddressFields();
    }

    clearAddressFields() {
        const allFields = [
            this.selectors.logradouro,
            this.selectors.bairro,
            this.selectors.cidade,
            this.selectors.estado,
            this.selectors.numero,
            this.selectors.complemento
        ];

        allFields.forEach(selector => {
            const field = document.querySelector(selector);
            if (field) {
                field.value = '';
                field.classList.remove('auto-filled');
            }
        });
    }

    clearManualFields() {
        const manualFields = [this.selectors.numero, this.selectors.complemento];
        
        manualFields.forEach(selector => {
            const field = document.querySelector(selector);
            if (field) {
                field.value = '';
            }
        });
    }

    disableAddressFields() {
        const autoFields = [
            this.selectors.logradouro,
            this.selectors.bairro,
            this.selectors.cidade,
            this.selectors.estado
        ];

        autoFields.forEach(selector => {
            const field = document.querySelector(selector);
            if (field) {
                field.setAttribute('readonly', 'readonly');
                field.classList.add('auto-filled');
            }
        });
    }

    enableAddressFields() {
        const autoFields = [
            this.selectors.logradouro,
            this.selectors.bairro,
            this.selectors.cidade,
            this.selectors.estado
        ];

        autoFields.forEach(selector => {
            const field = document.querySelector(selector);
            if (field) {
                field.removeAttribute('readonly');
                field.classList.remove('auto-filled');
            }
        });
    }

    focusNextField() {
        const numeroField = document.querySelector(this.selectors.numero);
        if (numeroField) {
            setTimeout(() => numeroField.focus(), 100);
        }
    }

    setLoadingState(isLoading) {
        const cepInput = document.querySelector(this.selectors.cep);
        const addressInputs = [
            this.selectors.logradouro,
            this.selectors.bairro,
            this.selectors.cidade,
            this.selectors.estado
        ];

        if (!cepInput) return;

        if (isLoading) {
            cepInput.classList.add('loading');
            cepInput.setAttribute('data-loading', 'true');
            
            // Add loading state to address inputs
            addressInputs.forEach(selector => {
                const input = document.querySelector(selector);
                if (input) {
                    input.classList.add('loading');
                    input.setAttribute('data-loading', 'true');
                    input.setAttribute('placeholder', 'Buscando endereço...');
                    input.value = '';
                }
            });
        } else {
            cepInput.classList.remove('loading');
            cepInput.removeAttribute('data-loading');
            
            // Remove loading state from address inputs
            addressInputs.forEach(selector => {
                const input = document.querySelector(selector);
                if (input) {
                    input.classList.remove('loading');
                    input.removeAttribute('data-loading');
                    input.removeAttribute('placeholder');
                }
            });
        }
    }

    handleCepNotFound() {
        this.clearAddressFields();
        this.enableAddressFields();
        this.showMessage('CEP não encontrado. Verifique o CEP informado ou preencha manualmente.', 'warning');
    }

    handleSearchError(error) {
        this.clearAddressFields();
        this.enableAddressFields();
        
        let message = 'Erro ao buscar endereço. ';
        
        if (error.message.includes('Failed to fetch') || error.name === 'TypeError') {
            message += 'Verifique sua conexão com a internet.';
        } else if (error.message.includes('timeout')) {
            message += 'Tempo limite excedido. Tente novamente.';
        } else {
            message += 'Tente novamente em alguns instantes.';
        }
        
        this.showMessage(message, 'error');
        console.error('Address search error:', error);
    }

    showMessage(message, type = 'info') {
        if (typeof window.showToast === 'function') {
            window.showToast(message, type);
        } else {
            console.warn('Toast system not available:', message);
        }
    }

    initializeFields() {
        const cepInput = document.querySelector(this.selectors.cep);
        if (!cepInput) return;

        const currentCep = cepInput.value;
        
        if (this.validateCep(currentCep)) {
            this.disableAddressFields();
        } else {
            this.disableAddressFields(); // Start with fields disabled
        }
    }

    // Public methods for manual control
    reset() {
        this.clearAddressFields();
        this.enableAddressFields();
        this.cache.clear();
    }

    search(cep) {
        return this.searchAddressByCep(cep);
    }
}

// Initialize when script loads
const addressManager = new AddressCepManager();

// Export for manual access if needed
window.AddressCepManager = AddressCepManager;
window.addressManager = addressManager;