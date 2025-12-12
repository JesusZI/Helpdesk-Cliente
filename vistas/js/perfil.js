/*=============================================
MANEJO DEL PERFIL DE USUARIO
=============================================*/

document.addEventListener("DOMContentLoaded", function() {
    console.log("=== PERFIL.JS CARGADO ===");
    
    const formPerfilUsuario = document.getElementById("formPerfilUsuario");
    const formDesactivarCuenta = document.getElementById("formDesactivarCuenta");
    const inputFotoPerfil = document.getElementById("fotoPerfil");
    const previewFotoPerfil = document.getElementById("previewFotoPerfil");
    const resetFotoPerfil = document.getElementById("resetFotoPerfil");
    const confirmDesactivar = document.getElementById("confirmDesactivar");
    const btnDesactivarCuenta = document.getElementById("btnDesactivarCuenta");
    const editarPassword = document.getElementById("editarPassword");
    const confirmarPassword = document.getElementById("confirmarPassword");
    
    if (inputFotoPerfil) {
        inputFotoPerfil.addEventListener("change", function() {
            console.log("Cambio de imagen detectado", this.files);
            if (this.files && this.files[0]) {
                if (this.files[0].size > 819200) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Archivo demasiado grande',
                        text: 'La imagen no debe superar los 800KB',
                        confirmButtonText: 'Aceptar'
                    });
                    this.value = '';
                    return;
                }
                
                const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (!validTypes.includes(this.files[0].type)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Formato no válido',
                        text: 'Solo se permiten archivos JPG, PNG o GIF',
                        confirmButtonText: 'Aceptar'
                    });
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewFotoPerfil.src = e.target.result;
                    console.log("Preview actualizado");
                }
                reader.readAsDataURL(this.files[0]);
                
                const oldResetInput = document.querySelector('input[name="resetFoto"]');
                if (oldResetInput) {
                    oldResetInput.remove();
                    console.log("Flag reset eliminado");
                }
            }
        });
    }
    
    if (resetFotoPerfil) {
        resetFotoPerfil.addEventListener("click", function() {
            console.log("Reset de imagen solicitado");
            if (inputFotoPerfil) {
                inputFotoPerfil.value = '';
            }
            previewFotoPerfil.src = "vistas/assets/img/avatars/default.jpg";
            
            const resetInput = document.createElement('input');
            resetInput.type = 'hidden';
            resetInput.name = 'resetFoto';
            resetInput.value = '1';
            
            const oldResetInput = document.querySelector('input[name="resetFoto"]');
            if (oldResetInput) {
                oldResetInput.remove();
            }
            
            formPerfilUsuario.appendChild(resetInput);
            console.log("Flag reset agregado");
        });
    }
    
    if (confirmDesactivar && btnDesactivarCuenta) {
        confirmDesactivar.addEventListener("change", function() {
            btnDesactivarCuenta.disabled = !this.checked;
        });
    }
    
    if (editarPassword && confirmarPassword) {
        const validatePasswords = function() {
            if (editarPassword.value && editarPassword.value !== confirmarPassword.value) {
                confirmarPassword.setCustomValidity("Las contraseñas no coinciden");
                confirmarPassword.classList.add("is-invalid");
            } else {
                confirmarPassword.setCustomValidity("");
                confirmarPassword.classList.remove("is-invalid");
            }
        };
        
        editarPassword.addEventListener("input", validatePasswords);
        confirmarPassword.addEventListener("input", validatePasswords);
    }
    
    if (formPerfilUsuario) {
        formPerfilUsuario.addEventListener("submit", function(e) {
            e.preventDefault();
            console.log("=== SUBMIT FORMULARIO ===");
            
            const nombre = document.getElementById("editarNombre").value.trim();
            const apellido = document.getElementById("editarApellido").value.trim();
            const email = document.getElementById("editarEmail").value.trim();
            
            console.log("Datos a enviar:", {nombre, apellido, email});
            
            if (!nombre || !apellido || !email) {
                Swal.fire({
                    icon: 'error',
                    title: 'Campos obligatorios',
                    text: 'Los campos Nombre, Apellido y Email son obligatorios',
                    confirmButtonText: 'Aceptar'
                });
                return;
            }
            
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Email inválido',
                    text: 'Por favor ingresa un email válido',
                    confirmButtonText: 'Aceptar'
                });
                return;
            }
            
            if (editarPassword.value && editarPassword.value !== confirmarPassword.value) {
                Swal.fire({
                    icon: 'error',
                    title: 'Las contraseñas no coinciden',
                    text: 'Por favor, verifica que hayas ingresado la misma contraseña en ambos campos',
                    confirmButtonText: 'Aceptar'
                });
                return;
            }
            
            console.log("Validaciones pasadas, enviando formulario...");
            
            Swal.fire({
                title: 'Actualizando perfil...',
                text: 'Por favor espera mientras procesamos tu información',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            const formData = new FormData(this);
            console.log("FormData creado:", formData);
            
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }
            
            this.submit();
        });
    }
    
    if (formDesactivarCuenta) {
        formDesactivarCuenta.addEventListener("submit", function(e) {
            e.preventDefault();
            
            if (!confirmDesactivar.checked) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Confirmación requerida',
                    text: 'Debes confirmar que deseas desactivar tu cuenta',
                    confirmButtonText: 'Aceptar'
                });
                return;
            }
            
            Swal.fire({
                title: '¿Estás completamente seguro?',
                text: "Esta acción no se puede deshacer. Perderás acceso a tu cuenta.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, desactivar mi cuenta',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'index.php?ruta=perfil';
                    form.style.display = 'none';
                    
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'desactivarCuenta';
                    input.value = document.getElementById('idUsuario').value;
                    
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    }
});