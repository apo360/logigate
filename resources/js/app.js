import './bootstrap';
import Alpine from 'alpinejs';
import toastr from 'toastr';

window.toastr = toastr;

toastr.options = {
    "closeButton": true,
    "progressBar": true,
    "positionClass": "toast-bottom-right",
    "timeOut": "3000",
};


window.Alpine = Alpine
Alpine.start()
