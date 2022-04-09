$(document).ready(function () {
    $("#form-contact").validate({
        rules: {
            full_name: "required",
            businnes: "required",
            email: {required: true, email: true},
            phone: {required: true, minlength: 10},
            message: "required"
        },
        messages: {
            full_name: "El campo nombre es requerido.",
            businnes: "El nombre de la empresa es requerido.",
            email: {
                required: "La direccion de email es requerida.",
                email: "El formato del email es invalido, EJ: example@example.com"
            },
            phone: {
                required: "El numero de teléfono es requerido.",
                minlength: "Ingrese un numero de teléfono valido EJ: 1145652798."
            },
            message: "Ingrese un mensaje para enviar."
        },
        errorElement: "span",
        submitHandler: function (form) {
            $.ajax({
                type: "POST",
                url: "php/send",
                data: $(form).serialize(),
                dataType: "json",
                beforeSend:function (){
                    let loader =    '<div class="spinner-border text-primary mx-auto" role="status">\n' +
                                    '  <span class="visually-hidden">Enviando...</span>\n' +
                                    '</div>';
                    $('#form-contact').html(loader)
                }
            }).done(function (data) {
                let message =   '<h2 class="text-primary font-px-md-34"> ' +
                                    'Gracias por contactar a LatSell' +
                                    '<div class="font-px-md-26">Nuestro equipo comercial se estará contactando con Ud. a la mayor brevedad posible.</div>\n' +
                                '</h2>';
                $('#form-contact').html(message)
            });
        },
        errorPlacement: function (error, element) {
            // Add the `invalid-feedback` class to the error element
            error.addClass("invalid-feedback");
            error.insertAfter(element);
        },
        highlight: function (element, errorClass, validClass) {
            $(element).addClass("is-invalid").removeClass("is-valid");
        },
        unhighlight: function (element, errorClass, validClass) {
            $(element).addClass("is-valid").removeClass("is-invalid");
        }
    });
});
