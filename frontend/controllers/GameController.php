<?php

namespace frontend\controllers;


use backend\models\Game;
use backend\models\Gamecards;
use backend\models\Gameusers;
use backend\models\User;
use yii\db\Expression;
use yii\web\Controller;
use yii\web\Response;

class GameController extends Controller
{
    public function beforeAction($action)
    {
//        if(!\Yii::$app->request->isAjax){
//            throw new ForbiddenHttpException("This Site is not available for web access!");
//        }
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    /**
     * Lets the player select a card to play
     * TODO: We still need a function for picking the winning card. Maybe also in this function?!
     *
     * Request params:
     * -clientToken
     * -gameId
     * -cardId (the id of the card that should be played)
     *
     * @return array
     */
    public function actionPlayCard()
    {
        $check = $this->checkRequest();
        if (!$check['success']) {
            return $check;
        }

        /** @var Game $lobby */
        $lobby = $check['lobby'];
        /** @var User $user */
        $user = $check['user'];

        if ($user->is_judge == 1) {
            return $this->errorResponse(['You cannot play cards as judge!']);
        }

        /** @var Gamecards $playedCard */
        $playedCard = Gamecards::find()->where(['game_id' => $lobby->game_id, 'card_id' => \Yii::$app->request->get('cardId'), 'user_id' => $user->user_id])->one();

        if (empty($playedCard)) {
            return $this->errorResponse(["This card is not in your Hand!"]);
        }

        $blackCard = $lobby->getCurrentBlackCard();
        $chosenCards = $user->getCurrentChosenCards();

        if (count($chosenCards) >= $blackCard->blanks) {
            return $this->errorResponse(['You cannot play any more cards than blanks on the Black card!']);
        }

        if ($playedCard->is_chosen == 1) {
            return $this->errorResponse(["You already have chosen this card. Choose another one!"]);
        }

        $playedCard->is_chosen = 1;
        $playedCard->save();
        $lobby->updateActivity();

        return ['success'=>true];
    }

    /**
     * Checks the gameId and clientToken of a request
     *
     * @return array
     */
    private function checkRequest()
    {
        $lobbyId = \Yii::$app->request->get('gameId');
        $clientToken = \Yii::$app->request->get('clientToken');

        if (empty($lobbyId)) {
            return $this->errorResponse(["gameId not set."]);
        }

        $tokenCheck = $this->checkClientToken($clientToken);

        if (!$tokenCheck['success']) {
            return $this->errorResponse([$tokenCheck['error']]);
        }

        /** @var User $user */
        $user = $tokenCheck['user'];

        /** @var Game $lobby */
        $lobby = Game::find()->joinWith('gameusers')->where(['cah_game.game_id' => $lobbyId, 'cah_gameusers.user_id' => $user->user_id])->one();

        if (empty($lobby)) {
            return $this->errorResponse(["No Lobby with this ID found or you are not a member of this lobby"]);
        }

        if ($lobby->state != Game::STATE_STARTED) {
            return $this->errorResponse(["Lobby is not started!"]);
        }
        $user->updateActivity();
        return ['success' => true, 'user' => $user, 'lobby' => $lobby];
    }

    /**
     * The standard form for an error response
     *
     * @param array $error
     * @return array
     */
    private function errorResponse($error = [])
    {
        return [
            'success' => false,
            'errors' => $error
        ];

    }

    /**
     * Function for checking the validity of a clientToken
     *
     * @param $clientToken
     * @return array
     */
    private function checkClientToken($clientToken)
    {
        if (empty($clientToken)) {
            return ['success' => false, 'error' => "ClientToken not set."];
        }

        /** @var User $user */
        $user = User::find()->where(['generated_id' => $clientToken])->one();
        if (empty($user)) {
            return ['success' => false, 'error' => "Invalid Token"];
        }

        return ['success' => true, 'user' => $user];
    }

    public function actionChooseWinner()
    {
        $check = $this->checkRequest();
        if (!$check['success']) {
            return $check;
        }

        /** @var Game $lobby */
        $lobby = $check['lobby'];
        /** @var User $user */
        $user = $check['user'];

        if ($user->is_judge != 1) {
            return $this->errorResponse(['You are not the judge!']);
        }

        /** @var Gamecards $chosenCard */
        $chosenCard = Gamecards::find()->where(['game_id' => $lobby->game_id, 'card_id' => \Yii::$app->request->get('cardId'), 'is_chosen' => 1])->one();

        if (empty($chosenCard)) {
            return $this->errorResponse(['Invalid card id. This is not a chosen card']);
        }

        $chosenCard->user->score++;
        $chosenCard->user->updateActivity();
        Gamecards::deleteAll(new Expression("game_id = {$lobby->game_id} AND is_chosen = 1 AND card_id <> {$chosenCard->card_id}"));
        $lobby->state = Game::STATE_END_OF_ROUND;
        $lobby->updateActivity();
        return ['success' => true];
    }

    public function actionNextRound()
    {
        $check = $this->checkRequest();
        if (!$check['success']) {
            return $check;
        }

        /** @var Game $lobby */
        $lobby = $check['lobby'];
        /** @var User $user */
        $user = $check['user'];

        if ($user->is_judge != 1) {
            return $this->errorResponse(['You are not the judge!']);
        }

        $allUsers = $lobby->users;

        $nextJudge = NULL;
        $foundJudge = false;
        $gameEnded = false;
        $winner = NULL;

        foreach ($allUsers as $player) {
            if ($player->score >= $lobby->target_score) {
                $gameEnded = true;
                $winner = $player;
            }
            if ($player->is_judge) {
                $foundJudge = true;
                $player->is_judge = 0;
            } elseif (empty($nextJudge) && $foundJudge) {
                $nextJudge = $player;
                $nextJudge->is_judge = 1;
            }
            $player->updateActivity();
        }

        if (empty($nextJudge) && !empty($allUsers[0])) {
            $nextJudge = $allUsers[0];
            $nextJudge->is_judge = 1;
            $nextJudge->updateActivity();
        }

        if ($gameEnded) {
            $lobby->state = Game::STATE_FINISHED;
            Gamecards::deleteAll(['game_id' => $lobby->game_id]);
            Gameusers::deleteAll(['game_id' => $lobby->game_id]);
        } else {
            $lobby->state = Game::STATE_STARTED;
        }

        $lobby->updateActivity();
        return ['success' => true, 'state' => $lobby->state, 'winner' => empty($winner) ? NULL : $winner->user_id];
    }

    public function actionCheckWinner()
    {
        $check = $this->checkRequest();
        if (!$check['success']) {
            return $check;
        }

        /** @var Game $lobby */
        $lobby = $check['lobby'];
        /** @var User $user */
        $user = $check['user'];
        $user->updateActivity();
        if ($lobby->state != Game::STATE_END_OF_ROUND) {
            return $this->errorResponse(['Game is not in "END OF ROUND"-State']);
        }

        /** @var Gamecards $winnerCard */
        $winnerCard = $lobby->getGamecards()->where(['is_chosen' => 1])->one();
        if (!empty($winnerCard)) {
            return ['success' => true, 'winner' => $winnerCard->user_id, 'winningCard' => $winnerCard->card_id];
        }
        return $this->errorResponse(['Could not determine winnercard!']);
    }

    /**
     * Returns the status of the chosen cards. If every user has chosen the correct amount of cards
     * they are also returned.
     *
     * Request params:
     * -clientToken
     * -gameId
     *
     * @return array
     */
    public function actionGetCurrentChosenCards()
    {
        $check = $this->checkRequest();
        if (!$check['success']) {
            return $check;
        }

        /** @var Game $game */
        $game = $check['lobby'];
        /** @var User $user */
        $user = $check['user'];

        $gameuser = Gameusers::find()->where(['game_id' => $game->game_id, 'user_id' => $user->user_id]);
        if(empty($gameuser)){
            return $this->errorResponse(["User not in this game"]);
        }

        $game->timeOutUsers();

        if ($game->getCurrentBlackCard()->blanks * ($game->getGameusers()->count() - 1) <= count($game->getGamecards()->andWhere(['is_chosen' => 1])->all())) {
            $allChosen = true;
        }else{
            $allChosen = false;
        }

        if (!$allChosen) {
            return ['cards' => [], 'all_chosen' => $allChosen];
        }

        /** @var Gamecards[] $gamecards */
        $gamecards = $game->getGamecards()->joinWith('card', false)->orderBy('user_id')->andWhere(['is_black' => 0, 'is_chosen' => 1])->andWhere(['IS NOT', 'user_id', NULL])->all();
        $sortedCards = [];
        if (!empty($gamecards)) {
            foreach ($gamecards as $card) {
                $sortedCards[$card->user_id][] = ['card_id' => $card->card_id, 'text' => $card->card->text];
            }
            shuffle($sortedCards);
        }
        return ['success' => true, 'cards' => $sortedCards, 'all_chosen' => $allChosen];
    }
}