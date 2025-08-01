<x-filament-panels::page>
    {{$this->setThisWeekAnnouncements()}}
    {{$this->setLastWeekAnnouncements()}}
    {{$this->setThisMonthAnnouncements()}}
<x-filament::tabs>
    @foreach($this->getTabs() as $tabKey => $tab)
    <x-filament::tabs.item
        :key="$tabKey"
        :badge="$tabKey == 1 ? $this->lastWeekAnnouncements : ($tabKey == 0 ? $this->thisWeekAnnouncements : $this->thisMonthAnnouncements)"
        :active="$this->activeTab === $tabKey"
        wire:click="$set('activeTab', {{$tabKey}})">
        {{$tab}}
    </x-filament::tabs.item>
    @endforeach
</x-filament::tabs>
{{$this->table}}
</x-filament-panels::page>
