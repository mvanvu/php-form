window.addEventListener('load', function () {
    var validate = function (element, error) {
        var messages = [];

        try {
            var rules = JSON.parse(element.dataset.formRules || '[]'),
                getValues = function (el) {
                    var result = [],
                        isMultiple = false;

                    if (el.classList.contains('checkbox-list-field-container')) {
                        isMultiple = true;
                        el.querySelectorAll('input[type="checkbox"]:checked').forEach(function (n) {
                            result.push(n.value);
                        });
                    } else if (el.classList.contains('radio-list-field-container')) {
                        result = el.querySelector('input[type="radio"]:checked');
                        result = result ? [result.value] : [];
                    } else if (el.nodeName === 'SELECT') {
                        isMultiple = !!el.multiple;
                        el.querySelectorAll('option:checked').forEach(function (n) {
                            result.push(n.value);
                        });
                    } else if (el.nodeName === 'INPUT' && (el.type === 'checkbox' || el.type === 'radio')) {
                        result = el.checked ? [el.value] : [];
                    } else {
                        result = [el.value];
                    }

                    result.sort();

                    return [result, isMultiple];
                },
                isValid = true,
                rule, i, n, el, el2, op, id, value, values, multiple;

            for (i = 0, n = rules.length; i < n; i++) {
                rule = rules[i];

                if (rule.length >= 4) {
                    id = rule[0];
                    el = document.getElementById(id);

                    if (el) {
                        [values, multiple] = getValues(el);
                        op = rule[1];
                        value = rule[2];

                        switch (op) {
                            case '$': // Confirm
                                el2 = document.getElementById(value);
                                isValid = el2 && JSON.stringify(getValues(el2)[0]) === JSON.stringify(values);
                                break;

                            case '': // empty
                            case '![-]': // not checked
                                isValid = !values.length;
                                break;

                            case '!': // not empty
                            case '[-]': // checked
                                isValid = multiple ? !!values.length : (values.length && values[0].length);
                                break;

                            case '>=': // min length
                                isValid = multiple ? values.length >= value : values[0].length >= value;
                                break;

                            case '<=': // max length
                                isValid = multiple ? values.length <= value : values[0].length <= value;
                                break;

                            case '#': // regex
                                isValid = values.length && (new RegExp(value, 'g').test(values[0]));
                                break;

                            case '==': // equal or not
                            default:

                                if (!values.length) {
                                    isValid = false;
                                } else {
                                    if ('!' === value.substring(0, 1)) {
                                        isValid = values[0] !== value.substring(1);
                                    } else {
                                        isValid = values[0] === value;
                                    }
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
            error.innerHTML = messages.join('<br/>');
            error.parentNode.removeAttribute('hidden');
        } else {
            element.classList.remove('invalid-field');
            error.parentNode.setAttribute('hidden', '');
        }
    };

    window.setUpPhpFormJsValidation = function () {
        document.querySelectorAll('form:not(.has-validated)').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                form.classList.add('has-validated');
                form.querySelectorAll('[data-form-rules]').forEach(function (element) {
                    var error = element.parentNode.querySelector('.errors-msg');
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