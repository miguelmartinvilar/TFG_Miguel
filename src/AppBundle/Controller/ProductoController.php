<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Lote;
use AppBundle\Entity\Producto;
use AppBundle\Form\Type\ProductoType;
use AppBundle\Service\TemporadaActual;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ProductoController extends Controller
{
    /**
     * @Route("/productos/listar", name="productos_listar")
     * @Security("is_granted('ROLE_ADMINISTRADOR') or is_granted('ROLE_EMPLEADO')")
     */
    public function indexAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $productos = $em->getRepository('AppBundle:Producto')
            ->findAll();

        return $this->render('producto/listar.html.twig', [
            'productos' => $productos
        ]);
    }

    /**
     * @Route("/productos/principal", name="productos_principal")
     * @Security("is_granted('ROLE_ENCARGADO')")
     */
    public function productosPrincipalAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        //Obtenemos los productos
        $productos = $em->getRepository('AppBundle:Producto')
            ->findAll();

        return $this->render('producto/principal.html.twig', [
            'productos' => $productos
        ]);
    }

    /**
     * @Route("/productos/aceite", name="productos_aceite")
     * @Security("is_granted('ROLE_ENCARGADO')")
     */
    public function productoAceiteAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        //Obtenemos las calidades de aceite
        $aceites = $em->getRepository('AppBundle:Aceite')
            ->findAll();

        return $this->render('producto/formAceite.html.twig', [
            'aceites' => $aceites
        ]);
    }

    /**
     * @Route("/producto/form/{producto}", name="producto_form")
     * @Security("is_granted('ROLE_ENCARGADO')")
     */
    public function formProductoAction(Request $request, Producto $producto)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        //Obtención temporada actual
        $temporadaActual = new TemporadaActual($em);
        $temporada = $temporadaActual->temporadaActualAction();

        //Obtenemos el aceite del producto
        $aceite = $producto->getLotes()[0]->getAceite();

        //Obtención cantidad del producto
        $cantidadProducto = $producto->getStock();

        $form = $this->createForm(ProductoType::class, $producto, [
            'temporada' => $temporada,
            'aceite' => $aceite
        ]);
        $form->handleRequest($request);

        //Si es válido
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                //Obtención de la cantidad en peso que se ha envasado
                $pesoEnvasar = $form['stock']->getData();

                //Pasamos ese peso a unidades de producto
                $cantidadEnvasar = (int)(($pesoEnvasar * $producto->getLotes()[0]->getAceite()->getDensidadKgLitro()) / ($producto->getEnvase()->getCapacidadLitros()));

                //Obtención del lote del que procede
                $lote = $form['lotes']->getData();

                //Suma cantidad al producto
                $em->persist($producto);
                $producto
                     ->setStock($cantidadProducto + $cantidadEnvasar);

                //Restamos la cantidad del stock del lote del que procede
                $em->persist($lote);
                $lote
                    ->setStock($lote->getStock() - $pesoEnvasar);

                $em->flush();
                $this->addFlash('estado', 'Cambios guardados con éxito');
                return $this->redirectToRoute('productos_principal');
            }
            catch(\Exception $e) {
                $this->addFlash('error', 'No se han podido guardar los cambios');
            }
        }

        return $this->render('producto/form.html.twig', [
            'formulario' => $form->createView()
        ]);
    }
}
