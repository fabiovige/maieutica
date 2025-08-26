<?php

namespace App\View\Components;

use Illuminate\View\Component;

class AddressForm extends Component
{
    public $cep;
    public $logradouro;
    public $numero;
    public $complemento;
    public $bairro;
    public $cidade;
    public $estado;
    public $required;
    public $title;

    public function __construct(
        $cep = '',
        $logradouro = '',
        $numero = '',
        $complemento = '',
        $bairro = '',
        $cidade = '',
        $estado = '',
        $required = false,
        $title = 'Endereço'
    ) {
        $this->cep = $cep;
        $this->logradouro = $logradouro;
        $this->numero = $numero;
        $this->complemento = $complemento;
        $this->bairro = $bairro;
        $this->cidade = $cidade;
        $this->estado = $estado;
        $this->required = $required;
        $this->title = $title;
    }

    public function render()
    {
        return view('components.address-form');
    }
}