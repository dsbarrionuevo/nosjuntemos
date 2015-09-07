$(document).ready(function () {
    $("#lista_dias li label").click(function () {
        var $this = $(this);
        var $checkbox = $this.prev();
        $checkbox.prop("checked", !$checkbox.prop("checked"));
    });
    $("#mensajeValidacion").hide();
    $("#txt_fecha").mask('00/00/0000');

    function campoValido(agrs) {
        agrs.field.removeClass("invalido");
    }
    function campoInvalido(agrs) {
        agrs.field.addClass("invalido");
    }
    function formularioValido(agrs) {
        //otra validacion extra...
        var unCheckado = false;
        var mensajes = [];
        var error = false;
        $("#lista_dias input[type='checkbox']").each(function () {
            if ($(this).is(":checked")) {
                unCheckado = true;
            }
        });
        if (!unCheckado) {
            mensajes.push("Seleccione un día por lo menos");
            error = true;
        }
        if (parseInt($("#txt_hora_fin")) >= parseInt($("#txt_hora_inicio"))) {
            mensajes.push("La hora de inicio debe ser mayor a la hora de fin");
            error = true;
        }
        if (error) {
            var mensaje = "";
            for (var i = 0; i < mensajes.length; i++) {
                mensaje += mensajes[i];
                if (i !== mensajes.length - 1) {
                    mensaje += "<br/>";
                }
            }
            $("#mensajeValidacion").html(mensaje);
            $("#mensajeValidacion").show();
            agrs.event.preventDefault();
        }
    }
    function formularioInvalido(agrs) {
        agrs.event.preventDefault();
    }

    var validador = ValidatorJS.createValidator($("form").eq(0), ValidatorJS.VALIDATE_ON_BLUR,
            {
                validField: campoValido,
                invalidField: campoInvalido,
                validForm: formularioValido,
                invalidForm: formularioInvalido
            });
    ValidatorJS.addCustomValidation("fecha_dd_mm_aaaa", function (campo) {
        //acepta tanto dd-mm-aaaa como dd/mm/aaaa
        return /^(0?[1-9]|[12][0-9]|3[01])[\/\-](0?[1-9]|1[012])[\/\-]\d{4}$/.test(campo.val().trim());
    });
    /*
     VALIDATION_TYPE_REQUIRED
     VALIDATION_TYPE_LENGTH
     VALIDATION_TYPE_NUMBER
     VALIDATION_TYPE_INT
     VALIDATION_TYPE_DECIMAL
     VALIDATION_TYPE_VALUE
     VALIDATION_TYPE_EMAIL
     VALIDATION_TYPE_GROUP
     VALIDATION_TYPE_RADIO_GROUP
     VALIDATION_TYPE_CHECKBOX_GROUP
     VALIDATION_TYPE_CUSTOM
     */
    validador.addValidation($("#txt_nombre"), ValidatorJS.VALIDATION_TYPE_REQUIRED);
    validador.addValidation($("#txt_nombre"), ValidatorJS.VALIDATION_TYPE_LENGTH, {max: 100});
    validador.addValidation($("#txt_lugar"), ValidatorJS.VALIDATION_TYPE_REQUIRED);
    validador.addValidation($("#txt_lugar"), ValidatorJS.VALIDATION_TYPE_LENGTH, {max: 100});
    validador.addValidation($("#txt_fecha"), ValidatorJS.VALIDATION_TYPE_REQUIRED);
    validador.addValidation($("#txt_fecha"), ValidatorJS.VALIDATION_TYPE_CUSTOM, {id: "fecha_dd_mm_aaaa"});
    validador.addValidation($("#txt_hora_inicio"), ValidatorJS.VALIDATION_TYPE_REQUIRED);
    validador.addValidation($("#txt_hora_inicio"), ValidatorJS.VALIDATION_TYPE_INT);
    validador.addValidation($("#txt_hora_inicio"), ValidatorJS.VALIDATION_TYPE_VALUE, {min: 0, max: 24});
    validador.addValidation($("#txt_hora_fin"), ValidatorJS.VALIDATION_TYPE_REQUIRED);
    validador.addValidation($("#txt_hora_fin"), ValidatorJS.VALIDATION_TYPE_INT);
    validador.addValidation($("#txt_hora_fin"), ValidatorJS.VALIDATION_TYPE_CHECKBOX_GROUP, {min: 0, max: 24});

});