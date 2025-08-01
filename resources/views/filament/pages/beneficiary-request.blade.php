<x-filament-panels::page>
    {{$this->setPendingRequests()}}
    {{$this->setPledgedRequests()}}
<x-filament::tabs>
    @foreach($this->getTabs() as $tabKey => $tab)
    <x-filament::tabs.item
        :key="$tabKey"
        :badge="$tabKey == 0 ? $this->pending_beneficiary_requests : $this->pledged_beneficiary_requests"
        :active="$this->activeTab === $tabKey"
        wire:click="$set('activeTab', {{$tabKey}})">
        {{$tab}}
    </x-filament::tabs.item>
    @endforeach
</x-filament::tabs>
{{ $this->table }}
</x-filament-panels::page>
