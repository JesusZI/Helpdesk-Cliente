/**
 * App Departamentos List
 */

'use strict';

// Datatable (js)
document.addEventListener('DOMContentLoaded', function (e) {
  var dt_departamentos_list_table = document.querySelector('.datatables-departamentos-list');

  // DataTable para Departamentos
  if (dt_departamentos_list_table) {
    var dt_departamento = new DataTable(dt_departamentos_list_table, {
      ajax: {
        url: 'ajax/departamentos.ajax.php',
        type: 'POST',
        data: function(d) {
          return { accion: 'mostrarDepartamentos' }
        }
      },
      columns: [
        // Columnas según los datos devueltos del servidor
        { data: 'id' },
        { data: 'nombre' },
        { data: 'descripcion' },
        { data: 'actions' }
      ],
      columnDefs: [
        {
          // For Responsive
          className: 'control',
          searchable: false,
          orderable: false,
          responsivePriority: 1,
          targets: 0,
          render: function (data, type, full, meta) {
            return '';
          }
        },
        {
          targets: 1,
          responsivePriority: 2,
          render: function (data, type, full, meta) {
            const name = full['nombre'];
            
            // Crear salida para Departamentos
            const rowOutput = `
              <div class="d-flex align-items-center">
                <div class="d-flex flex-column justify-content-center">
                  <span class="text-heading text-wrap fw-medium">${name}</span>
                </div>
              </div>`;
            return rowOutput;
          }
        },
        {
          // Descripción
          targets: 2,
          responsivePriority: 3,
          render: function (data, type, full, meta) {
            return '<div>' + full['descripcion'] + '</div>';
          }
        },
        {
          // Acciones - Ya está pre-renderizado desde el servidor
          targets: 3,
          title: 'Acciones',
          searchable: false,
          orderable: false
        }
      ],
      order: [1, 'asc'],
      layout: {
        topStart: {
          rowClass: 'row m-3 my-0 justify-content-between',
          features: [
            {
              search: {
                placeholder: 'Buscar departamento',
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
                text: `<i class="icon-base bx bx-plus icon-sm me-0 me-sm-2"></i><span class="d-none d-sm-inline-block">Agregar Departamento</span>`,
                className: 'add-new btn btn-primary',
                attr: {
                  'data-bs-toggle': 'offcanvas',
                  'data-bs-target': '#offcanvasEcommerceDepartamentoList'
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
        paginate: {
          next: '<i class="icon-base bx bx-chevron-right scaleX-n1-rtl icon-18px"></i>',
          previous: '<i class="icon-base bx bx-chevron-left scaleX-n1-rtl icon-18px"></i>'
        }
      },
      // Para popup responsive
      responsive: {
        details: {
          display: DataTable.Responsive.display.modal({
            header: function (row) {
              const data = row.data();
              return 'Detalles de departamento: ' + data['nombre'];
            }
          }),
          type: 'column',
          renderer: function (api, rowIdx, columns) {
            const data = columns
              .map(function (col) {
                return col.title !== ''
                  ? `<tr data-dt-row="${col.rowIndex}" data-dt-column="${col.columnIndex}">
                      <td>${col.title}:</td>
                      <td>${col.data}</td>
                    </tr>`
                  : '';
              })
              .join('');

            if (data) {
              return `<div class="table-responsive"><table class="table"><tbody>${data}</tbody></table></div>`;
            }
            return false;
          }
        }
      }
    });
  }

  // Manejar eventos de botones después de cargar datos
  $(dt_departamentos_list_table).on('click', '.btnEditarDepartamento', function() {
    var idDepartamento = $(this).attr('idDepartamento');
    
    // Cargar datos del departamento para editar
    $.ajax({
      url: "ajax/departamentos.ajax.php",
      method: "POST",
      data: {
        idDepartamento: idDepartamento
      },
      dataType: "json",
      success: function(respuesta) {
        $("#idDepartamento").val(respuesta["id"]);
        $("#editarDepartamento").val(respuesta["nombre"]);
        $("#editarDescripcion").val(respuesta["descripcion"]);
      },
      error: function(xhr, status, error) {
        console.error("Error al cargar datos:", error);
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "No se pudieron cargar los datos del departamento"
        });
      }
    });
  });

  $(dt_departamentos_list_table).on('click', '.btnEliminarDepartamento', function() {
    var idDepartamento = $(this).attr('idDepartamento');
    
    Swal.fire({
      title: '¿Está seguro de borrar el departamento?',
      text: '¡Si no lo está puede cancelar la acción!',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      cancelButtonText: 'Cancelar',
      confirmButtonText: 'Sí, borrar departamento!'
    }).then(function(result) {
      if (result.value) {
        window.location = 'index.php?ruta=departamentos&idDepartamento=' + idDepartamento;
      }
    });
  });

  // Filter form control to default size
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
});

// Validación de formulario
(function () {
  const eCommerceDepartamentoListForm = document.getElementById('eCommerceDepartamentoListForm');
  const editarDepartamentoForm = document.getElementById('editarDepartamentoForm');

  // Validación del formulario para agregar nuevo departamento
  if (eCommerceDepartamentoListForm) {
    const fv = FormValidation.formValidation(eCommerceDepartamentoListForm, {
      fields: {
        departamentoTitle: {
          validators: {
            notEmpty: {
              message: 'Por favor ingrese el nombre del departamento'
            }
          }
        }
      },
      plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({
          eleValidClass: 'is-valid',
          rowSelector: '.form-control-validation'
        }),
        submitButton: new FormValidation.plugins.SubmitButton(),
        autoFocus: new FormValidation.plugins.AutoFocus()
      }
    }).on('core.form.valid', function() {
      // Aquí procesamos el formulario cuando es válido
      var nombre = $("#ecommerce-departamento-title").val();
      var descripcion = $("#ecommerce-departamento-descripcion").val();
      
      $.ajax({
        url: "ajax/departamentos.ajax.php",
        method: "POST",
        data: {
          nuevoDepartamento: nombre,
          nuevaDescripcion: descripcion
        },
        success: function(respuesta) {
          if (respuesta === "ok") {
            Swal.fire({
              icon: "success",
              title: "¡El departamento ha sido guardado correctamente!",
              showConfirmButton: true,
              confirmButtonText: "Cerrar"
            }).then(function(result) {
              if (result.value) {
                window.location = "departamentos";
              }
            });
          } else {
            Swal.fire({
              icon: "error",
              title: "Error al guardar",
              text: respuesta
            });
          }
        },
        error: function(xhr, status, error) {
          console.error("Error AJAX:", error);
          Swal.fire({
            icon: "error",
            title: "Error de comunicación",
            text: "No se pudo conectar con el servidor"
          });
        }
      });
    });
  }
})();