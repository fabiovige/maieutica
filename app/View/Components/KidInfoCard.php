<?php

namespace App\View\Components;

use App\Models\Kid;
use Illuminate\View\Component;

class KidInfoCard extends Component
{
    public $kid;

    public function __construct(Kid $kid)
    {
        $this->kid = $kid;
    }

    public function render()
    {
        return view('components.kid-info-card');
    }
}
