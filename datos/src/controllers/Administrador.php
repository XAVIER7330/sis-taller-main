<?php

namespace App\controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;


class Administrador
{
    protected $container;
    private const ROL = 1;

    public function __construct(ContainerInterface $c)
    {
        $this->container = $c;
    }

    public function read(Request $request, Response $response, $args)
    {
        $sql = "CALL buscarAdministrador(:id);";
        $con = $this->container->get('base_datos');
        $query = $con->prepare($sql);

        $id = $args['id'] ?? null;
        $query->bindValue(":id", $id, PDO::PARAM_STR);

        try {
            $query->execute();
            $res = $query->fetchAll();
            $status = $query->rowCount() > 0 ? 200 : 204;
        } catch (PDOException $e) {
            $status = 500;
            $res = ["error" => $e->getMessage()];
        }

        $query = null;
        $con = null;

        $response->getBody()->write(json_encode($res));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }

    public function create(Request $request, Response $response, $args)
    {
        $body = json_decode($request->getBody());
        $sql = "SELECT nuevoAdministrador(:idAdministrador, :nombre, :apellido, :correo);";
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
            $resp = $query->fetch(PDO::FETCH_NUM)[0];

            $status = match ($resp) {
                0 => 201,
                1 => 409,
                2 => 409,
                default => throw new Exception("Error al crear administrador")
            };

            // Crear usuario asociado
            $id = $body->idAdministrador;
            $sql = "SELECT nuevoUsuario(:idUsuario,:rol,:passw);";
            $query = $con->prepare($sql);
            $query->bindValue(":idUsuario", $id, PDO::PARAM_STR);
            $query->bindValue(":rol", self::ROL, PDO::PARAM_INT);
            $query->bindValue(":passw", $id, PDO::PARAM_STR);
            $query->execute();

            if ($status == 409) {
                $con->rollBack();
            } else {
                $con->commit();
            }
        } catch (PDOException $e) {
            $status = 500;
            $con->rollBack();
        }

        $query = null;
        $con = null;
        return $response->withStatus($status);
    }

    public function update(Request $request, Response $response, $args)
    {
        $body = json_decode($request->getBody(), true);
        $sql = "SELECT editarAdministrador(:id, :idAdministrador, :nombre, :apellido, :correo);";
        $con = $this->container->get('base_datos');
        $con->beginTransaction();
        $query = $con->prepare($sql);

        $query->bindValue(":id", $args['id'], PDO::PARAM_INT);

        foreach ($body as $key => $value) {
            $TIPO = gettype($value) == "integer" ? PDO::PARAM_INT : PDO::PARAM_STR;
            $value = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
            $query->bindValue(":$key", $value, $TIPO);
        }

        try {
            $query->execute();
            $con->commit();
            $resp = $query->fetch(PDO::FETCH_NUM)[0];

            $status = match ($resp) {
                0 => 404,
                1 => 200
            };
        } catch (PDOException $e) {
            $status = 500;
            $con->rollBack();
            $resp = ["error" => $e->getMessage()];
        }

        $query = null;
        $con = null;

        $response->getBody()->write(json_encode($resp));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }

    public function delete(Request $request, Response $response, $args)
    {
        $sql = "SELECT eliminarAdministrador(:id)";
        $con = $this->container->get('base_datos');

        $query = $con->prepare($sql);
        $query->bindValue(":id", $args["id"], PDO::PARAM_INT);
        $query->execute();

        $resp = $query->fetch(PDO::FETCH_NUM)[0];
        $status = $resp > 0 ? 200 : 404;

        $query = null;
        $con = null;
        return $response->withStatus($status);
    }
}
