document.addEventListener('DOMContentLoaded', function () {
    const ticketId = document.querySelector('input[name="ticketId"]').value;

    // Cargar comentarios
    function cargarComentarios() {
        $.ajax({
            url: 'ajax/comentarios.ajax.php',
            method: 'POST',
            data: { ticketId: ticketId },
            dataType: 'json',
            success: function (respuesta) {
                let html = '';
                respuesta.forEach(comentario => {
                    html += `
                        <div class="mb-3">
                            <strong>${comentario.usuario_id}</strong>
                            <p>${comentario.contenido}</p>
                            <small>${comentario.fecha_creacion}</small>
                        </div>
                    `;
                });
                document.getElementById('comentarios-lista').innerHTML = html;
            }
        });
    }

    cargarComentarios();

    // Agregar comentario
    $('#formAgregarComentario').on('submit', function (e) {
        e.preventDefault();
        const datos = $(this).serialize();

        $.ajax({
            url: 'ajax/comentarios.ajax.php',
            method: 'POST',
            data: datos,
            success: function () {
                $('#contenidoComentario').val('');
                $('#esPrivado').prop('checked', false);
                cargarComentarios();
            }
        });
    });
});
