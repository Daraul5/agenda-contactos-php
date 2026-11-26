<?php
/**
 * includes/footer.php
 * Pie de página común con créditos del equipo y scripts JS
 */
?>
    </main>

    <!-- Footer Académico -->
    <footer class="footer bg-white border-top py-4 mt-auto">
        <div class="container text-center text-md-start">
            <div class="row align-items-center gy-3">
                <div class="col-md-6">
                    <h6 class="fw-bold text-dark mb-1 d-flex align-items-center justify-content-center justify-content-md-start gap-2">
                        <i class="bi bi-code-slash text-primary"></i> Mini Proyecto 2 — Programación Web (CRUD PDO)
                    </h6>
                    <p class="text-muted small mb-0">
                        Unidad 4: Programación del lado del servidor con PHP y MySQL.
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <div class="small text-muted mb-1">
                        <strong>Integrantes del Equipo:</strong>
                    </div>
                    <div class="team-members small">
                        <span class="badge bg-light text-dark border me-1">David Raúl Vázquez Yáñez</span>
                        <span class="badge bg-light text-dark border me-1">Rebeca Jiménez Díaz</span>
                        <span class="badge bg-light text-dark border">Elías Raziel Gómez Carrión</span>
                    </div>
                </div>
            </div>
            <hr class="my-3 opacity-25">
            <div class="row">
                <div class="col text-center small text-muted">
                    &copy; <?= date('Y'); ?> Agenda de Contactos con PHP y MySQL PDO · Implementación Segura con Sentencias Preparadas.
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3 Bundle JS con Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- Script para Previsualización de Imágenes en Formularios -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const fotoInput = document.getElementById('foto');
            const fotoPreview = document.getElementById('preview-image');

            if (fotoInput && fotoPreview) {
                fotoInput.addEventListener('change', function (e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function (event) {
                            fotoPreview.src = event.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }

            // Auto-cerrar alertas flash tras 6 segundos
            const flashAlert = document.querySelector('.alert-dismissible');
            if (flashAlert) {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(flashAlert);
                    bsAlert.close();
                }, 6000);
            }
        });
    </script>
</body>
</html>
