<?php
echo json_encode([
    'column' => rand(0, 8)
]);
exit;

//$grid = [
//    ['_', '_', '_', 'S', '_', '_', '_', '_', '_',],
//    ['F', 'S', '_', 'x', 'S', '_', '_', '_', '_',],
//    ['F', 'S', 'F', 'x', 'S', '_', '_', '_', 'F',],
//    ['F', '_', '_', 'x', 'F', '_', '_', '_', 'S',],
//    ['S', '_', 'S', 'x', 'S', '_', '_', '_', 'F',],
//    ['F', 'S', 'F', 'x', 'F', '_', 'S', 'F', 'F',],
//];


const COLUMN_COUNT  = 9;
const ROW_COUNT     = 6;
const PLAYER_PIECE  = 1;
const AI_PIECE      = 2;
const WINDOW_LENGTH = 4;
const V_EMPTY       = 0;
const INF           = PHP_INT_MAX;

$board = $_POST['board'];
minimax($board, 5, 5, 6, 2);

//getValidLocation($grid);

$moves = 0;
//$grid  = makeMove($grid);
// Считать таблицу
// Сделать ход
function makeMove($grid)
{
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

//function getValidLocation($grid)
//{
//    for ($i = 0; $i <= 6; $i++) {
//        for ($j = 0; $j <= 9; $j++) {
//            if ($grid[$i][$j] === '_') {
//            }
//        }
//    }
//}

function winning_move($board, $piece)
{
    // Check horizontal locations for win
    for ($c = 0; $c <= COLUMN_COUNT - 3; $c++) {
        for ($r = 0; $r < ROW_COUNT; $r++) {
            if ($board[$r][$c] == $piece
                && $board[$r][$c + 1] == $piece
                && $board[$r][$c + 2] == $piece
                && $board[$r][$c + 3] == $piece) {
                return true;
            }
        }
    }
}

function is_valid_location(array $board, int $col): bool
{
    global $ROW_COUNT;

    return $board[$ROW_COUNT - 1][$col] === 0;
}

function minimax($board, $depth, $alpha, $beta, $maximizingPlayer)
{
    $valid_locations = get_valid_locations($board);
    $is_terminal     = is_terminal_node($board);

    if ($depth == 0 || $is_terminal) {
        if ($is_terminal) {
            if (winning_move($board, AI_PIECE)) {
                return array(null, 100000000000000);
            } elseif (winning_move($board, PLAYER_PIECE)) {
                return array(null, -10000000000000);
            } else {
                return array(null, 0);
            }
        } else {
            return array(null, score_position($board, AI_PIECE));
        }
    }

    if ($maximizingPlayer) {
        $value  = -INF;
        $column = $valid_locations[array_rand($valid_locations)];
        foreach ($valid_locations as $col) {
            $row    = get_next_open_row($board, $col);
            $b_copy = $board;
            drop_piece($b_copy, $row, $col, AI_PIECE);
            $new_score = minimax($b_copy, $depth - 1, $alpha, $beta, false)[1];
            if ($new_score > $value) {
                $value  = $new_score;
                $column = $col;
            }
            $alpha = max($alpha, $value);
            if ($alpha >= $beta) {
                break;
            }
        }

        return array($column, $value);
    } else {
        $value  = INF;
        $column = $valid_locations[array_rand($valid_locations)];
        foreach ($valid_locations as $col) {
            $row    = get_next_open_row($board, $col);
            $b_copy = $board;
            drop_piece($b_copy, $row, $col, PLAYER_PIECE);
            $new_score = minimax($b_copy, $depth - 1, $alpha, $beta, true)[1];
            if ($new_score < $value) {
                $value  = $new_score;
                $column = $col;
            }
            $beta = min($beta, $value);
            if ($alpha >= $beta) {
                break;
            }
        }

        return array($column, $value);
    }
}

function is_terminal_node($board)
{
    return winning_move($board, PLAYER_PIECE) ||
           winning_move($board, AI_PIECE) ||
           count(get_valid_locations($board)) == 0;
}

function get_valid_locations($board)
{
    $valid_locations = array();
    for ($col = 0; $col < COLUMN_COUNT; $col++) {
        if (is_valid_location($board, $col)) {
            array_push($valid_locations, $col);
        }
    }

    return $valid_locations;
}

function score_position($board, $piece)
{
    $score = 0;

    // Score center column
    $center_array = array_map('intval', array_column($board, floor(COLUMN_COUNT / 2)));
    $center_count = count(array_keys($center_array, $piece));
    $score        += $center_count * 3;

    // Score Horizontal
    for ($r = 0; $r < ROW_COUNT; $r++) {
        $tmp       = range(0, COLUMN_COUNT, 1);
        $row_array = array_map('intval', array_column($board[$r], $tmp));
        for ($c = 0; $c < COLUMN_COUNT - 3; $c++) {
            $window = array_slice($row_array, $c, WINDOW_LENGTH);
            $score  += evaluate_window($window, $piece);
        }
    }

    // Score Vertical
    for ($c = 0; $c < COLUMN_COUNT; $c++) {
        $col_array = array_map('intval', array_column($board, $c));
        for ($r = 0; $r < ROW_COUNT - 3; $r++) {
            $window = array_slice($col_array, $r, WINDOW_LENGTH);
            $score  += evaluate_window($window, $piece);
        }
    }

    // Score positive sloped diagonal
    for ($r = 0; $r < ROW_COUNT - 3; $r++) {
        for ($c = 0; $c < COLUMN_COUNT - 3; $c++) {
            $window = array();
            for ($i = 0; $i < WINDOW_LENGTH; $i++) {
                $window[] = $board[$r + $i][$c + $i];
            }
            $score += evaluate_window($window, $piece);
        }
    }

    for ($r = 0; $r < ROW_COUNT - 3; $r++) {
        for ($c = 0; $c < COLUMN_COUNT - 3; $c++) {
            $window = array();
            for ($i = 0; $i < WINDOW_LENGTH; $i++) {
                $window[] = $board[$r + 3 - $i][$c + $i];
            }
            $score += evaluate_window($window, $piece);
        }
    }

    return $score;
}

function drop_piece($board, $row, $col, $piece)
{
    $board[$row][$col] = $piece;
}


function evaluate_window($window, $piece)
{
    $score     = 0;
    $opp_piece = PLAYER_PIECE;
    if ($piece == PLAYER_PIECE) {
        $opp_piece = AI_PIECE;
    }

    if (count(array_keys($window, $piece)) == 4) {
        $score += 100;
    } elseif (count(array_keys($window, $piece)) == 3 && count(array_keys($window, V_EMPTY)) == 1) {
        $score += 5;
    } elseif (count(array_keys($window, $piece)) == 2 && count(array_keys($window, V_EMPTY)) == 2) {
        $score += 2;
    }

    if (count(array_keys($window, $opp_piece)) == 3 && count(array_keys($window, V_EMPTY)) == 1) {
        $score -= 4;
    }

    return $score;
}

function get_next_open_row($board, $col)
{
    for ($r = 0; $r < ROW_COUNT; $r++) {
        if ($board[$r][$col] == 0) {
            return $r;
        }
    }
}

