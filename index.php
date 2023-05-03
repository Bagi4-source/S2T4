<?php
// Отправляем браузеру правильную кодировку,
// файл index.php должен быть в кодировке UTF-8 без BOM.
header('Content-Type: text/html; charset=UTF-8');
// В суперглобальном массиве $_SERVER PHP сохраняет некторые заголовки запроса HTTP
// и другие сведения о клиненте и сервере, например метод текущего запроса $_SERVER['REQUEST_METHOD'].

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $messages = array();
    if (!empty($_COOKIE['save'])) {
        setcookie('save', '', 100000);
        setcookie('fio_value', '', 100000);
        setcookie('email_value', '', 100000);
        setcookie('checkbox_value', '', 100000);
        setcookie('limbs_value', '', 100000);
        setcookie('abilities_value', '', 100000);
        setcookie('gender_value', '', 100000);
        setcookie('year_value', '', 100000);
        setcookie('biography_value', '', 100000);
        $messages[] = 'Спасибо, результаты сохранены.';
    }

    $errors = array();
    $errors['fio'] = !empty($_COOKIE['fio_error']);
    $errors['email'] = !empty($_COOKIE['email_error']);
    $errors['checkbox'] = !empty($_COOKIE['checkbox_error']);
    $errors['abilities'] = !empty($_COOKIE['abilities_error']);
    $errors['limbs'] = !empty($_COOKIE['limbs_error']);
    $errors['gender'] = !empty($_COOKIE['gender_error']);
    $errors['year'] = !empty($_COOKIE['year_error']);

    if ($errors['fio']) {
        setcookie('fio_error', '', 100000);
        $messages[] = '<div class="error">Заполните имя!</div>';
    }
    if ($errors['email']) {
        setcookie('email_error', '', 100000);
        $messages[] = '<div class="error">Заполните email!</div>';
    }
    if ($errors['checkbox']) {
        setcookie('checkbox_error', '', 100000);
        $messages[] = '<div class="error">Поставьте галочку!</div>';
    }
    if ($errors['abilities']) {
        setcookie('abilities_error', '', 100000);
        $messages[] = '<div class="error">Выберете способности!</div>';
    }
    if ($errors['limbs']) {
        setcookie('limbs_error', '', 100000);
        $messages[] = '<div class="error">Укажите количество конечностей!</div>';
    }
    if ($errors['gender']) {
        setcookie('gender_error', '', 100000);
        $messages[] = '<div class="error">Укажите пол!</div>';
    }
    if ($errors['year']) {
        setcookie('year_error', '', 100000);
        $messages[] = '<div class="error">Заполните год рождения!</div>';
    }

    $values = array();
    $values['fio'] = empty($_COOKIE['fio_value']) ? '' : $_COOKIE['fio_value'];
    $values['email'] = empty($_COOKIE['email_value']) ? '' : $_COOKIE['email_value'];
    $values['checkbox'] = empty($_COOKIE['checkbox_value']) ? '' : $_COOKIE['checkbox_value'];
    $values['abilities'] = empty($_COOKIE['abilities_value']) ? '' : unserialize($_COOKIE['abilities_value']);
    $values['limbs'] = empty($_COOKIE['limbs_value']) ? '' : $_COOKIE['limbs_value'];
    $values['gender'] = empty($_COOKIE['gender_value']) ? '' : $_COOKIE['gender_value'];
    $values['year'] = empty($_COOKIE['year_value']) ? '' : $_COOKIE['year_value'];
    $values['biography'] = empty($_COOKIE['biography_value']) ? '' : $_COOKIE['biography_value'];



    include('form.php');
    include('modal.php');

    exit();
}
// POST
$user = 'u52803';
$pass = '9294062';
$db = new PDO('mysql:host=localhost;dbname=u52803', $user, $pass, [PDO::ATTR_PERSISTENT => true]);

$abilities = [];
$abilities_query = $db->query("SELECT id FROM abilities;");
while ($row = $abilities_query->fetch(PDO::FETCH_ASSOC)) {
    array_push($abilities, $row['id']);
}

function validateAbilities() {
    global $abilities;
    foreach ($_POST['abilities'] as $ability_id) {
        if (!in_array($ability_id, $abilities))
            return false;
    }
    return true;
}

$errors = FALSE;
if (empty($_POST['fio'])) {
    setcookie('fio_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
} else {
    setcookie('fio_value', $_POST['fio'], time() + 30 * 24 * 60 * 60);
}

if (empty($_POST['email']) || !preg_match('/^[\w\-\.]+@([\w\-]+\.)+[\w\-]{2,4}$/', $_POST['email'])) {
    setcookie('email_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
} else {
    setcookie('email_value', $_POST['email'], time() + 30 * 24 * 60 * 60);
}

if (empty($_POST['checkbox']) || !($_POST['checkbox'] == 'on' || $_POST['checkbox'] == 1)) {
    setcookie('checkbox_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
} else {
    setcookie('checkbox_value', $_POST['checkbox'], time() + 30 * 24 * 60 * 60);
}

if (empty($_POST['abilities']) || !is_array($_POST['abilities']) || !validateAbilities()) {
    setcookie('abilities_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
} else {
    setcookie('abilities_value', serialize($_POST['abilities']), time() + 30 * 24 * 60 * 60);
}

if (empty($_POST['limbs']) || !is_numeric($_POST['limbs']) || $_POST['limbs'] > 4 || $_POST['limbs'] < 0) {
    setcookie('limbs_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
} else {
    setcookie('limbs_value', $_POST['limbs'], time() + 30 * 24 * 60 * 60);
}

if (empty($_POST['gender']) || $_POST['gender'] != 'м' && $_POST['gender'] != 'ж') {
    setcookie('gender_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
} else {
    setcookie('gender_value', $_POST['gender'], time() + 30 * 24 * 60 * 60);
}

if (empty($_POST['year'])) {
    setcookie('year_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
} else {
    setcookie('year_value', $_POST['year'], time() + 30 * 24 * 60 * 60);
}

if ($errors) {
    header('Location: index.php');
    exit();
}

try {
    $stmt = $db->prepare("INSERT INTO users SET name = ?, year = ?, biography = ?, email = ?, limbs = ?, gender = ?, checkbox = ?");
    $stmt->execute([$_POST['fio'], $_POST['year'], $_POST['biography'], $_POST['email'], $_POST['limbs'], $_POST['gender'], 1]);
    if (!$stmt) {
        print('Error : ' . $stmt->errorInfo());
    }
} catch (PDOException $e) {
    print('Error : ' . $e->getMessage());
    exit();
}

$user_id = $db->lastInsertId();

foreach ($_POST['abilities'] as $ability_id) {
    try {
        $stmt = $db->prepare("INSERT INTO relations SET user_id = ?, ability_id = ?");
        $stmt->execute([$user_id, $ability_id]);
        if (!$stmt) {
            print('Error : ' . $stmt->errorInfo());
        }
    } catch (PDOException $e) {
        print('Error : ' . $e->getMessage());
        exit();
    }
}

setcookie('save', '1');
header('Location: index.php');
