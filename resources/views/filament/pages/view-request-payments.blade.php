@php
   use Illuminate\Http\Request;
   $record = $_REQUEST['record'] ?? $this->record;
@endphp
<x-filament-panels::page>
{{ $this->setRecord($record) }}
{{ $this->setUnverifiedPledges() }}
{{ $this->setVerifiedPledges() }}
{{ $this->setVerifiedAmountPledges() }}
{{ $this->setUnVerifiedAmountPledges() }}
<div>
    @livewire(\App\Livewire\BeneficiaryRequestsOverview::class, ['record' => $this->record])
</div>
<x-filament::tabs>
    @foreach($this->getTabs() as $tabKey => $tab)
    <x-filament::tabs.item
        :key="$tabKey"
        :badge="$tabKey == 0 ? $this->verifiedPledges : ($tabKey == 1 ? $this->unverifiedPledges : ($tabKey == 2 ? $this->unVerifiedAmountPledges : $this->verifiedAmountPledges))"
        :active="$this->activeTab === $tabKey"
        wire:click="$set('activeTab', {{$tabKey}})">
        {{$tab}}
    </x-filament::tabs.item>
    @endforeach
</x-filament::tabs>
{{$this->table}}
</x-filament-panels::page>
