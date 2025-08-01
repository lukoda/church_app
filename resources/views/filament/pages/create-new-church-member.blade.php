<x-filament-panels::page>
        <div>
            <form wire:submit="createChurchMember">
                {{ $this->form }}

                <x-filament::button
                    type="submit"
                    wire:target="createChurchMember">
                    Create New Church Member
                </x-filament::button>                
            </form>
            
            <x-filament-actions::modals />
        </div>
</x-filament-panels::page>
