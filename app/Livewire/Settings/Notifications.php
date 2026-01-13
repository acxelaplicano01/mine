<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.cuenta')]
class Notifications extends Component
{
    public function render()
    {
        return view('livewire.settings.notifications');
    }
}
