<?php

namespace App\controller;

use DI\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;
use PDO;
use PDOException;

class Oficinista extends Usuario
{
    
    public function __construct(public ContainerInterface $container)
    {
    }


    public function MostrarTodos(Request $request, Response $response, $args)
    {
        //Retornar todos los registros con limit
        $limite = $args['limite'];
        $indice = ($args['indice'] - 1) * $limite;

        $con = $this->container->get('bd');
        $sql = "call todosOficinista(:ind, :lim);";
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
        $sql = "call filtrarOficinista('$cadena', '$indice', '$limite');";
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
        $sql = "call numRegsOficinista('$cadena');";
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



    // temporal se debe modificar
    private function buscarIdOficinista($id){
        $sql = "call buscarOficinista(:id, '');"; // call para procedimientos almacenados
        $con = $this->container->get('bd');
        $query = $con->prepare($sql);
        $query->bindParam('id', $id, PDO::PARAM_STR); //bindParam vincula parametro
        $query->execute(); 
        $res= $query->rowCount()>0 ? $query->fetchAll() : 0 ;
       if ($res==0) return 0;
        return $res[0]->idOficinista;
    }


    public function buscar(Request $request, Response $response, $args)
    {
        //Retornar un registro por c??digo
        $sql = "call buscarOficinista(:id, '');"; // call para procedimientos almacenados
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
        $body->idOficinista = "O" . $body->idOficinista;
        $sql = "select nuevoOficinista(";
        $d = [];
        foreach ($body as $campo => $valor) {
            $sql .= ":$campo,";
            $d[$campo] = filter_var($valor, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }
        $sql = rtrim($sql, ',') . ");";

        $d['idUsuario']=$d['idOficinista'];
        // esta linea puede ser del cliente o generado automaticamente
        $d['passw']=Hash::hash($d['idOficinista']);

        $res = $this->guardarUsuario($sql, $d, 4);
        $status = $res == 0 ? 201 : 409;
        return $response
            ->withStatus($status);
    }



    public function modificar(Request $request, Response $response, $args)
    {
        //$id = $args['id'];
        $body = json_decode($request->getBody());
        $body->idOficinista = "T" . $body->idOficinista;
        $sql = "select editarOficinista(:id,";
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
        $idOficinista=$this -> buscarIdOficinista($args['id']);
        $res=0;
        if($idOficinista ==!0){
        //var_dump($idCliente);
        $sql = "select eliminarOficinista(:id);"; //LLamada de procedimiento en sql
        $res = $this->eliminarUsuario($sql, $idOficinista, $args['id']);
        }
        $status = $res > 0 ? 200 : 404;

        return $response
            ->withStatus($status);
    }
}