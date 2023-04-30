<?php
namespace App\Controller;

use App\Controller\GameController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TicTacToeController extends AbstractController {
    private $gameController;
    
    public function index(Request $request): Response {
        
        if ($request->isMethod('POST')) {
            $board = $request->request->get('board');
            $board = json_decode($board, true);
        }
        
        $this->gameController = new GameController();
        
        $board = $this->gameController->getBoard();
        $currentPlayer = $this->gameController->getCurrentPlayer();
        $winner = $this->gameController->getWinner();

        // Storing game data to Session
        $session = $request->getSession();
        $this->gameController->storeGameData($session);
        
        return $this->render('tictactoe/play.html.twig', [
            'board' => $board,
            'currentPlayer' => $currentPlayer,
            'winner' => $winner
        ]);
    }

    public function play(Request $request) {
        $session = $request->getSession();
        $this->gameController = new GameController();
        $this->gameController->restoreGameData($session);
        
        $arr = [];
        
        foreach($_POST as $key => $val) {
            $arr = explode(",", $key);
        }

        
        
        $winner = $this->gameController->playNextMove($arr[0], $arr[1]);
        $board = $this->gameController->getBoard();
        $currentPlayer = $this->gameController->getCurrentPlayer();

        if ($winner === 'X' || $winner === 'O') {
            $this->addFlash('game-end', 'Spiel ist zu Ende. Spieler ' . $winner . ' hat gewonnen.');
        }

        $this->gameController->storeGameData($session);

        return $this->render('tictactoe/play.html.twig', [
            'board' => $board,
            'currentPlayer' => $currentPlayer,
            'winner' => $winner
        ]);
    }
}