<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель</title>
    <link rel="stylesheet" href="style.css"> 
</head>
<body>
<?php

if (empty($_SERVER['PHP_AUTH_USER']) ||
    empty($_SERVER['PHP_AUTH_PW']) ||
    $_SERVER['PHP_AUTH_USER'] != 'admin' ||
    md5($_SERVER['PHP_AUTH_PW']) != md5('123')) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="My site"');
    print('<h1>401 Требуется авторизация</h1>');
    exit();
}

$db = new PDO('mysql:host=localhost;dbname=u67439', 'u67439', '4415842', array(PDO::ATTR_PERSISTENT => true));

$stmt = $db->query("SELECT * FROM application");
$usersData = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo '<table border="1">';
echo '<tr><th>Имя</th><th>Телефон</th><th>Email</th><th>Год рождения</th><th>Пол</th><th>Биография</th><th>Языки программирования</th><th>Действия</th></tr>';
foreach ($usersData as $userData) {

    echo '<tr>';
    echo '<td>' . htmlspecialchars($userData['names'], ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td>' . htmlspecialchars($userData['phones'], ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td>' . htmlspecialchars($userData['email'], ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td>' . htmlspecialchars($userData['dates'], ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td>' . htmlspecialchars($userData['gender'], ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td>' . htmlspecialchars($userData['biography'], ENT_QUOTES, 'UTF-8') . '</td>';


    $stmt = $db->prepare("SELECT id_lang FROM application_languages WHERE id = ?");
    $stmt->execute([$userData['id']]);
    $userLanguages = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo '<td>' . implode(', ', $userLanguages) . '</td>';

    echo '<td><a href="edit_user.php?id=' . $userData['id'] . '">Редактировать</a> | <form action="delete_user.php" method="post"><input type="hidden" name="id" value="' . $userData['id'] . '"><input type="submit" value="Удалить"></form></td>';
    echo '</tr>';
}
echo '</table>';

$stmt = $db->query("SELECT id_lang, COUNT(*) AS count FROM application_languages GROUP BY id_lang");
$languagesStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo '<h2>Статистика по языкам программирования</h2>';
echo '<ul>';
foreach ($languagesStats as $languageStat) {
    echo '<li>' . $languageStat['id_lang'] . ': ' . $languageStat['count'] . ' пользователей</li>';
}
echo '</ul>';

?>