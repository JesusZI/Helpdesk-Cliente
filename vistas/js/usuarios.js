/*=============================================
CARGAR LA TABLA DINÁMICA DE USUARIOS
=============================================*/

$(document).ready(function() {
    let tablaUsuarios = $('.datatables-users').DataTable({
        ajax: {
            url: 'ajax/usuarios.ajax.php',
            type: 'POST',
            data: { accion: 'mostrarUsuarios' },
            dataSrc: 'data'
        },
        columns: [
            { data: 'id' },
            { 
                data: null,
                render: function(data, type, row) {
                    let avatarUrl = row.foto_perfil_url || 'vistas/assets/img/avatars/default.jpg';
                    let nombreCompleto = (row.nombre || '') + ' ' + (row.apellido || '');
                    let usuario = row.usuario || '';
                    return `
                        <div class="d-flex align-items-center">
                            <div class="avatar-wrapper">
                                <img src="${avatarUrl}" alt="Avatar" class="rounded-circle me-3" width="32" height="32" onerror="this.src='vistas/assets/img/avatars/default.jpg'">
                            </div>
                            <div class="d-flex flex-column">
                                <span class="text-heading fw-medium">${nombreCompleto}</span>
                                <span class="text-truncate mb-0 d-none d-sm-block"><small>@${usuario}</small></span>
                            </div>
                        </div>
                    `;
                }
            },
            { data: 'perfil' },
            { data: 'documento' },
            { data: 'email' },
            { data: 'estado' },
            { data: 'acciones', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        dom: '<"row me-2"<"col-md-2"<"me-3"l>><"col-md-10"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-3 mb-md-0"fB>>>t<"row mx-2"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        language: {
            sLengthMenu: '_MENU_',
            search: '',
            searchPlaceholder: 'Buscar..',
            paginate: {
                sFirst: 'Primero',
                sLast: 'Último', 
                sNext: 'Siguiente',
                sPrevious: 'Anterior'
            },
            info: 'Mostrando _START_ a _END_ de _TOTAL_ entradas',
            infoEmpty: 'Mostrando 0 a 0 de 0 entradas',
            emptyTable: 'No hay datos disponibles en la tabla',
            loadingRecords: 'Cargando...',
            processing: 'Procesando...',
            zeroRecords: 'No se encontraron registros coincidentes'
        },
        buttons: [
            {
                text: '<i class="bx bx-plus me-0 me-sm-1"></i><span class="d-none d-sm-inline-block">Agregar Usuario</span>',
                className: 'add-new btn btn-primary',
                attr: {
                    'data-bs-toggle': 'offcanvas',
                    'data-bs-target': '#offcanvasAddUser'
                }
            }
        ],
        responsive: true
    });

    $('#offcanvasAddUser').on('show.bs.offcanvas', function() {
        $('#addNewUserForm')[0].reset();
        $('#addNewUserForm').removeClass('was-validated');
        $('.form-control').removeClass('is-valid is-invalid');
    });

    $('#addNewUserForm').on('submit', function(e) {
        e.preventDefault();
        
        if (this.checkValidity()) {
            let formData = new FormData(this);
            let datos = {};
            
            for (let [key, value] of formData.entries()) {
                datos[key] = value;
            }
            
            // NO PROCESAR IMAGEN AL CREAR USUARIO
            enviarDatosUsuario(datos);
        } else {
            $(this).addClass('was-validated');
        }
    });

    function enviarDatosUsuario(datos) {
        $.ajax({
            url: 'ajax/usuarios.ajax.php',
            method: 'POST',
            data: { datosUsuario: JSON.stringify(datos) },
            dataType: 'json',
            beforeSend: function() {
                $('#btnGuardarUsuario').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Guardando...');
            },
            success: function(response) {
                if (response.status === 'ok') {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: response.mensaje,
                        confirmButtonText: 'Cerrar'
                    }).then(function() {
                        $('#offcanvasAddUser').offcanvas('hide');
                        tablaUsuarios.ajax.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.mensaje,
                        confirmButtonText: 'Cerrar'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error de conexión con el servidor',
                    confirmButtonText: 'Cerrar'
                });
            },
            complete: function() {
                $('#btnGuardarUsuario').prop('disabled', false).html('Guardar');
            }
        });
    }

    $(document).on('click', '.btnEditarUsuario', function() {
        let idUsuario = $(this).attr('idUsuario');
        
        $.ajax({
            url: 'ajax/usuarios.ajax.php',
            method: 'POST',
            data: { idUsuario: idUsuario },
            dataType: 'json',
            success: function(data) {
                if(data && data.id) {
                    $('#idUsuario').val(data.id);
                    $('#editarNombre').val(data.nombre);
                    $('#editarApellido').val(data.apellido);
                    $('#editarUsuario').val(data.usuario);
                    $('#editarDocumento').val(data.documento);
                    $('#editarEmail').val(data.email);
                    $('#editarTelefono').val(data.telefono);
                    $('#editarDireccion').val(data.direccion);
                    $('#editarFechaNacimiento').val(data.fecha_nacimiento);
                    $('#editarPerfil').val(data.perfil);
                    $('#passwordActual').val(data.password);
                    $('#fotoActual').val(data.foto_perfil);
                    
                    if (data.foto_perfil && data.foto_perfil !== '') {
                        $('#userAvatar').attr('src', data.foto_perfil);
                    } else {
                        $('#userAvatar').attr('src', 'vistas/assets/img/avatars/default.jpg');
                    }
                    
                    $('#offcanvasEditUser').offcanvas('show');
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo cargar la información del usuario',
                    confirmButtonText: 'Cerrar'
                });
            }
        });
    });

    $(document).on('click', '.btnActivar', function() {
        let idUsuario = $(this).attr('idUsuario');
        let estadoUsuario = $(this).attr('estadoUsuario');
        
        $.ajax({
            url: 'ajax/usuarios.ajax.php',
            method: 'POST',
            data: {
                activarUsuario: estadoUsuario,
                activarId: idUsuario
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'ok') {
                    tablaUsuarios.ajax.reload();
                    
                    Swal.fire({
                        icon: 'success',
                        title: '¡Actualizado!',
                        text: response.mensaje,
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.mensaje,
                        confirmButtonText: 'Cerrar'
                    });
                }
            }
        });
    });

    $(document).on('click', '.btnEliminarUsuario', function() {
        let idUsuario = $(this).attr('idUsuario');
        
        Swal.fire({
            title: '¿Está seguro de borrar el usuario?',
            text: "¡Esta acción no se puede revertir!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(function(result) {
            if (result.value) {
                $.ajax({
                    url: 'ajax/usuarios.ajax.php?accion=borrar&idUsuario=' + idUsuario,
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'ok') {
                            tablaUsuarios.ajax.reload();
                            
                            Swal.fire({
                                icon: 'success',
                                title: '¡Eliminado!',
                                text: response.mensaje,
                                confirmButtonText: 'Cerrar'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.mensaje,
                                confirmButtonText: 'Cerrar'
                            });
                        }
                    }
                });
            }
        });
    });

    $('#nuevoUsuario').on('blur', function() {
        validarCampoUnico($(this), 'validarUsuario', 'El usuario ya existe');
    });

    $('#nuevoEmail').on('blur', function() {
        validarCampoUnico($(this), 'validarEmail', 'El email ya está registrado');
    });

    $('#nuevoDocumento').on('blur', function() {
        validarCampoUnico($(this), 'validarDocumento', 'El documento ya está registrado');
    });

    function validarCampoUnico(campo, accion, mensaje) {
        let valor = campo.val();
        
        if (valor.length > 0) {
            let data = {};
            data[accion] = valor;
            
            $.ajax({
                url: 'ajax/usuarios.ajax.php',
                method: 'POST',
                data: data,
                dataType: 'json',
                success: function(response) {
                    if (response && response !== false) {
                        campo.addClass('is-invalid').removeClass('is-valid');
                        campo.siblings('.invalid-feedback').text(mensaje);
                    } else {
                        campo.removeClass('is-invalid').addClass('is-valid');
                    }
                }
            });
        }
    }

    $('.form-password-toggle .input-group-text').on('click', function() {
        let input = $(this).siblings('input');
        let icon = $(this).find('i');
        
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('bx-hide').addClass('bx-show');
        } else {
            input.attr('type', 'password');
            icon.removeClass('bx-show').addClass('bx-hide');
        }
    });
});

  function gestionarBackdrop() {
    const backdrops = document.querySelectorAll('.modal-backdrop, .offcanvas-backdrop');
    
    if (backdrops.length > 1) {
      for (let i = 0; i < backdrops.length - 1; i++) {
        backdrops[i].remove();
      }
    }
  }

  $(document).on('hidden.bs.modal', function () {
    gestionarBackdrop();
  });

  $(document).on('hidden.bs.offcanvas', function () {
    gestionarBackdrop();
  });

  $(document).on('shown.bs.modal', function () {
    gestionarBackdrop();
  });

  $(document).on('shown.bs.offcanvas', function (e) {
    gestionarBackdrop();
    
    const offcanvasElement = e.target;
    if (offcanvasElement) {
      offcanvasElement.style.zIndex = '1080';
    }
  });