<?php

namespace Usuarios\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Form\Form;
use Zend\Authentication\AuthenticationService;
use Usuarios\Model\Entity\Usuario;
use Usuarios\Model\Dao\UsuarioDao;
use Usuarios\Form\Usuario\UsuarioEdit;
use Usuarios\Form\Usuario\UsuarioAdd;
use Usuarios\Form\Usuario\UsuarioValidator;

class IndexController extends AbstractActionController {

    private $tableGateway;
    private $usuarioDao;
    private $auth;
    private $adapter;

    public function __construct() {
        $this->auth = new AuthenticationService();
    }

    public function setTableGateway($tableGateway) {
        $this->tableGateway = $tableGateway;
    }

    public function setUsuarioDao($usuarioDao) {
        $this->usuarioDao = $usuarioDao;
    }

    public function setAdapter($adapter) {
        $this->adapter = $adapter;
    }

    public function IndexAction() {

        $id = $_SESSION["miSession"]["usuario"]->getId();

        $tipo = $_SESSION["miSession"]["usuario"]->getTipo();

        return new ViewModel(array(
            
        ));
    }


}

