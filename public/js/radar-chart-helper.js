/**
 * Helper para criar gráficos de radar padronizados
 * Usado nas páginas de análise e overview
 */

/**
 * Opções padrão para gráficos de radar do sistema
 * Escala 0-3 com labels textuais
 */
function getRadarChartOptions() {
    return {
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        // Formata o valor com 1 casa decimal
                        label += context.parsed.r.toFixed(1);
                        return label;
                    }
                }
            }
        },
        scales: {
            r: {
                suggestedMin: 0,
                suggestedMax: 3,
                ticks: {
                    stepSize: 1,
                    callback: function (value) {
                        if (value === 0) return 'Não observado';
                        if (value === 1) return 'Não desenvolvido';
                        if (value === 2) return 'Em desenvolvimento';
                        if (value === 3) return 'Desenvolvido';
                        return value;
                    }
                }
            }
        }
    };
}

/**
 * Cria um gráfico de radar padronizado
 * @param {string} canvasId - ID do elemento canvas
 * @param {Array} labels - Labels para os eixos do radar
 * @param {Array} datasets - Datasets do Chart.js (pode conter múltiplos datasets)
 * @returns {Chart} - Instância do Chart.js
 */
function createRadarChart(canvasId, labels, datasets) {
    const ctx = document.getElementById(canvasId).getContext('2d');

    return new Chart(ctx, {
        type: 'radar',
        data: {
            labels: labels,
            datasets: datasets
        },
        options: getRadarChartOptions()
    });
}

/**
 * Converte percentual (0-100) para escala de notas (0-3)
 * @param {number} percentage - Percentual de 0 a 100
 * @returns {number} - Valor na escala 0-3, arredondado para 1 casa decimal
 */
function percentageToNoteScale(percentage) {
    const raw = (percentage / 100) * 3;
    return parseFloat(raw.toFixed(1));
}

/**
 * Converte escala de notas (0-3) para percentual (0-100)
 * @param {number} note - Nota de 0 a 3
 * @returns {number} - Percentual de 0 a 100
 */
function noteScaleToPercentage(note) {
    return (note / 3) * 100;
}

/**
 * Obtém o texto de status baseado na nota
 * @param {number} value - Valor de 0 a 3
 * @returns {string} - Texto do status
 */
function getNoteStatusText(value) {
    if (value === 0) return 'Não observado';
    if (value === 1) return 'Não desenvolvido';
    if (value === 2) return 'Em desenvolvimento';
    if (value === 3) return 'Desenvolvido';
    return '';
}
