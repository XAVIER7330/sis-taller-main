<?php

namespace App\controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;

use PDO;

class Artefacto
{
    protected $container;

    public function __construct(ContainerInterface $c)
    {
        $this->container = $c;
    }
    public function read(Request $request, Response $response, $args)
    {
        $sql = "CALL buscarArtefacto(:id);"; // Llamar al procedimiento almacenado
        $con = $this->container->get('base_datos');
        $query = $con->prepare($sql);

        // Vincular el parámetro
        $id = $args['id'] ?? null;
        $query->bindValue(":id", $id, PDO::PARAM_INT);

        try {
            $query->execute();
            $res = $query->fetchAll();
            $status = $query->rowCount() > 0 ? 200 : 204; // 200 si hay resultados, 204 si no hay resultados
        } catch (PDOException $e) {
            $status = 500; // Error interno del servidor
            $res = ["error" => $e->getMessage()];
        }

        $query = null;
        $con = null;

        $response->getBody()->write(json_encode($res));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }



    public function create(Request $request, Response $response, $args)
    {
        $body = json_decode($request->getBody());
        $sql = "SELECT nuevoArtefacto(:idCliente, :serie, :modelo, :marca, :categoria, :descripcion);"; //?Llamar a la función nuevoArtefacto
        $con = $this->container->get('base_datos');
        $con->beginTransaction();

        $query = $con->prepare($sql);

        foreach ($body as $key => $value) {
            $TIPO = gettype($value) == "integer" ? PDO::PARAM_INT : PDO::PARAM_STR;
            $value = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
            $query->bindValue(":$key", $value, $TIPO);
        }

        try {
            $query->execute();
            $con->commit();
            $resp = $query->fetch(PDO::FETCH_NUM)[0]; //?Obtener el id del artefacto creado

            $status = match ($resp) {
                0 => 201, //?Creado paso directo
                1 => 409, //?Conflicto ya existe
                2 => 428 //?Precondición requerida, cliente no existe
            };
        } catch (PDOException $e) {
            $status = 500; //?Error interno del servidor
            $con->rollBack(); //?Deshacer la transacción
        }

        $query = null;
        $con = null;

        return $response->withStatus($status);
    }

    public function update(Request $request, Response $response, $args) //!Actualizo un artefacto
    {
        $body = json_decode($request->getBody());
        $sql = "SELECT editarArtefacto(:id, :serie, :modelo, :marca, :categoria, :descripcion);"; //?Llamar a la función nuevoArtefacto
        $con = $this->container->get('base_datos');
        $con->beginTransaction();
        $query = $con->prepare($sql);

        //!Nuevo
        $value = filter_var($args['id'], FILTER_SANITIZE_SPECIAL_CHARS); //?Sanitizar el id
        $query->bindValue("id", $value, PDO::PARAM_INT); //?Vincular el id


        foreach ($body as $key => $value) {
            $TIPO = gettype($value) == "integer" ? PDO::PARAM_INT : PDO::PARAM_STR;
            $value = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
            $query->bindValue($key, $value, $TIPO);
        }

        try {
            $query->execute();
            $con->commit();
            $resp = $query->fetch(PDO::FETCH_NUM)[0]; //?Obtener el id del artefacto creado

            $status = match ($resp) {
                0 => 404, //?No encontrado
                1 => 200 //? Actualizado
            };
        } catch (PDOException $e) {
            $status = 500; //?Error interno del servidor
            $con->rollBack(); //?Deshacer la transacción
        }

        $query = null;
        $con = null;

        return $response->withStatus($status);
    }


    public function delete(Request $request, Response $response, $args)
    {
        $sql = "SELECT eliminarArtefacto(:id)"; // Usar marcador correcto
        $con = $this->container->get('base_datos');

        $query = $con->prepare($sql);
        $query->bindValue("id", $args["id"], PDO::PARAM_INT); // Vincular el id correctamente
        $query->execute();

        $resp = $query->fetch(PDO::FETCH_NUM)[0]; // Obtener el resultado

        $status = $resp > 0 ? 200 : 404; // Verificar si fue exitoso

        $query = null;
        $con = null;

        return $response->withStatus($status);
    }


    public function filtrar(Request $request, Response $response, $args) //!Filtrar artefactos
    {

        // Obtener los parámetros de la consulta
        $datos = $request->getQueryParams();

        //! -- %serie%&%modelo%&%marca%&%categoria%&
        $filtro = "%";
        foreach ($datos as $key => $value) {
            $filtro .= "$value%&%";
        }
        $filtro = substr($filtro, 0, -1); //?Eliminar el último "&"
        $sql = "CALL filtrarArtefacto('$filtro', {$args['pag']}, {$args['lim']});"; //?Llamar a la función filtrarArtefacto
        $con = $this->container->get('base_datos');
        $query = $con->prepare($sql);
        $query->execute();
        $res = $query->fetchAll(); //?Obtener todos los resultados
        $status = $query->rowCount() > 0 ? 200 : 404; //?Si hay resultados, devuelve 200, sino 404
        $query = null;
        $con = null;
        $response->getBody()->write(json_encode($res));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status); //?Devolver el resultado en formato JSON
    }
}
