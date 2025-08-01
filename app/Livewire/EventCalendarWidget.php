<?php

namespace App\Livewire;

use Filament\Widgets\Widget;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class EventCalendarWidget extends FullCalendarWidget
{
    // protected static string $view = 'livewire.event-calendar-widget';

    public function fetchEvents(array $fetchInfo): array
    {
        // You can use $fetchInfo to filter events by date.
        // This method should return an array of event-like objects. See: https://github.com/saade/filament-fullcalendar/blob/3.x/#returning-events
        // You can also return an array of EventData objects. See: https://github.com/saade/filament-fullcalendar/blob/3.x/#the-eventdata-class
        return [];
    }
}
