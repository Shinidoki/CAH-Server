<?php
/**
 * Created by PhpStorm.
 * User: Tony
 * Date: 02.12.2016
 * Time: 10:03
 */

namespace frontend\controllers;


use backend\models\Game;
use backend\models\Gamecards;
use backend\models\Gameusers;
use backend\models\User;
use yii\web\Controller;
use yii\web\Response;

class CardController extends Controller
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
     * Draws the needed amount of cards to have a full hand as normal player.
     * As judge, a black card is drawn
     *
     * Request params:
     * -clientToken
     * -gameId
     *
     * @param $clientToken
     * @param $gameId
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionDrawCard($clientToken, $gameId)
    {
        $check = $this->checkRequest($clientToken, $gameId);
        if (!$check['success']) {
            return $check;
        }

        /** @var Game $game */
        $game = $check['lobby'];
        /** @var User $user */
        $user = $check['user'];

        /** @var Gamecards[] $gamecards */
        $numberofcards = $user->getGamecards()->count();
        if ($user['is_judge'] == 1){
            $currentBlack = $game->getCurrentBlackCard();
            if (!empty($currentBlack)) {
                return ['success' => true, 'cards' => [$currentBlack]];
            }
            $gamecards = $game->getFreeCards()->andWhere(['is_black' => 1])->all();
            $cardstodraw = 1;
        }else{
            $gamecards = $game->getFreeCards()->andWhere(['is_black' => 0])->all();
            $cardstodraw = Game::MAX_CARDS - $numberofcards;
        }

        if ($cardstodraw <= 0){
            return ['success' => true, 'cards' => []];
        }

        $card = array_rand($gamecards, $cardstodraw);

        if(is_array($card)){
            $usercards = [];
            foreach($card as $crd){
                $usercards[]=$gamecards[$crd];
                \Yii::$app->db->createCommand()->update(Gamecards::tableName(), ['user_id' => $user->user_id], ['game_id' => $game->game_id, 'card_id' => $gamecards[$crd]->card_id])->execute();
            }
            return ['success' => true, 'cards' => $usercards];
        }else{
            \Yii::$app->db->createCommand()->update(Gamecards::tableName(),['user_id'=>$user->user_id],['game_id'=>$game->game_id, 'card_id'=>$gamecards[$card]->card_id])->execute();
            return ['success' => true, 'cards' => [$gamecards[$card]]];
        }
    }

    /**
     * Checks the gameId and clientToken of a request
     *
     * @param $clientToken
     * @param $lobbyId
     * @return array
     */
    private function checkRequest($clientToken, $lobbyId)
    {
        if (empty($lobbyId)) {
            return $this->errorResponse(["GameID not set."]);
        }

        $tokenCheck = $this->checkClientToken($clientToken);

        if (!$tokenCheck['success']) {
            return $this->errorResponse([$tokenCheck['error']]);
        }

        /** @var User $user */
        $user = $tokenCheck['user'];

        /** @var Game $lobby */
        $lobby = Game::find()->joinWith('gameusers')->where([Game::tableName().'.game_id' => $lobbyId, Gameusers::tableName().'.user_id' => $user->user_id])->one();

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

    /**
     * Gets the current cards in hand of a player
     *
     * Request params:
     * -clientToken
     * -gameId
     *
     * @param $clientToken
     * @param $gameId
     * @return array
     */
    public function actionGetCurrentCards($clientToken, $gameId)
    {
        $check = $this->checkRequest($clientToken, $gameId);
        if (!$check['success']) {
            return $check;
        }

        /** @var Game $game */
        $game = $check['lobby'];
        /** @var User $user */
        $user = $check['user'];

        $gamecards = $game->getGamecards()->joinWith('card', false)->select(['cah_card.card_id', 'text', 'is_black', 'blanks'])->asArray()->andWhere(['is_black' => $user['is_judge'], 'user_id' => $user->user_id])->all();

        return ['success' => true, 'cards' => $gamecards];
    }

    /**
     * Returns the current black-card of the round
     *
     * Request params:
     * -clientToken
     * -gameId
     *
     * @param $clientToken
     * @param $gameId
     * @return array
     */
    public function actionGetCurrentBlackcard($clientToken, $gameId)
    {
        $check = $this->checkRequest($clientToken, $gameId);
        if (!$check['success']) {
            return $check;
        }

        /** @var Game $game */
        $game = $check['lobby'];

        $gamecard = $game->getGamecards()->joinWith('card', false)->select(['cah_card.card_id', 'text', 'is_black', 'blanks'])->asArray()->andWhere(['is_black' => 1])->andWhere(['IS NOT', 'user_id', NULL])->one();

        return ['success' => true, 'card' => $gamecard];
    }
}