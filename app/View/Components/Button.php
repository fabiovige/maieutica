<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Button extends Component
{
    public $href;
    public $type; // button || link
    public $class; // btn-dark
    public $name; // salvar, voltar, cadastrar
    public $icon;

    public function __construct($href = '', $type = '', $class = '', $name = '', $icon = '')
    {
        $this->href = $href;
        $this->type = $type;
        $this->class = $class;
        $this->name = $name;
        $this->icon = $icon;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.button');
    }
}
