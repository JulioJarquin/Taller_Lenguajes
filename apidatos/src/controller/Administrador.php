<?php

namespace App\controller;

use DI\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;
use PDO;
use PDOException;

class Administrador extends Usuario
{

    public function __construct(public ContainerInterface $container)
    {
    }


    public function filtrar(Request $request, Response $response, $args)
    {
        //Retornar todos los registros con limit
        $limite = $args['limite'];
        $indice = ($args['indice'] - 1) * $limite;

        $datos = $request->getQueryParams();
        $cadena = "";
        foreach ($datos as $valor) {
            $cadena .= "%$valor%&";
        }
        $sql = "call filtrarAdministrador('$cadena', '$indice', '$limite');";
        $con = $this->container->get('bd');
        $query = $con->prepare($sql);
        $query->execute();
        $status = $query->rowCount() > 0 ? 200 : 204;
        $res = $query->fetchAll();

        $query = null;
        $con = null;

        $response->getBody()->write(json_encode($res));
        return $response
            ->withHeader('Content-Type', 'Application/json')
            ->withStatus($status);
    }


    public function numRegs(Request $request, Response $response, $args)
    {
        //Retornar todos los registros con limit

        $datos = $request->getQueryParams();
        $cadena = "";
        foreach ($datos as $valor) {
            $cadena .= "%$valor%&";
        }
        $sql = "call numRegsAdministrador('$cadena');";
        $con = $this->container->get('bd');
        $query = $con->prepare($sql);
        $query->execute();
        $res['cant'] = $query->fetch(PDO::FETCH_NUM)[0];
        $query = null;
        $con = null;
        $response->getBody()->write(json_encode($res));
        return $response
            ->withHeader('Content-Type', 'Application/json')
            ->withStatus(200);
    }


    public function buscarAdmin(Request $request, Response $response, $args)
    {
        //Retornar un registro por código
        $sql = "call buscarAdministrador(:id);"; // call para procedimientos almacenados
        $con = $this->container->get('bd');
        $query = $con->prepare($sql);
        $query->bindParam('id', $args['id'], PDO::PARAM_STR); //bindParam vincula parametro
        $query->execute();
        if ($query->rowCount() > 0) {
            $res = $query->fetch();
            $status = 200;
        } else {
            $res = [];
            $status = 204;
        }
        $query = null;
        $con = null;

        $response->getBody()->write(json_encode($res));
        return $response
            ->withHeader('Content-Type', 'Application/json')
            ->withStatus($status);
    }



    public function crear(Request $request, Response $response, $args)
    {
        $body = json_decode($request->getBody());
        $body->idAdmin = "A" . $body->idAdmin;
        $sql = "select nuevoAdministrador(";
        $d = [];
        foreach ($body as $campo => $valor) {
            $sql .= ":$campo,";
            $d[$campo] = filter_var($valor, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }
        $sql = rtrim($sql, ',') . ");";

        $d['idUsuario'] = $d['idAdmin'];
        // esta linea puede ser del cliente o generado automaticamente
        $d['passw'] = Hash::hash($d['idAdmin']);

        $res = $this->guardarUsuario($sql, $d, 0);
        $status = $res == 0 ? 201 : 409;
        return $response
            ->withStatus($status);
    }



    public function modificar(Request $request, Response $response, $args)
    {
        //$id = $args['id'];
        $body = json_decode($request->getBody());
        $body->idAdmin = "A" . $body->idAdmin;

        $sql = "select editarAdministrador(:id,";
        $d['id'] = $args['id'];
        foreach ($body as $campo => $valor) {
            $sql .= ":$campo,";
            $d[$campo] = filter_var($valor, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }
        $sql = rtrim($sql, ',') . ");";

        $con = $this->container->get('bd');
        $query  = $con->prepare($sql);
        $query->execute($d);
        $res = $query->fetch(PDO::FETCH_NUM);
        $query = null;
        $con = null;

        $status = $res[0] > 0 ? 200 : 404;
        return $response
            ->withHeader('Content-Type', 'Application/json')
            ->withStatus($status);
    }

    public function eliminar(Request $request, Response $response, $args)
    {
        $sql = "select eliminarAdministrador(:id);"; //LLamada de procedimiento en sql
        $con = $this->container->get('bd');
        $query = $con->prepare($sql);
        $query->bindParam('id', $args['id'], PDO::PARAM_STR);
        $query->execute();
        $res = $query->fetch(PDO::FETCH_NUM);
        $status = $res[0] > 0 ? 200 : 404;
        $query = null;
        $con = null;
        return $response
            ->withStatus($status);
    }
}
