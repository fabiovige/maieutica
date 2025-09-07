<?php

declare(strict_types=1);

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AddressForm extends Component
{
    public string $cep;
    public string $logradouro;
    public string $numero;
    public string $complemento;
    public string $bairro;
    public string $cidade;
    public string $estado;
    public bool $required;
    public string $title;

    public function __construct(
        ?string $cep = null,
        ?string $logradouro = null,
        ?string $numero = null,
        ?string $complemento = null,
        ?string $bairro = null,
        ?string $cidade = null,
        ?string $estado = null,
        bool $required = false,
        string $title = 'Endereço'
    ) {
        $this->cep = $cep ?? '';
        $this->logradouro = $logradouro ?? '';
        $this->numero = $numero ?? '';
        $this->complemento = $complemento ?? '';
        $this->bairro = $bairro ?? '';
        $this->cidade = $cidade ?? '';
        $this->estado = $estado ?? '';
        $this->required = $required;
        $this->title = $title;
    }

    public function render(): View
    {
        return view('components.address-form');
    }

    public function shouldRenderTitle(): bool
    {
        return !empty(trim($this->title));
    }

    public function getCepFormatted(): string
    {
        $cleanCep = preg_replace('/\D/', '', $this->cep ?? '');
        if (strlen($cleanCep) === 8) {
            return substr($cleanCep, 0, 5) . '-' . substr($cleanCep, 5);
        }
        return $this->cep ?? '';
    }

    public function getRequiredAttribute(): string
    {
        return $this->required ? 'required' : '';
    }

    public function getRequiredIndicator(): string
    {
        return $this->required ? '<span class="text-danger">*</span>' : '';
    }
}