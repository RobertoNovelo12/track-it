{{-- ============================================================
    «Completar ahora» (cuenta incompleta)
    ------------------------------------------------------------
    Si el formulario de «Editar perfil» ya está en la página
    (Ajustes › Cuenta), solo abre la ventana: sin recargar. Si no,
    navega a Ajustes › Cuenta con ?completar=perfil, que la abre.
============================================================ --}}
<script>
    window.trackitCompletarPerfil = function (url) {
        const campo = document.getElementById('editFechaNacimiento');

        if (campo) {
            window.dispatchEvent(new CustomEvent('abrir-edicion-perfil'));
            setTimeout(() => campo.focus(), 160);

            return;
        }

        window.Livewire?.navigate ? window.Livewire.navigate(url) : (window.location.href = url);
    };
</script>
