var installments = [];
var row = '<tr data-id="{{id}}" style="text-align: center"> <td><input size="3" type="text" name="amount_{{id}}_from" value="{{from}}" class="form-control" /></td> <td><input size="3" type="text" name="amount_{{id}}_to" value="{{to}}" class="form-control" /></td> <td><input size="1" type="text" name="max_{{id}}" value="{{max}}" class="form-control" /></td> <td><a class="remove-installment" href="#" style="font-weight: bold; font-size:20px; text-decoration: none; color: red;">&#8722;</a></td> </tr>';
$(function() {
    var table = '<table class="form"> <thead> <tr> <th>Amount From</th> <th>Amount To</th> <th>Installments</th> <th><a href="#" id="add-installment" style="font-weight: bold; font-size:20px; text-decoration: none; color: green;">&#43;</a></th> </tr> </thead> <tbody></tbody> </table>';

    Mustache.parse(table);
    var renderedTable = Mustache.render(table, {});
    $('#installments-everypay').html(renderedTable);

    var input = $('#everypay-installments').val();
    if (input) {
        installments = JSON.parse(input);
        createElements();
    }

    $('#add-installment').click(function (e) {
        e.preventDefault();
        var maxRows = maxElementIndex();


        Mustache.parse(row);
        var element = {id: maxRows, from: 0, to: 100, max: 12};
        var renderedRow = Mustache.render(row, element);
        $row = $(renderedRow);
        addInstallment($row);
        $row.find('input').change(function (e){
            addInstallment($(this).parent().parent());
        });
        $('#installments-everypay table tbody').append($row);
        $row.find('.remove-installment').click(function (e){
            e.preventDefault();
            removeInstallment($(this).parent().parent());
            $(this).parent().parent().remove();
        });
    });
});

var addInstallment = function (row) {
    var element = {
        id: row.attr('data-id'),
        from: row.find('input[name$="from"]').val(),
        to:  row.find('input[name$="to"]').val(),
        max:  row.find('input[name^="max"]').val(),
    }

    index = elementExists(element.id);
    if (false !== index) {
        installments[index] = element;
    } else {
        installments.push(element);
    }
    $('#everypay-installments').val(JSON.stringify(installments));
};

var removeInstallment = function (row) {
    var index = false;
    var id = row.attr('data-id');
    for (var i = 0, l = installments.length; i < l; i++) {
        if (installments[i].id == id) {
            index = i;
        }
    }

    if (false !== index) {
        installments.splice(index, 1);
    }
    $('#everypay-installments').val(JSON.stringify(installments));
};

var elementExists = function (id) {
    for (var i = 0, l = installments.length; i < l; i++) {
        if (installments[i].id == id) {
            return i;
        }
    }

    return false;
}

var maxElementIndex = function (row) {
    var length = $('#installments-everypay table tbody tr').length;
    if (0 == length) {
        return 1;
    }

    length = $('#installments-everypay table tbody tr:last').attr('data-id');
    length = parseInt(length);

    return length + 1;
}

var createElements = function () {
    Mustache.parse(row);
    for (var i = 0, l = installments.length; i < l; i++) {
        var element = installments[i];
        var renderedRow = Mustache.render(row, element);
        $row = $(renderedRow);
        $row.find('input').change(function (e){
            addInstallment($(this).parent().parent());
        });
        $('#installments-everypay table tbody').append($row);
        $row.find('.remove-installment').click(function (e){
            e.preventDefault();
            removeInstallment($(this).parent().parent());
            $(this).parent().parent().remove();
        });
    }
}
