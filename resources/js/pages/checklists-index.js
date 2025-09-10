window.openDateModal = function() {
    const dateModal = new bootstrap.Modal(document.getElementById('dateModal'));
    document.getElementById('retroactiveDate').value = '';
    document.getElementById('checklistTypeAtual').checked = true;
    document.getElementById('retroactiveDateGroup').style.display = 'none';
    dateModal.show();
};

window.createChecklistWithDate = function(date, button) {
    button.disabled = true;
    const buttonContent = button.innerHTML;
    button.innerHTML = `
        <span class="d-flex align-items-center">
            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            Criando...
        </span>
    `;

    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let bodyData = {
        kid_id: window.kidId,
        level: 4
    };
    
    if (date) {
        bodyData.created_at = date;
    }
    
    fetch(window.checklistStoreRoute, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify(bodyData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                button.disabled = false;
                button.innerHTML = buttonContent;
                alert('Erro ao criar checklist: ' + (data.error || 'Erro desconhecido'));
            }
        })
        .catch(error => {
            button.disabled = false;
            button.innerHTML = buttonContent;
            alert('Erro ao criar checklist: ' + error.message);
        });
};

document.addEventListener('DOMContentLoaded', function() {
    const checklistTypeAtual = document.getElementById('checklistTypeAtual');
    const checklistTypeRetro = document.getElementById('checklistTypeRetro');
    const confirmDateBtn = document.getElementById('confirmDateBtn');
    const retroactiveDateGroup = document.getElementById('retroactiveDateGroup');

    if (checklistTypeAtual) {
        checklistTypeAtual.addEventListener('change', function() {
            if (retroactiveDateGroup) {
                retroactiveDateGroup.style.display = 'none';
            }
        });
    }
    
    if (checklistTypeRetro) {
        checklistTypeRetro.addEventListener('change', function() {
            if (retroactiveDateGroup) {
                retroactiveDateGroup.style.display = 'block';
            }
        });
    }

    if (confirmDateBtn) {
        confirmDateBtn.addEventListener('click', function() {
            const checkedType = document.querySelector('input[name="checklistType"]:checked');
            if (!checkedType) return;
            
            const type = checkedType.value;
            if (type === 'retro') {
                const dateInput = document.getElementById('retroactiveDate');
                const date = dateInput ? dateInput.value : null;
                if (!date) {
                    alert('Por favor, selecione uma data.');
                    return;
                }
                window.createChecklistWithDate(date, this);
            } else {
                window.createChecklistWithDate(null, this);
            }
        });
    }
});