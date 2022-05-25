<?php

namespace App\controller;

use DI\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;
use PDO;
use PDOException;
use Firebase\JWT\JWT;

class Auth extends Usuario
{
    public function __construct(public ContainerInterface $container)
    {
    }

    public function generarTokens(string $idUsuario, int $rol)
    {
        $key = getenv('key');
        $payload = [
            "iss" => $_SERVER['SERVER_NAME'],
            "iat" => time(),
            "exp" => time() + (60),
            "sub" => $idUsuario,
            "rol" => $rol
        ];
        $payloadRf = [
            "iss" => $_SERVER['SERVER_NAME'],
            "iat" => time(),
            "rol" => $rol
        ];
        $tkRef = JWT::encode($payloadRf, $key, 'HS256');
        $this->modificarToken(idUsuario: $idUsuario, tokenRef: $tkRef);
        return [
            "token" => JWT::encode($payload, $key, 'HS256'),
            "refreshToken => $tkRef"
        ];
    }

    public function modificarToken(string $idUsuario, string $tokenRef = "")
    {
        $con = $this->container->get('bd');
        $sql = "select modificarToken(:idUsuario, :tk);";
        $query = $con->prepare($sql);
        $query->execute(["idUsuario" => $idUsuario, "tk" => $tokenRef]);
        $datos = $query->fetch(PDO::FETCH_NUM);
        $query = null;
        $con = null;
        return $datos;
    }

    /*
    private function generarToken($rol, $idUsuario){
       // die($key=getenv('key'));
        $key=getenv('key');
        $payload = [
            "iss"=> $_SERVER['SERVER_NAME'],
            "iat"=> time(),
            "exp"=>time() + (60),
            "sub"=> $idUsuario,
            "rol"=> $rol
        ];
        return [
        $token = JWT::encode($payload, $key, 'HS256')
        ];
    }

    private function autenticar($usr, $passw)
    {
        $datos = $this->buscarU($usr);
        return (($datos) && (password_verify($passw, $datos->passw))) ?
            ["rol" => $datos->rol] : null;
    }*/


   


    public function iniciarSesion(Request $request, Response $response, $args)
    {
        $body = json_decode($request->getBody());
        $res = $this->autenticar($body->idUsuario, $body->passw);
        if ($res) {
            $retorno = $this->generarTokens($body->idUsuario, $res['rol']);
            $response->getBody()->write(json_encode($retorno));
            $status = 200;
        } else {
            $status = 401;
        }
        return $response
            ->withHeader('Content-Type', 'Application/json')
            ->withStatus($status);
    }
/*
    public function cerrarSesion(Request $request, Response $response, $args)
    {
        $body= json_decode($request->getBody());
        $this->modificarToken(idUsuario:$body->idUsuario);
        return $response
        ->withStatus(200);
    }*/

    public function cerrarSesion(Request $request, Response $response, $args)
    {
        $body = json_decode($request->getBody());
        $this->modificarToken(idUsuario: $body->idUsuario);
        return $response
            ->withStatus(200);
    }



    public function refrescarSesion(Request $request, Response $response, $args)
    {
        $body = json_decode($request->getBody());
        $rol = $this->verificarRefresco($body->idUsuario, $body->tkR);

        if($rol){
            $resultado = $this->generarTokens($body->idUsuario, $rol);           
        }
        if(isset($Resultado)){
            $status = 200;
            $response ->getBody()->write(json_encode($resultado));
            
        }else {
            $status = 401;
        }
        return $response
        ->withHeader('Content-Type', 'Application/json')
        ->withStatus(200);


    }

    private function verificarRefresco(String $idUsuario, string $tokenRef)
    {
        $con = $this->container->get('bd');
        $sql = "call verificarTokenR(:idUsuario, :tk);";
        $query = $con->prepare($sql);
        $query->execute(["idUsuario" => $idUsuario, "tk" => $tokenRef]);
        $datos = $query->fetchColumn();
        $query = null;
        $con = null;
        return $datos;
    }
/*
    public function verificarRefresco(string $idUsuario, string $tokenRef){
        $con = $this->container->get('bd');
        $sql = "select verificarTokenR(:idUsuario, :tk);";
        $query = $con->prepare($sql);
        $query->execute(["idUsuario" => $idUsuario, "tk" => $tokenRef]);
        $datos = $query->fetchColumn();
        $query = null;
        $con = null;
        return $datos;
    }

    public function refrescarSesion(Request $request, Response $response, $args)
    {
        $body= json_decode($request->getBody());
        $rol= $this->verificarRefresco($body->idUsuario, $body->tkR);
        if($rol){
            $resultado =$this->generarTokens($body->idUsuario, $rol);
        }
        if(isset($resultado)){
            $status=200;
            $response->getBody()->write(json_encode($resultado));
        }else{
            $status=401;
        }
        return $response
            ->withHeader('Content-Type', 'Application/json')
            ->withStatus($status);
    }*/
}
