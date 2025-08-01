<x-filament-panels::page>
{{$this->setTotalPendingAuctions()}}
{{$this->setTotalAuctions()}}
{{$this->setTotalCompletedAuctions()}}
<div>
    @livewire(\App\Livewire\PledgeStatOverview::class)
</div>
<x-filament::tabs>
    @foreach($this->getTabs() as $tabKey => $tab)
    <x-filament::tabs.item
        :key="$tabKey"
        :badge="$tabKey == 1 ? $this->totalPendingAuctions : ($tabKey == 0 ? $this->totalAuctions : $this->totalCompletedAuctions)"
        :badge-color="$tab == 'All' ? 'gray' : ($tab == 'Pending Auctions' ? 'warning' : 'success')"
        :active="$this->activeTab === $tabKey"
        wire:click="$set('activeTab', {{$tabKey}})">
        {{$tab}}
    </x-filament::tabs.item>
    @endforeach
</x-filament::tabs>
{{$this->table}}
</x-filament-panels::page>
