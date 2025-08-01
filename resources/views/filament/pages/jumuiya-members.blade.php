<x-filament-panels::page>
{{ $this->setVerifiedMembers() }}
{{ $this->setAllMembers() }}
{{ $this->setUnverifiedMembers() }}
{{ $this->setNotApprovedMembers() }}
<div>
    @livewire(\App\Livewire\JumuiyaMembersOverview::class)
</div>
<x-filament::tabs>
    @foreach($this->getTabs() as $tabKey => $tab)
    <x-filament::tabs.item
        :key="$tabKey"
        :badge="$tabKey == 0 ? $this->all_members : ($tabKey == 1 ? $this->verified_members : ($tabKey == 2 ? $this->unverified_members : $this->notapproved_members))"
        :active="$this->activeTab === $tabKey"
        wire:click="$set('activeTab', {{$tabKey}})">
        {{$tab}}
    </x-filament::tabs.item>
    @endforeach
</x-filament::tabs>
{{ $this->table }}
</x-filament-panels::page>
