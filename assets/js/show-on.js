window.addEventListener('load', function () {
    var fireShowOn = function (field) {
        try {
            var showOnData = JSON.parse(field.dataset.showOn),
                willShow = true,
                targetField, i, n, condValues, values, first, op;
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
                    condValues = [];

                    if (targetField[0].nodeName === 'INPUT' && -1 !== ['radio', 'checkbox'].indexOf(targetField[0].type)) {
                        condValues = [];
                        targetField.forEach(function () {
                            if (this.checked) {
                                condValues.push(this.value);
                            }
                        });

                    } else if (targetField[0].nodeName === 'SELECT' && targetField[0].multiple) {
                        targetField[0].querySelectorAll('option').forEach(function (option) {
                            if (option.selected) {
                                condValues.push(option.value);
                            }
                        });
                    } else {
                        condValues = targetField[0].value;
                    }

                    switch (showOnData[i].value) {
                        case '': // empty
                            willShow = !condValues.length;
                            break;

                        case '!': // not empty
                            willShow = !!condValues.length;
                            break;

                        case '[-]': // checked
                            willShow = targetField[0].checked;
                            break;

                        case '![-]': // not checked
                            willShow = !targetField[0].checked;
                            break;

                        default:

                            if ('>=' === showOnData[i].value.substring(0, 2)) { // min length
                                willShow = condValues.length >= showOnData[i].value.substring(2);
                            } else if ('<=' === showOnData[i].value.substring(0, 2)) { // max length
                                willShow = condValues.length <= showOnData[i].value.substring(2);
                            } else {
                                first = showOnData[i].value.substring(0, 1);

                                if ('!' === first) {
                                    values = showOnData[i].value.substring(1).split(',');
                                } else {
                                    values = showOnData[i].value.split(',');
                                }

                                for (var j = 0, m = values.length, match; j < m; j++) {
                                    match = condValues === values[j];
                                    willShow = ('!' === first && !match) || ('!' !== first && match);

                                    if (!willShow) {
                                        break;
                                    }
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