<?php
declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('Hello world!');
        return $response;
    });

    $app->get('/cliente', function (Request $request, Response $response,$args) {
        $db = $this->get(PDO::class);
        $sth = $db->prepare("SELECT * FROM cliente order by nombre");
        $sth->execute();
        $data = $sth->fetchAll(PDO::FETCH_ASSOC);
        $payload = json_encode($data);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/servicio', function (Request $request, Response $response,$args) {
        $db = $this->get(PDO::class);
        $sth = $db->prepare("SELECT * FROM servicio order by descripcion");
        $sth->execute();
        $data = $sth->fetchAll(PDO::FETCH_ASSOC);
        $payload = json_encode($data);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    });

    
    $app->get('/detalle-factura/{id}', function (Request $request, Response $response,$args) {
        $db = $this->get(PDO::class);
        $id=$args['id'];
        $sth = $db->prepare("SELECT * FROM detalle_factura dt JOIN servicio s ON  dt.`cod_servicio`=s.`cod_servicio`  WHERE cod_factura=".$id);
        $sth->execute();
        $data = $sth->fetchAll(PDO::FETCH_ASSOC);
        $payload = json_encode($data);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    });


    $app->post('/factura', function (Request $request,Response $response,$args){
        try {
            $db = $this->get(PDO::class);
            $input=$request->getParsedBody();
            $sql="INSERT INTO factura(fecha,cod_cliente) values(:fecha,:cod_cliente)";
            $sth=$db->prepare($sql);
            $sth->bindParam("fecha",$input['fecha']);
            $sth->bindParam("cod_cliente",$input['cod_cliente']);
            $sth->execute();
            $data=$db->lastInsertId();
            $id_json=json_encode($data);
            $response->getBody()->write($id_json);
            return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
        } catch (PDOException $e) {
            $error = array(
              "message" => $e->getMessage()
            );
         
            $response->getBody()->write(json_encode($error));
            return $response
              ->withHeader('content-type', 'application/json')
              ->withStatus(500);
          }
        });


        $app->post('/detalle-factura', function (Request $request,Response $response,$args){
            try {
                $db = $this->get(PDO::class);
                $input=$request->getParsedBody();
                $sql="INSERT INTO detalle_factura(cod_factura,cod_servicio,precio,cantidad) values(:cod_factura,:cod_servicio,:precio,:cantidad)";
                $sth=$db->prepare($sql);
                $sth->bindParam("cod_factura",$input['cod_factura']);
                $sth->bindParam("cod_servicio",$input['cod_servicio']);
                $sth->bindParam("precio",$input['precio']);
                $sth->bindParam("cantidad",$input['cantidad']);
                $sth->execute();
                $data=$db->lastInsertId();
                $id_json=json_encode($data);
                $response->getBody()->write($id_json);
                return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
            } catch (PDOException $e) {
                $error = array(
                  "message" => $e->getMessage()
                );
             
                $response->getBody()->write(json_encode($error));
                return $response
                  ->withHeader('content-type', 'application/json')
                  ->withStatus(500);
              }
            });

    $app->group('/users', function (Group $group) {
        $group->get('', ListUsersAction::class);
        $group->get('/{id}', ViewUserAction::class);
    });
};
