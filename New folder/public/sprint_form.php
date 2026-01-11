<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../config/database.php";

// Mostrar errores si existen
$sprint_errors = [];
if (isset($_SESSION['sprint_errors'])) {
    $sprint_errors = $_SESSION['sprint_errors'];
    unset($_SESSION['sprint_errors']);
}

// Obtener proyectos del usuario para el select
$projectsSql = "SELECT id, title FROM projects WHERE owner_id = :owner_id ORDER BY created_at DESC";
$projectsStmt = $pdo->prepare($projectsSql);
$projectsStmt->execute([':owner_id' => $_SESSION["user_id"]]);
$projects = $projectsStmt->fetchAll(PDO::FETCH_ASSOC);

// Si no hay proyectos, redirigir
if (empty($projects)) {
    $_SESSION['error'] = "Primero debes crear un proyecto";
    header("Location: project_form.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Sprint - Athena</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .project-selector {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 15px 0;
        }
        
        .project-option {
            flex: 1;
            min-width: 200px;
        }
        
        .calendar-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
            pointer-events: none;
        }
        
        .date-input-group {
            position: relative;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-rocket"></i> Nuevo Sprint</h1>
            <div class="user-info">
                <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Volver al Dashboard</a>
            </div>
        </div>

        <!-- Quick Navigation -->
        <div class="nav">
            <a href="project_form.php"><i class="fas fa-folder-plus"></i> Nuevo Proyecto</a>
            <a href="task_form.php"><i class="fas fa-tasks"></i> Nueva Tarea</a>
            <a href="dashboard.php"><i class="fas fa-chart-bar"></i> Dashboard</a>
        </div>

        <!-- Form Container -->
        <div class="form-container">
            <h2><i class="fas fa-rocket"></i> Crear Nuevo Sprint</h2>
            <p style="text-align: center; color: var(--gray); margin-bottom: 1.5rem;">
                Organiza tu trabajo en períodos de tiempo definidos
            </p>
            
            <?php if (!empty($sprint_errors)): ?>
                <div class="message error">
                    <i class="fas fa-exclamation-circle"></i>
                    <strong>Por favor corrige los siguientes errores:</strong>
                    <ul style="margin: 10px 0 0 20px;">
                        <?php foreach ($sprint_errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="create_sprint.php">
                <!-- Selección de Proyecto -->
                <div class="form-group">
                    <label for="project_id"><i class="fas fa-project-diagram"></i> Proyecto *</label>
                    <select id="project_id" name="project_id" class="form-control" required>
                        <option value="">Selecciona un proyecto</option>
                        <?php foreach ($projects as $project): ?>
                            <option value="<?php echo $project['id']; ?>">
                                <?php echo htmlspecialchars($project['title']); ?> (ID: <?php echo $project['id']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color: var(--gray); font-size: 0.85rem;">
                        <i class="fas fa-info-circle"></i> El sprint pertenecerá a este proyecto
                    </small>
                </div>

                <!-- Nombre del Sprint -->
                <div class="form-group">
                    <label for="name"><i class="fas fa-tag"></i> Nombre del Sprint *</label>
                    <input type="text" id="name" name="name" class="form-control" 
                           placeholder="Ej: Sprint 1 - Desarrollo Backend" required>
                    <small style="color: var(--gray); font-size: 0.85rem;">
                        <i class="fas fa-lightbulb"></i> Usa un nombre descriptivo para identificar fácilmente este sprint
                    </small>
                </div>

                <!-- Fechas -->
                <div class="form-grid">
                    <div class="form-group date-input-group">
                        <label for="start_date"><i class="fas fa-calendar-plus"></i> Fecha Inicio *</label>
                        <input type="date" id="start_date" name="start_date" class="form-control" required>
                        <i class="fas fa-calendar-alt calendar-icon"></i>
                        <small style="color: var(--gray); font-size: 0.85rem;">
                            Fecha de inicio del sprint
                        </small>
                    </div>
                    
                    <div class="form-group date-input-group">
                        <label for="end_date"><i class="fas fa-calendar-minus"></i> Fecha Fin *</label>
                        <input type="date" id="end_date" name="end_date" class="form-control" required>
                        <i class="fas fa-calendar-alt calendar-icon"></i>
                        <small style="color: var(--gray); font-size: 0.85rem;">
                            Fecha de finalización del sprint
                        </small>
                    </div>
                </div>

                <!-- Duración Calculada -->
                <div class="card" style="background: #f8f9fa; margin: 1.5rem 0;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong><i class="fas fa-clock"></i> Duración del Sprint</strong>
                            <p style="margin: 5px 0 0; font-size: 0.9rem; color: var(--gray);" id="duration-text">
                                Selecciona las fechas para calcular la duración
                            </p>
                        </div>
                        <div id="duration-days" style="font-size: 1.5rem; font-weight: bold; color: var(--primary);">
                            0 días
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <button type="submit" class="btn">
                    <i class="fas fa-rocket"></i> Crear Sprint
                </button>
                
                <a href="dashboard.php" class="btn btn-outline mt-2">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </form>
        </div>

        <!-- Tips -->
        <div class="card" style="margin-top: 2rem; background: #e8f4fd; border-left-color: #4cc9f0;">
            <h3 style="color: #4cc9f0; margin-bottom: 10px;">
                <i class="fas fa-lightbulb"></i> Consejos para sprints efectivos
            </h3>
            <ul style="padding-left: 20px; color: #555;">
                <li>Los sprints típicos duran entre 1 y 4 semanas</li>
                <li>Planifica tareas realistas para el período del sprint</li>
                <li>Asegúrate de que el equipo conozca las fechas del sprint</li>
                <li>Revisa el progreso regularmente</li>
            </ul>
        </div>
    </div>

    <script>
        // Calcular duración del sprint
        function calculateDuration() {
            const startInput = document.getElementById('start_date');
            const endInput = document.getElementById('end_date');
            const durationText = document.getElementById('duration-text');
            const durationDays = document.getElementById('duration-days');
            
            if (startInput.value && endInput.value) {
                const startDate = new Date(startInput.value);
                const endDate = new Date(endInput.value);
                
                // Validar que la fecha de inicio no sea posterior a la de fin
                if (startDate > endDate) {
                    durationText.innerHTML = '<span style="color: #f72585;">⚠️ La fecha de inicio no puede ser posterior a la de fin</span>';
                    durationDays.textContent = '0 días';
                    return;
                }
                
                // Calcular diferencia en días
                const diffTime = Math.abs(endDate - startDate);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1; // +1 para incluir ambos días
                
                durationDays.textContent = diffDays + ' día' + (diffDays !== 1 ? 's' : '');
                
                // Clasificar duración
                let durationType = '';
                if (diffDays <= 7) durationType = 'Corto';
                else if (diffDays <= 14) durationType = 'Estándar';
                else if (diffDays <= 30) durationType = 'Largo';
                else durationType = 'Extendido';
                
                durationText.textContent = `${durationType} (${startDate.toLocaleDateString()} - ${endDate.toLocaleDateString()})`;
                
                // Color según duración
                if (diffDays <= 7) durationDays.style.color = '#4cc9f0';
                else if (diffDays <= 14) durationDays.style.color = '#7209b7';
                else if (diffDays <= 30) durationDays.style.color = '#f8961e';
                else durationDays.style.color = '#f72585';
            }
        }
        
        // Setear fechas por defecto (hoy y en 2 semanas)
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date();
            const twoWeeksLater = new Date(today);
            twoWeeksLater.setDate(today.getDate() + 14);
            
            // Formatear a YYYY-MM-DD
            const formatDate = (date) => date.toISOString().split('T')[0];
            
            document.getElementById('start_date').value = formatDate(today);
            document.getElementById('end_date').value = formatDate(twoWeeksLater);
            
            // Calcular duración inicial
            calculateDuration();
        });
        
        // Escuchar cambios en fechas
        document.getElementById('start_date').addEventListener('change', calculateDuration);
        document.getElementById('end_date').addEventListener('change', calculateDuration);
        
        // Validación de fechas antes de enviar
        document.querySelector('form').addEventListener('submit', function(e) {
            const startDate = new Date(document.getElementById('start_date').value);
            const endDate = new Date(document.getElementById('end_date').value);
            
            if (startDate > endDate) {
                e.preventDefault();
                alert('❌ Error: La fecha de inicio no puede ser posterior a la fecha de fin');
                return false;
            }
            
            // Validar que no sea un sprint demasiado largo (más de 3 meses)
            const diffTime = Math.abs(endDate - startDate);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            if (diffDays > 90) {
                if (!confirm(`⚠️ Este sprint dura ${diffDays} días (más de 3 meses). ¿Estás seguro de continuar?`)) {
                    e.preventDefault();
                    return false;
                }
            }
        });
    </script>
</body>
</html>