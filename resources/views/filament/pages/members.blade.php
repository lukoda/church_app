<x-filament-panels::page>
    @if(auth()->user()->churchMember()->count() > 0)    
    {{$this->makeInfolist()}}
    @endif
</x-filament-panels::page>
