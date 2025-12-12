/**
 * App eCommerce Ticket List
 */

'use strict';

document.addEventListener('DOMContentLoaded', function () {
    var dt_ticket_list_table = document.querySelector('.datatables-ticket-list');

    // DataTable para Tickets
    if (dt_ticket_list_table) {
        var dt_ticket = new DataTable(dt_ticket_list_table, {
            ajax: {
                url: 'ajax/tickets.ajax.php',
                type: 'POST',
                data: { accion: 'mostrarTickets' }
            },
            columns: [
                { data: 'id' },
                { data: 'titulo' },
                { data: 'descripcion' },
                { data: 'estado' },
                {
                    data: 'acciones',
                    render: function (data, type, row) {
                        return `
                            <div class="d-flex justify-content-center gap-2">
                                <a href="index.php?ruta=consultar-ticket&idTicket=${row.id}" class="btn btn-sm btn-info">
                                    <i class="bx bx-search"></i> Consultar
                                </a>
                                <button class="btn btn-sm btn-primary btnEditarTicket" idTicket="${row.id}" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEditTicket">
                                    <i class="bx bx-edit"></i> Editar
                                </button>
                                <button class="btn btn-sm btn-danger btnEliminarTicket" idTicket="${row.id}">
                                    <i class="bx bx-trash"></i> Eliminar
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            columnDefs: [
                {
                    targets: 0,
                    className: 'control',
                    orderable: false,
                    render: function () {
                        return '';
                    }
                }
            ],
            order: [1, 'asc'],
            language: {
                paginate: {
                    next: '<i class="icon-base bx bx-chevron-right"></i>',
                    previous: '<i class="icon-base bx bx-chevron-left"></i>'
                }
            }
        });
    }

    // Manejar eventos de botones después de cargar datos
    $(dt_ticket_list_table).on('click', '.btnEditarTicket', function () {
        var idTicket = $(this).attr('idTicket');

        // Cargar datos del ticket para editar
        $.ajax({
            url: 'ajax/tickets.ajax.php',
            method: 'POST',
            data: { idTicket: idTicket },
            dataType: 'json',
            success: function (respuesta) {
                $('#idTicket').val(respuesta['id']);
                $('#editarTitulo').val(respuesta['titulo']);
                $('#editarDescripcion').val(respuesta['descripcion']);
                $('#editarEstado').val(respuesta['estado']);
            }
        });
    });

    $(dt_ticket_list_table).on('click', '.btnEliminarTicket', function () {
        var idTicket = $(this).attr('idTicket');

        Swal.fire({
            title: '¿Está seguro de borrar el ticket?',
            text: '¡Si no lo está puede cancelar la acción!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, borrar ticket!'
        }).then(function (result) {
            if (result.value) {
                window.location = 'index.php?ruta=tickets&idTicket=' + idTicket;
            }
        });
    });

    // Cargar comentarios si estamos en la consulta de un ticket
    const ticketId = document.querySelector('input[name="ticketId"]')?.value;
    if (ticketId) {
        // Cargar comentarios y archivos
        function cargarComentariosYArchivos() {
            $.ajax({
                url: 'ajax/comentarios.ajax.php',
                method: 'POST',
                data: { ticketId: ticketId },
                dataType: 'json',
                success: function (respuesta) {
                    let html = '';
                    if (respuesta && Array.isArray(respuesta)) {
                        respuesta.forEach(comentario => {
                            const archivos = comentario.archivos || []; // Asegurarse de que archivos esté definido
                            html += `
                                <div class="mb-3">
                                    <strong>${comentario.nombre_usuario}</strong>
                                    <p>${comentario.contenido}</p>
                                    <small>${comentario.fecha_creacion}</small>
                                    ${archivos.length > 0 ? '<strong>Archivos:</strong>' : ''}
                                    <ul>
                                        ${archivos.map(archivo => `
                                            <li>
                                                <a href="${archivo.ruta}" target="_blank">${archivo.nombre}</a>
                                                <button class="btn btn-sm btn-danger btnEliminarArchivo" data-id="${archivo.id}">Eliminar</button>
                                            </li>
                                        `).join('')}
                                    </ul>
                                </div>
                            `;
                        });
                    } else {
                        html = '<p>No hay comentarios ni archivos para este ticket.</p>';
                    }
                    document.getElementById('comentarios-lista').innerHTML = html;
                },
                error: function (xhr, status, error) {
                    console.error('Error al cargar los comentarios y archivos:', error);
                    document.getElementById('comentarios-lista').innerHTML = '<p>Error al cargar los comentarios y archivos.</p>';
                }
            });
        }

        cargarComentariosYArchivos();

        // Cargar comentarios y archivos al cambiar a la pestaña de comentarios
        document.getElementById('comentarios-tab').addEventListener('click', function () {
            cargarComentariosYArchivos();
        });

        // Agregar comentario con archivo
        $('#formAgregarComentario').on('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            $.ajax({
                url: 'ajax/comentarios.ajax.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (respuesta) {
                    if (respuesta.trim() === "ok") {
                        $('#contenidoComentario').val('');
                        $('#archivoComentario').val('');
                        $('#esPrivado').prop('checked', false);
                        cargarComentariosYArchivos();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo agregar el comentario o subir el archivo.'
                        });
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error al agregar el comentario:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Hubo un problema al intentar agregar el comentario.'
                    });
                }
            });
        });

        // Eliminar archivo
        $(document).on('click', '.btnEliminarArchivo', function () {
            const archivoId = $(this).data('id');

            Swal.fire({
                title: '¿Está seguro de eliminar este archivo?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'ajax/archivos.ajax.php',
                        method: 'POST',
                        data: { idArchivo: archivoId, accion: 'eliminar' },
                        success: function (respuesta) {
                            if (respuesta.trim() === "ok") {
                                cargarComentariosYArchivos();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'No se pudo eliminar el archivo.'
                                });
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error('Error al eliminar el archivo:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Hubo un problema al intentar eliminar el archivo.'
                            });
                        }
                    });
                }
            });
        });
    }
});