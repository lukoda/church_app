@php
   use Illuminate\Http\Request;
   $record = $_REQUEST['record'] ?? $this->record;
@endphp
<div>
   <x-filament-panels::page>

   {{$this->setRecord($record)}}
   {{$this->setAnnouncementDocuments($record)}}
   {{$this->makeInfolist}}
   <x-filament-actions::modals />
   </x-filament-panels::page>
</div>