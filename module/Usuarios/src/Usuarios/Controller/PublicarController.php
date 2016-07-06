<?php

namespace Usuarios\Controller;

if (session_status() == PHP_SESSION_NONE) {
    session_start('teste');
}
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Authentication\AuthenticationService;
use Usuarios\Model\Entity\Usuario;
use Usuarios\Model\Dao\UsuarioDao;
use Usuarios\Form\Usuario\UsuarioEdit;
use Usuarios\Form\Usuario\UsuarioAdd;
use Usuarios\Form\Usuario\UsuarioValidator;
use Usuarios\MisClases\Meli;

class PublicarController extends AbstractActionController {

    private $secret = '4270558986407679';
    private $key = 'ArbqnANwnlxz9W0EXwnfvb0niBAWZhGf';
    private $username = "jotapey";
    private $password = "g4cir9dx";

    private function getSessionValues($meli){

    }

    public function auth(){

        echo "<pre>";
        print_r(array(
            "_SESSION" => $_SESSION
        ));
        die();

        if(isset($_SESSION['access_token'],$_SESSION['refresh_token']) && ($_SESSION['access_token'] && $_SESSION['refresh_token'])){
            return true;
        }

        if(!isset($_GET['code'])){
            $_GET['code'] = "";
        }

        if(!isset($_SESSION['access_token'])){
            $_SESSION['access_token'] = "";
        }

        if(!isset($_SESSION['refresh_token'])){
            $_SESSION['refresh_token'] = "";
        }

        $meli = new Meli('4270558986407679', 'ArbqnANwnlxz9W0EXwnfvb0niBAWZhGf', $_SESSION['access_token'], $_SESSION['refresh_token']);

        if($_GET['code'] || $_SESSION['access_token']) {

            // If code exist and session is empty
            if($_GET['code'] && !($_SESSION['access_token'])) {
                // If the code was in get parameter we authorize
                $user = $meli->authorize($_GET['code'], 'https://geeksuy.com/ml/auth.php');
                // Now we create the sessions with the authenticated user
                $_SESSION['access_token'] = $user['body']->access_token;
                $_SESSION['expires_in'] = time() + $user['body']->expires_in;
                $_SESSION['refresh_token'] = $user['body']->refresh_token;
                return true;
            } else {

                // We can check if the access token in invalid checking the time
                if($_SESSION['expires_in'] < time()) {
                    try {
                        // Make the refresh proccess
                        $refresh = $meli->refreshAccessToken();

                        // Now we create the sessions with the new parameters
                        $_SESSION['access_token'] = $refresh['body']->access_token;
                        $_SESSION['expires_in'] = time() + $refresh['body']->expires_in;
                        $_SESSION['refresh_token'] = $refresh['body']->refresh_token;
                    } catch (Exception $e) {
                        echo "Exception: ",  $e->getMessage(), "\n";
                    }
                }
            }
        } else {
            $session = $this->getSessionValues();
            echo '<a href="' . $meli->getAuthUrl('https://geeksuy.com/ml/auth.php', Meli::$AUTH_URL['MLU']) . '">Login using MercadoLibre oAuth 2.0</a>';
            return false;
        }
    }

    public function publicarArticulo($article){
        if(isset($_SESSION['access_token'])){
            $meli = new Meli('4270558986407679', 'ArbqnANwnlxz9W0EXwnfvb0niBAWZhGf', $_SESSION['access_token'], $_SESSION['refresh_token']);

			if(!is_array($article)){
				$body = json_decode($article,true);
			}else{
				$body = json_encode($article);
			}

            $params = array('access_token' => $_SESSION['access_token']);



            $resp = $meli->post("items",$body,$params);

            $error = false;

            if(isset($resp["body"])){
                if(isset($resp["body"]->error)){
					throw new \Exception('Error en la respuesta');

                    //ERROR PUBLISH
                    return json_encode(array(
                        "error" => $resp["body"]->error,
                        "message" => $resp["body"]->message,
                    ));
                }else{
                    //SUCCESS PUBLISH
					throw new \Exception('Success');
                    return json_encode(array(
                        "error" => false,
                        "body" => $resp["body"],
                        "httpCode" => $resp["httpCode"]
                    ));
                }
            }else{
				throw new \Exception('No hay Body');
			}
        }
    }

    public function getArticulos(){
        $articulos = array();

        $articulos[] = json_decode('{"title":"Gato TEST01","category_id":"MLU1082","price":99,"currency_id":"UYU","available_quantity":1,"buying_mode":"buy_it_now","listing_type_id":"bronze","condition":"new","description": "Item:, <strong> TTTTEEEESSTTT Ray-Ban WAYFARER Gloss Black RB2140 901 </strong> Model: RB2140. Size: 50mm. Name:    WAYFARER. Color: Gloss Black. Includes Ray-Ban Carrying Case and Cleaning Cloth. New in Box","video_id": "YOUTUBE_ID_HERE","warranty": "12 months by Ray Ban","pictures":[{"source":"http://upload.wikimedia.org/wikipedia/commons/f/fd/Ray_Ban_Original_Wayfarer.jpg"},{"source":"http://en.wikipedia.org/wiki/File:Teashades.gif"}]}',true);
        $articulos[] = json_decode('{"title":"Gato TEST02","category_id":"MLU1082","price":99,"currency_id":"UYU","available_quantity":1,"buying_mode":"buy_it_now","listing_type_id":"bronze","condition":"new","description": "Item:, <strong> TTTTEEEESSTTT Ray-Ban WAYFARER Gloss Black RB2140 901 </strong> Model: RB2140. Size: 50mm. Name:    WAYFARER. Color: Gloss Black. Includes Ray-Ban Carrying Case and Cleaning Cloth. New in Box","video_id": "YOUTUBE_ID_HERE","warranty": "12 months by Ray Ban","pictures":[{"source":"http://upload.wikimedia.org/wikipedia/commons/f/fd/Ray_Ban_Original_Wayfarer.jpg"},{"source":"http://en.wikipedia.org/wiki/File:Teashades.gif"}]}',true);
        $articulos[] = json_decode('{"title":"Gato TEST03","category_id":"MLU1082","price":99,"currency_id":"UYU","available_quantity":1,"buying_mode":"buy_it_now","listing_type_id":"bronze","condition":"new","description": "Item:, <strong> TTTTEEEESSTTT Ray-Ban WAYFARER Gloss Black RB2140 901 </strong> Model: RB2140. Size: 50mm. Name:    WAYFARER. Color: Gloss Black. Includes Ray-Ban Carrying Case and Cleaning Cloth. New in Box","video_id": "YOUTUBE_ID_HERE","warranty": "12 months by Ray Ban","pictures":[{"source":"http://upload.wikimedia.org/wikipedia/commons/f/fd/Ray_Ban_Original_Wayfarer.jpg"},{"source":"http://en.wikipedia.org/wiki/File:Teashades.gif"}]}',true);

        return $articulos;
    }

    public function authAction(){
        echo "<pre>";
        print_r($_REQUEST);
        die();
    }

    public function indexAction(){
        $auth = $this->auth();
        if($auth){
            foreach($this->getArticulos() as $articulo){
                $this->publicarArticulo($articulo);
            }
        }

    }
}
