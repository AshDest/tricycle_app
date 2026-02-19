<!-- sidebar @s -->
<div class="app-sidebar">
    <div class="sidebar-brand">
        <a href="{{ route('dashboard') }}">
            <span class="brand-icon">
                <i class="fas fa-motorcycle"></i>
            </span>
            <span class="brand-text">Tricycle<span class="text-primary">App</span></span>
        </a>
    </div>

    <div class="sidebar-content">
        <ul class="sidebar-menu">
            {{-- Dashboard - All Users --}}
            <li class="sidebar-heading">Menu Principal</li>
            <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Tableau de bord</span>
                </a>
            </li>

            {{-- Admin Menu --}}
            @role('admin')
            <li class="sidebar-heading">Administration</li>
            <li class="has-submenu {{ request()->is('admin/motards*') ? 'active' : '' }}">
                <a href="#" class="toggle-submenu">
                    <i class="bi bi-people"></i>
                    <span>Motards</span>
                    <i class="bi bi-chevron-down submenu-icon"></i>
                </a>
                <ul class="submenu">
                    <li><a href="{{ route('admin.motards.index') }}">Liste des motards</a></li>
                    <li><a href="{{ route('admin.motards.create') }}">Ajouter un motard</a></li>
                </ul>
            </li>
            <li class="has-submenu {{ request()->is('admin/motos*') ? 'active' : '' }}">
                <a href="#" class="toggle-submenu">
                    <i class="bi bi-bicycle"></i>
                    <span>Motos</span>
                    <i class="bi bi-chevron-down submenu-icon"></i>
                </a>
                <ul class="submenu">
                    <li><a href="{{ route('admin.motos.index') }}">Liste des motos</a></li>
                    <li><a href="{{ route('admin.motos.maintenance-list') }}"><i class="bi bi-tools me-1"></i>Maintenances Prévues</a></li>
                    <li><a href="{{ route('admin.motos.create') }}">Ajouter une moto</a></li>
                </ul>
            </li>
            <li class="has-submenu {{ request()->is('admin/proprietaires*') ? 'active' : '' }}">
                <a href="#" class="toggle-submenu">
                    <i class="bi bi-building"></i>
                    <span>Propri&eacute;taires</span>
                    <i class="bi bi-chevron-down submenu-icon"></i>
                </a>
                <ul class="submenu">
                    <li><a href="{{ route('admin.proprietaires.index') }}">Liste</a></li>
                    <li><a href="{{ route('admin.proprietaires.create') }}">Ajouter</a></li>
                </ul>
            </li>
            <li class="has-submenu {{ request()->is('admin/caissiers*') ? 'active' : '' }}">
                <a href="#" class="toggle-submenu">
                    <i class="bi bi-cash-coin"></i>
                    <span>Caissiers</span>
                    <i class="bi bi-chevron-down submenu-icon"></i>
                </a>
                <ul class="submenu">
                    <li><a href="{{ route('admin.caissiers.index') }}">Liste</a></li>
                    <li><a href="{{ route('admin.caissiers.create') }}">Ajouter</a></li>
                </ul>
            </li>
            <li class="has-submenu {{ request()->is('admin/collecteurs*') ? 'active' : '' }}">
                <a href="#" class="toggle-submenu">
                    <i class="bi bi-geo-alt"></i>
                    <span>Collecteurs</span>
                    <i class="bi bi-chevron-down submenu-icon"></i>
                </a>
                <ul class="submenu">
                    <li><a href="{{ route('admin.collecteurs.index') }}">Liste</a></li>
                    <li><a href="{{ route('admin.collecteurs.create') }}">Ajouter</a></li>
                </ul>
            </li>

            <li class="sidebar-heading">Finances</li>
            <li class="{{ request()->is('admin/versements*') ? 'active' : '' }}">
                <a href="{{ route('admin.versements.index') }}">
                    <i class="bi bi-cash-stack"></i>
                    <span>Versements</span>
                </a>
            </li>
            <li class="{{ request()->is('admin/payments*') ? 'active' : '' }}">
                <a href="{{ route('admin.payments.index') }}">
                    <i class="bi bi-wallet2"></i>
                    <span>Paiements Propri&eacute;taires</span>
                </a>
            </li>

            <li class="sidebar-heading">Collectes</li>
            <li class="{{ request()->is('admin/tournees*') ? 'active' : '' }}">
                <a href="{{ route('admin.tournees.index') }}">
                    <i class="bi bi-calendar-event"></i>
                    <span>Tourn&eacute;es</span>
                </a>
            </li>
            <li class="{{ request()->is('admin/zones*') ? 'active' : '' }}">
                <a href="{{ route('admin.zones.index') }}">
                    <i class="bi bi-map"></i>
                    <span>Zones</span>
                </a>
            </li>

            <li class="sidebar-heading">Technique</li>
            <li class="{{ request()->is('admin/maintenances*') ? 'active' : '' }}">
                <a href="{{ route('admin.maintenances.index') }}">
                    <i class="bi bi-tools"></i>
                    <span>Maintenances</span>
                </a>
            </li>
            <li class="{{ request()->is('admin/accidents*') ? 'active' : '' }}">
                <a href="{{ route('admin.accidents.index') }}">
                    <i class="bi bi-exclamation-triangle"></i>
                    <span>Accidents</span>
                </a>
            </li>

            <li class="sidebar-heading">Rapports</li>
            <li class="has-submenu {{ request()->is('admin/reports*') ? 'active' : '' }}">
                <a href="#" class="toggle-submenu">
                    <i class="bi bi-bar-chart-line"></i>
                    <span>Rapports</span>
                    <i class="bi bi-chevron-down submenu-icon"></i>
                </a>
                <ul class="submenu">
                    <li><a href="{{ route('admin.reports.daily') }}">Rapport Quotidien</a></li>
                    <li><a href="{{ route('admin.reports.weekly') }}">Rapport Hebdomadaire</a></li>
                    <li><a href="{{ route('admin.reports.monthly') }}">Rapport Mensuel</a></li>
                </ul>
            </li>

            <li class="sidebar-heading">Param&egrave;tres</li>
            <li class="{{ request()->is('admin/users*') ? 'active' : '' }}">
                <a href="{{ route('admin.users.index') }}">
                    <i class="bi bi-people-fill"></i>
                    <span>Utilisateurs</span>
                </a>
            </li>
            <li class="{{ request()->is('admin/settings*') ? 'active' : '' }}">
                <a href="{{ route('admin.settings.index') }}">
                    <i class="bi bi-gear"></i>
                    <span>Configuration</span>
                </a>
            </li>
            @endrole

            {{-- Supervisor Menu (OKAMI) --}}
            @role('supervisor')
            <li class="sidebar-heading">Supervision OKAMI</li>
            <li class="{{ request()->is('supervisor/motards*') ? 'active' : '' }}">
                <a href="{{ route('supervisor.motards.index') }}">
                    <i class="bi bi-people"></i>
                    <span>Motards</span>
                </a>
            </li>
            <li class="{{ request()->is('supervisor/motos*') ? 'active' : '' }}">
                <a href="{{ route('supervisor.motos.index') }}">
                    <i class="bi bi-bicycle"></i>
                    <span>Motos</span>
                </a>
            </li>
            <li class="{{ request()->is('supervisor/versements*') ? 'active' : '' }}">
                <a href="{{ route('supervisor.versements.index') }}">
                    <i class="bi bi-cash-stack"></i>
                    <span>Versements</span>
                </a>
            </li>
            <li class="sidebar-heading">Maintenance &amp; Accidents</li>
            <li class="{{ request()->is('supervisor/maintenances') && !request()->is('supervisor/maintenances/prochaines') ? 'active' : '' }}">
                <a href="{{ route('supervisor.maintenances.index') }}">
                    <i class="bi bi-tools"></i>
                    <span>Maintenances</span>
                </a>
            </li>
            <li class="{{ request()->is('supervisor/maintenances/prochaines') ? 'active' : '' }}">
                <a href="{{ route('supervisor.maintenances.prochaines') }}">
                    <i class="bi bi-calendar-event text-warning"></i>
                    <span>Prochaines Maintenances</span>
                </a>
            </li>
            <li class="{{ request()->is('supervisor/accidents*') ? 'active' : '' }}">
                <a href="{{ route('supervisor.accidents.index') }}">
                    <i class="bi bi-exclamation-triangle"></i>
                    <span>Accidents</span>
                </a>
            </li>
            <li class="sidebar-heading">Propri&eacute;taires</li>
            <li class="{{ request()->is('supervisor/proprietaires') ? 'active' : '' }}">
                <a href="{{ route('supervisor.proprietaires.index') }}">
                    <i class="bi bi-building"></i>
                    <span>Liste Propri&eacute;taires</span>
                </a>
            </li>
            <li class="{{ request()->is('supervisor/proprietaires/create') ? 'active' : '' }}">
                <a href="{{ route('supervisor.proprietaires.create') }}">
                    <i class="bi bi-person-plus"></i>
                    <span>Nouveau Propri&eacute;taire</span>
                </a>
            </li>
            <li class="sidebar-heading">Paiements Propri&eacute;taires</li>
            <li class="{{ request()->is('supervisor/payments') ? 'active' : '' }}">
                <a href="{{ route('supervisor.payments.index') }}">
                    <i class="bi bi-wallet2"></i>
                    <span>Gestion Paiements</span>
                </a>
            </li>
            <li class="{{ request()->is('supervisor/payments/create') ? 'active' : '' }}">
                <a href="{{ route('supervisor.payments.create') }}">
                    <i class="bi bi-plus-circle"></i>
                    <span>Nouvelle Demande</span>
                </a>
            </li>
            <li class="has-submenu {{ request()->is('supervisor/reports*') ? 'active' : '' }}">
                <a href="#" class="toggle-submenu">
                    <i class="bi bi-bar-chart-line"></i>
                    <span>Rapports</span>
                    <i class="bi bi-chevron-down submenu-icon"></i>
                </a>
                <ul class="submenu">
                    <li><a href="{{ route('supervisor.reports.daily') }}">Quotidien</a></li>
                    <li><a href="{{ route('supervisor.reports.weekly') }}">Hebdomadaire</a></li>
                    <li><a href="{{ route('supervisor.reports.monthly') }}">Mensuel</a></li>
                </ul>
            </li>
            @endrole

            {{-- Owner Menu --}}
            @role('owner')
            <li class="sidebar-heading">Mon Espace</li>
            <li class="{{ request()->is('owner/motos*') ? 'active' : '' }}">
                <a href="{{ route('owner.motos.index') }}">
                    <i class="bi bi-bicycle"></i>
                    <span>Mes Motos</span>
                </a>
            </li>
            <li class="{{ request()->is('owner/versements*') ? 'active' : '' }}">
                <a href="{{ route('owner.versements.index') }}">
                    <i class="bi bi-cash-stack"></i>
                    <span>Versements</span>
                </a>
            </li>
            <li class="{{ request()->is('owner/payments*') ? 'active' : '' }}">
                <a href="{{ route('owner.payments.index') }}">
                    <i class="bi bi-wallet2"></i>
                    <span>Mes Paiements</span>
                </a>
            </li>
            <li class="{{ request()->is('owner/reports*') ? 'active' : '' }}">
                <a href="{{ route('owner.reports.index') }}">
                    <i class="bi bi-file-earmark-text"></i>
                    <span>Relev&eacute;s</span>
                </a>
            </li>
            @endrole

            {{-- Driver Menu --}}
            @role('driver')
            <li class="sidebar-heading">Mon Espace</li>
            <li class="{{ request()->is('driver/statut*') ? 'active' : '' }}">
                <a href="{{ route('driver.statut') }}">
                    <i class="bi bi-person-check"></i>
                    <span>Mon Statut</span>
                </a>
            </li>
            <li class="{{ request()->is('driver/historique*') ? 'active' : '' }}">
                <a href="{{ route('driver.historique') }}">
                    <i class="bi bi-clock-history"></i>
                    <span>Historique Versements</span>
                </a>
            </li>
            <li class="{{ request()->is('driver/accidents*') ? 'active' : '' }}">
                <a href="{{ route('driver.accidents.create') }}">
                    <i class="bi bi-exclamation-triangle"></i>
                    <span>D&eacute;clarer un Accident</span>
                </a>
            </li>
            @endrole

            {{-- Cashier Menu --}}
            @role('cashier')
            <li class="sidebar-heading">Caisse</li>
            <li class="{{ request()->is('cashier/versements*') ? 'active' : '' }}">
                <a href="{{ route('cashier.versements.index') }}">
                    <i class="bi bi-cash-stack"></i>
                    <span>Versements du jour</span>
                </a>
            </li>
            <li class="{{ request()->is('cashier/nouveau*') ? 'active' : '' }}">
                <a href="{{ route('cashier.versements.create') }}">
                    <i class="bi bi-plus-circle"></i>
                    <span>Nouveau Versement</span>
                </a>
            </li>
            <li class="{{ request()->is('cashier/solde*') ? 'active' : '' }}">
                <a href="{{ route('cashier.solde') }}">
                    <i class="bi bi-coin"></i>
                    <span>Mon Solde</span>
                </a>
            </li>
            <li class="sidebar-heading">D&eacute;p&ocirc;ts</li>
            <li class="{{ request()->is('cashier/depot') ? 'active' : '' }}">
                <a href="{{ route('cashier.depot') }}">
                    <i class="bi bi-box-arrow-up"></i>
                    <span>D&eacute;poser au Collecteur</span>
                </a>
            </li>
            <li class="{{ request()->is('cashier/depots/historique*') ? 'active' : '' }}">
                <a href="{{ route('cashier.depots.historique') }}">
                    <i class="bi bi-clock-history"></i>
                    <span>Historique D&eacute;p&ocirc;ts</span>
                </a>
            </li>
            @endrole

            {{-- Collector Menu --}}
            @role('collector')
            <li class="sidebar-heading">Collectes</li>
            <li class="{{ request()->is('collector/tournee*') ? 'active' : '' }}">
                <a href="{{ route('collector.tournee.index') }}">
                    <i class="bi bi-calendar-event"></i>
                    <span>Ma Tourn&eacute;e du Jour</span>
                </a>
            </li>
            <li class="{{ request()->is('collector/collectes*') ? 'active' : '' }}">
                <a href="{{ route('collector.collectes.index') }}">
                    <i class="bi bi-list-check"></i>
                    <span>Mes Collectes</span>
                </a>
            </li>
            <li class="{{ request()->is('collector/depots*') ? 'active' : '' }}">
                <a href="{{ route('collector.depots.index') }}">
                    <i class="bi bi-box-arrow-in-down"></i>
                    <span>D&eacute;p&ocirc;ts Caissiers</span>
                </a>
            </li>
            <li class="sidebar-heading">Paiements</li>
            <li class="{{ request()->is('collector/payments*') ? 'active' : '' }}">
                <a href="{{ route('collector.payments.index') }}">
                    <i class="bi bi-wallet2"></i>
                    <span>Demandes &agrave; Traiter</span>
                </a>
            </li>
            <li class="{{ request()->is('collector/proprietaires*') ? 'active' : '' }}">
                <a href="{{ route('collector.proprietaires.index') }}">
                    <i class="bi bi-people"></i>
                    <span>Solde Propri&eacute;taires</span>
                </a>
            </li>
            <li class="{{ request()->is('collector/historique*') ? 'active' : '' }}">
                <a href="{{ route('collector.historique') }}">
                    <i class="bi bi-clock-history"></i>
                    <span>Historique</span>
                </a>
            </li>
            @endrole
        </ul>
    </div>
</div>
<!-- sidebar @e -->
