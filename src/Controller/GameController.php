<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GameController extends AbstractController {
    private $board;
    private $player1;
    private $player2;
    private $currentPlayer;
    private $moves = 0;
    private $gameFinished = false;
    private $winner = '-';
    private $difficulty = 1;
    private $pvp = false;

    public function __construct() {
        $this->setGame();
    }

    public function setGame() {
        $this->board = [
            ['-', '-', '-'],
            ['-', '-', '-'],
            ['-', '-', '-'],
        ];

        $this->player1 = 'X';
        $this->player2 = 'O';

        $this->currentPlayer = $this->player1;
    }

    private function changePlayer() {
        if ($this->currentPlayer === $this->player1) {
            $this->currentPlayer = $this->player2;
        } else  {
            $this->currentPlayer = $this->player1;
        }
    }

    private function makeMove($x, $y, $player) {
        $moveMade = false;
        
        if ($this->board[$x][$y] === '-') {

            
            $this->board[$x][$y] = $player;
            $this->changePlayer(); 
            $this->moves += 1;

            $moveMade = true;
        }

        if ($this->moves >=5 && $player1Win = $this->checkWin($this->player1)) {
            $this->gameFinished = true;
            $this->winner = $this->player1;
        } else if ($this->moves >=5 && $player2Win = $this->checkWin($this->player2)) {
            $this->gameFinished = true;
            $this->winner = $this->player2;                
        }


        return $moveMade;
    }

    public function playNextMove($x, $y) {
        if ($x == null || $y == null) { 
            return $this->winner; 
        } 

        if (!$this->gameFinished) {
            $this->makeMove($x, $y, $this->currentPlayer);
            
            if ($this->moves < 9) {
                $this->letAIPlay();
            }
        }

        if ($this->moves == 9 && $this->winner == '-') {
            $this->gameFinished = true;
            $this->winner = 'Keiner';
        }

        return $this->winner;
    }

    private function letAIPlay() {
        $possibleMoves = [];
        for ($i = 0; $i < 3; $i++) {
            for ($j = 0; $j < 3; $j++) {
                $curMove = $this->board[$i][$j];

                if ($curMove === '-') {
                    array_push($possibleMoves, [$i, $j]);
                }
            }
        }
        
        if ($this->difficulty == 2) {
            $this->letIntelligentAIPlay($possibleMoves);
        } else {
            if (count($possibleMoves) > 1) {
                $rnd = rand(1, count($possibleMoves) -1);
                $move = $possibleMoves[$rnd];

                $this->makeMove($move[0], $move[1], $this->currentPlayer);
            }
        }
    }

    private function letIntelligentAIPlay($possibleMoves) {
        // to do: Intelligent AI Logic
    }

    private function getPlayersMoves($player) {
        $count = 0;
        foreach ($this->board as $row) {
            foreach ($row as $field) {
                if ($field === $player) {
                    $count++;
                }
            }
        }

        return $count;
    }

    public function checkWin($player) {
        return (
            $this->board[0][0] === $this->board[0][1] && $this->board[0][1] === $this->board[0][2] && $this->board[0][1] === $player
            || $this->board[1][0] === $this->board[1][1] && $this->board[1][1] === $this->board[1][2] && $this->board[1][1] === $player
            || $this->board[2][0] === $this->board[2][1] && $this->board[2][1] === $this->board[2][2] && $this->board[2][1] === $player
            || $this->board[0][0] === $this->board[1][0] && $this->board[1][0] === $this->board[2][0] && $this->board[1][0] === $player
            || $this->board[0][1] === $this->board[1][1] && $this->board[1][1] === $this->board[2][1] && $this->board[1][1] === $player
            || $this->board[0][2] === $this->board[1][2] && $this->board[1][2] === $this->board[2][2] && $this->board[1][2] === $player
            || $this->board[0][0] === $this->board[1][1] && $this->board[1][1] === $this->board[2][2] && $this->board[2][2] === $player
            || $this->board[0][2] === $this->board[1][1] && $this->board[1][1] === $this->board[2][0] && $this->board[0][2] === $player
        );
    }

    public function storeGameData($session) {
        $gameController = [];
        $gameController['board'] = serialize($this->board);
        $gameController['currentPlayer'] = serialize($this->currentPlayer);
        $gameController['moves'] = serialize($this->moves);
        $gameController['gameEnd'] = serialize($this->gameFinished);
        $gameController['winner'] = serialize($this->winner);

        $session->set('gameController', $gameController);
    }
    

    public function restoreGameData($session) {
        $this->gameController = new GameController();
        if ($session->has('gameController')) {
            $gameController = $session->get('gameController');
        
            // Getting game data from session
            $this->board = unserialize($gameController['board']);
            $this->currentPlayer = unserialize($gameController['currentPlayer']);
            $this->moves = unserialize($gameController['moves']);
            $this->gameFinished = unserialize($gameController['gameEnd']);
            $this->winner = unserialize($gameController['winner']);
        }
        

    }

    public function getBoard() {
        return $this->board;
    }

    public function getCurrentPlayer() {
        return $this->currentPlayer;
    }

    public function setBoard($board) {
        $this->board = $board;
    }

    public function setCurrentPlayer($currentPlayer) {
        $this->currentPlayer = $currentPlayer;
    }

    public function getMoves() {
        return $this->moves;
    }

    public function setMoves($moves) {
        $this->moves = $moves;
    }

    public function getWinner() {
        return $this->winner;
    }

    public function setWinner($winner) {
        $this->winner = $winner;
    }

    public function finishGame($gameFinished) {
        $this->gameFinished = $gameFinished;
    }
    
    public function isGameFinished() {
        return $this->gameFinished;
    }
}