<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ModalDelete extends Component
{
    public $id;

    public $name;

    public function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.modal-delete');
    }
}
