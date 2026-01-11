<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Proyecto - Athena</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2><i class="fas fa-folder-plus"></i> Nuevo Proyecto</h2>
            
            <form method="POST" action="create_project.php">
                <div class="form-group">
                    <label for="title"><i class="fas fa-heading"></i> Título del Proyecto</label>
                    <input type="text" id="title" name="title" class="form-control" placeholder="Ej: Sistema de Gestión" required>
                </div>
                
                <div class="form-group">
                    <label for="description"><i class="fas fa-align-left"></i> Descripción</label>
                    <textarea id="description" name="description" class="form-control" placeholder="Describe los objetivos del proyecto..."></textarea>
                </div>
                
                <button type="submit" class="btn">
                    <i class="fas fa-plus-circle"></i> Crear Proyecto
                </button>
                
                <a href="dashboard.php" class="btn btn-outline mt-2">
                    <i class="fas fa-arrow-left"></i> Volver al Dashboard
                </a>
            </form>
        </div>
    </div>
</body>
</html>