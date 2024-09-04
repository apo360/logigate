<x-app-layout>
    <div class="container_rh" style="padding: 10px;">
        <div class="row">
            <div class="col-md-2">
                <div class="card card-navy">
                    <div class="card-header">
                        <div class="card-title">
                            <span> <i class="fas fa-list"></i> Menu RH </span>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="sidebar">
                            <nav class="mt-2">
                                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                                    <li class="nav-item">
                                        <a href="" class="nav-link">Departamento</a>
                                    </li>
                                    <li class="nav-item">Cargos</li>
                                    <li class="nav-item">Funcionários</li>
                                    <li class="nav-item">Contratos</li>
                                    <li class="nav-item">Abonos</li>
                                    <li class="nav-item">Descontos</li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-10">
                {{ $slot }}
            </div>
        </div>
    </div>
</x-app-layout>