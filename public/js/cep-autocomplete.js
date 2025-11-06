/**
 * Função para busca automática de endereço por CEP
 * Utiliza a API do ViaCEP para preencher automaticamente os campos de endereço
 */
function initCepAutocomplete() {
    const cepInput = document.getElementById('cep');

    if (!cepInput) return;

    // Campos de endereço que serão preenchidos automaticamente
    const logradouroInput = document.getElementById('logradouro');
    const bairroInput = document.getElementById('bairro');
    const cidadeInput = document.getElementById('cidade');
    const estadoInput = document.getElementById('estado');
    const numeroInput = document.getElementById('numero');
    const complementoInput = document.getElementById('complemento');

    // Função para limpar campos de endereço
    function clearAddressFields() {
        if (logradouroInput) logradouroInput.value = '';
        if (bairroInput) bairroInput.value = '';
        if (cidadeInput) cidadeInput.value = '';
        if (estadoInput) estadoInput.value = '';
        if (numeroInput) numeroInput.value = '';
        if (complementoInput) complementoInput.value = '';
    }

    // Função para buscar endereço por CEP
    async function searchAddressByCep(cep) {
        try {
            // Remove caracteres não numéricos
            cep = cep.replace(/\D/g, '');

            if (cep.length !== 8) {
                clearAddressFields();
                return;
            }

            // Mostra indicador de carregamento
            if (cepInput) {
                cepInput.classList.add('loading');
            }

            const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);

            if (!response.ok) {
                throw new Error('Erro na requisição');
            }

            const data = await response.json();

            if (data.erro) {
                // CEP não encontrado
                clearAddressFields();
                alert('CEP não encontrado. Por favor, verifique o CEP informado.');
                return;
            }

            // Preenche os campos com os dados retornados
            if (logradouroInput) logradouroInput.value = data.logradouro || '';
            if (bairroInput) bairroInput.value = data.bairro || '';
            if (cidadeInput) cidadeInput.value = data.localidade || '';
            if (estadoInput) estadoInput.value = data.uf || '';

            // Limpa número e complemento para o usuário preencher
            if (numeroInput) numeroInput.value = '';
            if (complementoInput) complementoInput.value = '';

            // Foca no campo número
            if (numeroInput) {
                setTimeout(() => {
                    numeroInput.focus();
                }, 100);
            }

        } catch (error) {
            console.error('Erro ao buscar CEP:', error);
            clearAddressFields();
            alert('Erro ao buscar CEP. Tente novamente.');
        } finally {
            // Remove indicador de carregamento
            if (cepInput) {
                cepInput.classList.remove('loading');
            }
        }
    }

    // Event listener para quando o usuário terminar de digitar o CEP
    cepInput.addEventListener('blur', function() {
        const cep = this.value;
        if (cep.length >= 8) {
            searchAddressByCep(cep);
        }
    });

    // Event listener para quando o usuário pressionar Enter no campo CEP
    cepInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const cep = this.value;
            if (cep.length >= 8) {
                searchAddressByCep(cep);
            }
        }
    });

    // Event listener para quando o usuário digitar no campo CEP
    cepInput.addEventListener('input', function() {
        const cep = this.value.replace(/\D/g, '');
        if (cep.length === 8) {
            // Pequeno delay para permitir que o usuário termine de digitar
            setTimeout(() => {
                if (this.value.replace(/\D/g, '').length === 8) {
                    searchAddressByCep(this.value);
                }
            }, 500);
        }
    });
}

// Inicializa quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', function() {
    initCepAutocomplete();
});

// Função para ser chamada manualmente se necessário
window.initCepAutocomplete = initCepAutocomplete;
