# Funcionalidade de Busca de Endereço por CEP

## Descrição

Esta funcionalidade permite o preenchimento automático dos campos de endereço através da digitação do CEP. Utiliza a API gratuita do ViaCEP para buscar as informações de endereço.

## Como Funciona

1. **Campo CEP**: O usuário digita o CEP no formato 00000-000
2. **Busca Automática**: Quando o usuário termina de digitar o CEP (8 dígitos), a busca é realizada automaticamente
3. **Preenchimento**: Os campos são preenchidos automaticamente com os dados retornados pela API
4. **Campos Editáveis**: Apenas os campos "Número" e "Complemento" permanecem editáveis pelo usuário

## Campos Preenchidos Automaticamente

- **Logradouro**: Preenchido automaticamente (somente leitura)
- **Bairro**: Preenchido automaticamente (somente leitura)
- **Cidade**: Preenchida automaticamente (somente leitura)
- **Estado**: Preenchido automaticamente (somente leitura)

## Campos Editáveis pelo Usuário

- **Número**: Deve ser preenchido manualmente pelo usuário
- **Complemento**: Opcional, pode ser preenchido pelo usuário

## Arquivos Implementados

### JavaScript
- `public/js/cep-autocomplete.js`: Script principal da funcionalidade

### CSS
- `public/css/cep-autocomplete.css`: Estilos para a funcionalidade

### Views Atualizadas
- `resources/views/users/create.blade.php`: Formulário de criação de usuários
- `resources/views/users/edit.blade.php`: Formulário de edição de usuários
- `resources/views/profile/edit.blade.php`: Formulário de edição de perfil
- `resources/views/components/address.blade.php`: Componente de endereço reutilizável

### Validações
- `app/Http/Requests/UserRequest.php`: Validações para formulários de usuários
- `app/Http/Requests/ProfileUpdateRequest.php`: Validações para formulário de perfil

## Como Implementar em Novos Formulários

### 1. Incluir os arquivos CSS e JS

```html
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/cep-autocomplete.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/cep-autocomplete.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('input[name="cep"]').mask('00000-000');
        });
    </script>
@endpush
```

### 2. Estrutura dos Campos

```html
<!-- Campo CEP -->
<div class="col-md-4">
    <label for="cep" class="form-label">
        <i class="bi bi-search"></i>
        CEP
    </label>
    <input type="text" class="form-control" id="cep" name="cep" 
           placeholder="00000-000" maxlength="9">
    <small class="form-text text-muted">Digite o CEP para preenchimento automático</small>
</div>

<!-- Campos de Endereço (somente leitura) -->
<div class="col-md-6">
    <label for="logradouro" class="form-label">Logradouro</label>
    <input type="text" class="form-control" id="logradouro" name="logradouro" readonly>
</div>

<div class="col-md-2">
    <label for="numero" class="form-label">Número</label>
    <input type="text" class="form-control" id="numero" name="numero" placeholder="123">
</div>

<div class="col-md-4">
    <label for="complemento" class="form-label">Complemento</label>
    <input type="text" class="form-control" id="complemento" name="complemento" placeholder="Apto 101">
</div>

<div class="col-md-4">
    <label for="bairro" class="form-label">Bairro</label>
    <input type="text" class="form-control" id="bairro" name="bairro" readonly>
</div>

<div class="col-md-3">
    <label for="cidade" class="form-label">Cidade</label>
    <input type="text" class="form-control" id="cidade" name="cidade" readonly>
</div>

<div class="col-md-1">
    <label for="estado" class="form-label">UF</label>
    <input type="text" class="form-control" id="estado" name="estado" readonly>
</div>
```

## Funcionalidades

### Indicador de Carregamento
- Mostra um ícone de carregamento no campo CEP durante a busca
- Animação de rotação para indicar que a busca está em andamento

### Tratamento de Erros
- Exibe alerta se o CEP não for encontrado
- Exibe alerta em caso de erro na requisição
- Habilita campos manualmente em caso de erro

### Máscara de CEP
- Aplica máscara automática no formato 00000-000
- Remove caracteres não numéricos antes da busca

### Foco Automático
- Após o preenchimento automático, o foco vai para o campo "Número"
- Facilita o fluxo de preenchimento para o usuário

## API Utilizada

- **URL**: `https://viacep.com.br/ws/{cep}/json/`
- **Método**: GET
- **Formato**: JSON
- **Gratuita**: Sim, sem necessidade de API key

### Exemplo de Resposta

```json
{
  "cep": "01001-000",
  "logradouro": "Praça da Sé",
  "complemento": "lado ímpar",
  "bairro": "Sé",
  "localidade": "São Paulo",
  "uf": "SP",
  "ibge": "3550308",
  "gia": "1004",
  "ddd": "11",
  "siafi": "7107"
}
```

## Compatibilidade

- **Navegadores**: Todos os navegadores modernos
- **Dependências**: jQuery (para máscaras), Bootstrap Icons (para ícones)
- **Framework**: Laravel 8+

## Manutenção

### Atualizações
- O script é reutilizável e pode ser facilmente atualizado
- Mudanças no CSS afetam todos os formulários automaticamente

### Debug
- Logs de erro são exibidos no console do navegador
- Alertas informativos para o usuário em caso de problemas

## Considerações de Segurança

- Utiliza HTTPS para comunicação com a API
- Validação de entrada no lado cliente e servidor
- Sanitização de dados antes do envio 
