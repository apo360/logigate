@if (session('success'))
    <div {{ $attributes->merge(['class' => 'alert alert-success']) }}>
        {{ session('success') }}
    </div>

    <script>
        toastr.success('{{ session('success') }}');
    </script>
@endif
