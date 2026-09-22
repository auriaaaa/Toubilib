<?php
namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

use toubilib\application\ports\api\ServiceRendezVousInterface;
use toubilib\domain\exceptions\RendezVousDejaPasseException;
use toubilib\domain\exceptions\StatutRendezVousInvalideException;

class AnnulerRendezVousAction {
    
    private ServiceRendezVousInterface $serviceRendezVous;

    public function __construct(ServiceRendezVousInterface $serviceRendezVous) {
        $this->serviceRendezVous = $serviceRendezVous;
    }

    public function __invoke(
        ServerRequestInterface $rq,
        ResponseInterface $rs,
        array $args
    ): ResponseInterface {
        $id = $args['id'] ?? null;

        if ($id === null || $id === '') {
            throw new HttpBadRequestException($rq, "L'ID du rendez-vous est requis.");
        }

        if (!is_string($id)) {
            throw new HttpBadRequestException($rq, "L'ID du rendez-vous doit être une chaîne de caractères.");
        }

        try {
            $dto = $this->serviceRendezVous->annuler($id);
        } catch (StatutRendezVousInvalideException $e) {
            throw new HttpBadRequestException(
                $rq,
                $e->getMessage()
            );
        } catch (RendezVousDejaPasseException $e) {
            throw new HttpBadRequestException(
                $rq,
                $e->getMessage()
            );
        } catch (\Exception $e) {
            throw new HttpNotFoundException(
                $rq,
                "Rendez-vous non trouvé."
            );
        }

        $rs->getBody()->write(json_encode($dto));

        return $rs
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

}
