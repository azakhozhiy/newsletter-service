#!/usr/bin/env php
<?php

declare(strict_types=1);

use WaHelp\Core\Application;
use WaHelp\Core\Database;

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

require __DIR__.'/vendor/autoload.php';

/** @var Application $app */
$app = require __DIR__.'/bootstrap/app.php';

/** @var Database $database */
$database = $app->make(Database::class);

$stmt = $database->getConnection()->prepare('SELECT * FROM user_newsletter');
$stmt->execute(); // Выполняем запрос
$result = $stmt->fetchAll(PDO::FETCH_ASSOC); // Получаем все строки в виде ассоциативного массива

die(print_r($result));

// Путь к папке с SQL скриптами
$sqlDir = __DIR__.'/sql';

// Проверяем, существует ли папка и читаем файлы
if (!is_dir($sqlDir)) {
    echo "Папка с SQL скриптами не найдена.\n";
    exit(1);
}

$sqlFiles = array_values(array_diff(scandir($sqlDir), ['.', '..']));

if (empty($sqlFiles)) {
    echo "Нет доступных SQL скриптов в папке.\n";
    exit(1);
}

// Выводим все имена файлов в консоль
echo "Доступные SQL скрипты:\n";
foreach ($sqlFiles as $index => $fileName) {
    echo "[$index] $fileName\n";
}

// Просим пользователя выбрать скрипт
echo "Выберите номер скрипта для выполнения: ";
$input = trim(fgets(STDIN));

// Проверяем ввод пользователя
if (!isset($sqlFiles[$input])) {
    echo "Неправильный выбор. Скрипт не найден.\n";
    exit(1);
}

// Получаем выбранное имя файла
$selectedFile = $sqlFiles[$input];
echo "Вы выбрали: $selectedFile\n";

// Полный путь к выбранному файлу
$selectedFilePath = $sqlDir.'/'.$selectedFile;
echo "Полный путь к файлу: $selectedFilePath\n";

// Читаем содержимое файла
$sqlScript = file_get_contents($selectedFilePath);
if ($sqlScript === false) {
    echo "Не удалось прочитать содержимое файла.\n";
    exit(1);
}
// Выполняем скрипт через класс Database
try {
    $database->getConnection()->exec($sqlScript); // Выполняем SQL скрипт

    echo "Скрипт успешно выполнен.\n";
} catch (Exception $e) {
    echo "Ошибка при выполнении скрипта: ".$e->getMessage()."\n";
}


