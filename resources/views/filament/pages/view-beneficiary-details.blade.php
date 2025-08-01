@php
   use Illuminate\Http\Request;
   $record = $_REQUEST['record'] ?? $this->record;
@endphp
<x-filament-panels::page>
{{$this->setRecord($record)}}
{{$this->setDonationDetails()}}
{{$this->setRequestedItems()}}
{{$this->setRequestedItemPledges()}}
{{$this->setRequestedAmountPledges()}}
{{$this->makeInfolist}}
<x-filament::tabs>
    @foreach($this->getTabs() as $tabKey => $tab)
    <x-filament::tabs.item
        :key="$tabKey"
        :badge="$tabKey == 1 ? $this->requested_item_pledges : ($tabKey == 0 ? $this->requested_items : $this->requested_amount_pledges)"
        :active="$this->activeTab === $tabKey"
        wire:click="$set('activeTab', {{$tabKey}})">
        {{$tab}}
    </x-filament::tabs.item>
    @endforeach
</x-filament::tabs>
{{$this->table}}
<x-filament-actions::modals />
</x-filament-panels::page>
