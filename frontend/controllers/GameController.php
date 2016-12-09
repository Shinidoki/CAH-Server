<?php

namespace frontend\controllers;


use backend\models\Game;
use backend\models\Gamecards;
use backend\models\Gameusers;
use backend\models\User;
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

        /** @var Gamecards $playedCard */
        $playedCard = Gamecards::find()->where(['game_id' => $lobby->game_id, 'card_id' => \Yii::$app->request->get('cardId'), 'user_id' => $user->user_id])->one();

        if (empty($playedCard)) {
            return $this->errorResponse(["This card is not in your Hand!"]);
        }

        $blackCard = $lobby->getCurrentBlackCard();
        $chosenCards = $user->getCurrentChosenCards();

        if (count($chosenCards) >= $blackCard->blanks) {
            return $this->errorResponse(['You cannot pick any more cards than blanks on the Black card!']);
        }

        $playedCard->is_chosen = 1;
        $playedCard->save();
        $user->updateActivity();
        $lobby->updateActivity();

        return ['success'=>true];
    }

    private function checkRequest()
    {
        $lobbyId = \Yii::$app->request->get('lobbyId');
        $clientToken = \Yii::$app->request->get('clientToken');

        if (empty($lobbyId)) {
            return $this->errorResponse(["LobbyId not set."]);
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

        return ['success' => true, 'user' => $user, 'lobby' => $lobby];
    }

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

    public function actionGetCurrentChosenCards()
    {
        $clientToken = \Yii::$app->request->get('clientToken');
        $lobbyId = \Yii::$app->request->get('lobbyId');
        if(empty($clientToken)){
            return $this->errorResponse(["No clienttoken defined!"]);
        }

        /** @var User $user */
        $user = User::find()->where(['generated_id' => $clientToken])->one();
        if(empty($user)){
            return $this->errorResponse(["Invalid Token"]);
        }
        /** @var Game $game */
        $game = Game::find()->where(['game_id' => $lobbyId])->one();
        if(empty($game)){
            return $this->errorResponse(["Invalid Game"]);
        }
        $gameuser = Gameusers::find()->where(['game_id' => $game->game_id, 'user_id' => $user->user_id]);
        if(empty($gameuser)){
            return $this->errorResponse(["User not in this game"]);
        }

        if($game->getCurrentBlackCard()->blanks * count($game->getGameusers()->all()) == count($game->getGamecards()->andWhere(['is_chosen' => 1])->all())){
            $allChosen = 1;
        }else{
            $allChosen = 0;
        }

        $gamecards = $game->getGamecards()->joinWith('card',false)->select(['cah_card.card_id','text'])->asArray()->andWhere(['is_black' => 0, 'is_chosen' => 1])->andWhere(['IS NOT', 'user_id', NULL])->all();
        return ['cards'=>$gamecards,'allChosen'=>$allChosen];
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
}