require('./bootstrap');

$ = window.$ = window.jQuery = require('jquery');

import 'jquery-ui/ui/widgets/datepicker.js';

const Swal = require("sweetalert2");
const Chart = require('chart.js');

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

import { createApp, onMounted } from 'vue'
import Competences from './components/Competences'
import Checklists from './components/Checklists'
import Select2 from 'vue3-select2-component';
import VueSweetalert2 from "vue-sweetalert2";

const app = createApp({
    setup() {
        //onMounted(getUser)
    }
});
app.use(VueSweetalert2)
app.component('Competences', Competences)
app.component('Checklists', Checklists)
app.component('Select2', Select2)
app.mount('#app')
