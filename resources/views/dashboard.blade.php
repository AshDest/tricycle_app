<x-dashlite-layout>
    @section('title', 'Tableau de bord')

    {{-- Redirection vers le dashboard approprié selon le rôle --}}
    @role('admin')
        <livewire:admin.dashboard />
    @endrole

    @role('supervisor')
        <livewire:supervisor.dashboard />
    @endrole

    @role('owner')
        <livewire:owner.dashboard />
    @endrole

    @role('driver')
        <livewire:driver.dashboard />
    @endrole

    @role('cashier')
        <livewire:cashier.dashboard />
    @endrole

    @role('collector')
        <livewire:collector.dashboard />
    @endrole
</x-dashlite-layout>

