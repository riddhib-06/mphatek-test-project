// Sends the calculator form to the server whenever an input changes
// and fills the result box with the response.
(function () {
    var token = document.querySelector('meta[name="csrf-token"]').content;
    var rupees = new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 });

    document.querySelectorAll('form[data-calculator]').forEach(function (form) {
        var resultBox = document.querySelector('.result');
        var errorBox = resultBox.querySelector('[data-error]');
        var timer;

        function show(data) {
            errorBox.textContent = '';
            resultBox.classList.remove('has-error');

            resultBox.querySelectorAll('[data-result]').forEach(function (el) {
                var value = data[el.dataset.result];

                if (value === null || value === undefined) {
                    el.textContent = '-';
                } else if (el.hasAttribute('data-money')) {
                    el.textContent = rupees.format(value);
                } else {
                    el.textContent = value + (el.dataset.suffix || '');
                }
            });
        }

        function showError(message) {
            errorBox.textContent = message;
            resultBox.classList.add('has-error');
        }

        function calculate() {
            fetch(form.action, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body: new FormData(form)
            })
                .then(function (response) {
                    return response.json().then(function (data) {
                        if (!response.ok) throw new Error(data.message || 'Something went wrong.');
                        return data;
                    });
                })
                .then(function (data) {
                    show(data);
                    form.dispatchEvent(new CustomEvent('calculated', { detail: data }));
                })
                .catch(function (error) {
                    showError(error.message);
                });
        }

        form.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(calculate, 300);
        });
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            calculate();
        });

        calculate();
    });
})();
