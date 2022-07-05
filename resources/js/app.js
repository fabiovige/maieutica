require('./bootstrap');

const Swal = require("sweetalert2");

const Chart = require('chart.js');

import 'jquery-ui/ui/widgets/datepicker.js';

$.datepicker.regional['pt-BR'] = {
    closeText: 'Fechar',
    prevText: '&#x3c;Anterior',
    nextText: 'Pr&oacute;ximo&#x3e;',
    currentText: 'Hoje',
    monthNames: ['Janeiro','Fevereiro','Mar&ccedil;o','Abril','Maio','Junho',
        'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
    monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun',
        'Jul','Ago','Set','Out','Nov','Dez'],
    dayNames: ['Domingo','Segunda-feira','Ter&ccedil;a-feira','Quarta-feira','Quinta-feira','Sexta-feira','Sabado'],
    dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sab'],
    dayNamesMin: ['Dom','Seg','Ter','Qua','Qui','Sex','Sab'],
    weekHeader: 'Sm',
    dateFormat: 'dd/mm/yy',
    firstDay: 0,
    isRTL: false,
    showMonthAfterYear: false,
    yearSuffix: ''};
$.datepicker.setDefaults($.datepicker.regional['pt-BR']);

$('.datepicker').datepicker();

$('div.alert').delay(4000).fadeOut(500);

$('.form-delete').click(function (e) {
    e.preventDefault();
    Swal.fire({
        title: 'Tem certeza?',
        text: "Ao confirmar o registro será enviado para lixeira!",
        icon: 'warning',
        showCancelButton: true,
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sim, confirmar!'
    }).then((result) => {
        if (result.isConfirmed) {
            let formDelete = document.querySelectorAll('form')[1];
            console.log(formDelete);
            formDelete.submit();
        }
    });
});
