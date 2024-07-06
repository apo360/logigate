<!-- resources/views/components/import-form.blade.php -->

<div class="container">
    <div class="card">
        @if(session('status'))
            <p>{{ session('status') }}</p>
        @endif

        <form action="{{ $route }}" method="POST" enctype="multipart/form-data" style="margin-bottom: 20px;">
            @csrf

            <p class="">{{ $texto }}</p>

            <x-input type="file" name="file" accept=".csv,.xlsx" required />
            <button type="submit" class="mt-4 btn btn-success">{{ $buttonText }}</button>
        </form>
    </div>
</div>