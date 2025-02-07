<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\Kid;

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
