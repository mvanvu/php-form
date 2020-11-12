window.addEventListener('load', function () {
    var fireShowOn = function (field) {
        try {
            var showOnData = JSON.parse(field.dataset.showOn),
                willShow = true,
                targetField, i, n, value, values, first, op;
            for (i = 0, n = showOnData.length; i < n; i++) {
                op = showOnData[i].op;

                if ((willShow && op === '|') || (!willShow && op === '&')) {
                    break;
                }

                targetField = document.querySelectorAll('[name="' + showOnData[i].field + '"]');

                if (!targetField.length) {
                    targetField = document.querySelectorAll('[name="' + showOnData[i].field + '[]"]');
                }

                if (targetField.length) {
                    value = null;
                    if (targetField[0].nodeName === 'INPUT' && -1 !== ['radio', 'checkbox'].indexOf(targetField[0].type)) {
                        value = [];
                        targetField.forEach(function () {
                            if (this.checked) {
                                value.push(this.value);
                            }
                        });

                        value = value.join(',');
                    } else {
                        value = targetField[0].value;
                    }

                    switch (showOnData[i].value) {
                        case 'is not empty':
                            willShow = null !== value && !!value.length;
                            break;

                        case 'is empty':
                            willShow = null === value || !value.length;
                            break;

                        case 'is not checked':
                            willShow = !targetField[0].checked;
                            break;

                        case 'is checked':
                            willShow = targetField[0].checked;
                            break;

                        case 'is not selected':
                            willShow = !targetField[0].selected;
                            break;

                        case 'is selected':
                            willShow = targetField[0].selected;
                            break;

                        default:
                            first = showOnData[i].value.substring(0, 1);

                            if ('!' === first) {
                                values = showOnData[i].value.substring(1);

                                if (-1 === values.indexOf(',')) {
                                    willShow = (values !== value);
                                } else {
                                    willShow = (values.split(',').indexOf(value) === -1);
                                }
                            } else {
                                values = showOnData[i].value;

                                if (-1 === values.indexOf(',')) {
                                    willShow = (values === value);
                                } else {
                                    willShow = (values.split(',').indexOf(value) !== -1);
                                }

                            }

                            break;
                    }
                }
            }

            if (window.jQuery) {
                willShow ? window.jQuery(field).slideDown() : window.jQuery(field).slideUp();
            } else {
                if (willShow) {
                    field.removeAttribute('hidden');
                    field.classList.add('on-shown');
                } else {
                    field.setAttribute('hidden', '');
                    field.classList.remove('on-shown');
                }
            }

        } catch (err) {
            console.log(err);
        }
    };

    window.setUpPhpFormJsShowOn = function () {
        var setUpShowOn = function () {
            var fields = document.querySelectorAll('[data-show-on]');

            if (fields.length) {
                fields.forEach(function (field) {
                    fireShowOn(field);
                });
            }
        };

        // Init show on
        setUpShowOn();

        // Init events
        document.querySelectorAll('textarea:not(.show-on-input-handled), input:not(.show-on-input-handled), select:not(.show-on-input-handled)').forEach(function (input) {
            input.classList.add('show-on-input-handled');
            input.addEventListener('change', setUpShowOn);
            input.addEventListener('keyup', setUpShowOn);
        });
    };

    // Run
    setUpPhpFormJsShowOn();
    document.addEventListener('DOMNodeInserted', setUpPhpFormJsShowOn);
});