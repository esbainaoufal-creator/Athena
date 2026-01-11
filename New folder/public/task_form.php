<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../config/database.php";

// Mostrar errores si existen
$task_error = "";
if (isset($_SESSION['task_error'])) {
    $task_error = $_SESSION['task_error'];
    unset($_SESSION['task_error']);
}

// Obtener proyectos del usuario
$projectsSql = "SELECT id, title FROM projects WHERE owner_id = :owner_id ORDER BY title";
$projectsStmt = $pdo->prepare($projectsSql);
$projectsStmt->execute([':owner_id' => $_SESSION["user_id"]]);
$projects = $projectsStmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener sprints de los proyectos del usuario
$sprintsSql = "SELECT s.id, s.name, p.title as project_title, 
               CONCAT(p.title, ' - ', s.name) as display_name,
               s.start_date, s.end_date
               FROM sprints s 
               JOIN projects p ON s.project_id = p.id 
               WHERE p.owner_id = :owner_id 
               ORDER BY s.start_date DESC, p.title";
$sprintsStmt = $pdo->prepare($sprintsSql);
$sprintsStmt->execute([':owner_id' => $_SESSION["user_id"]]);
$sprints = $sprintsStmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener usuarios para asignar (solo admin/manager)
$users = [];
if (in_array($_SESSION["user_role"], ["admin", "manager"])) {
    $userSql = "SELECT id, name, email FROM users ORDER BY name";
    $userStmt = $pdo->prepare($userSql);
    $userStmt->execute();
    $users = $userStmt->fetchAll(PDO::FETCH_ASSOC);
}

// Si no hay sprints, mostrar mensaje
if (empty($sprints)) {
    $_SESSION['task_error'] = "Primero debes crear un sprint en algún proyecto";
    header("Location: sprint_form.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Tarea - Athena</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .wizard-steps {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
            position: relative;
        }
        
        .wizard-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0 1.5rem;
            position: relative;
            z-index: 1;
        }
        
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--gray-light);
            color: var(--gray);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 0.5rem;
            border: 3px solid white;
            transition: all 0.3s;
        }
        
        .wizard-step.active .step-number {
            background: var(--primary);
            color: white;
        }
        
        .wizard-step.completed .step-number {
            background: var(--success);
            color: white;
        }
        
        .step-label {
            font-size: 0.85rem;
            color: var(--gray);
            text-align: center;
        }
        
        .wizard-connector {
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--gray-light);
            z-index: 0;
        }
        
        .priority-badges {
            display: flex;
            gap: 10px;
            margin: 15px 0;
        }
        
        .priority-badge {
            flex: 1;
            padding: 10px;
            border-radius: var(--radius-sm);
            text-align: center;
            cursor: pointer;
            border: 2px solid var(--border);
            transition: all 0.3s;
        }
        
        .priority-badge:hover {
            transform: translateY(-2px);
        }
        
        .priority-badge.selected {
            border-color: var(--primary);
            background: rgba(67, 97, 238, 0.1);
        }
        
        .priority-badge.low { border-left-color: #4cc9f0; }
        .priority-badge.medium { border-left-color: #f8961e; }
        .priority-badge.high { border-left-color: #f72585; }
        
        .project-sprint-selector {
            display: flex;
            gap: 15px;
            margin-bottom: 1.5rem;
        }
        
        .project-sprint-selector > div {
            flex: 1;
        }
        
        @media (max-width: 768px) {
            .project-sprint-selector {
                flex-direction: column;
            }
            
            .wizard-step {
                padding: 0 0.5rem;
            }
            
            .step-label {
                font-size: 0.75rem;
            }
        }
        
        .sprint-info {
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: var(--radius-sm);
            margin: 10px 0;
            font-size: 0.9rem;
        }
        
        .character-counter {
            text-align: right;
            font-size: 0.8rem;
            color: var(--gray);
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-tasks"></i> Nueva Tarea</h1>
            <div class="user-info">
                <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Dashboard</a>
            </div>
        </div>

        <!-- Quick Navigation -->
        <div class="nav">
            <a href="project_form.php"><i class="fas fa-folder-plus"></i> Proyecto</a>
            <a href="sprint_form.php"><i class="fas fa-rocket"></i> Sprint</a>
            <a href="dashboard.php"><i class="fas fa-home"></i> Inicio</a>
        </div>

        <!-- Wizard Steps -->
        <div class="wizard-steps">
            <div class="wizard-connector"></div>
            <div class="wizard-step active">
                <div class="step-number">1</div>
                <div class="step-label">Detalles</div>
            </div>
            <div class="wizard-step">
                <div class="step-number">2</div>
                <div class="step-label">Asignación</div>
            </div>
            <div class="wizard-step">
                <div class="step-number">3</div>
                <div class="step-label">Revisión</div>
            </div>
        </div>

        <!-- Form Container -->
        <div class="form-container">
            <h2><i class="fas fa-plus-circle"></i> Crear Nueva Tarea</h2>
            <p style="text-align: center; color: var(--gray); margin-bottom: 1.5rem;">
                Desglosa el trabajo en tareas manejables
            </p>
            
            <?php if (!empty($task_error)): ?>
                <div class="message error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($task_error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="create_task.php" id="taskForm">
                <!-- Proyecto y Sprint -->
                <div class="project-sprint-selector">
                    <div class="form-group">
                        <label for="project_filter"><i class="fas fa-filter"></i> Filtrar por Proyecto</label>
                        <select id="project_filter" class="form-control">
                            <option value="">Todos los proyectos</option>
                            <?php foreach ($projects as $project): ?>
                                <option value="project_<?php echo $project['id']; ?>">
                                    <?php echo htmlspecialchars($project['title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="sprint_id"><i class="fas fa-rocket"></i> Sprint *</label>
                        <select id="sprint_id" name="sprint_id" class="form-control" required>
                            <option value="">Selecciona un sprint</option>
                            <?php foreach ($sprints as $sprint): 
                                $startDate = date('d/m/Y', strtotime($sprint['start_date']));
                                $endDate = date('d/m/Y', strtotime($sprint['end_date']));
                                $daysLeft = floor((strtotime($sprint['end_date']) - time()) / (60 * 60 * 24));
                                $statusClass = $daysLeft < 0 ? 'past' : ($daysLeft < 7 ? 'ending' : 'active');
                            ?>
                                <option value="<?php echo $sprint['id']; ?>" 
                                        data-project="project_<?php echo array_column($projects, 'id', 'title')[$sprint['project_title']] ?? 0; ?>"
                                        data-sprint-name="<?php echo htmlspecialchars($sprint['name']); ?>"
                                        data-project-title="<?php echo htmlspecialchars($sprint['project_title']); ?>"
                                        data-start-date="<?php echo $startDate; ?>"
                                        data-end-date="<?php echo $endDate; ?>"
                                        data-days-left="<?php echo $daysLeft; ?>">
                                    <?php echo htmlspecialchars($sprint['project_title'] . ' - ' . $sprint['name']); ?>
                                    <?php if ($daysLeft < 7 && $daysLeft >= 0): ?> ⏰<?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Información del Sprint Seleccionado -->
                <div id="sprintInfo" class="sprint-info" style="display: none;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong id="selectedProject"></strong> - <span id="selectedSprint"></span>
                        </div>
                        <div>
                            <span id="selectedDates"></span> | 
                            <span id="daysLeft"></span>
                        </div>
                    </div>
                </div>

                <!-- Título y Descripción -->
                <div class="form-group">
                    <label for="title"><i class="fas fa-heading"></i> Título de la Tarea *</label>
                    <input type="text" id="title" name="title" class="form-control" 
                           placeholder="Ej: Implementar sistema de login con autenticación JWT" required
                           maxlength="150">
                    <div class="character-counter">
                        <span id="titleCounter">0</span>/150 caracteres
                    </div>
                </div>

                <!-- Prioridad -->
                <div class="form-group">
                    <label><i class="fas fa-flag"></i> Prioridad</label>
                    <div class="priority-badges">
                        <div class="priority-badge low" data-priority="low">
                            <i class="fas fa-arrow-down"></i>
                            <div style="font-weight: bold; margin-top: 5px;">Baja</div>
                            <small style="font-size: 0.8rem;">Sin urgencia</small>
                        </div>
                        <div class="priority-badge medium selected" data-priority="medium">
                            <i class="fas fa-minus"></i>
                            <div style="font-weight: bold; margin-top: 5px;">Media</div>
                            <small style="font-size: 0.8rem;">Prioridad normal</small>
                        </div>
                        <div class="priority-badge high" data-priority="high">
                            <i class="fas fa-arrow-up"></i>
                            <div style="font-weight: bold; margin-top: 5px;">Alta</div>
                            <small style="font-size: 0.8rem;">Urgente</small>
                        </div>
                    </div>
                    <input type="hidden" id="priority" name="priority" value="medium">
                </div>

                <!-- Asignación -->
                <div class="form-group">
                    <label for="user_id"><i class="fas fa-user-check"></i> Asignar a</label>
                    <select id="user_id" name="user_id" class="form-control">
                        <option value="<?php echo $_SESSION['user_id']; ?>" selected>
                            <i class="fas fa-user"></i> Yo mismo (<?php echo htmlspecialchars($_SESSION['user_name']); ?>)
                        </option>
                        <?php if (!empty($users)): ?>
                            <optgroup label="Miembros del equipo">
                                <?php foreach ($users as $user): 
                                    if ($user['id'] == $_SESSION['user_id']) continue;
                                ?>
                                    <option value="<?php echo $user['id']; ?>">
                                        <?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['email']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>
                    </select>
                    <small style="color: var(--gray); font-size: 0.85rem;">
                        <?php if ($_SESSION['user_role'] === 'member'): ?>
                            <i class="fas fa-info-circle"></i> Solo puedes asignarte tareas a ti mismo
                        <?php else: ?>
                            <i class="fas fa-info-circle"></i> Puedes asignar esta tarea a cualquier miembro del equipo
                        <?php endif; ?>
                    </small>
                </div>

                <!-- Estado y Esfuerzo Estimado -->
                <div class="form-grid" style="grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="status"><i class="fas fa-tachometer-alt"></i> Estado</label>
                        <select id="status" name="status" class="form-control">
                            <option value="todo" selected>📝 Por hacer</option>
                            <option value="in_progress">⚡ En progreso</option>
                            <option value="done">✅ Completado</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="estimated_hours"><i class="fas fa-clock"></i> Esfuerzo estimado (horas)</label>
                        <input type="number" id="estimated_hours" name="estimated_hours" 
                               class="form-control" min="1" max="100" value="8" step="0.5">
                        <small style="color: var(--gray); font-size: 0.85rem;">
                            Tiempo estimado para completar esta tarea
                        </small>
                    </div>
                </div>

                <!-- Descripción Detallada -->
                <div class="form-group">
                    <label for="description"><i class="fas fa-align-left"></i> Descripción detallada</label>
                    <textarea id="description" name="description" class="form-control" 
                              rows="4" placeholder="Describe los detalles de la tarea, requisitos, criterios de aceptación..."></textarea>
                    <div class="character-counter">
                        <span id="descCounter">0</span>/1000 caracteres
                    </div>
                </div>

                <!-- Botones -->
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn">
                        <i class="fas fa-check-circle"></i> Crear Tarea
                    </button>
                    
                    <button type="button" id="saveDraft" class="btn btn-outline">
                        <i class="fas fa-save"></i> Guardar Borrador
                    </button>
                    
                    <a href="dashboard.php" class="btn btn-outline" style="flex: 1;">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>

        <!-- Tips -->
        <div class="card" style="margin-top: 2rem; background: #f0f7ff; border-left-color: #4361ee;">
            <h3 style="color: #4361ee; margin-bottom: 10px;">
                <i class="fas fa-lightbulb"></i> Consejos para tareas efectivas
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div>
                    <h4><i class="fas fa-bullseye"></i> Específicas</h4>
                    <p style="font-size: 0.9rem; color: #555;">Define tareas claras y concretas</p>
                </div>
                <div>
                    <h4><i class="fas fa-ruler"></i> Medibles</h4>
                    <p style="font-size: 0.9rem; color: #555;">Establece criterios de completitud</p>
                </div>
                <div>
                    <h4><i class="fas fa-hourglass-half"></i> Temporizadas</h4>
                    <p style="font-size: 0.9rem; color: #555;">Asigna tiempos realistas</p>
                </div>
                <div>
                    <h4><i class="fas fa-user-check"></i> Asignables</h4>
                    <p style="font-size: 0.9rem; color: #555;">Una persona responsable por tarea</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Contadores de caracteres
        document.getElementById('title').addEventListener('input', function(e) {
            document.getElementById('titleCounter').textContent = e.target.value.length;
        });
        
        document.getElementById('description').addEventListener('input', function(e) {
            document.getElementById('descCounter').textContent = e.target.value.length;
        });

        // Selector de prioridad
        document.querySelectorAll('.priority-badge').forEach(badge => {
            badge.addEventListener('click', function() {
                document.querySelectorAll('.priority-badge').forEach(b => b.classList.remove('selected'));
                this.classList.add('selected');
                document.getElementById('priority').value = this.dataset.priority;
            });
        });

        // Filtro de proyectos
        document.getElementById('project_filter').addEventListener('change', function() {
            const selectedProject = this.value;
            const sprintSelect = document.getElementById('sprint_id');
            
            // Mostrar/ocultar opciones según el proyecto seleccionado
            Array.from(sprintSelect.options).forEach(option => {
                if (option.value === '') return;
                
                if (selectedProject === '' || option.dataset.project === selectedProject) {
                    option.style.display = 'block';
                } else {
                    option.style.display = 'none';
                }
            });
            
            // Resetear la selección si la opción actual está oculta
            if (sprintSelect.value !== '' && sprintSelect.selectedOptions[0].style.display === 'none') {
                sprintSelect.value = '';
                document.getElementById('sprintInfo').style.display = 'none';
            }
            
            // Actualizar información del sprint si hay uno seleccionado
            updateSprintInfo();
        });

        // Información del sprint seleccionado
        document.getElementById('sprint_id').addEventListener('change', updateSprintInfo);
        
        function updateSprintInfo() {
            const sprintSelect = document.getElementById('sprint_id');
            const selectedOption = sprintSelect.selectedOptions[0];
            const sprintInfo = document.getElementById('sprintInfo');
            
            if (sprintSelect.value && selectedOption.dataset.projectTitle) {
                document.getElementById('selectedProject').textContent = selectedOption.dataset.projectTitle;
                document.getElementById('selectedSprint').textContent = selectedOption.dataset.sprintName;
                document.getElementById('selectedDates').textContent = 
                    `${selectedOption.dataset.startDate} - ${selectedOption.dataset.endDate}`;
                
                const daysLeft = parseInt(selectedOption.dataset.daysLeft);
                let daysText = '';
                let daysColor = '';
                
                if (daysLeft < 0) {
                    daysText = `Sprint terminado hace ${Math.abs(daysLeft)} días`;
                    daysColor = '#f72585';
                } else if (daysLeft === 0) {
                    daysText = 'Sprint termina hoy';
                    daysColor = '#f8961e';
                } else if (daysLeft === 1) {
                    daysText = '1 día restante';
                    daysColor = '#f8961e';
                } else if (daysLeft < 7) {
                    daysText = `${daysLeft} días restantes`;
                    daysColor = '#f8961e';
                } else {
                    daysText = `${daysLeft} días restantes`;
                    daysColor = '#4cc9f0';
                }
                
                document.getElementById('daysLeft').innerHTML = 
                    `<span style="color: ${daysColor}; font-weight: bold;">${daysText}</span>`;
                
                sprintInfo.style.display = 'block';
            } else {
                sprintInfo.style.display = 'none';
            }
        }

        // Guardar borrador
        document.getElementById('saveDraft').addEventListener('click', function() {
            const formData = new FormData(document.getElementById('taskForm'));
            
            // Guardar en localStorage
            const draft = {
                title: formData.get('title'),
                description: formData.get('description'),
                sprint_id: formData.get('sprint_id'),
                priority: formData.get('priority'),
                user_id: formData.get('user_id'),
                status: formData.get('status'),
                estimated_hours: formData.get('estimated_hours'),
                saved_at: new Date().toISOString()
            };
            
            localStorage.setItem('task_draft', JSON.stringify(draft));
            
            // Mostrar mensaje
            alert('✅ Borrador guardado. Puedes continuar más tarde.');
            
            // Redirigir al dashboard
            window.location.href = 'dashboard.php';
        });

        // Cargar borrador si existe
        document.addEventListener('DOMContentLoaded', function() {
            const draft = localStorage.getItem('task_draft');
            if (draft) {
                const data = JSON.parse(draft);
                
                // Llenar campos
                if (data.title) document.getElementById('title').value = data.title;
                if (data.description) document.getElementById('description').value = data.description;
                if (data.sprint_id) document.getElementById('sprint_id').value = data.sprint_id;
                if (data.priority) {
                    document.getElementById('priority').value = data.priority;
                    document.querySelector(`.priority-badge[data-priority="${data.priority}"]`).click();
                }
                if (data.user_id) document.getElementById('user_id').value = data.user_id;
                if (data.status) document.getElementById('status').value = data.status;
                if (data.estimated_hours) document.getElementById('estimated_hours').value = data.estimated_hours;
                
                // Actualizar contadores
                document.getElementById('titleCounter').textContent = data.title?.length || 0;
                document.getElementById('descCounter').textContent = data.description?.length || 0;
                
                // Actualizar info del sprint
                updateSprintInfo();
                
                // Preguntar si quieren usar el borrador
                if (confirm('📝 Tienes un borrador guardado. ¿Quieres cargarlo?')) {
                    console.log('Borrador cargado');
                }
            }
            
            // Limpiar borrador al enviar
            document.getElementById('taskForm').addEventListener('submit', function() {
                localStorage.removeItem('task_draft');
            });
            
            // Inicializar contadores
            document.getElementById('titleCounter').textContent = 
                document.getElementById('title').value.length;
            document.getElementById('descCounter').textContent = 
                document.getElementById('description').value.length;
        });

        // Validación antes de enviar
        document.getElementById('taskForm').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const sprintId = document.getElementById('sprint_id').value;
            
            if (!title) {
                e.preventDefault();
                alert('❌ El título de la tarea es obligatorio');
                document.getElementById('title').focus();
                return false;
            }
            
            if (!sprintId) {
                e.preventDefault();
                alert('❌ Debes seleccionar un sprint');
                document.getElementById('sprint_id').focus();
                return false;
            }
            
            // Mostrar confirmación
            if (!confirm('¿Estás seguro de crear esta tarea?')) {
                e.preventDefault();
                return false;
            }
            
            return true;
        });
    </script>
</body>
</html>