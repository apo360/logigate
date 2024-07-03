<div class="porto-origem">
    <span class="pais-codigo" data-toggle="tooltip" title="{{ $paisNome }}">
        <span>{{ $paisCodigo }}</span>
        <img src="path/to/flag/{{ $paisCodigo }}.png" alt="{{ $paisCodigo }}" />
    </span>
    {{ $value }}
</div>

<!-- Inclua o JavaScript do Bootstrap Tooltip -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipElements = document.querySelectorAll('[data-toggle="tooltip"]');
        tooltipElements.forEach(function(element) {
            new bootstrap.Tooltip(element);
        });
    });
</script>

<style>
    .porto-origem {
        display: flex;
        align-items: center;
    }

    .pais-codigo {
        margin-left: 5px;
    }

    .pais-codigo img {
        width: 20px;
        height: 20px;
    }
</style>
