<?php
namespace App\Controller;

use App\Controller\GameController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TicTacToeController extends AbstractController {
    private $gameController;
    
    public function index(Request $request): Response {
        return $this->render('menu/menu.html.twig');
    }

    /**
     * Initializes GameController with the required Values and redirects to Play Route
     */
    public function initGame(Request $request): Response {
        
        $this->gameController = new GameController();

        if (isset($_POST['playertype']) && $_POST['playertype'] === 'pvp') {
            $this->gameController->setPVP(true);
        } else {
            $this->gameController->setPVP(false);
        }

        // Storing game data to Session
        $session = $request->getSession();
        $this->gameController->storeGameData($session);
        
        return $this->redirectToRoute('tic_tac_toe.game');
    }

    /**
     * Plays game and renders Gameboard with the new Values
     */
    public function play(Request $request):Response {
        // Restoring GameController from Session
        $session = $request->getSession();
        $this->gameController = new GameController();
        $this->gameController->restoreGameData($session);
        
        // Reading Users move
        $arr = [];
        foreach($_POST as $key => $val) {
            $arr = explode(",", $key);
        }

        // Playing users next Move
        $winner = $this->gameController->getWinner();
        if (!empty($arr)) {
            $winner = $this->gameController->playNextMove($arr[0], $arr[1]);
        }
        
        // Initializing variables to bind to View
        $board = $this->gameController->getBoard();
        $currentPlayer = $this->gameController->getCurrentPlayer();
        $pvp = $this->gameController->getPVP();

        // Storing GameController to Session
        $this->gameController->storeGameData($session);

        // Binding required Variables to View and returning the View
        return $this->render('tictactoe/play.html.twig', [
            'board' => $board,
            'currentPlayer' => $currentPlayer,
            'winner' => $winner,
            'pvp' => $pvp
        ]);
    }
}