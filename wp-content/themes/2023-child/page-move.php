<?php
$grid = [
    ['_', '_', '_', 'S', '_', '_', '_', '_', '_',],
    ['F', 'S', '_', 'x', 'S', '_', '_', '_', '_',],
    ['F', 'S', 'F', 'x', 'S', '_', '_', '_', 'F',],
    ['F', '_', '_', 'x', 'F', '_', '_', '_', 'S',],
    ['S', '_', 'S', 'x', 'S', '_', '_', '_', 'F',],
    ['F', 'S', 'F', 'x', 'F', '_', 'S', 'F', 'F',],
];


echo '<pre style="border: 1px solid red; padding: 35px; width: 75%; margin: 20px auto; display: block;">';
print_r($_POST);
echo '</pre>';

function getValidLocation($grid)
{
    for ($i = 0; $i <= 6; $i++) {
        for ($j = 0; $j <= 9; $j++) {
            if ($grid[$i][$j] === '_') {
            }
        }
    }
}

getValidLocation($grid);

$moves = 0;
$grid = makeMove($grid);
// Считать таблицу
// Сделать ход
function makeMove ($grid) {
    for ($i = 0; $i <= 6; $i++) {
        for ($j = 0; $j <= 9; $j++) {
            if ($grid[$i + 3][$j + 3] !== 'x' &&
                $grid[$i][$j] === 'F' &&
                $grid[$i + 1][$j + 1] === 'F' &&
                $grid[$i + 2][$j + 2] === 'F') {
                $grid[$i + 3][$j + 3] = 'S';
            }
            if ($grid[$i + 3][$j + 3] !== 'x' &&
                $grid[$i][$j] === 'F' &&
                $grid[$i][$j + 1] === 'F' &&
                $grid[$i][$j + 2] === 'F') {
                $grid[$i][$j + 3] = 'S';
            }
            if ($grid[$i][$j] === 'F' &&
                $grid[$i + 1][$j] === 'F' &&
                $grid[$i + 2][$j] === 'F') {
                $grid[$i][$j + 3] = 'S';
            }
        }
    }
    return $grid;
}

// Проверка на чей первый ход, чтобы узнать пустая ли доска
function me()
{
    return $_POST['player'] === 'F' ? 'S' : 'F';
}
