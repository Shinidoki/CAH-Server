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
     * @param string $name
     * @return array
     */
    public function actionAuthenticate($name = null)
    {
        if (empty($name)) {
            return $this->errorResponse(["No Username defined!"]);
        }

        $newUser = new User();
        $newUser->user_name = $name;
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
     * @param $clientToken
     * @param $gameName
     * @return array
     */
    public function actionCreateLobby($clientToken = null, $gameName = null)
    {
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
        $newGame->target_score = 5;

        if ($newGame->updateActivity()) {
            $newGame->link('hostUser', $user);
            $newGame->link('categories', Category::find()->one());
            $user->removeFromAllGames();
            $user->link('games',$newGame);
            $user->is_judge = 1;
            $user->score = 0;
            $user->updateActivity();
            return [
                'success' => true,
                'game_id' => $newGame->game_id
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
     * -gameId
     *
     * @param $clientToken
     * @param $gameId
     * @return array
     */
    public function actionJoinLobby($clientToken = null, $gameId = null)
    {
        if (empty($gameId)) {
            return $this->errorResponse(["GameId not set."]);
        }

        $tokenCheck = $this->checkClientToken($clientToken);

        if (!$tokenCheck['success']) {
            return $this->errorResponse([$tokenCheck['error']]);
        }

        /** @var User $user */
        $user = $tokenCheck['user'];

        /** @var Game $game */
        $game = Game::find()->where(['game_id' => $gameId])->one();
        if (empty($game)) {
            return $this->errorResponse(["There is no Game with this ID"]);
        }

        if (count($game->gameusers) >= Game::MAX_PLAYERS) {
            return $this->errorResponse(["Max. Player count reached."]);
        }

        if ($game->state != Game::STATE_INLOBBY) {
            return $this->errorResponse(["Game already started!"]);
        }

        $alreadyInGame = Gameusers::find()->where(['game_id' => $gameId, 'user_id' => $user->user_id])->one();

        if (!empty($alreadyInGame)) {
            return $this->errorResponse(["You are already in this game!"]);
        }

        $user->removeFromAllGames();

        $user->is_judge = 0;
        $user->score = 0;
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
        $gameId = \Yii::$app->request->get('gameId');

        if (!empty($gameId)) {
            Game::deleteAll(['game_id' => $gameId]);
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
     * -gameId
     *
     * @param $gameId
     * @return array
     */
    public function actionGetLobbyState($gameId = null)
    {
        /** @var Game $game */
        $game = Game::find()->with('categories')->where(['game_id' => $gameId])->one();

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
     * -gameId
     * -clientToken
     * -removePlayer (player ID of the user that should be kicked)
     *
     * @param $gameId
     * @param $clientToken
     * @param $removePlayer
     * @return array
     */
    public function actionRemoveFromLobby($gameId = null, $clientToken = null, $removePlayer = null)
    {
        if (empty($gameId)) {
            return $this->errorResponse(["GameId not set."]);
        }
        if (empty($removePlayer)) {
            return $this->errorResponse(["Remove Player ID not set."]);
        }

        $tokenCheck = $this->checkClientToken($clientToken);

        if (!$tokenCheck['success']) {
            return $this->errorResponse([$tokenCheck['error']]);
        }
        /** @var User $host */
        $host = $tokenCheck['user'];

        /** @var Game $lobby */
        $lobby = Game::find()->where(['game_id' => $gameId, 'host_user_id' => $host->user_id])->one();

        if (empty($lobby)) {
            return $this->errorResponse(["User is not the host, or lobby with this ID doesn't exist!"]);
        }
        $host->updateActivity();
        $lobby->updateActivity();
        Gameusers::deleteAll(['game_id' => $lobby->game_id, 'user_id' => $removePlayer]);
        return ['success' => true];
    }

    /**
     * Lets the host start a game
     *
     * Request params:
     * -gameId
     * -clientToken
     *
     * @param null $gameId
     * @param null $clientToken
     * @return array
     */
    public function actionStartGame($gameId = null, $clientToken = null)
    {
        if (empty($gameId)) {
            return $this->errorResponse(["GameId not set."]);
        }

        $tokenCheck = $this->checkClientToken($clientToken);

        if (!$tokenCheck['success']) {
            return $this->errorResponse([$tokenCheck['error']]);
        }

        /** @var User $host */
        $host = $tokenCheck['user'];

        /** @var Game $lobby */
        $lobby = Game::find()->where(['game_id' => $gameId, 'host_user_id' => $host->user_id])->one();

        if (empty($lobby)) {
            return $this->errorResponse(["No game with this ID found or you are not the host"]);
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