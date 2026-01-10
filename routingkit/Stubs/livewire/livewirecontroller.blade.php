{!! '<?php' !!}

namespace {{ $data['controller']['namespace'] }};
use Livewire\Component;

use Livewire\Attributes\Layout;


class {{ $data['controller']['className'] }} extends Component
{
   
    public function render()
    {
       return view('{{ $data['view']['viewName'] }}');
    }
}