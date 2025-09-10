require('./bootstrap')

$ = window.$ = window.jQuery = require('jquery')

window.bootstrap = require('bootstrap')

import 'jquery-ui/ui/widgets/datepicker.js'

const mask = require('jquery-mask-plugin')

const Swal = require('sweetalert2')

$.datepicker.regional['pt-BR'] = {
  closeText: 'Fechar',
  prevText: '&#x3c;Anterior',
  nextText: 'Pr&oacute;ximo&#x3e;',
  currentText: 'Hoje',
  monthNames: [
    'Janeiro',
    'Fevereiro',
    'Mar&ccedil;o',
    'Abril',
    'Maio',
    'Junho',
    'Julho',
    'Agosto',
    'Setembro',
    'Outubro',
    'Novembro',
    'Dezembro',
  ],
  monthNamesShort: [
    'Jan',
    'Fev',
    'Mar',
    'Abr',
    'Mai',
    'Jun',
    'Jul',
    'Ago',
    'Set',
    'Out',
    'Nov',
    'Dez',
  ],
  dayNames: [
    'Domingo',
    'Segunda-feira',
    'Ter&ccedil;a-feira',
    'Quarta-feira',
    'Quinta-feira',
    'Sexta-feira',
    'Sabado',
  ],
  dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'],
  dayNamesMin: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'],
  weekHeader: 'Sm',
  dateFormat: 'dd/mm/yy',
  firstDay: 0,
  isRTL: false,
  showMonthAfterYear: false,
  yearSuffix: '',
}
$.datepicker.setDefaults($.datepicker.regional['pt-BR'])

$('.datepicker').datepicker()
$('div.alert').delay(4000).fadeOut(500)

$('.form-delete').click(function (e) {
  e.preventDefault()
  Swal.fire({
    title: 'Tem certeza?',
    text: 'Ao confirmar o registro será enviado para lixeira!',
    icon: 'warning',
    showCancelButton: true,
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Sim, confirmar!',
  }).then(result => {
    if (result.isConfirmed) {
      let formDelete = document.querySelectorAll('form')[2]
      formDelete.submit()
    }
  })
})

$('.cell').mask('(99) 99999-9999')
$('.cpf').mask('999.999.999-02')
$('.cnpj').mask('99.999.999/9999-99')

window.changePagination = function(perPageValue) {
  const urlParams = new URLSearchParams(window.location.search)
  urlParams.set('per_page', perPageValue)
  urlParams.set('page', '1')
  
  const newUrl = window.location.pathname + '?' + urlParams.toString()
  window.location.href = newUrl
}

import { createApp, onMounted } from 'vue'
import Competences from './components/Competences'
import Checklists from './components/Checklists'
import Charts from './components/Charts'
import Planes from './components/Planes'
import Dashboard from './components/Dashboard'
import Select2 from 'vue3-select2-component'
import VueSweetalert2 from 'vue-sweetalert2'
import Resume from './components/Resume'
import Resumekid from './components/Resumekid'
import KidsViewToggle from './components/KidsViewToggle'

const app = createApp({
  setup() {
    //onMounted(getUser)
  },
})
app.use(VueSweetalert2)
app.component('Competences', Competences)
app.component('Checklists', Checklists)
app.component('Charts', Charts)
app.component('Planes', Planes)
app.component('Dashboard', Dashboard)
app.component('Resume', Resume)
app.component('Resumekid', Resumekid)
app.component('KidsViewToggle', KidsViewToggle)
app.component('Select2', Select2)
app.mount('#app')
