{{--
    Flash messages as toastr popups.
    Controllers use ->with('success' | 'info' | 'warning' | 'error', 'message').
    Form validation errors also get one popup (the field errors stay under each field).
--}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>

@php
    $toasts = [];
    foreach (['success', 'info', 'warning', 'error'] as $type) {
        if (session()->has($type)) {
            $toasts[] = ['type' => $type, 'message' => session($type)];
        }
    }
    if ($errors->any()) {
        $toasts[] = ['type' => 'error', 'message' => $errors->count() === 1
            ? $errors->first()
            : 'Please correct the '.$errors->count().' highlighted fields.'];
    }
@endphp

<script>
    (function () {
        var toasts = @json($toasts);

        // CDN not reachable: fall back to a plain message box so nothing is lost
        if (!window.toastr) {
            window.toastr = {};
            ['success', 'info', 'warning', 'error'].forEach(function (type) {
                window.toastr[type] = function (message) {
                    var box = document.createElement('div');
                    box.className = 'alert ' + type;
                    box.textContent = message;
                    document.querySelector('main').prepend(box);
                };
            });
        } else {
            toastr.options = {
                positionClass: 'toast-top-right',
                closeButton: true,
                progressBar: true,
                newestOnTop: true,
                timeOut: 5000,
                extendedTimeOut: 2000,
                preventDuplicates: true
            };
        }

        toasts.forEach(function (t) { toastr[t.type](t.message); });
    })();
</script>
