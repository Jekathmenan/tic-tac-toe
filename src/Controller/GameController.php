<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GameController extends AbstractController {
    
    // Declaring (and initializing) GameController's Properties
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

    /**
     * Resets GameController with default values
     */
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

    /**
     * Changes Current Player
     */
    private function changePlayer() {
        if ($this->currentPlayer === $this->player1) {
            $this->currentPlayer = $this->player2;
        } else  {
            $this->currentPlayer = $this->player1;
        }
    }

    /**
     * 
     */
    private function makeMove($x, $y, $player) {
        $moveMade = false;
        
        // Makes Move for Player and changes Player 
        if ($this->board[$x][$y] === '-') {
            $this->board[$x][$y] = $player;
            $this->changePlayer(); 
            $this->moves += 1;
            
            // setting move as made
            $moveMade = true;
        }

        // Checking if game is won
        if ($this->moves >=5 && $player1Win = $this->checkWin($this->player1)) {
            $this->gameFinished = true;
            $this->winner = $this->player1;
        } else if ($this->moves >=5 && $player2Win = $this->checkWin($this->player2)) {
            $this->gameFinished = true;
            $this->winner = $this->player2;                
        }

        // returning if move was made
        return $moveMade;
    }

    /**
     * Method to Play next move
     * 
     * Is called from TicTacToeController
     */
    public function playNextMove($x, $y) {
        // Move is not possible
        if ($x == null || $y == null) { 
            return $this->winner; 
        } 

        if (!$this->gameFinished) {
            // Move Player
            $playerMoved = $this->makeMove($x, $y, $this->currentPlayer);
            
            // AI schould play if player has moved, player has not won yet and there are moves Left
            if (!$this->gameFinished && !$this->pvp && $this->moves < 9 && $playerMoved) {
                $this->letAIPlay();
            }
        }

        // Checking if no one has won
        if ($this->moves == 9 && $this->winner == '-') {
            $this->gameFinished = true;
            $this->winner = 'Keiner';
        }

        return $this->winner;
    }

    /**
     * Method to let AI Play
     */
    private function letAIPlay() {
        $possibleMoves = [];
        // Finding possible Moves
        for ($i = 0; $i < 3; $i++) {
            for ($j = 0; $j < 3; $j++) {
                $curMove = $this->board[$i][$j];
                if ($curMove === '-') {
                    array_push($possibleMoves, [$i, $j]);
                }
            }
        }
        
        // defining which AI should play
        if ($this->difficulty == 2) {
            // Letting intelligent AI play
            $this->letIntelligentAIPlay($possibleMoves);
        } else {
            // Letting random AI play
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

    /**
     * Method to find out how many moves a player has made
     */
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

    /**
     * Method to check if given Player has won
     */
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

    /**
     * Method to store gameController to Session
     */
    public function storeGameData($session) {
        // Defining gameController Array with serialized values
        $gameController = [];
        $gameController['board'] = serialize($this->board);
        $gameController['currentPlayer'] = serialize($this->currentPlayer);
        $gameController['moves'] = serialize($this->moves);
        $gameController['gameEnd'] = serialize($this->gameFinished);
        $gameController['winner'] = serialize($this->winner);
        $gameController['pvp'] = serialize($this->pvp);

        // Setting gameController to Session
        $session->set('gameController', $gameController);
    }
    
    /**
     * Method to restore Game Data from Session
     */
    public function restoreGameData($session) {
        if ($session->has('gameController')) {
            $gameController = $session->get('gameController');
        
            // Getting game data from session and setting them to this object
            $this->board = unserialize($gameController['board']);
            $this->currentPlayer = unserialize($gameController['currentPlayer']);
            $this->moves = unserialize($gameController['moves']);
            $this->gameFinished = unserialize($gameController['gameEnd']);
            $this->winner = unserialize($gameController['winner']);
            $this->pvp = unserialize($gameController['pvp']);
        }
    }

    // Getters and Setters
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

    public function getPVP() {
        return $this->pvp;
    }

    public function setPVP($pvp) {
        $this->pvp = $pvp;
    }
}