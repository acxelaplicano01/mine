<?php

namespace App\Livewire\Content;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.collapsable')]
class Files extends Component
{
    public function render()
    {
        return view('livewire.content.files');
    }
}
