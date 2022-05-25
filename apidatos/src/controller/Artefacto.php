<?php

namespace App\controller;

use DI\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;
use PDO;

class Artefacto
{

    public function __construct(public ContainerInterface $container)
    {
    }

    
    public function mostrarTodos(Request $request, Response $response, $args)
    {
        //Retornar todos los registros con limit
        $limite = $args['limite'];
        $indice = ($args['indice'] - 1) * $limite;
        $con = $this->container->get('bd');
        $sql = "call todosArtefacto(:ind, :lim);";
        $query = $con->prepare($sql);
        $query->execute(array("ind" => $indice, "lim" => $limite));
        if ($query->rowCount() > 0) {
            $reg = $query->fetchAll();
        }
        $query = null;
        $con = null;
        $response->getBody()->write(json_encode($reg));
        return $response
            ->withHeader('Content-Type', 'Application/json')
            ->withStatus(200);
    }
    public function buscar(Request $request, Response $response, $args)
    {
        //Retornar un registro por código
        $sql = "call buscarArtefacto(:id);"; // call para procedimientos almacenados
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
        $sql = "call filtrarArtefacto('$cadena', '$indice', '$limite');";
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


    public function crear(Request $request, Response $response, $args)
    {
        //Crear nuevo
        $body = json_decode($request->getBody());
        $sql = "select nuevoArtefacto(";
        $d = [];
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
        //echo json_encode($res);
        $status = $res[0] == 1 ? 201 : 409;
        return $response
            ->withHeader('Content-Type', 'Application/json')
            ->withStatus($status);
    }

    public function modificar(Request $request, Response $response, $args)
    {
        $body = json_decode($request->getBody());
        $sql = "select editarArtefacto(:id,";
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
        $status = $res[0] == 1 ? 200 : 404;
        return $response
            ->withHeader('Content-Type', 'Application/json')
            ->withStatus($status);
    }


    public function eliminar(Request $request, Response $response, $args)
    {
        //eliminar
        $sql = "select eliminarArtefacto(:id);"; //LLamada de procedimiento en sql
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
