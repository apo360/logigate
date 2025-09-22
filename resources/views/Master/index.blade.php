@extends('layouts.master.app')

@section('title', 'Painel Administrativo')

@section('content')
    <nav class="flex mb-4" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1">
            <li class="inline-flex items-center">
                <a href="#" class="text-gray-700 hover:text-gray-900">Home</a>
            </li>
            <li>
                <span class="mx-2">/</span>
            </li>
            <li class="inline-flex items-center">
                <span class="text-gray-500">Dashboard</span>
            </li>
        </ol>
    </nav>
    <div class="bg-white p-6 rounded-lg shadow">
        <!-- #cards for statistic -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700">Total de Empresas</h3>
                        <p class="text-gray-500">{{ count($empresa)}}</p>
                    </div>
                    <i class="fas fa-building text-3xl text-blue-500"></i>
                </div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700">Total de Países</h3>
                        <p class="text-gray-500">10</p>
                    </div>
                    <i class="fas fa-globe text-3xl text-blue-500"></i>
                </div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="flex items
                -center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700">Total de Portos</h3>
                        <p class="text-gray-500">10</p>
                    </div>
                    <i class="fas fa-anchor text-3xl text-blue-500"></i>
                </div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="flex items
                -center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700">Total de Produtos</h3>
                        <p class="text-gray-500">10</p>
                    </div>
                    <i class="fas fa-box
                    text-3xl text-blue-500"></i>
                </div>
            </div>
        </div>
        <!-- #other statistics (graphic, bar, pie, etc) -->
    </div>
@endsection

@push('scripts')
    <script>
        console.log('Script personalizado carregado.');
    </script>
@endpush