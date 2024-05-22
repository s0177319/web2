<?php
$db = new PDO('mysql:host=localhost;dbname=u67439', 'u67439', '4415842', array(PDO::ATTR_PERSISTENT => true));

if (!isset($_GET['id'])) {
    error_log("User ID not provided in request.");
    echo "Ошибка: ID пользователя не указан.";
    exit();
}

$stmt = $db->prepare("SELECT * FROM application WHERE id = ?");
$stmt->execute([$_GET['id']]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$userData) {
    error_log("User with ID {$_GET['id']} not found.");
    echo "Пользователь с указанным ID не найден.";
    exit();
}


session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Ошибка CSRF: недопустимый токен.");
    }

    $stmt = $db->prepare("UPDATE application SET names = ?, phones = ?, email = ?, dates = ?, gender = ?, biography = ? WHERE id = ?");
    $stmt->execute([
        htmlspecialchars($_POST['names']),
        htmlspecialchars($_POST['phones']),
        htmlspecialchars($_POST['email']),
        htmlspecialchars($_POST['dates']),
        htmlspecialchars($_POST['gender']),
        htmlspecialchars($_POST['biography']),
        htmlspecialchars($_GET['id'])
    ]);

    header("Location: admin.php");
    exit();
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {

    $userId = $_GET['id'];

    $stmt = $db->prepare("DELETE FROM application_languages WHERE id_app = ?");
    $stmt->execute([$userId]);

    $stmt = $db->prepare("DELETE FROM application WHERE id = ?");
    $stmt->execute([$userId]);

    header("Location: admin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование пользователя</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Редактирование пользователя</h1>
    <form method="POST">
        <label for="names">Имя:</label><br>
        <input type="text" id="names" name="names" value="<?php echo $userData['names']; ?>"><br>
        <label for="phones">Телефон:</label><br>
        <input type="tel" id="phones" name="phones" value="<?php echo $userData['phones']; ?>"><br>
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" value="<?php echo $userData['email']; ?>"><br>
        <label for="dates">Дата рождения:</label><br>
        <input type="date" id="dates" name="dates" value="<?php echo $userData['dates']; ?>"><br>
        <label for="gender">Пол:</label><br>
        <select id="gender" name="gender">
            <option value="M" <?php if ($userData['gender'] == 'M') echo 'selected'; ?>>Мужской</option>
            <option value="F" <?php if ($userData['gender'] == 'F') echo 'selected'; ?>>Женский</option>
        </select><br>
        <label for="biography">Биография:</label><br>
        <textarea id="biography" name="biography"><?php echo $userData['biography']; ?></textarea><br>
        <input type="submit" name="update" value="Сохранить изменения">
        <input type="submit" name="delete" value="Удалить пользователя" onclick="return confirm('Вы уверены, что хотите удалить этого пользователя?')">
    </form>
</body>
</html>