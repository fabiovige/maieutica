/**
 * Helper para criar gráficos de radar padronizados
 * Usado nas páginas de análise e overview
 */

/**
 * Opções padrão para gráficos de radar do sistema
 * Escala 0-3 com labels textuais OU 0-100 com percentuais
 * @param {boolean} showPercentageInTooltip - Se true, exibe percentual ao invés de nota no tooltip
 * @param {boolean} usePercentageScale - Se true, usa escala 0-100 ao invés de 0-3
 */
function getRadarChartOptions(showPercentageInTooltip = false, usePercentageScale = false) {
    if (usePercentageScale) {
        // Escala em percentual (0-100)
        return {
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += context.parsed.r.toFixed(1) + '%';
                            return label;
                        }
                    }
                },
                datalabels: {
                    color: '#000',
                    backgroundColor: 'rgba(255, 255, 255, 0.7)',
                    borderRadius: 4,
                    font: {
                        weight: 'bold',
                        size: 11
                    },
                    formatter: function(value) {
                        return value.toFixed(1) + '%';
                    },
                    padding: 4
                }
            },
            scales: {
                r: {
                    suggestedMin: 0,
                    suggestedMax: 100,
                    ticks: {
                        stepSize: 25,
                        callback: function (value) {
                            if (value === 0) return '0% - Não observado';
                            if (value === 33.33 || value === 25) return '33% - Não desenvolvido';
                            if (value === 66.67 || value === 50) return '67% - Em desenvolvimento';
                            if (value === 75) return '75%';
                            if (value === 100) return '100% - Desenvolvido';
                            return value + '%';
                        }
                    }
                }
            }
        };
    } else {
        // Escala 0-3 (original)
        return {
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }

                            // Se showPercentageInTooltip = true, converte para percentual
                            if (showPercentageInTooltip) {
                                const percentage = noteScaleToPercentage(context.parsed.r);
                                label += percentage.toFixed(1) + '%';
                            } else {
                                // Formata o valor com 1 casa decimal (nota 0-3)
                                label += context.parsed.r.toFixed(1);
                            }

                            return label;
                        }
                    }
                },
                datalabels: {
                    color: '#000',
                    backgroundColor: 'rgba(255, 255, 255, 0.7)',
                    borderRadius: 4,
                    font: {
                        weight: 'bold',
                        size: 11
                    },
                    formatter: function(value) {
                        // Se showPercentageInTooltip = true, exibe percentual no label
                        if (showPercentageInTooltip) {
                            const percentage = noteScaleToPercentage(value);
                            return percentage.toFixed(1) + '%';
                        } else {
                            return value.toFixed(1);
                        }
                    },
                    padding: 4
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
}

/**
 * Cria um gráfico de radar padronizado
 * @param {string} canvasId - ID do elemento canvas
 * @param {Array} labels - Labels para os eixos do radar
 * @param {Array} datasets - Datasets do Chart.js (pode conter múltiplos datasets)
 * @param {boolean} showPercentageInTooltip - Se true, exibe percentual ao invés de nota no tooltip
 * @param {boolean} usePercentageScale - Se true, usa escala 0-100 ao invés de 0-3
 * @returns {Chart} - Instância do Chart.js
 */
function createRadarChart(canvasId, labels, datasets, showPercentageInTooltip = false, usePercentageScale = false) {
    const ctx = document.getElementById(canvasId).getContext('2d');

    return new Chart(ctx, {
        type: 'radar',
        data: {
            labels: labels,
            datasets: datasets
        },
        options: getRadarChartOptions(showPercentageInTooltip, usePercentageScale)
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
