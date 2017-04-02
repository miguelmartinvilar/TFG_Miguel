<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Amasada;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AmasadaController extends Controller
{
    /**
     * @Route("/amasadas/listar", name="amasadas_listar")
     */
    public function indexAction()
    {
        /** @var EntityManager $em */
        $em=$this->getDoctrine()->getManager();

        $amasadas = $em->createQueryBuilder()
            ->select('a')
            ->from('AppBundle:Amasada', 'a')
            ->getQuery()
            ->getResult();

        return $this->render('amasada/listar.html.twig', [
            'amasadas' => $amasadas
        ]);
    }

    /**
     * @Route("/amasadas/insertar", name="amasadas_insertar")
     */
    public function insertarAction()
    {
        /** @var EntityManager $em */
        $em=$this->getDoctrine()->getManager();

        $aceites = $em->createQueryBuilder()
            ->select('a')
            ->from('AppBundle:Aceite', 'a')
            ->getQuery()
            ->getResult();

        /** @var EntityManager $em */
        $em=$this->getDoctrine()->getManager();

        $depositos = $em->createQueryBuilder()
            ->select('d')
            ->from('AppBundle:Deposito', 'd')
            ->getQuery()
            ->getResult();

        $amasadas = [
            [0, "2017-03-28", $aceites[0], $depositos[0]],
            [0, "2017-03-28", $aceites[1], $depositos[1]],
            [0, "2017-03-28", $aceites[0], $depositos[1]]
        ];

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        foreach ($amasadas as $item) {
            $amasada = new Amasada();
            $em->persist($amasada);
            $amasada
                ->setFechaFabricacion($item[1])
                ->setAceite($item[2])
                ->setDeposito($item[3]);

            $em->flush();
        }
        $mensaje = 'Amasadas insertadas correctamente';

        return $this->render('amasada/operaciones.html.twig', [
            'mensaje' => $mensaje
        ]);
    }
}
