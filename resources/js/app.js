import './bootstrap';
import toastr from 'toastr';

window.toastr = toastr;

toastr.options = {
    "closeButton": true,
    "progressBar": true,
    "positionClass": "toast-bottom-right",
    "timeOut": "3000",
};

/**
 * Livewire 3 já carrega Alpine automaticamente.
 * Não iniciar Alpine aqui para evitar:
 * "Detected multiple instances of Alpine running"
 */
