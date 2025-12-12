/**
 * Page User List - Adaptado para sistema helpdesk
 */

'use strict';

// Datatable (js)
document.addEventListener('DOMContentLoaded', function (e) {
  let borderColor, bodyBg, headingColor;

  if (config) {
    borderColor = config.colors.borderColor;
    bodyBg = config.colors.bodyBg;
    headingColor = config.colors.headingColor;
  }

  // Variable declaration for table
  const dt_user_table = document.querySelector('.datatables-users');
  
  // Definir estados para usuarios - Usar valores numéricos para evitar confusiones
  const statusObj = {
    1: { title: 'Activo', class: 'bg-label-success' },
    0: { title: 'Inactivo', class: 'bg-label-danger' }
  };

  // Definir iconos para perfiles
  const perfilIconos = {
    'Administrador': '<i class="icon-base bx bx-crown text-danger me-2"></i>',
    'Tecnico': '<i class="icon-base bx bx-wrench text-warning me-2"></i>',
    'Cliente': '<i class="icon-base bx bx-user text-success me-2"></i>'
  };

  // Variable global para la tabla
  let dt_user;

  // Función simple para gestionar un único backdrop
  function gestionarBackdrop() {
    // Eliminar todos los backdrops excepto el último
    const backdrops = document.querySelectorAll('.modal-backdrop, .offcanvas-backdrop');
    
    // Si hay más de un backdrop, eliminar todos menos el último
    if (backdrops.length > 1) {
      for (let i = 0; i < backdrops.length - 1; i++) {
        backdrops[i].remove();
      }
    }
  }

  // Users datatable
  if (dt_user_table) {
    dt_user = new DataTable(dt_user_table, {
      ajax: {
        url: 'ajax/usuarios.ajax.php',
        type: 'POST',
        data: function(d) {
          return { accion: 'mostrarUsuarios' }
        }
      },
      columns: [
        // Columnas según los datos devueltos del servidor
        { data: '' }, // Columna vacía para responsive
        { data: 'usuario' },
        { data: 'perfil' },
        { data: 'documento' },
        { data: 'email' },
        { data: 'estado' },
        { data: 'acciones' }
      ],
      columnDefs: [
        {
          // Para Responsive
          className: 'control',
          searchable: false,
          orderable: false,
          responsivePriority: 2,
          targets: 0,
          render: function (data, type, full, meta) {
            return '';
          }
        },
        {
          // Usuario ya viene formateado con avatar desde el servidor
          targets: 1,
          responsivePriority: 1
        },
        {
          // Perfil - Añadir iconos
          targets: 2,
          render: function (data, type, row, meta) {
            let icono = perfilIconos[data] || '<i class="icon-base bx bx-user text-primary me-2"></i>';
            return "<span class='text-truncate d-flex align-items-center text-heading'>" + 
                   icono + data + "</span>";
          }
        },
        {
          // Acciones - Agregar una clase especial para responsive
          targets: -1,
          className: 'actions-column' // Clase especial para identificar la columna de acciones
        }
      ],
      order: [[1, 'asc']],
      // Nuevo layout personalizado basado en categorías
      layout: {
        topStart: {
          rowClass: 'row m-3 my-0 justify-content-between',
          features: [
            {
              search: {
                placeholder: 'Buscar usuario',
                text: '_INPUT_'
              }
            }
          ]
        },
        topEnd: {
          rowClass: 'row m-3 my-0 justify-content-between',
          features: {
            pageLength: {
              menu: [10, 25, 50, 100],
              text: '_MENU_'
            },
            buttons: [
              {
                text: '<i class="icon-base bx bx-plus icon-sm me-0 me-sm-2"></i><span class="d-none d-sm-inline-block">Agregar Usuario</span>',
                className: 'add-new btn btn-primary',
                attr: {
                  'data-bs-toggle': 'offcanvas',
                  'data-bs-target': '#offcanvasAddUser'
                }
              }
            ]
          }
        },
        bottomStart: {
          rowClass: 'row mx-3 justify-content-between',
          features: ['info']
        },
        bottomEnd: {
          paging: {
            firstLast: false
          }
        }
      },
      language: {
        sLengthMenu: '_MENU_',
        search: '',
        searchPlaceholder: 'Buscar Usuario',
        info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
        paginate: {
          next: '<i class="icon-base bx bx-chevron-right scaleX-n1-rtl icon-18px"></i>',
          previous: '<i class="icon-base bx bx-chevron-left scaleX-n1-rtl icon-18px"></i>'
        },
        emptyTable: "No hay datos disponibles",
        zeroRecords: "No se encontraron registros coincidentes"
      },
      // Configuración MEJORADA para responsive - Mantener el modal pero con funcionalidad completa
      responsive: {
        details: {
          display: DataTable.Responsive.display.modal({
            header: function (row) {
              const data = row.data();
              return 'Detalles de usuario';
            }
          }),
          type: 'column',
          renderer: function (api, rowIdx, columns) {
            const data = columns
              .map(function (col) {
                return col.title !== '' && col.title !== undefined
                  ? `<tr data-dt-row="${col.rowIndex}" data-dt-column="${col.columnIndex}">
                      <td>${col.title}:</td>
                      <td>${col.data}</td>
                    </tr>`
                  : '';
              })
              .join('');

            return `<div class="table-responsive"><table class="table"><tbody>${data}</tbody></table></div>`;
          }
        }
      },
      initComplete: function () {
        const api = this.api();

        // Añadir el título a la tabla
        $('.head-label').html('<h5 class="card-title mb-0">Lista de Usuarios</h5>');

        // Filtrar por perfil
        const perfilSelect = document.createElement('select');
        perfilSelect.id = 'UserRole';
        perfilSelect.className = 'form-select text-capitalize';
        perfilSelect.innerHTML = '<option value="">Seleccionar Perfil</option>';
        document.querySelector('.user_role').appendChild(perfilSelect);

        // Obtener valores únicos para el filtro de perfil
        const perfiles = new Set();
        api.column(2).data().each(function(data) {
          const perfil = $(data).text().trim();
          if (perfil) perfiles.add(perfil);
        });
        
        // Añadir opciones al select
        perfiles.forEach(perfil => {
          const option = document.createElement('option');
          option.value = perfil;
          option.textContent = perfil;
          perfilSelect.appendChild(option);
        });

        perfilSelect.addEventListener('change', function() {
          const val = this.value;
          api.column(2).search(val ? val : '', true, false).draw();
        });

        // Filtrar por estado
        const estadoSelect = document.createElement('select');
        estadoSelect.id = 'FilterTransaction';
        estadoSelect.className = 'form-select text-capitalized';
        estadoSelect.innerHTML = '<option value="">Seleccionar Estado</option><option value="Activo">Activo</option><option value="Inactivo">Inactivo</option>';
        document.querySelector('.user_status').appendChild(estadoSelect);

        estadoSelect.addEventListener('change', function() {
          const val = this.value;
          api.column(5).search(val ? val : '', true, false).draw();
        });
      },
      drawCallback: function() {
        // Delegar eventos directamente al documento para manejar los elementos del modal
        delegateEvents();
      }
    });

    // Función para delegación de eventos - funciona en la vista normal y en el modal
    function delegateEvents() {
      // Eliminar previamente todos los event listeners para evitar duplicados
      $(document).off('click', '.btnEliminarUsuario, .btnActivar, .btnEditarUsuario');

      $(document).on('click', '.btnEliminarUsuario', function(e) {
        e.preventDefault();
        const idUsuario = $(this).attr('idUsuario');
        
        Swal.fire({
          title: '¿Está seguro de borrar el usuario?',
          text: "¡Esta acción no se puede revertir!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          cancelButtonText: 'Cancelar',
          confirmButtonText: 'Sí, borrar usuario!'
        }).then((result) => {
          if (result.isConfirmed) {
            // Si estamos en un modal de DataTables, lo cerramos primero
            const $modal = $(this).closest('.dtr-bs-modal');
            if ($modal.length) {
              $modal.modal('hide');
            }
            
            // Procedemos con la eliminación vía AJAX
            fetch(`ajax/usuarios.ajax.php?accion=borrar&idUsuario=${idUsuario}`, {
              method: 'GET'
            })
            .then(response => response.json())
            .then(data => {
              if (data.status === "ok") {
                // Recargar la tabla en lugar de intentar manipular filas directamente
                dt_user.ajax.reload(null, false);
                
                Swal.fire(
                  '¡Eliminado!',
                  'El usuario ha sido eliminado.',
                  'success'
                );
              } else {
                Swal.fire(
                  'Error',
                  'Hubo un problema al eliminar el usuario.',
                  'error'
                );
              }
            })
            .catch(error => {
              console.error('Error:', error);
              Swal.fire({
                icon: 'error',
                title: '¡Error!',
                text: 'Hubo un problema al procesar la solicitud',
                confirmButtonText: 'Aceptar'
              });
            });
          }
        });
      });
      
      $(document).on('click', '.btnActivar', function(e) {
        e.preventDefault();
        const idUsuario = $(this).attr('idUsuario');
        const estadoUsuario = $(this).attr('estadoUsuario');
        
        Swal.fire({
          title: '¿Estás seguro?',
          text: estadoUsuario == 1 ? "El usuario será activado" : "El usuario será desactivado",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Sí, confirmar',
          cancelButtonText: 'Cancelar'
        }).then((result) => {
          if (result.isConfirmed) {
            // Si estamos en un modal de DataTables, lo cerramos primero
            const $modal = $(this).closest('.dtr-bs-modal');
            if ($modal.length) {
              $modal.modal('hide');
            }
            
            // Procedemos con la activación/desactivación vía AJAX
            fetch('ajax/usuarios.ajax.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
              },
              body: 'activarId=' + idUsuario + '&activarUsuario=' + estadoUsuario
            })
            .then(response => response.json())
            .then(data => {
              if (data.status === "ok") {
                // Recargar los datos de la tabla
                dt_user.ajax.reload(null, false);
                
                Swal.fire(
                  'Completado',
                  'El estado del usuario ha sido actualizado.',
                  'success'
                );
              } else {
                Swal.fire({
                  icon: 'error',
                  title: '¡Error!',
                  text: 'Hubo un error al cambiar el estado del usuario',
                  confirmButtonText: 'Aceptar'
                });
              }
            })
            .catch(error => {
              console.error('Error:', error);
              Swal.fire({
                icon: 'error',
                title: '¡Error!',
                text: 'Hubo un error al cambiar el estado del usuario',
                confirmButtonText: 'Aceptar'
              });
            });
          }
        });
      });
      
      // Manejador para el botón de editar - versión simplificada
      $(document).on('click', '.btnEditarUsuario', function(e) {
        e.preventDefault();
        const idUsuario = $(this).attr('idUsuario');
        
        // Si estamos en un modal, lo cerramos primero
        const $modal = $(this).closest('.dtr-bs-modal');
        if ($modal.length) {
          $modal.modal('hide');
          $('.modal-backdrop').remove(); // Eliminar backdrop del modal
        }
        
        // Cargar los datos del usuario
        cargarDatosUsuario(idUsuario);
      });
      
      function cargarDatosUsuario(idUsuario) {
        // Cargar los datos del usuario para edición
        fetch('ajax/usuarios.ajax.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: 'idUsuario=' + idUsuario
        })
        .then(response => response.json())
        .then(data => {
          if (data) {
            document.getElementById('idUsuario').value = data.id;
            document.getElementById('editarNombre').value = data.nombre;
            document.getElementById('editarApellido').value = data.apellido;
            document.getElementById('editarUsuario').value = data.usuario;
            document.getElementById('editarDocumento').value = data.documento;
            document.getElementById('editarEmail').value = data.email;
            document.getElementById('editarTelefono').value = data.telefono || '';
            document.getElementById('editarDireccion').value = data.direccion || '';
            document.getElementById('editarFechaNacimiento').value = data.fecha_nacimiento || '';
            document.getElementById('editarPerfil').value = data.perfil;
            document.getElementById('passwordActual').value = data.password;
            
            if(data.foto_perfil) {
              document.getElementById('userAvatar').src = data.foto_perfil;
            } else {
              document.getElementById('userAvatar').src = "vistas/assets/img/avatars/default.jpg";
            }
            
            document.getElementById('fotoActual').value = data.foto_perfil || '';
            
            // Mostrar el offcanvas - código simplificado
            const offcanvasEl = document.getElementById('offcanvasEditUser');
            const offcanvas = new bootstrap.Offcanvas(offcanvasEl);
            
            // Asegurar que solo haya un backdrop
            gestionarBackdrop();
            
            // Mostrar el offcanvas
            offcanvas.show();
          }
        })
        .catch(error => {
          console.error('Error:', error);
          Swal.fire({
            icon: 'error',
            title: '¡Error!',
            text: 'Hubo un error al cargar los datos del usuario',
            confirmButtonText: 'Aceptar'
          });
        });
      }
    }

    // Aplicar delegación de eventos inmediatamente
    delegateEvents();

    // Aplicar clases CSS adicionales para mejorar la apariencia visual
    setTimeout(() => {
      const elementsToModify = [
        { selector: '.dt-buttons .btn', classToRemove: 'btn-secondary' },
        { selector: '.dt-search .form-control', classToRemove: 'form-control-sm', classToAdd: 'ms-0' },
        { selector: '.dt-search', classToAdd: 'mb-0 mb-md-6' },
        { selector: '.dt-length .form-select', classToRemove: 'form-select-sm' },
        { selector: '.dt-layout-table', classToRemove: 'row mt-2', classToAdd: 'border-top' },
        { selector: '.dt-layout-start', classToAdd: 'px-3 mt-0' },
        { selector: '.dt-layout-end', classToAdd: 'px-3 column-gap-2 mt-0 mb-md-0 mb-4' },
        { selector: '.dt-layout-full', classToAdd: 'table-responsive' }
      ];

      elementsToModify.forEach(({ selector, classToRemove, classToAdd }) => {
        document.querySelectorAll(selector).forEach(element => {
          if (classToRemove) {
            classToRemove.split(' ').forEach(className => element.classList.remove(className));
          }
          if (classToAdd) {
            classToAdd.split(' ').forEach(className => element.classList.add(className));
          }
        });
      });
    }, 100);
  }

  // Manejo del formulario para crear usuario mediante AJAX
  const addNewUserForm = document.getElementById('addNewUserForm');
  if (addNewUserForm) {
    addNewUserForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      if (!this.checkValidity()) {
        e.stopPropagation();
        this.classList.add('was-validated');
        return;
      }
      
      // Crear FormData para enviar el formulario completo con archivos
      const formData = new FormData(this);
      const datos = Object.fromEntries(formData);
      
      // Si hay una foto, procesarla para incluirla como base64
      const fotoInput = document.getElementById('nuevaFoto');
      if (fotoInput && fotoInput.files.length > 0) {
        const reader = new FileReader();
        reader.onload = function(e) {
          datos.nuevaFotoBase64 = e.target.result;
          enviarDatos(datos);
        };
        reader.readAsDataURL(fotoInput.files[0]);
      } else {
        enviarDatos(datos);
      }
      
      function enviarDatos(datos) {
        // Mostrar indicador de carga
        document.getElementById('btnGuardarUsuario').disabled = true;
        document.getElementById('btnGuardarUsuario').innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...';
        
        // Enviar datos mediante fetch
        fetch('ajax/usuarios.ajax.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: 'datosUsuario=' + encodeURIComponent(JSON.stringify(datos))
        })
        .then(response => {
          if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
          }
          return response.json();
        })
        .then(data => {
          // Restaurar botón
          document.getElementById('btnGuardarUsuario').disabled = false;
          document.getElementById('btnGuardarUsuario').innerHTML = 'Guardar';
          
          if (data && data.status === "ok") {
            // Cerrar el offcanvas correctamente
            const offcanvas = document.getElementById('offcanvasAddUser');
            const bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvas);
            if (bsOffcanvas) {
              bsOffcanvas.hide();
            }
            
            // Asegurar un solo backdrop
            gestionarBackdrop();
            
            // Resetear formulario
            addNewUserForm.reset();
            
            // Mostrar mensaje de éxito
            Swal.fire({
              icon: 'success',
              title: '¡Éxito!',
              text: data.mensaje || 'Usuario creado correctamente',
              confirmButtonText: 'Aceptar'
            }).then(() => {
              // Recargar datos de la tabla sin recargar la página
              dt_user.ajax.reload();
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: '¡Error!',
              text: (data && data.mensaje) ? data.mensaje : 'Error desconocido al crear el usuario',
              confirmButtonText: 'Aceptar'
            });
          }
        })
        .catch(error => {
          console.error('Error:', error);
          document.getElementById('btnGuardarUsuario').disabled = false;
          document.getElementById('btnGuardarUsuario').innerHTML = 'Guardar';
          
          Swal.fire({
            icon: 'error',
            title: '¡Error!',
            text: 'Hubo un problema al procesar la solicitud',
            confirmButtonText: 'Aceptar'
          });
        });
      }
    });
  }

  // Validación del formulario para editar usuario con AJAX
  const editUserForm = document.querySelector('.edit-user-form');
  if (editUserForm) {
    editUserForm.addEventListener('submit', function(e) {
      e.preventDefault(); // Prevenir envío tradicional
      
      if (!this.checkValidity()) {
        e.stopPropagation();
        this.classList.add('was-validated');
        return;
      }
      
      // Crear FormData para obtener todos los campos del formulario
      const formData = new FormData(this);
      
      // Procesar imagen si existe
      const fotoInput = document.getElementById('editarFoto');
      if (fotoInput && fotoInput.files.length > 0) {
        // Procesar con FormData (ya está incluido)
        enviarDatosEdicion(formData);
      } else {
        enviarDatosEdicion(formData);
      }
    });
    
    function enviarDatosEdicion(formData) {
      // Mostrar cargando en botón
      const btnSubmit = editUserForm.querySelector('button[type="submit"]');
      const originalText = btnSubmit.innerHTML;
      btnSubmit.disabled = true;
      btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Actualizando...';
      
      fetch('ajax/usuarios.ajax.php', {
        method: 'POST',
        body: formData
      })
      .then(response => {
        if (!response.ok) {
          throw new Error('Error en la respuesta del servidor');
        }
        return response.text();
      })
      .then(text => {
        // Restaurar botón
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = originalText;
        
        // Cerrar el offcanvas correctamente
        const offcanvas = document.getElementById('offcanvasEditUser');
        const bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvas);
        if (bsOffcanvas) {
          bsOffcanvas.hide();
        }
        
        // Asegurar un solo backdrop
        gestionarBackdrop();
        
        // Verificar si hay mensajes de éxito en la respuesta
        if (text.includes('success')) {
          Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: 'Usuario actualizado correctamente',
            confirmButtonText: 'Aceptar'
          }).then(() => {
            // Recargar la tabla
            dt_user.ajax.reload();
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudo actualizar el usuario',
            confirmButtonText: 'Aceptar'
          });
        }
      })
      .catch(error => {
        console.error('Error:', error);
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = originalText;
        
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Hubo un problema al procesar la solicitud',
          confirmButtonText: 'Aceptar'
        });
      });
    }
  }

  // Preview de imagen para edición
  const editarFotoInput = document.getElementById('editarFoto');
  const userAvatar = document.getElementById('userAvatar');
  
  if (editarFotoInput && userAvatar) {
    editarFotoInput.addEventListener('change', function(e) {
      if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
          userAvatar.src = e.target.result;
        };
        reader.readAsDataURL(this.files[0]);
      }
    });
  }
  
  // Evento para manejar el cierre de modales
  $(document).on('hidden.bs.modal', function () {
    gestionarBackdrop();
  });

  // Evento para manejar el cierre de offcanvas
  $(document).on('hidden.bs.offcanvas', function () {
    gestionarBackdrop();
  });

  // Evento para manejar la apertura de modales
  $(document).on('shown.bs.modal', function () {
    gestionarBackdrop();
  });

  // Evento para manejar la apertura de offcanvas
  $(document).on('shown.bs.offcanvas', function (e) {
    gestionarBackdrop();
    
    // Forzar el z-index del offcanvas mostrado
    const offcanvasElement = e.target;
    if (offcanvasElement) {
      offcanvasElement.style.zIndex = '1080'; // Mayor que el navbar (1075)
    }
  });

  // Reemplazar los estilos CSS existentes con valores mejorados
  const style = document.createElement('style');
  style.textContent = `
    /* Asegurar z-index correcto para que offcanvas aparezca sobre navbar */
    .modal-backdrop, .offcanvas-backdrop {
      z-index: 1040;
    }
    .modal {
      z-index: 1045;
    }
    .offcanvas {
      z-index: 1080 !important; /* Mayor que el navbar (1075) */
    }
    
    /* Mejoras visuales para botones en modal */
    .dtr-bs-modal .modal-body .btn {
      margin: 2px;
    }
    
    /* Asegurar que el backdrop del offcanvas esté por debajo del navbar */
    .layout-navbar {
      position: relative;
      z-index: 1060;
    }

    /* Estilos adicionales para corregir problema específico del navbar */
    body:not(.modal-open) .layout-content-navbar .layout-navbar {
      z-index: 1075; /* Mantener el z-index original del navbar */
    }
  `;
  document.head.appendChild(style);
});