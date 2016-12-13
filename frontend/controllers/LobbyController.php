<?php
namespace frontend\controllers;


use backend\models\Category;
use backend\models\Game;
use backend\models\Gameusers;
use backend\models\User;
use yii\db\Expression;
use yii\web\Controller;
use yii\web\Response;

class LobbyController extends Controller
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
     * Registers the user in the database and returns him his clientToken
     *
     * Request Params:
     * -name
     *
     * @return array
     */
    public function actionAuthenticate()
    {
        $userName = \Yii::$app->request->get('name');

        if(empty($userName)){
            return $this->errorResponse(["No Username defined!"]);
        }

        $newUser = new User();
        $newUser->user_name = $userName;
        if ($newUser->updateActivity()) {
            return [
                'success' => true,
                'clientToken' => $newUser->generated_id
            ];
        } else {
            return $this->errorResponse($newUser->errors);
        }
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
     * Returns all games with the player count
     *
     * @return array
     */
    public function actionGetLobbies()
    {
        $games = Game::find()->select(['cah_game.*', 'user_count' => 'COUNT(user_id)'])->addSelect(new Expression("10 as max_players"))->leftJoin('cah_gameusers', 'cah_game.game_id = cah_gameusers.game_id')->groupBy('cah_game.game_id')->asArray()->all();

        return [
            'success' => true,
            'lobbies' => $games
        ];
    }

    /**
     * Lets the user create a new lobby/game
     *
     * Request Params:
     * -clientToken
     * -gameName (optional)
     *
     * @return array
     */
    public function actionCreateLobby()
    {
        $clientToken = \Yii::$app->request->get('clientToken');
        $gameName = \Yii::$app->request->get('gameName');

        $tokenCheck = $this->checkClientToken($clientToken);

        if (!$tokenCheck['success']) {
            return $this->errorResponse([$tokenCheck['error']]);
        }

        /** @var User $user */
        $user = $tokenCheck['user'];

        $newGame = new Game();
        $newGame->game_name = empty($gameName) ? "CAH Game ".mt_rand() : $gameName;
        $newGame->state = Game::STATE_INLOBBY;
        $newGame->host_user_id = $user->user_id;

        if ($newGame->updateActivity()) {
            $newGame->link('hostUser', $user);
            $newGame->link('categories', Category::find()->one());
            Gameusers::deleteAll(['user_id' => $user->user_id]);
            $user->link('games',$newGame);
            $user->is_judge = 1;
            $user->updateActivity();
            return [
                'success' => true,
                'lobbyId' => $newGame->game_id
            ];
        } else {
            return $this->errorResponse($newGame->errors);
        }
    }

    /**
     * Checks if the clientToken is registered on the server and returns the user
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
     * Lets a user join an open lobby
     *
     * Request params:
     * -clientToken
     * -lobbyId
     *
     * @return array
     */
    public function actionJoinLobby()
    {
        $clientToken = \Yii::$app->request->get('clientToken');
        $lobbyId = \Yii::$app->request->get('lobbyId');

        if (empty($lobbyId)) {
            return $this->errorResponse(["LobbyId not set."]);
        }

        $tokenCheck = $this->checkClientToken($clientToken);

        if (!$tokenCheck['success']) {
            return $this->errorResponse([$tokenCheck['error']]);
        }

        /** @var User $user */
        $user = $tokenCheck['user'];

        /** @var Game $game */
        $game = Game::find()->where(['game_id' => $lobbyId])->one();
        if (empty($game)) {
            return $this->errorResponse(["There is no Game with this ID"]);
        }

        if (count($game->gameusers) >= Game::MAX_PLAYERS) {
            return $this->errorResponse(["Max. Player count reached."]);
        }
        $user->is_judge = 0;
        $user->updateActivity();
        $game->updateActivity();
        $game->link('users', $user);

        return [
            'success' => true
        ];
    }

    /**
     * TODO: Remove function after testing.
     * ONLY FOR TEST PURPOSES
     * Deletes a specified game or all games
     * Also deletes all Users that had no activity for 1 week
     *
     * @return array
     */
    public function actionClean()
    {
        $lobbyId = \Yii::$app->request->get('lobbyId');

        if (!empty($lobbyId)) {
            Game::deleteAll(['game_id' => $lobbyId]);
        } else {
            Game::deleteAll();
        }
        $oldDate = date("Y-m-d H:i:s", strtotime('-1 Week'));
        User::deleteAll(['<=', 'last_activity', $oldDate]);
        return ['success' => true];
    }

    /**
     * TODO: Remove function after testing.
     * ONLY FOR TEST PURPOSES
     * Returns all available users with clientTokens
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public function actionUsers()
    {
        return User::find()->all();
    }

    /**
     * Returns the current state of a lobby
     *
     * Request params:
     * -lobbyId
     *
     * @return array
     */
    public function actionGetLobbyState()
    {
        $lobbyId = \Yii::$app->request->get('lobbyId');

        /** @var Game $game */
        $game = Game::find()->with('categories')->where(['game_id' => $lobbyId])->one();

        if(empty($game)){
            return $this->errorResponse(["Lobby not found."]);
        }
        $settings = $game->toArray();
        $settings['max_players'] = Game::MAX_PLAYERS;
        $settings['categories'] = $game->categories;
        return [
            'success' => true,
            'settings' => $settings,
            'players' => $game->getCensoredUsers(),
        ];
    }

    /**
     * A host can kick another player with this function
     * TODO: Alter function, so users can use this function to leave a lobby by themselves
     *
     * Request params:
     * -lobbyId
     * -clientToken
     * -removePlayer (player ID of the user that should be kicked)
     *
     * @return array
     */
    public function actionRemoveFromLobby()
    {
        $lobbyId = \Yii::$app->request->get('lobbyId');
        $clientToken = \Yii::$app->request->get('clientToken');
        $kickedUser = \Yii::$app->request->get('removePlayer');

        if (empty($lobbyId)) {
            return $this->errorResponse(["LobbyId not set."]);
        }
        if (empty($kickedUser)) {
            return $this->errorResponse(["Remove Player ID not set."]);
        }

        $tokenCheck = $this->checkClientToken($clientToken);

        if (!$tokenCheck['success']) {
            return $this->errorResponse([$tokenCheck['error']]);
        }
        /** @var User $host */
        $host = $tokenCheck['user'];

        /** @var Game $lobby */
        $lobby = Game::find()->where(['game_id' => $lobbyId, 'host_user_id' => $host->user_id])->one();

        if (empty($lobby)) {
            return $this->errorResponse(["User is not the host, or lobby with this ID doesn't exist!"]);
        }
        $host->updateActivity();
        $lobby->updateActivity();
        Gameusers::deleteAll(['game_id' => $lobby->game_id, 'user_id' => $kickedUser]);
        return ['success' => true];
    }

    /**
     * Lets the host start a game
     *
     * Request params:
     * -lobbyId
     * -clientToken
     *
     * @return array
     */
    public function actionStartGame()
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

        /** @var User $host */
        $host = $tokenCheck['user'];

        /** @var Game $lobby */
        $lobby = Game::find()->where(['game_id' => $lobbyId, 'host_user_id' => $host->user_id])->one();

        if (empty($lobby)) {
            return $this->errorResponse(["No Lobby with this ID found or you are not the host"]);
        }

        if ($lobby->state != Game::STATE_INLOBBY) {
            return $this->errorResponse(["Lobby is already ingame!"]);
        }
        $host->updateActivity();
        $lobby->updateActivity();
        $lobby->start();
        return ['success' => true];
    }
}