window.addEventListener('load', function () {
    var validate = function (element, error) {
        var messages = [];

        try {
            var rules = JSON.parse(element.dataset.rules || '[]'),
                isValid = true,
                rule, i, n, el, el2, op, value;

            for (i = 0, n = rules.length; i < n; i++) {
                rule = rules[i];

                if (rule.length >= 4) {
                    el = document.querySelector('[name="' + rule[0] + '"]');

                    if (el) {

                        op = rule[1];
                        value = rule[2];

                        switch (op) {
                            case '$': // Query selector
                                el2 = document.querySelector(value);
                                isValid = (el2 && el2.value === el.value);
                                break;

                            case '': // empty
                                isValid = !el.value;
                                break;

                            case '!': // not empty
                                isValid = !!el.value;
                                break;

                            case '[-]': // checked
                                isValid = !!el.checked;
                                break;

                            case '![-]': // not checked
                                isValid = !el.checked;
                                break;

                            case '>=': // min length
                                isValid = el.value.length >= value;
                                break;

                            case '<=': // max length
                                isValid = el.value.length <= value;
                                break;

                            case '#': // regex
                                isValid = (new RegExp(value, 'g').test(el.value));
                                break;

                            case '==': // equal or not
                            default:

                                if ('!' === el.value.substring(0, 1)) {
                                    isValid = el.value.substring(1).split(',').indexOf(value) === -1
                                } else {
                                    isValid = el.value.split(',').indexOf(value) !== -1
                                }

                                break;
                        }

                        if (!isValid) {
                            messages.push(rule[3]);
                        }
                    }
                }
            }

        } catch (err) {
            console.log(err);
        }

        if (messages.length) {
            element.classList.add('invalid-field');
            error.innerHTML = '<small class="form-text text-danger uk-form-controls-text uk-text-danger">' + messages.join('<br/>') + '</small>';
        } else {
            element.classList.remove('invalid-field');
        }
    };

    window.setUpPhpFormJsValidation = function () {
        document.querySelectorAll('form:not(.has-validated)').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                form.classList.add('has-validated');
                form.querySelectorAll('[data-rules]').forEach(function (element) {
                    var error = document.getElementById(element.id + '-errors-msg');

                    if (!error) {
                        error = document.createElement('div');
                        error.setAttribute('id', element.id + '-errors-msg');
                        element.parentNode.insertBefore(error, element);
                        error.parentNode.insertBefore(element, error);
                    }

                    error.innerHTML = '';
                    validate(element, error);
                });

                if (form.querySelector('.invalid-field')) {
                    e.preventDefault();
                }
            });
        });
    };

    setUpPhpFormJsValidation();
    document.addEventListener('DOMNodeInserted', setUpPhpFormJsValidation);
});