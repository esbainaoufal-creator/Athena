<!DOCTYPE html>
<html>
<head>
    <title>Crear Sprint</title>
</head>
<body>

<h2>Crear Sprint</h2>

<form method="POST" action="create_sprint.php">
    <input type="number" name="project_id" placeholder="ID del proyecto" required><br><br>
    <input type="text" name="name" placeholder="Nombre del sprint" required><br><br>
    <input type="date" name="start_date" required><br><br>
    <input type="date" name="end_date" required><br><br>
    <button type="submit">Crear Sprint</button>
</form>

</body>
</html>
