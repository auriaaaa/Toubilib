<?php

namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use toubilib\application\dto\CreateRendezVousDTO;
use toubilib\application\exceptions\ValidationException;
use toubilib\application\ports\api\ServiceRendezVousInterface;

class CreerRendezVousAction
{
    private ServiceRendezVousInterface $serviceRendezVous;

    public function __construct(ServiceRendezVousInterface $serviceRendezVous)
    {
        $this->serviceRendezVous = $serviceRendezVous;
    }

    public function __invoke(
        ServerRequestInterface $rq,
        ResponseInterface $rs,
        array $args
    ): ResponseInterface {

        $donnees = json_decode((string) $rq->getBody(), true);

        if (!is_array($donnees) || json_last_error() !== JSON_ERROR_NONE) {
            $rs->getBody()->write(json_encode(['erreur' => 'JSON invalide.']));

            return $rs
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }

        try {
            $dto = CreateRendezVousDTO::fromArray($donnees);
            $rdv = $this->serviceRendezVous->creer($dto);

            $rs->getBody()->write(json_encode($rdv));

            return $rs
                ->withHeader('Content-Type', 'application/json')
                ->withHeader('Location', '/rdv/' . $rdv->id)
                ->withStatus(201);

        } catch (ValidationException $e) {
            $rs->getBody()->write(json_encode(['erreur' => 'Données invalides.']));

            return $rs
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);

        } catch (\Throwable $e) {
            $rs->getBody()->write(json_encode(['erreur' => 'Erreur interne du serveur.']));

            return $rs
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
}